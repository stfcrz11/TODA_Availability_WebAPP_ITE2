<?php
// includes/sidebar.php - Shared sidebar. Set $activePage before including.
$activePage = isset($activePage) ? $activePage : '';
$sidebarStatus = null;
if (isDriver()) {
    $sidebarDriver = getDriverByUser($_SESSION['user_id']);
    if ($sidebarDriver) {
        $sidebarStatus = getDriverStatus($sidebarDriver['driverid']);
    }
}
?>
<nav class="sidebar">
    <div class="sidebar-header">
        <h1>
            <i class="fas fa-taxi"></i>
            <span>TODA System</span>
        </h1>
        <div class="user-info">
            <span><i class="fas fa-user"></i> <?php echo h($_SESSION['username']); ?></span>
            <span class="user-role">
                <i class="fas fa-<?php echo isAdmin() ? 'crown' : 'taxi'; ?>"></i>
                <?php echo h(ucfirst($_SESSION['role'])); ?>
            </span>
        </div>
        <?php if ($sidebarStatus): $s = $STATUSES[$sidebarStatus]; ?>
        <div style="margin-top: 10px;">
            <span style="font-size: 12px; color: #64748b;">Status: </span>
            <span id="current-status" class="badge badge-<?php echo $s['badge']; ?>"><?php echo $s['label']; ?></span>
        </div>
        <?php endif; ?>
    </div>

    <div class="sidebar-nav">
        <a href="index.php" class="nav-item">
            <i class="fas fa-home"></i>
            <span>Passenger View</span>
        </a>
        <a href="dashboard.php" class="nav-item <?php echo $activePage === 'dashboard' ? 'active' : ''; ?>">
            <i class="fas fa-chart-line"></i>
            <span>Dashboard</span>
        </a>

        <?php if (isAdmin()): ?>
            <div class="nav-divider"></div>
            <div style="padding: 0 20px; margin: 20px 0;">
                <span style="font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 1px;">
                    <i class="fas fa-crown"></i> Admin Panel
                </span>
            </div>
            <a href="user_management.php" class="nav-item <?php echo $activePage === 'users' ? 'active' : ''; ?>">
                <i class="fas fa-user-tie"></i>
                <span>User Management</span>
            </a>
            <a href="trip_logs.php" class="nav-item <?php echo $activePage === 'trips' ? 'active' : ''; ?>">
                <i class="fas fa-clipboard-list"></i>
                <span>Trip Logs</span>
            </a>
        <?php elseif (isDriver()): ?>
            <div class="nav-divider"></div>
            <a href="driver_status.php" class="nav-item <?php echo $activePage === 'status' ? 'active' : ''; ?>">
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
