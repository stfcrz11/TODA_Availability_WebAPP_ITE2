<?php
require_once 'config.php';
checkLogin();

// Ensure userid is valid
$userid = isset($_SESSION['userid']) ? intval($_SESSION['userid']) : 0;
$currentStatus = 'unavailable'; // default

if ($userid > 0) {
    $result = mysql_query("SELECT status FROM driver WHERE userid=$userid");
    if ($result && $row = mysql_fetch_assoc($result)) {
        $currentStatus = $row['status'];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Driver Status - TODA System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        /* Styles copied from your original */
        .current-status-display { background: linear-gradient(135deg,#f8fafc 0%,#e2e8f0 100%); padding:30px; border-radius:16px; margin-bottom:30px; text-align:center; border:2px solid #e2e8f0;}
        .status-icon-large { font-size:64px; margin-bottom:20px; height:100px; width:100px; display:flex; align-items:center; justify-content:center; margin:0 auto 20px; border-radius:50%;}
        .available-icon { background: rgba(16,185,129,.1); color:#10b981;}
        .busy-icon { background: rgba(245,158,11,.1); color:#f59e0b;}
        .unavailable-icon { background: rgba(239,68,68,.1); color:#ef4444;}
        .status-tips { background: linear-gradient(135deg,#f8fafc,#e2e8f0); padding:25px; border-radius:16px; margin-top:30px; border:1px solid #e2e8f0;}
        .tips-list { color:#64748b; list-style:none; padding:0; margin:0;}
        .tips-list li { padding:12px 0; display:flex; align-items:flex-start; gap:15px; border-bottom:1px solid rgba(226,232,240,.5);}
        .tips-list li:last-child { border-bottom:none; }
        .tip-icon { font-size:20px; flex-shrink:0; width:24px;}
        .status-description { color:#64748b; font-size:15px; line-height:1.6; margin-top:5px;}
        .status-controls { display:flex; gap:20px; flex-wrap:wrap;}
        .status-card { flex:1 1 200px; padding:20px; border-radius:12px; cursor:pointer; transition:0.2s; border:1px solid #e2e8f0; text-align:center;}
        .status-card.active { border:2px solid #6366f1;}
        .status-card .status-icon { font-size:32px; margin-bottom:10px;}
    </style>
</head>
<body>

<nav class="sidebar">
    <div class="sidebar-header">
        <h1><i class="fas fa-taxi"></i> TODA System</h1>
        <div class="user-info">
            <span><i class="fas fa-user"></i> <?php echo $_SESSION['username']; ?></span>
            <span class="user-role"><i class="fas fa-taxi"></i> Driver</span>
        </div>
        <div style="margin-top:10px;">
            <span style="font-size:12px; color:#64748b;"><i class="fas fa-circle"></i> Current Status:</span>
            <span id="current-status" class="badge badge-<?php 
                echo $currentStatus=='available'?'success':($currentStatus=='busy'?'warning':'danger'); ?>">
                <i class="fas fa-<?php 
                    echo $currentStatus=='available'?'check-circle':($currentStatus=='busy'?'clock':'ban'); ?>"></i>
                <?php echo ucfirst($currentStatus); ?>
            </span>
        </div>
    </div>
    <div class="sidebar-nav">
        <a href="dashboard.php" class="nav-item"><i class="fas fa-chart-line"></i> Dashboard</a>
        <div class="nav-divider"></div>
        <a href="driver_status.php" class="nav-item active"><i class="fas fa-bolt"></i> Update Status</a>
        <div class="nav-divider"></div>
        <a href="logout.php" class="nav-item" style="color:#ef4444;"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</nav>

<main class="main-content">
    <div class="content-header">
        <h2><i class="fas fa-bolt"></i> Update Your Status</h2>
        <p>Let passengers know your availability in real-time</p>
    </div>

    <div class="card">
        <h3><i class="fas fa-user-clock"></i> Current Status</h3>
        <div class="current-status-display">
            <div class="status-icon-large <?php
                echo $currentStatus=='available'?'available-icon':($currentStatus=='busy'?'busy-icon':'unavailable-icon'); ?>">
                <i class="fas fa-<?php
                    echo $currentStatus=='available'?'user-check':($currentStatus=='busy'?'user-clock':'user-slash'); ?>"></i>
            </div>
            <h3>You are currently 
                <span id="current-status-badge" class="badge badge-<?php
                    echo $currentStatus=='available'?'success':($currentStatus=='busy'?'warning':'danger'); ?>">
                    <i class="fas fa-<?php
                        echo $currentStatus=='available'?'check':($currentStatus=='busy'?'clock':'ban'); ?>"></i>
                    <?php echo ucfirst($currentStatus); ?>
                </span>
            </h3>
            <p class="status-description" id="status-description">
                <?php
                    if($currentStatus=='available') echo '<i class="fas fa-eye"></i> Passengers can see you and request rides';
                    elseif($currentStatus=='busy') echo '<i class="fas fa-route"></i> You are currently on a trip';
                    else echo '<i class="fas fa-bed"></i> You are not available for rides';
                ?>
            </p>
        </div>

        <h3><i class="fas fa-sync-alt"></i> Update Status</h3>
        <p class="status-description">Click on a status card below to update your availability instantly</p>

        <div class="status-controls">
            <?php
            $statuses = [
                'available'=>['icon'=>'user-check','title'=>'Available','desc'=>'Ready to accept passengers'],
                'busy'=>['icon'=>'user-clock','title'=>'Busy','desc'=>'Currently on a trip'],
                'unavailable'=>['icon'=>'user-slash','title'=>'Unavailable','desc'=>'Not taking passengers']
            ];
            foreach($statuses as $status=>$info):
            ?>
            <div class="status-card <?php echo $status; ?> <?php echo $currentStatus==$status?'active':''; ?>" data-status="<?php echo $status; ?>">
                <div class="status-icon"><i class="fas fa-<?php echo $info['icon']; ?>"></i></div>
                <div class="status-title"><?php echo $info['title']; ?></div>
                <div class="status-desc"><?php echo $info['desc']; ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="status-tips">
            <h4>Status Tips</h4>
            <ul class="tips-list">
                <li><span class="tip-icon" style="color:#10b981;"><i class="fas fa-check-circle"></i></span>
                    <div><strong>Set to "Available" when ready</strong><p>Visible to passengers.</p></div></li>
                <li><span class="tip-icon" style="color:#f59e0b;"><i class="fas fa-clock"></i></span>
                    <div><strong>Set to "Busy" on a trip</strong><p>Passengers know you're occupied.</p></div></li>
                <li><span class="tip-icon" style="color:#ef4444;"><i class="fas fa-ban"></i></span>
                    <div><strong>Set to "Unavailable" when off duty</strong><p>No ride requests will come in.</p></div></li>
            </ul>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusCards = document.querySelectorAll('.status-card');
    const statusBadge = document.getElementById('current-status-badge');
    const statusDesc = document.getElementById('status-description');
    const statusIconLarge = document.querySelector('.status-icon-large i');

    statusCards.forEach(card => {
        card.addEventListener('click', function() {
            const newStatus = this.dataset.status;

            if(!confirm(`Change status to "${newStatus}"?`)) return;

            // Spinner & fade
            statusCards.forEach(c=>c.style.opacity='0.5');
            this.style.opacity='1';
            this.querySelector('i').className='fas fa-spinner fa-spin';

            const xhr = new XMLHttpRequest();
            xhr.open('POST','update_driver_status.php',true);
            xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
            xhr.onload = function() {
                const res = JSON.parse(xhr.responseText);
                if(res.success){
                    statusCards.forEach(c=>{
                        c.classList.remove('active'); 
                        c.querySelector('i').className = c.dataset.status==='available'?'fas fa-user-check':c.dataset.status==='busy'?'fas fa-user-clock':'fas fa-user-slash';
                    });
                    card.classList.add('active');

                    // Update main badge & icon
                    statusBadge.textContent = newStatus.charAt(0).toUpperCase()+newStatus.slice(1);
                    statusBadge.className = 'badge badge-'+(newStatus==='available'?'success':newStatus==='busy'?'warning':'danger');
                    statusIconLarge.className = newStatus==='available'?'fas fa-user-check':newStatus==='busy'?'fas fa-user-clock':'fas fa-user-slash';

                    // Update description
                    switch(newStatus){
                        case 'available': statusDesc.innerHTML='<i class="fas fa-eye"></i> Passengers can see you and request rides'; break;
                        case 'busy': statusDesc.innerHTML='<i class="fas fa-route"></i> You are currently on a trip'; break;
                        case 'unavailable': statusDesc.innerHTML='<i class="fas fa-bed"></i> You are not available for rides'; break;
                    }
                } else { alert('Failed to update status'); }
                statusCards.forEach(c=>c.style.opacity='1');
            };
            xhr.send('status='+encodeURIComponent(newStatus));
        });
    });
});
</script>

</body>
</html>
