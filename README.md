# TODA Management System
## Tricycle Operators and Drivers Association Management System

A web-based responsive system for managing tricycle drivers, trips, and passenger information.

**Live demo (static, no server needed):** see [GitHub Pages demo](#github-pages-demo) below.

### Features
- User Authentication (Admin/Driver)
- Driver Status Management (Available/On Trip/Offline)
- Trip Logging System (trips open automatically when a driver goes "On Trip")
- Passenger View (Public)
- Dashboard with Statistics
- Weather API Integration
- Responsive Design
- CRUD Operations
- MySQL Database

### Requirements
- PHP 7.0 or higher (tested on PHP 8.2 / XAMPP) with the `mysqli` extension
- MySQL 5.5+ or MariaDB
- Apache Web Server (XAMPP works out of the box)
- Modern Web Browser

### Installation

1. **Copy Files**
   Put this folder in `C:\xampp\htdocs\` (or your web root).

2. **Database Setup**
   Start MySQL in XAMPP, then import `assets/database.sql` in phpMyAdmin
   (or run `mysql -u root < assets/database.sql`). It creates the `toda_system` database.

3. **Configure**
   Edit the `DB_*` constants in `config.php` if your MySQL user/password differ.

4. **Open**
   Go to `http://localhost/TODA_Availability_WebAPP_ITE2-main/`.

Passwords stored as old SHA-256 hashes still work; they are upgraded to `password_hash()` automatically on first login.

### Project Structure
```
config.php              DB connection + shared helpers (statuses, auth, CSRF)
includes/sidebar.php    Sidebar used by all logged-in pages
index.php               Public passenger view
login.php / logout.php
dashboard.php           Stats + driver quick actions + weather
driver_status.php       Driver: change availability
trip_logs.php           Admin: trip history, CSV export, pagination
user_management.php     Admin: add/delete users and driver profiles
ajax/fetch_drivers.php  JSON list of available drivers
ajax/update_status.php  JSON endpoint for drivers to change status
api/weather.php         OpenWeatherMap proxy
assets/                 style.css, script.js, database.sql
docs/                   Static GitHub Pages demo (see below)
```

### GitHub Pages Demo

GitHub Pages can't run PHP or MySQL, so `docs/` contains a static copy of the app that
works entirely in the browser:

- Same pages, styles and `script.js` as the PHP version.
- `docs/assets/demo.js` replaces the backend: it keeps the tables in `localStorage`
  and answers the same `ajax/*.php` URLs, so status changes, trip logs and user
  management all work. Each visitor gets their own copy of the data
  ("Reset data" button at the bottom of every page).
- Weather comes from [Open-Meteo](https://open-meteo.com/) (no API key needed).

Demo logins: `admin` / `admin123`, `juan` / `driver123`, `maria` / `driver123`.

**To publish:**
1. Push this repository to GitHub.
2. Open **Settings → Pages**.
3. Under *Build and deployment*, choose **Deploy from a branch**, branch `main`, folder **`/docs`**, then **Save**.
4. After a minute the site is live at `https://<your-username>.github.io/<repo-name>/`.

You can also preview it locally by opening `docs/index.html` in a browser.

See [BUGFIXES.md](BUGFIXES.md) for the list of bugs that were fixed.
