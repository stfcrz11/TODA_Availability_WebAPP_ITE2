<?php
// config.php - Database connection and shared helpers
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'toda_system');

// OpenWeatherMap key used by api/weather.php
define('WEATHER_API_KEY', '9a3bd740c75e34eefbc779c69d997096');

mysqli_report(MYSQLI_REPORT_OFF);
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$conn) {
    die('DB connection error: ' . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8');

// Driver statuses (must match the enum in the availability table)
$STATUSES = array(
    'available' => array('label' => 'Available', 'badge' => 'success', 'icon' => 'user-check', 'desc' => 'Ready to accept passengers'),
    'ontrip'    => array('label' => 'On Trip',   'badge' => 'warning', 'icon' => 'user-clock', 'desc' => 'Currently on a trip'),
    'offline'   => array('label' => 'Offline',   'badge' => 'danger',  'icon' => 'user-slash', 'desc' => 'Not taking passengers'),
);

// Run a prepared query. Returns a mysqli_result for SELECTs, true/false otherwise.
function q($sql, $params = array()) {
    global $conn;
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        return false;
    }
    if ($params) {
        $types = '';
        foreach ($params as $p) {
            $types .= is_int($p) ? 'i' : 's';
        }
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    if (!mysqli_stmt_execute($stmt)) {
        return false;
    }
    $result = mysqli_stmt_get_result($stmt);
    return $result === false ? true : $result;
}

// Fetch a single row (or null)
function qRow($sql, $params = array()) {
    $res = q($sql, $params);
    return ($res instanceof mysqli_result) ? mysqli_fetch_assoc($res) : null;
}

// Escape output for HTML
function h($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

// CSRF protection for forms
function csrfToken() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function checkCsrf() {
    return isset($_POST['csrf']) && hash_equals(csrfToken(), $_POST['csrf']);
}

// Login check
function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isDriver() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'driver';
}

function requireAdmin() {
    checkLogin();
    if (!isAdmin()) {
        http_response_code(403);
        die("Access denied. Admins only.");
    }
}

// Driver profile linked to a user account
function getDriverByUser($userid) {
    return qRow("SELECT driverid, fullname, tricycleid FROM driver WHERE userid=? ORDER BY driverid LIMIT 1", array((int)$userid));
}

// A driver's current status is the latest row in the availability table
function getDriverStatus($driverid) {
    $row = qRow("SELECT status FROM availability WHERE driverid=? ORDER BY availabilityid DESC LIMIT 1", array((int)$driverid));
    return $row ? $row['status'] : 'offline';
}

// SQL fragment: latest status per driver (alias "a")
define('LATEST_STATUS_JOIN', "
    INNER JOIN availability a ON a.availabilityid = (
        SELECT MAX(a2.availabilityid) FROM availability a2 WHERE a2.driverid = d.driverid
    )");

// Change a driver's status, logging it and opening/closing trips
function setDriverStatus($driver, $status) {
    global $conn;
    $driverid = (int)$driver['driverid'];
    $current = getDriverStatus($driverid);
    if ($current === $status) {
        return true;
    }

    mysqli_begin_transaction($conn);
    $ok = q("INSERT INTO availability (driverid, status, last_updated) VALUES (?, ?, NOW())", array($driverid, $status))
       && q("INSERT INTO driver_status_log (driverid, status) VALUES (?, ?)", array($driverid, $status));

    if ($ok && $current === 'ontrip') {
        $ok = q("UPDATE triplogs SET trip_end=NOW() WHERE driverid=? AND trip_end IS NULL", array($driverid));
    }
    if ($ok && $status === 'ontrip') {
        $ok = q("INSERT INTO triplogs (driverid, tricycleid, trip_start) VALUES (?, ?, NOW())", array($driverid, (int)$driver['tricycleid']));
    }

    if ($ok) {
        mysqli_commit($conn);
    } else {
        mysqli_rollback($conn);
    }
    return $ok;
}
