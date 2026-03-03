# LINE Pay Core V4 套件改進提案

本文件記錄針對 `carllee/line-pay-core-v4` 套件發現的問題和改進建議。

## 概述

在審查 LINE Pay Online V4 SDK 時，發現核心套件中的 2 項問題：

| # | 問題 | 嚴重性 | 影響 | 優先級 |
|---|---|---|---|---|
| MEDIUM-4 | Timeout 判斷依賴字符串匹配 | MEDIUM | 脆弱、易損壞 | 高 |
| MEDIUM-6 | Env 路由邏輯使用 default | MEDIUM | 靜默錯誤、難除蟲 | 高 |

---

## MEDIUM-4: LinePayBaseClient Timeout 判斷過於脆弱

### 問題描述

**檔案**: `vendor/carllee/line-pay-core-v4/src/LinePayBaseClient.php:183-186`

**現狀代碼**:
```php
} catch (ConnectException $e) {
    if (str_contains($e->getMessage(), 'timed out') || str_contains($e->getMessage(), 'timeout')) {
        throw new LinePayTimeoutError($this->timeout, $this->baseUrl . $url);
    }
    throw $e;
}
```

### 問題分析

#### 1. **脆弱性** 🔴
- 依賴 Guzzle HTTP 客戶端的內部錯誤訊息文字
- 如果 Guzzle 版本更新改變訊息格式，判斷就會失效
- 例如：Guzzle 版本升級可能改為 "Connection timeout" 或其他表述

#### 2. **完整性不足**
- 只檢查兩個字符串變體 (`timed out` 和 `timeout`)
- 可能遺漏其他 timeout 相關的訊息格式
- 部分邊界情況可能被誤分類

#### 3. **維護困難**
- 當 Guzzle 更新時，無法及時發現 timeout 判斷失效
- 需要手動維護字符串列表
- 沒有測試覆蓋（難以驗證正確性）

#### 4. **實例情況**
```
Guzzle 版本升級：
- 當前（可能）: "cURL error 28: Operation timed out"
- 升級後（可能）: "Connection timeout after 20 seconds"
- 判斷結果：✗ 無法識別 → 拋出 ConnectException 而非 LinePayTimeoutError
```

### 改進方案

#### 方案 A: 使用 Guzzle HandlerContext（推薦）✅

```php
} catch (ConnectException $e) {
    // 檢查 Guzzle 的 handler context 以確定是否為 timeout
    $context = $e->getHandlerContext();

    if (isset($context['errno']) && $context['errno'] == 28) {
        // cURL 錯誤代碼 28 = CURLE_OPERATION_TIMEDOUT
        throw new LinePayTimeoutError($this->timeout, $this->baseUrl . $url);
    }

    // 備用：檢查 error_type
    if (isset($context['error_type']) && str_contains($context['error_type'], 'timeout')) {
        throw new LinePayTimeoutError($this->timeout, $this->baseUrl . $url);
    }

    throw $e;
}
```

**優點**:
- ✅ 直接檢查 HTTP 層的 error code 而非訊息文字
- ✅ 與 Guzzle 版本升級無關
- ✅ 更可靠、更標準化
- ✅ cURL errno 28 是官方標準

**缺點**:
- 需要驗證 Guzzle 版本中 HandlerContext 的可用性

#### 方案 B: 更全面的字符串匹配

```php
} catch (ConnectException $e) {
    $message = strtolower($e->getMessage());

    // 檢查多個可能的 timeout 相關訊息
    $timeoutPatterns = [
        'timed out',
        'timeout',
        'operation timed out',
        'connection timeout',
        'read timed out',
        'write timed out',
        'errno 28',  // cURL timeout errno
    ];

    foreach ($timeoutPatterns as $pattern) {
        if (str_contains($message, $pattern)) {
            throw new LinePayTimeoutError($this->timeout, $this->baseUrl . $url);
        }
    }

    throw $e;
}
```

**優點**:
- ✅ 涵蓋更多可能的訊息格式
- ✅ 易於維護和擴展
- ✅ 向後兼容

**缺點**:
- ⚠️ 仍然依賴訊息文字
- ⚠️ 可能有誤判風險

### 推薦實施

**優先級**:
1. **首選**: 方案 A（HandlerContext） - 最可靠
2. **備選**: 方案 B + 方案 A（雙重檢查） - 確保向後兼容

**實施步驟**:
1. 在 `LinePayBaseClient` 中添加 `isCurlTimeoutError()` 私有方法
2. 支持多種檢查方式（HandlerContext 優先，字符串匹配備用）
3. 添加單元測試
4. 更新 CHANGELOG

