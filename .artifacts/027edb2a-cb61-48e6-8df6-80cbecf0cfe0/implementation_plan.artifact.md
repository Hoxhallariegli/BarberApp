# Sistemi i Menaxhimit të Berberëve dhe Rezervimeve me Konfirmim SMS

Ky plan detajon implementimin e llogjikës për oraret e punës të berberëve, disponueshmërinë e tyre, dhe sistemin e rikujtesave me konfirmim përmes SMS.

## User Review Required

> [!IMPORTANT]
> Ky ndryshim do të shtojë tabela të reja në databazë për oraret e punës dhe mungesat. 
> Ju lutem konfirmoni nëse formati i orareve (p.sh. 09:00 - 18:00) është i mjaftueshëm apo keni nevojë për orare të ndara (p.sh. pushimi i drekës).

## Proposed Changes

### 1. Modelet e Reja dhe Databaza

#### [NEW] [BarberSchedule.php](file:///C:/laragon/www/Barbers/app/Models/BerberApp/BarberSchedule.php)
Do të mbajë oraret javore për çdo berber (E Hënë - E Diel).

#### [NEW] [BarberAbsence.php](file:///C:/laragon/www/Barbers/app/Models/BerberApp/BarberAbsence.php)
Do të mbajë pushimet, largimet e papritura ose ditët kur berberi nuk është në punë.

#### [MODIFY] [ba_bookings table](file:///C:/laragon/www/Barbers/database/migrations/2026_09_01_000000_update_bookings_table.php)
Shtimi i fushave:
- `token`: Një kod unik për lidhjen e konfirmimit.
- `status`: (pending, confirmed, cancelled, completed).

### 2. Llogjika e Disponueshmërisë (Availability Service)

#### [NEW] [AvailabilityService.php](file:///C:/laragon/www/Barbers/app/Services/BerberApp/AvailabilityService.php)
Një klasë që do të llogarisë oraret e lira duke kombinuar:
1. Oraret javore të berberit.
2. Mungesat/Pushimet e tij.
3. Rezervimet ekzistuese.

### 3. Sistemi i SMS dhe Konfirmimit

#### [MODIFY] [Booking.php](file:///C:/laragon/www/Barbers/app/Models/BerberApp/Booking.php)
Shtimi i një *Observer* ose *Event* që dërgon SMS sapo krijohet rezervimi.

#### [NEW] [BookingReminderJob.php](file:///C:/laragon/www/Barbers/app/Jobs/SendBookingReminder.php)
Një task i planifikuar që ekzekutohet çdo minutë për të gjetur rezervimet që fillojnë pas 30 minutash dhe dërgon SMS me linkun e konfirmimit.

#### [NEW] [ConfirmBookingController.php](file:///C:/laragon/www/Barbers/app/Http/Controllers/BerberApp/ConfirmBookingController.php)
Trajton klikimin e linkut nga klienti për të konfirmuar ose anuluar rezervimin.

## Verification Plan

### Automated Tests
- Testimi i `AvailabilityService` për të siguruar që nuk lejon rezervime të dyfishta ose jashtë orarit.
- Testimi i gjenerimit të token-it të rezervimit.

### Manual Verification
- Krijimi i një rezervimi nga Landing Page dhe kontrolli i logut të SMS.
- Simulimi i rikujtesës 30 minuta para.
- Testimi i faqes së konfirmimit përmes celularit.
