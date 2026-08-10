<?php
require_once 'config.php';
checkLogin();

// Make sure we have a proper session variable
$userid = isset($_SESSION['userid']) ? intval($_SESSION['userid']) : 0;

// Get current driver status if user is driver
$currentStatus = '';
if(isset($_SESSION['role']) && $_SESSION['role'] == 'driver' && $userid > 0) {
    $result = mysql_query("SELECT status FROM driver WHERE userid=$userid");
    if($result && $row = mysql_fetch_assoc($result)) {
        $currentStatus = $row['status'];
    }
}

// Total Users
$userCountRes = mysql_query("SELECT COUNT(*) AS total_users FROM users");
$totalUsers = ($userCountRes && $row = mysql_fetch_assoc($userCountRes)) ? $row['total_users'] : 0;

// Total trips today
$today = date('Y-m-d');
$tripCountRes = mysql_query("SELECT COUNT(*) AS total_trips FROM triplogs WHERE DATE(trip_start) = '$today'");
$totalTrips = ($tripCountRes && $row = mysql_fetch_assoc($tripCountRes)) ? $row['total_trips'] : 0;

// Active drivers (status = available)
$activeDriversRes = mysql_query("SELECT COUNT(*) AS active_drivers FROM driver WHERE status = 'available'");
$activeDrivers = ($activeDriversRes && $row = mysql_fetch_assoc($activeDriversRes)) ? $row['active_drivers'] : 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - TODA System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
    <script src="assets/script.js" defer></script>
</head>
<body>
    <!-- Sidebar Navigation -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <h1>
                <i class="fas fa-taxi"></i>
                <span>TODA System</span>
            </h1>
            <div class="user-info">
                <span><i class="fas fa-user"></i> <?php echo $_SESSION['username']; ?></span>
                <span class="user-role"><?php echo $_SESSION['role']; ?></span>
            </div>
            <?php if($currentStatus): ?>
            <div style="margin-top: 10px;">
                <span style="font-size: 12px; color: #64748b;">Status: </span>
                <span id="current-status" class="badge badge-<?php 
                    echo $currentStatus == 'available' ? 'success' : 
                         ($currentStatus == 'busy' ? 'warning' : 'danger');
                ?>"><?php echo $currentStatus; ?></span>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="sidebar-nav">
            <a href="dashboard.php" class="nav-item active">
                <i class="fas fa-chart-line"></i>
                <span>Dashboard</span>
            </a>
            
            <?php if(isAdmin()): ?>
                <div class="nav-divider"></div>
                <div style="padding: 0 20px; margin: 20px 0;">
                    <span style="font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 1px;">Admin</span>
                </div>
                <a href="user_management.php" class="nav-item">
                    <i class="fas fa-user-tie"></i>
                    <span>User Management</span>
                </a>
                <a href="trip_logs.php" class="nav-item">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Trip Logs</span>
                </a>
            <?php elseif($_SESSION['role'] == 'driver'): ?>
                <div class="nav-divider"></div>
                <a href="driver_status.php" class="nav-item">
                    <i class="fas fa-bolt"></i>
                    <span>Update Status</span>
                </a>
            <?php endif; ?>
            
            <div class="nav-divider"></div>
            <a href="logout.php" class="nav-item" style="color: #ef4444;">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="content-header">
            <h2>Welcome to Dashboard, <?php echo $_SESSION['username']; ?>!</h2>
            <p>Manage your TODA operations efficiently from this dashboard</p>
        </div>

        <div class="card">
            <h3><i class="fas fa-chart-bar"></i> Quick Stats</h3>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-taxi"></i>
                    </div>
                    <div class="stat-value" id="active-drivers-count"><?php echo $activeDrivers; ?></div>
                    <div class="stat-label">Active Drivers</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-route"></i>
                    </div>
                    <div class="stat-value" id="total-trips"><?php echo $totalTrips; ?></div>
                    <div class="stat-label">Today's Trips</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value" id="total-users"><?php echo $totalUsers; ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-value" id="rating">4.8</div>
                    <div class="stat-label">Avg Rating</div>
                </div>
            </div>
        </div>

        <?php if($_SESSION['role'] == 'driver'): ?>
        <div class="card">
            <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
            <div class="status-controls">
                <div class="status-card available" data-status="available">
                    <div class="status-icon"></div>
                    <div class="status-title">Available</div>
                    <div class="status-desc">Ready to accept passengers</div>
                </div>

                <div class="status-card busy" data-status="busy">
                    <div class="status-icon"></div>
                    <div class="status-title">Busy</div>
                    <div class="status-desc">Currently on a trip</div>
                </div>

                <div class="status-card unavailable" data-status="unavailable">
                    <div class="status-icon"></div>
                    <div class="status-title">Unavailable</div>
                    <div class="status-desc">Not taking passengers</div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div id="weather-container"></div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Optional: load weather or other JS
        if (typeof loadWeather === 'function') {
            loadWeather('Santiago');
        }
    });
    </script>
</body>
</html>
