# Bug Fixes

Summary of the problems found in the TODA Availability Web App and how each was fixed.

## Blocking (the app could not run)

| # | Problem | Fix |
|---|---------|-----|
| 1 | Every page used the `mysql_*` functions, which were **removed in PHP 7**. On XAMPP (PHP 8.2) every page crashed with a fatal error. | Rewrote the database layer with `mysqli` and prepared statements (`q()` / `qRow()` helpers in `config.php`). |
| 2 | Code read `driver.status`, but **the `driver` table has no `status` column**. The status lives in the `availability` table. The driver list, dashboard count and status page all failed. | A driver's current status is now the latest `availability` row (`getDriverStatus()`, `LATEST_STATUS_JOIN`). |
| 3 | **Session keys didn't match.** Login set `user_id` and `role`, but other pages read `userid` and `userrole`. Drivers never saw their status, and `ajax/update_status.php` always exited. | Every file now uses `$_SESSION['user_id']` and `$_SESSION['role']`. |
| 4 | **Status values didn't match.** The database enum is `available / ontrip / offline`, but the UI sent `available / busy / unavailable`. | The UI, JS and CSS use the database values, with the labels Available / On Trip / Offline. |
| 5 | **Broken URLs.** `index.php` fetched `ajax/fetch_driver.php` (missing "s"). `script.js` fetched `ajax/fetch_drivers` and `ajax/update_status` (no `.php`). `driver_status.php` posted to `update_driver_status.php`, which doesn't exist. | All of them point to the real endpoints. |
| 6 | The SQL file was named `database,sql` (comma), so tools didn't recognise it. | Renamed to `assets/database.sql`. |

## Functional bugs

- **Trips were never logged.** Changing status didn't touch `triplogs` or `driver_status_log`. Now, going "On Trip" opens a trip, the next status change closes it, and every change is logged (`setDriverStatus()`, in a transaction).
- **New driver accounts were useless.** Adding a driver user created no `driver` row, so they couldn't set a status. The add-user form now also collects the name, contact, address and tricycle, and creates the driver profile.
- **Deleting a user who had status history failed** (`driver_status_log` and `printlogs` have no `ON DELETE CASCADE`). Those rows are now removed first, in a transaction. Admins also can't delete their own account.
- **Trip Logs showed the trip ID as "Driver ID".** It now shows the real driver ID.
- **Export, Previous and Next did nothing on Trip Logs.** Export now downloads a real CSV, and pagination works (15 per page).
- **The weather showed Santiago, Chile.** "Santiago" resolves to Chile; the default is now Santiago City, Isabela, by coordinates. The weather also loaded twice on the dashboard.
- **The password eye toggle fired twice** (inline `onclick` plus the global handler) and added a duplicate button, so it never worked on User Management.
- **Every sidebar link was highlighted** because `href.includes('')` is always true.
- **The user count was attached to the wrong card** on User Management.
- **Admins could open the driver status page.** It now redirects non-drivers.
- **The passwords hashed when adding a user didn't match the ones checked at login** (`htmlspecialchars` + escaping were applied before hashing).
- The **"Avg Rating 4.8"** stat was hard-coded. It's replaced with a real "Ongoing Trips" count.
- An invalid mid-file `@import` in `style.css` was removed.

## Security

- **SQL injection:** all queries use prepared statements.
- **XSS:** usernames, driver names and API data are escaped (`h()` in PHP, `escapeHtml()` in JS). Driver names are no longer put inside `onclick="…'name'…"`.
- **Passwords:** new passwords use `password_hash()`. Legacy SHA-256 hashes still log in and are upgraded automatically.
- **Session fixation:** `session_regenerate_id()` runs on login.
- **CSRF:** add/delete user requires a token. Delete changed from a GET link to a POST form.
- **API key:** the OpenWeatherMap key stays server-side in `config.php`. The static demo uses Open-Meteo, which needs no key.

## How it was tested

The fixed PHP app ran on PHP 8.2 against a temporary copy of the database imported from `assets/database.sql`. These all passed: login (wrong and correct password, legacy-hash upgrade), dashboard stats, adding a driver, deleting a user with history, delete-self and bad-CSRF rejection, driver status changes (trip opened and closed), role checks on the endpoints, CSV export, and the weather proxy.
