<?php
require_once 'config.php';
requireAdmin();

$baseQuery = "
    SELECT tl.triplogid, tl.driverid, d.fullname, t.plate_no, tl.trip_start, tl.trip_end
    FROM triplogs tl
    INNER JOIN driver d ON tl.driverid = d.driverid
    INNER JOIN tricycle t ON tl.tricycleid = t.tricycleid
    ORDER BY tl.triplogid DESC";

// CSV export
if (isset($_GET['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="trip_logs_' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, array('Trip ID', 'Driver ID', 'Driver', 'Plate No', 'Trip Start', 'Trip End', 'Status'));
    $res = q($baseQuery);
    while ($res && $row = mysqli_fetch_assoc($res)) {
        fputcsv($out, array($row['triplogid'], $row['driverid'], $row['fullname'], $row['plate_no'],
            $row['trip_start'], $row['trip_end'], $row['trip_end'] ? 'Completed' : 'In Progress'));
    }
    fclose($out);
    exit;
}

// Pagination
$perPage = 15;
$countRow = qRow("SELECT COUNT(*) AS c FROM triplogs tl INNER JOIN driver d ON tl.driverid = d.driverid INNER JOIN tricycle t ON tl.tricycleid = t.tricycleid");
$totalTrips = $countRow ? (int)$countRow['c'] : 0;
$totalPages = max(1, (int)ceil($totalTrips / $perPage));
$page = isset($_GET['page']) ? max(1, min($totalPages, (int)$_GET['page'])) : 1;
$offset = ($page - 1) * $perPage;

$trips = q($baseQuery . " LIMIT ? OFFSET ?", array($perPage, $offset));
if (!($trips instanceof mysqli_result)) {
    die('Query failed: ' . h(mysqli_error($conn)));
}

function fmtDate($ts, $format) {
    return $ts ? date($format, strtotime($ts)) : '—';
}

$activePage = 'trips';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trip Logs - TODA System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
    <script src="assets/script.js" defer></script>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="content-header">
            <h2><i class="fas fa-clipboard-list"></i> Trip Logs</h2>
            <p>View and monitor all transportation trips</p>
        </div>

        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
                <h3 style="margin: 0; border: none; padding: 0;">
                    <i class="fas fa-route"></i> All Trips
                </h3>
                <div style="display: flex; gap: 15px;">
                    <a class="btn" href="trip_logs.php?export=csv" style="padding: 10px 20px; font-size: 14px; text-decoration: none;">
                        <i class="fas fa-download"></i> Export CSV
                    </a>
                    <a class="btn" href="trip_logs.php?page=<?php echo $page; ?>" style="padding: 10px 20px; font-size: 14px; background: #10b981; text-decoration: none;">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </a>
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
                        <?php if ($totalTrips > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($trips)): ?>
                                <tr>
                                    <td><strong>#<?php echo (int)$row['triplogid']; ?></strong></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #6366f1, #8b5cf6); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 16px; font-weight: 600;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div>
                                                <div style="font-weight: 600; color: #1e293b;"><?php echo h($row['fullname']); ?></div>
                                                <div style="font-size: 12px; color: #64748b;">
                                                    <i class="fas fa-id-badge"></i> Driver ID: <?php echo (int)$row['driverid']; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge" style="background: #f1f5f9; color: #475569;">
                                            <i class="fas fa-car"></i> <?php echo h($row['plate_no']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: #1e293b;">
                                            <i class="fas fa-calendar-day"></i> <?php echo fmtDate($row['trip_start'], 'M d, Y'); ?>
                                        </div>
                                        <div style="font-size: 12px; color: #64748b;">
                                            <i class="fas fa-clock"></i> <?php echo fmtDate($row['trip_start'], 'h:i A'); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($row['trip_end']): ?>
                                            <div style="font-weight: 600; color: #1e293b;">
                                                <i class="fas fa-calendar-check"></i> <?php echo fmtDate($row['trip_end'], 'M d, Y'); ?>
                                            </div>
                                            <div style="font-size: 12px; color: #64748b;">
                                                <i class="fas fa-clock"></i> <?php echo fmtDate($row['trip_end'], 'h:i A'); ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge badge-warning"><i class="fas fa-spinner fa-spin"></i> Ongoing</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($row['trip_end']): ?>
                                            <span class="badge badge-success"><i class="fas fa-check-circle"></i> Completed</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning"><i class="fas fa-clock"></i> In Progress</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 60px 20px;">
                                    <div style="font-size: 64px; margin-bottom: 20px; color: #e2e8f0;"><i class="fas fa-clipboard"></i></div>
                                    <h3 style="color: #64748b; margin-bottom: 10px;">No Trip Records</h3>
                                    <p style="color: #94a3b8;">Trips are recorded when a driver sets their status to "On Trip".</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalTrips > 0): ?>
            <div style="margin-top: 25px; padding: 20px; background: #f8fafc; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; border: 1px solid #e2e8f0; flex-wrap: wrap; gap: 10px;">
                <div style="color: #64748b; font-size: 14px;">
                    <i class="fas fa-info-circle"></i>
                    Showing <?php echo $offset + 1; ?>–<?php echo min($offset + $perPage, $totalTrips); ?> of <?php echo $totalTrips; ?> trip records
                    (page <?php echo $page; ?> of <?php echo $totalPages; ?>)
                </div>
                <div style="display: flex; gap: 10px;">
                    <?php if ($page > 1): ?>
                    <a class="btn" href="?page=<?php echo $page - 1; ?>" style="padding: 8px 16px; font-size: 14px; background: #6b7280; text-decoration: none;">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                    <?php endif; ?>
                    <?php if ($page < $totalPages): ?>
                    <a class="btn" href="?page=<?php echo $page + 1; ?>" style="padding: 8px 16px; font-size: 14px; text-decoration: none;">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
