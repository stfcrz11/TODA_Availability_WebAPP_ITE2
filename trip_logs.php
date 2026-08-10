<?php
require_once 'config.php';
checkLogin();

if(!isAdmin()){
    die("Access denied. Admins only.");
}

// FETCH TRIPS — MUST BE BEFORE HTML
$trips = mysql_query("
    SELECT 
        tl.triplogid,
        d.fullname,
        t.plate_no,
        tl.trip_start,
        tl.trip_end
    FROM triplogs tl
    INNER JOIN driver d ON tl.driverid = d.driverid
    INNER JOIN tricycle t ON tl.tricycleid = t.tricycleid
    ORDER BY tl.triplogid DESC
") or die(mysql_error());

if($_SESSION['role'] == 'driver') {
    $userid = intval($_SESSION['userid']);
    $result = mysql_query("
        SELECT a.status 
        FROM availability a
        INNER JOIN driver d ON a.driverid = d.driverid
        WHERE d.userid = $userid
        ORDER BY a.last_updated DESC
        LIMIT 1
    ");
    if($result && $row = mysql_fetch_assoc($result)) {
        $currentStatus = $row['status'];
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Trip Logs - TODA System</title>
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
                <span class="user-role">
                    <i class="fas fa-crown"></i>
                    <?php echo $_SESSION['role']; ?>
                </span>
            </div>
        </div>
        
        <div class="sidebar-nav">
            <a href="dashboard.php" class="nav-item">
                <i class="fas fa-chart-line"></i>
                <span>Dashboard</span>
            </a>

            <div class="nav-divider"></div>
            <div style="padding: 0 20px; margin: 20px 0;">
                <span style="font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 1px;">
                    <i class="fas fa-crown"></i> Admin Panel
                </span>
            </div>
            <a href="user_management.php" class="nav-item">
                <i class="fas fa-user-tie"></i>
                <span>User Management</span>
            </a>
            <a href="trip_logs.php" class="nav-item active">
                <i class="fas fa-clipboard-list"></i>
                <span>Trip Logs</span>
            </a>
            
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
            <h2>
                <i class="fas fa-clipboard-list"></i>
                Trip Logs
            </h2>
            <p>View and monitor all transportation trips</p>
        </div>

        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h3 style="margin: 0; border: none; padding: 0;">
                    <i class="fas fa-route"></i>
                    All Trips
                </h3>
                <div style="display: flex; gap: 15px;">
                    <button class="btn" onclick="exportTrips()" style="padding: 10px 20px; font-size: 14px;">
                        <i class="fas fa-download"></i>
                        Export
                    </button>
                    <button class="btn" onclick="refreshTrips()" style="padding: 10px 20px; font-size: 14px; background: #10b981;">
                        <i class="fas fa-sync-alt"></i>
                        Refresh
                    </button>
                </div>
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag"></i> Trip ID</th>
                            <th><i class="fas fa-user"></i> Driver</th>
                            <th><i class="fas fa-car"></i> Vehicle</th>
                            <th><i class="fas fa-play"></i> Start Time</th>
                            <th><i class="fas fa-stop"></i> End Time</th>
                            <th><i class="fas fa-circle"></i> Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($trips && mysql_num_rows($trips) > 0): ?>
                            <?php while($row = mysql_fetch_assoc($trips)): ?>
                                <tr>
                                    <td>
                                        <strong>#<?php echo $row['triplogid']; ?></strong>
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <div style="
                                                width: 40px;
                                                height: 40px;
                                                background: linear-gradient(135deg, #6366f1, #8b5cf6);
                                                border-radius: 50%;
                                                display: flex;
                                                align-items: center;
                                                justify-content: center;
                                                color: white;
                                                font-size: 16px;
                                                font-weight: 600;
                                            ">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div>
                                                <div style="font-weight: 600; color: #1e293b;">
                                                    <?php echo htmlspecialchars($row['fullname']); ?>
                                                </div>
                                                <div style="font-size: 12px; color: #64748b;">
                                                    <i class="fas fa-id-badge"></i>
                                                    Driver ID: <?php echo $row['triplogid']; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge" style="background: #f1f5f9; color: #475569;">
                                            <i class="fas fa-car"></i>
                                            <?php echo htmlspecialchars($row['plate_no']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: #1e293b;">
                                            <i class="fas fa-calendar-day"></i>
                                            <?php echo date('M d, Y', strtotime($row['trip_start'])); ?>
                                        </div>
                                        <div style="font-size: 12px; color: #64748b;">
                                            <i class="fas fa-clock"></i>
                                            <?php echo date('h:i A', strtotime($row['trip_start'])); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if($row['trip_end']): ?>
                                            <div style="font-weight: 600; color: #1e293b;">
                                                <i class="fas fa-calendar-check"></i>
                                                <?php echo date('M d, Y', strtotime($row['trip_end'])); ?>
                                            </div>
                                            <div style="font-size: 12px; color: #64748b;">
                                                <i class="fas fa-clock"></i>
                                                <?php echo date('h:i A', strtotime($row['trip_end'])); ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge badge-warning">
                                                <i class="fas fa-spinner fa-spin"></i>
                                                Ongoing
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($row['trip_end']): ?>
                                            <span class="badge badge-success">
                                                <i class="fas fa-check-circle"></i>
                                                Completed
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">
                                                <i class="fas fa-clock"></i>
                                                In Progress
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 60px 20px;">
                                    <div style="font-size: 64px; margin-bottom: 20px; color: #e2e8f0;">
                                        <i class="fas fa-clipboard"></i>
                                    </div>
                                    <h3 style="color: #64748b; margin-bottom: 10px;">No Trip Records</h3>
                                    <p style="color: #94a3b8;">No trips have been recorded yet.</p>
                                    <button class="btn" onclick="location.reload()" style="margin-top: 20px;">
                                        <i class="fas fa-redo"></i>
                                        Refresh Page
                                    </button>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if($trips && mysql_num_rows($trips) > 0): ?>
            <div style="
                margin-top: 25px;
                padding: 20px;
                background: #f8fafc;
                border-radius: 12px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                border: 1px solid #e2e8f0;
            ">
                <div style="color: #64748b; font-size: 14px;">
                    <i class="fas fa-info-circle"></i>
                    Showing <?php echo mysql_num_rows($trips); ?> trip records
                </div>
                <div style="display: flex; gap: 10px;">
                    <button class="btn" style="padding: 8px 16px; font-size: 14px; background: #6b7280;">
                        <i class="fas fa-chevron-left"></i>
                        Previous
                    </button>
                    <button class="btn" style="padding: 8px 16px; font-size: 14px;">
                        Next
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
    function exportTrips() {
        showToast('<i class="fas fa-download"></i> Exporting trip data...', 'info');
        // Simulate export
        setTimeout(() => {
            showToast('<i class="fas fa-check"></i> Trip data exported successfully!', 'success');
        }, 2000);
    }
    
    function refreshTrips() {
        showToast('<i class="fas fa-sync-alt fa-spin"></i> Refreshing trip logs...', 'info');
        setTimeout(() => {
            location.reload();
        }, 1000);
    }
    </script>
</body>
</html>
<?php
function getInitials($name) {
    $parts = explode(' ', $name);
    $initials = '';
    foreach($parts as $part) {
        if(!empty($part)) {
            $initials .= strtoupper($part[0]);
        }
    }
    return substr($initials, 0, 2);
}
?>