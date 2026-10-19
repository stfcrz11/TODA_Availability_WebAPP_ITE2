<?php
require_once 'config.php';
checkLogin();

// Current driver status if user is a driver
$driver = isDriver() ? getDriverByUser($_SESSION['user_id']) : null;
$currentStatus = $driver ? getDriverStatus($driver['driverid']) : '';

$row = qRow("SELECT COUNT(*) AS c FROM users");
$totalUsers = $row ? $row['c'] : 0;

$row = qRow("SELECT COUNT(*) AS c FROM triplogs WHERE DATE(trip_start) = CURDATE()");
$totalTrips = $row ? $row['c'] : 0;

$row = qRow("SELECT COUNT(*) AS c FROM driver d " . LATEST_STATUS_JOIN . " WHERE a.status = 'available'");
$activeDrivers = $row ? $row['c'] : 0;

$row = qRow("SELECT COUNT(*) AS c FROM triplogs WHERE trip_end IS NULL");
$ongoingTrips = $row ? $row['c'] : 0;

$activePage = 'dashboard';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - TODA System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
    <script src="assets/script.js" defer></script>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="content-header">
            <h2>Welcome to Dashboard, <?php echo h($_SESSION['username']); ?>!</h2>
            <p>Manage your TODA operations efficiently from this dashboard</p>
        </div>

        <div class="card">
            <h3><i class="fas fa-chart-bar"></i> Quick Stats</h3>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-taxi"></i></div>
                    <div class="stat-value" id="active-drivers-count"><?php echo (int)$activeDrivers; ?></div>
                    <div class="stat-label">Available Drivers</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-route"></i></div>
                    <div class="stat-value" id="total-trips"><?php echo (int)$totalTrips; ?></div>
                    <div class="stat-label">Today's Trips</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-value" id="total-users"><?php echo (int)$totalUsers; ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-spinner"></i></div>
                    <div class="stat-value" id="ongoing-trips"><?php echo (int)$ongoingTrips; ?></div>
                    <div class="stat-label">Ongoing Trips</div>
                </div>
            </div>
        </div>

        <?php if (isDriver()): ?>
        <div class="card">
            <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
            <?php if (!$driver): ?>
                <div class="alert alert-warning">
                    <i class="alert-icon fas fa-exclamation-triangle"></i>
                    <span>No driver profile is linked to your account. Ask an administrator to set one up.</span>
                </div>
            <?php else: ?>
            <div class="status-controls">
                <?php foreach ($STATUSES as $key => $info): ?>
                <div class="status-card <?php echo $key; ?> <?php echo $currentStatus === $key ? 'active' : ''; ?>" data-status="<?php echo $key; ?>" data-label="<?php echo $info['label']; ?>">
                    <div class="status-icon"></div>
                    <div class="status-title"><?php echo $info['label']; ?></div>
                    <div class="status-desc"><?php echo $info['desc']; ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div id="weather-container"></div>
    </main>
</body>
</html>
