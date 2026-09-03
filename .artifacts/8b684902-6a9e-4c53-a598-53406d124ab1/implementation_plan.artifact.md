# Plani i Implementimit: Mobile Pro Dashboard for Barbers

Ky projekt synon transformimin e APK-së ekzistuese "Station Gateway" në një aplikacion menaxhimi "Pro" që lejon kontrollin e plotë të biznesit nga telefoni (Dashboard, Barbers, Bookings, etj.).

## User Review Required

> [!IMPORTANT]
> Aplikacioni do të kërkojë Login me Email dhe Password (si në web).
> Do të përdorim Laravel Sanctum për sigurinë e API-ve.
> Gateway SMS do të mbetet funksional në background, por do të jetë i lidhur me llogarinë e përdoruesit.

## Proposed Changes

### 1. Laravel Backend (API)

#### [MODIFY] [api.php](file:///C:/laragon/www/Barbers/routes/api.php)
Shtimi i rrugëve të reja për:
- `POST /login`: Autentifikimi i përdoruesit.
- `GET /mobile/dashboard`: Përmbledhje e të dhënave (statistikat).
- `GET /mobile/barbers`: Lista e berberëve.
- `GET /mobile/bookings`: Lista e rezervimeve.
- `GET /mobile/customers`: Lista e klientëve.
- `GET /mobile/services`: Lista e shërbimeve.
- `GET /mobile/payments`: Lista e pagesave.
- `GET /mobile/reminders`: Kujtesat.
- `GET /mobile/sms-settings`: Cilësimet e SMS.
- `GET /mobile/sms-templates`: Modelet e SMS.

#### [NEW] [MobileDashboardController.php](file:///C:/laragon/www/Barbers/app/Http/Controllers/Api/Mobile/MobileDashboardController.php)
Menaxhimi i kërkesave nga mobile.

### 2. Flutter Mobile App (Mobile Gateway)

#### [MODIFY] [pubspec.yaml](file:///C:/laragon/www/Barbers/mobile-gateway/pubspec.yaml)
Shtimi i paketave të nevojshme:
- `http`: Për kërkesat API.
- `shared_preferences`: Për ruajtjen e token-it.
- `flutter_secure_storage`: Për siguri më të lartë të token-it (opsionale).
- `intl`: Për formatimin e datave.

#### [MODIFY] [lib/main.dart](file:///C:/laragon/www/Barbers/mobile-gateway/lib/main.dart)
Refaktori i `main.dart` për të përfshirë:
- `LoginScreen`: Faqja e hyrjes.
- `MainScreen`: Me `BottomNavigationBar` dhe `Drawer`.
- `DashboardPage`: Me Grid View (taps).
- `ListPage`: Një komponent gjenerik për listat (Barbers, Bookings, etj.).

## Verification Plan

### Automated Tests
- Testimi i API endpoints me Postman/Insomnia.
- Verifikimi i Login dhe ruajtjes së Token-it në Flutter.

### Manual Verification
- Navigimi nëpër menu dhe kontrolli i të dhënave.
- Testimi i dërgimit të SMS në background ndërkohë që përdoret dashboard.
