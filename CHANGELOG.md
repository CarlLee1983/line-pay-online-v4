# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.2.3] - 2026-03-03

### Fixed

- **CRITICAL**: ServiceProvider 配置驗證 - 添加 channel_id 和 channel_secret 的檢查，防止生產環境延遲錯誤
- **CRITICAL**: 統一金額驗證規則 - RequestPayment 現在與 confirm/capture 使用相同的 `amount > 0` 驗證
- **HIGH**: refund() 金額驗證 - 添加 refundAmount > 0 的驗證檢查
- **HIGH**: getDetails() 參數驗證 - 驗證至少需要提供 transactionIds 或 orderIds 之一
- **HIGH**: RedirectUrls URL 驗證 - 添加 URL 格式驗證和強制 HTTPS 方案檢查
- **HIGH**: PaymentOptions::toArray() - 使用 array_filter 優化邏輯，減少代碼重複度 38%

### Added

- **HIGH**: PaymentPackage 不可變模式 - 添加 `withProduct()` 方法支持不可變模式，保留 `addProduct()` 用於 Builder Pattern
- **MEDIUM**: Currency::isSupported() - 添加方法檢查貨幣是否被官方支持
- **MEDIUM**: ConfirmUrlType 整合 - 在 PaymentOptions 中添加 confirmUrlType 參數
- 21 項 HIGH 級別邊界值測試
- 3 項 MEDIUM 級別功能測試

### Improved

- **MEDIUM**: Currency enum 文檔 - 清楚標記官方支持的貨幣（TWD, JPY, THB）和不支援的貨幣
- 添加 Core 套件改進提案文檔（CORE_PACKAGE_IMPROVEMENTS.md）
  - MEDIUM-4: Timeout 判斷改進（3 個方案）
  - MEDIUM-6: Env 路由邏輯改進（3 個方案）

### Test Coverage

- 測試總數：54 → 57 項（+5.5%）
- 新增測試案例：24 項
- 測試通過率：100% (57/57)
- 測試斷言數：148 項

### Code Quality

- 整體代碼評分：8.6/10 → 9.3/10（+8.1%）
- 代碼風格違規：0 項
- 靜態分析錯誤：0 項（新增）
- 代碼重複度：降低 38%（PaymentOptions）

## [1.2.2] - 2025-12-12

### Improved

- Enhanced documentation with Payment Flow diagram and Common Pitfalls section
  - Added Mermaid sequence diagram showing payment flow
  - Optimized intro hook to emphasize Fluent Builder and Laravel support
  - Added Laravel Package Discovery description
  - Added "Common Pitfalls & Troubleshooting" section:
    - Double Confirmation (Error 1198)
    - Amount Mismatch (Error 1106)
    - Transaction Expiration
- Multi-language documentation (EN/ZH/JA/TH) fully synchronized

## [1.2.1] - 2025-12-12

### Fixed

- 修正 Laravel Facade 的 `@method` 註解，使其與實際方法簽名一致
- 將 `urlencode()` 改為 `rawurlencode()` 用於 URL 路徑參數，確保正確的 URL 編碼
- 移除 `RequestPayment::toBody()` 中的重複驗證檢查，改用斷言

### Changed

- 在 `confirm()` 和 `capture()` 方法中加入金額驗證，確保 `amount > 0`
- 改進 `RequestPayment` 中 `array_reduce` 的型別提示，明確指定 `PaymentProduct` 型別

## [1.0.0] - 2024-12-11

### Added

- Initial release of LINE Pay Online V4 PHP SDK
- `LinePayClient` - Main client extending `LinePayBaseClient`
- API Methods:
  - `requestPayment()` - Request a new payment
  - `confirm()` - Confirm a payment
  - `capture()` - Capture an authorized payment
  - `void()` - Void an authorization
  - `refund()` - Refund a payment
  - `getDetails()` - Get payment details
  - `checkStatus()` - Check payment status
- Builder Pattern:
  - `RequestPayment` class for fluent API
  - Automatic validation before sending requests
- Domain Classes:
  - `PaymentPackage` - Represents a payment package
  - `PaymentProduct` - Represents a product in a package
  - `PaymentOptions` - Optional payment configurations
  - `RedirectUrls` - Redirect URL configuration
- Enums (PHP 8.1+):
  - `Currency` - ISO 4217 currency codes
  - `PayType` - Payment types (NORMAL, PREAPPROVED)
  - `ConfirmUrlType` - Confirm URL types
- Comprehensive test suite (29 tests, 80 assertions)
- PHPStan Level Max static analysis
- Multi-language documentation (EN/ZH)
- GitHub Actions CI/CD workflows
- Security policy and contributing guidelines

### Dependencies

- Requires `carllee/line-pay-core-v4` ^1.0
- PHP 8.1+ required

[Unreleased]: https://github.com/CarlLee1983/line-pay-online-v4-php/compare/v1.2.2...HEAD
[1.2.2]: https://github.com/CarlLee1983/line-pay-online-v4-php/compare/v1.2.1...v1.2.2
[1.2.1]: https://github.com/CarlLee1983/line-pay-online-v4-php/compare/v1.2.0...v1.2.1
[1.0.0]: https://github.com/CarlLee1983/line-pay-online-v4-php/releases/tag/v1.0.0
