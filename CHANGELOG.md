# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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

[Unreleased]: https://github.com/CarlLee1983/line-pay-online-v4-php/compare/v1.2.1...HEAD
[1.2.1]: https://github.com/CarlLee1983/line-pay-online-v4-php/compare/v1.2.0...v1.2.1
[1.0.0]: https://github.com/CarlLee1983/line-pay-online-v4-php/releases/tag/v1.0.0
