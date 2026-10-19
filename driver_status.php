<?php
require_once 'config.php';
checkLogin();

if (!isDriver()) {
    header("Location: dashboard.php");
    exit;
}

$driver = getDriverByUser($_SESSION['user_id']);
$currentStatus = $driver ? getDriverStatus($driver['driverid']) : 'offline';
$info = $STATUSES[$currentStatus];

$descriptions = array(
    'available' => '<i class="fas fa-eye"></i> Passengers can see you and request rides',
    'ontrip'    => '<i class="fas fa-route"></i> You are currently on a trip',
    'offline'   => '<i class="fas fa-bed"></i> You are not available for rides',
);

$activePage = 'status';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Status - TODA System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
    <script src="assets/script.js" defer></script>
    <style>
        .current-status-display { background: linear-gradient(135deg,#f8fafc 0%,#e2e8f0 100%); padding:30px; border-radius:16px; margin-bottom:30px; text-align:center; border:2px solid #e2e8f0;}
        .status-icon-large { font-size:64px; height:100px; width:100px; display:flex; align-items:center; justify-content:center; margin:0 auto 20px; border-radius:50%;}
        .status-icon-large.available { background: rgba(16,185,129,.1); color:#10b981;}
        .status-icon-large.ontrip { background: rgba(245,158,11,.1); color:#f59e0b;}
        .status-icon-large.offline { background: rgba(239,68,68,.1); color:#ef4444;}
        .status-tips { background: linear-gradient(135deg,#f8fafc,#e2e8f0); padding:25px; border-radius:16px; margin-top:30px; border:1px solid #e2e8f0;}
        .tips-list { color:#64748b; list-style:none; padding:0; margin:0;}
        .tips-list li { padding:12px 0; display:flex; align-items:flex-start; gap:15px; border-bottom:1px solid rgba(226,232,240,.5);}
        .tips-list li:last-child { border-bottom:none; }
        .tip-icon { font-size:20px; flex-shrink:0; width:24px;}
        .status-description { color:#64748b; font-size:15px; line-height:1.6; margin-top:5px;}
    </style>
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<main class="main-content">
    <div class="content-header">
        <h2><i class="fas fa-bolt"></i> Update Your Status</h2>
        <p>Let passengers know your availability in real-time</p>
    </div>

    <div class="card">
        <?php if (!$driver): ?>
            <div class="alert alert-warning">
                <i class="alert-icon fas fa-exclamation-triangle"></i>
                <span>No driver profile is linked to your account. Ask an administrator to set one up.</span>
            </div>
        <?php else: ?>
        <h3><i class="fas fa-user-clock"></i> Current Status</h3>
        <div class="current-status-display">
            <div class="status-icon-large <?php echo $currentStatus; ?>" id="status-icon-large">
                <i class="fas fa-<?php echo $info['icon']; ?>"></i>
            </div>
            <h3>You are currently
                <span id="current-status-badge" class="badge badge-<?php echo $info['badge']; ?>"><?php echo $info['label']; ?></span>
            </h3>
            <p class="status-description" id="status-description"><?php echo $descriptions[$currentStatus]; ?></p>
        </div>

        <h3><i class="fas fa-sync-alt"></i> Update Status</h3>
        <p class="status-description">Click on a status card below to update your availability instantly</p>

        <div class="status-controls">
            <?php foreach ($STATUSES as $key => $s): ?>
            <div class="status-card <?php echo $key; ?> <?php echo $currentStatus === $key ? 'active' : ''; ?>" data-status="<?php echo $key; ?>" data-label="<?php echo $s['label']; ?>">
                <div class="status-icon"></div>
                <div class="status-title"><?php echo $s['label']; ?></div>
                <div class="status-desc"><?php echo $s['desc']; ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="status-tips">
            <h4>Status Tips</h4>
            <ul class="tips-list">
                <li><span class="tip-icon" style="color:#10b981;"><i class="fas fa-check-circle"></i></span>
                    <div><strong>Set to "Available" when ready</strong><p>Visible to passengers.</p></div></li>
                <li><span class="tip-icon" style="color:#f59e0b;"><i class="fas fa-clock"></i></span>
                    <div><strong>Set to "On Trip" when you pick up a passenger</strong><p>A trip log is started automatically and closed when you change status again.</p></div></li>
                <li><span class="tip-icon" style="color:#ef4444;"><i class="fas fa-ban"></i></span>
                    <div><strong>Set to "Offline" when off duty</strong><p>You will be hidden from passengers.</p></div></li>
            </ul>
        </div>
        <?php endif; ?>
    </div>
</main>

</body>
</html>