---

## MEDIUM-6: Env::getBaseUrl() 路由邏輯過於寬鬆

### 問題描述

**檔案**: `vendor/carllee/line-pay-core-v4/src/Config/Env.php:39-42`

**現狀代碼**:
```php
return match ($env) {
    'production' => self::BASE_URL_PRODUCTION,
    default => self::BASE_URL_SANDBOX,
};
```

### 問題分析

#### 1. **靜默錯誤** 🔴
- 拼錯的環境名稱（例如：`'prod'`, `'live'`, `'staging'`）被默認導向 sandbox
- 開發人員無法察覺配置錯誤
- 可能導致意外地對 sandbox 進行測試

#### 2. **調試困難**
- 無清晰的錯誤訊息告知開發人員環境名稱無效
- 除非仔細檢查 API 響應，否則難以發現問題
- 可能在生產環境前期發現不了配置錯誤

#### 3. **真實場景**
```
scenario: 環境變數設定錯誤
- 預期: env: 'production'
- 實際: env: 'prod'  // 拼錯
- 結果: 靜默使用 sandbox URL
- 後果: 測試數據被發送到 sandbox，生產交易延遲

scenario: 環境變數遺漏
- 預期: env: (來自環境變數)
- 實際: env: null 或空字符串
- 結果: 默認使用 sandbox
- 後果: 不明確的行為，難以追蹤
```

#### 4. **潛在風險**
```php
// 這些都會被靜默導向 sandbox
$env = getenv('APP_ENV'); // 'development' 或未設定
$baseUrl = Env::getBaseUrl($env); // ✗ 預期 'production' 但得到 sandbox

// 導致：
// - API 調用指向錯誤的環境
// - 測試數據污染或生產數據延遲
// - 難以追蹤問題來源
```

### 改進方案

#### 方案 A: 明確匹配 + 異常提示（推薦）✅

```php
/**
 * Get the base URL for the specified environment.
 *
 * @param string $env The environment name ('sandbox' or 'production')
 *
 * @return string The base URL for the environment
 *
 * @throws \InvalidArgumentException If the environment name is invalid
 */
public static function getBaseUrl(string $env): string
{
    return match (strtolower(trim($env))) {
        'sandbox' => self::BASE_URL_SANDBOX,
        'production', 'prod' => self::BASE_URL_PRODUCTION,
        default => throw new \InvalidArgumentException(
            "Invalid environment '{$env}'. Supported values: 'sandbox', 'production'"
        ),
    };
}
```

**優點**:
- ✅ 明確的錯誤訊息
- ✅ 支持常見的別名（如 'prod'）
- ✅ 防止靜默失敗
- ✅ 開發人員立即發現配置錯誤

**缺點**:
- ⚠️ 破壞現有代碼（如果有依賴 default 行為）
- 需要在升級指南中說明

#### 方案 B: 寬鬆模式 + 日誌警告

```php
/**
 * Get the base URL for the specified environment.
 *
 * @param string $env The environment name ('sandbox' or 'production')
 *
 * @return string The base URL for the environment
 */
public static function getBaseUrl(string $env): string
{
    $env = strtolower(trim($env));

    $url = match ($env) {
        'production', 'prod' => self::BASE_URL_PRODUCTION,
        default => self::BASE_URL_SANDBOX,
    };

    // 如果環境不匹配任何已知值，記錄警告
    if (!in_array($env, ['sandbox', 'production', 'prod'], true)) {
        trigger_error(
            "Unknown environment '{$env}', defaulting to sandbox. "
            . "Supported values: 'sandbox', 'production'",
            E_USER_WARNING
        );
    }

    return $url;
}
```

**優點**:
- ✅ 向後兼容
- ✅ 提供警告但不中斷
- ✅ 清晰的診斷訊息

**缺點**:
- ⚠️ 仍然依賴開發人員檢查警告
- ⚠️ 不強制糾正配置錯誤

#### 方案 C: 明確列表 + 常量

```php
private const VALID_ENVIRONMENTS = [
    'sandbox' => self::BASE_URL_SANDBOX,
    'production' => self::BASE_URL_PRODUCTION,
    'prod' => self::BASE_URL_PRODUCTION, // 別名
];

/**
 * Get the base URL for the specified environment.
 *
 * @param string $env The environment name
 *
 * @return string The base URL for the environment
 *
 * @throws \InvalidArgumentException If the environment name is not supported
 */
public static function getBaseUrl(string $env): string
{
    $env = strtolower(trim($env));

    if (!isset(self::VALID_ENVIRONMENTS[$env])) {
        throw new \InvalidArgumentException(
            sprintf(
                "Invalid environment '%s'. Supported values: %s",
                $env,
                implode(', ', array_keys(self::VALID_ENVIRONMENTS))
            )
        );
    }

    return self::VALID_ENVIRONMENTS[$env];
}
```

