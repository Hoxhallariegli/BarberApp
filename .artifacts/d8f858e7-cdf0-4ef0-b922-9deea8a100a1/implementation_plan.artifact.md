# Upgrade Mobile Scaffolder (MakeMobileModule) to Premium Pro

Improve the `MakeMobileModule` Laravel command to generate high-quality Flutter code with validation, advanced UI components, and automated builds.

## User Review Required

> [!IMPORTANT]
> The automation step will attempt to run `flutter clean` and `flutter build apk --release` if the `--build` flag is provided. Ensure Flutter SDK is in your PATH.

> [!NOTE]
> Translatable fields (like `name` in `Service`) will be handled as Maps in Flutter to ensure compatibility with the backend.

## Proposed Changes

### Backend & Scaffolder

#### [MODIFY] [MakeMobileModule.php](file:///C:/laragon/www/Barbers/app/Console/Commands/MakeMobileModule.php)
- Upgrade `controllerTemplate` to use `Model::rules()` for validation.
- Implement advanced translatable field detection.
- Update `listTemplate` (Flutter) to use "Pro" Cards with grid-based info display (excluding ID and created_at).
- Update `formTemplate` (Flutter) to use `TextFormField` with `validator`, icons, and `OutlineInputBorder`.
- Add relationship selectors that handle search/selection better.
- Add `--build` flag support to automate Flutter cleanup and build.

## Verification Plan

### Automated Tests
- Run `php artisan make:mobile-module Barber --force` and verify `BarberListPage` and `BarberFormScreen` code.
- Run `php artisan make:mobile-module Booking --force` and verify relationship dropdowns for Barber, Customer, and Service.
- Run `php artisan make:mobile-module Service --force` and verify translatable name handling.

### Manual Verification
- The user will check the generated Flutter screens in the app.
- Verify that forms don't submit if fields are empty (validation).
- Verify that index cards show all relevant data fields.
- (If requested) Run the build command to see if APK is generated without errors.
