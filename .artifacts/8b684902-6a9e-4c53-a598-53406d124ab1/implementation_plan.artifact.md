# Trip Console Fixes: Timer and Map Route

This plan addresses the issues where the trip timer stays at `00:00:00` and the map route disappears after starting a trip.

## User Review Required

> [!IMPORTANT]
> I will be updating the `Trip` model and the `DriverConsole` Livewire component/view. These changes focus on data precision and timezone synchronization.
> I have already updated the migration file for coordinate precision, but if you haven't run `php artisan migrate:fresh` (which deletes data), I can provide a new migration to update the columns safely.

## Proposed Changes

### [Database & Models]

#### [MODIFY] [Trip.php](file:///C:/laragon/www/fleettrack/app/Models/Trip.php)
- Add float casts for `start_lat`, `start_lng`, `end_lat`, and `end_lng` to ensure high precision when handling coordinates in PHP.

---

### [Livewire Components]

#### [MODIFY] [DriverConsole.php](file:///C:/laragon/www/fleettrack/app/Livewire/Admin/DriverConsole/DriverConsole.php)
- Ensure coordinates are correctly persisted and passed.
- (Optional) Improve the `trip-started` event to avoid a full page reload if possible, but first we will fix the current reload-based logic.

#### [MODIFY] [driver-console.blade.php](file:///C:/laragon/www/fleettrack/resources/views/livewire/admin/driver-console/driver-console.blade.php)
- Update `startProTimer` to use ISO 8601 strings for consistent timezone handling between server and client.
- Fix the `dayjs` duration calculation to handle timezone offsets correctly.
- Ensure the map correctly initializes the route and markers from the `activeTrip` data after a page reload.
- Add error handling for cases where coordinates might be missing or invalid.

## Verification Plan

### Manual Verification
1. Start a trip and verify the timer begins counting up immediately.
2. Verify the map shows the start and end markers and the orange route line after the trip starts.
3. Check that the current location (blue pulse) is tracked relative to the route.