**優點**:
- ✅ 明確的錯誤訊息
- ✅ 易於擴展新環境
- ✅ 單一來源的環境配置

**缺點**:
- ⚠️ 多出常量定義

### 推薦實施

**優先級**:
1. **首選**: 方案 A（異常提示） - 最安全、最直接
2. **次選**: 方案 C（常量列表） - 易於維護
3. **備選**: 方案 B（日誌警告） - 如果需要向後兼容

**實施步驟**:
1. 更新 `Env::getBaseUrl()` 方法
2. 添加單元測試覆蓋無效環境名稱
3. 更新文檔說明支持的環境值
4. 在 CHANGELOG 中標記破壞性變更（如適用）
5. 更新升級指南

---

## 實施時間表

### Phase 1: 準備 (即刻)
- [ ] 在 `carllee/line-pay-core-v4` 倉庫中建立 issues
- [ ] 獲取維護人員反饋
- [ ] 確定實施方案優先級

### Phase 2: 開發 (1-2 週)
- [ ] 實施 MEDIUM-6（更優先，改動較小）
- [ ] 為兩個改進添加完整的單元測試
- [ ] 更新文檔和 CHANGELOG

### Phase 3: 發佈 (1-2 週)
- [ ] 發佈 v1.1.0（包含 MEDIUM-6）
- [ ] 發佈 v1.2.0（包含 MEDIUM-4）
- [ ] 更新上游套件 (line-pay-online-v4) 依賴版本

### Phase 4: 驗證 (即刻)
- [ ] 測試上游套件在新 core 版本上的相容性
- [ ] 運行完整的測試套件
- [ ] 驗證問題已解決

---

## 測試案例範例

### MEDIUM-4: Timeout 判斷

```php
public function testConnectExceptionTimeoutIsDetected(): void
{
    // 測試各種 timeout 訊息格式
    $timeoutMessages = [
        'cURL error 28: Operation timed out',
        'Connection timeout after 20 seconds',
        'Read timed out',
    ];

    foreach ($timeoutMessages as $message) {
        $exception = new ConnectException($message);

        try {
            // 模擬 sendRequest() 處理
            $this->handleConnectException($exception);
        } catch (LinePayTimeoutError $e) {
            $this->assertStringContainsString('timeout', $e->getMessage());
        }
    }
}

public function testNonTimeoutConnectExceptionIsNotConverted(): void
{
    $exception = new ConnectException('DNS lookup failed');

    try {
        $this->handleConnectException($exception);
    } catch (ConnectException $e) {
        $this->assertNotInstanceOf(LinePayTimeoutError::class, $e);
    }
}
```

### MEDIUM-6: Env 路由

```php
public function testValidEnvironmentNamesReturnCorrectUrls(): void
{
    $this->assertEquals(
        Env::BASE_URL_SANDBOX,
        Env::getBaseUrl('sandbox')
    );

    $this->assertEquals(
        Env::BASE_URL_PRODUCTION,
        Env::getBaseUrl('production')
    );

    $this->assertEquals(
        Env::BASE_URL_PRODUCTION,
        Env::getBaseUrl('prod')  // 別名
    );
}

public function testInvalidEnvironmentNameThrowsException(): void
{
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid environment');

    Env::getBaseUrl('staging');
}

public function testEnvironmentNameIsCaseInsensitive(): void
{
    $this->assertEquals(
        Env::getBaseUrl('PRODUCTION'),
        Env::getBaseUrl('production')
    );
}
```

---

## 附錄: 聯繫信息

### 相關倉庫
- **Core 套件**: https://github.com/CarlLee1983/line-pay-core-v4
- **Online SDK**: https://github.com/CarlLee1983/line-pay-online-v4-php

### 提交者
- **審查人**: 通過 LINE Pay Online V4 SDK 代碼審查
- **日期**: 2026-03-03
- **相關提案**: 該文件

### 參考文件
- LINE Pay API 文檔: https://pay.line.me/developers
- Guzzle 文檔: https://docs.guzzlephp.org/
- cURL 錯誤碼: https://curl.se/libcurl/c/libcurl-errors.html

---

**文件版本**: 1.0
**最後更新**: 2026-03-03
**狀態**: 提議中
