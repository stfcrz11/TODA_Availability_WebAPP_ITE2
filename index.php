<?php
require_once 'config.php';

// Check login
$loggedIn = isset($_SESSION['user_id']);
$username = $loggedIn ? $_SESSION['username'] : '';
$role = $loggedIn ? $_SESSION['role'] : 'guest';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>TODA System - Find Available Drivers</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body.passenger-page {
            display: block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            padding: 20px;
            margin: 0;
        }
        .passenger-container { max-width: 1200px; margin: 0 auto; }
        .passenger-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; }
        .logo-container { display: flex; align-items: center; gap: 15px; }
        .logo-icon {
            width: 60px; height: 60px; background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 28px;
        }
        .logo-text h1 {
            color: #1e293b; font-size: 24px; margin:0; background: linear-gradient(135deg, #6366f1, #8b5cf6);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .logo-text p { color: #64748b; font-size: 14px; margin: 5px 0 0 0; }
        .user-info { display:flex; align-items:center; gap:15px; margin-top:10px; }
        .user-badge {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color:white; padding:8px 16px; border-radius:20px; font-size:14px; font-weight:600;
            display:flex; align-items:center; gap:8px;
        }
        .nav-buttons { display:flex; gap:15px; margin-top:20px; flex-wrap: wrap; padding-top:20px; border-top:1px solid rgba(226,232,240,0.5);}
        .nav-btn {
            background: rgba(255,255,255,0.9); border:2px solid #e2e8f0; padding:12px 24px; border-radius:12px;
            color:#1e293b; text-decoration:none; font-weight:600; display:flex; align-items:center; gap:10px;
            transition: all 0.3s ease;
        }
        .nav-btn:hover { background: linear-gradient(135deg,#6366f1,#8b5cf6); color:white; transform: translateY(-2px); box-shadow:0 8px 20px rgba(99,102,241,0.3); border-color:#6366f1; }
        .nav-btn.primary { background: linear-gradient(135deg,#6366f1,#8b5cf6); color:white; border-color:#6366f1; }
        .nav-btn.logout { background: rgba(239,68,68,0.1); border-color: rgba(239,68,68,0.3); color:#ef4444; }
        .nav-btn.logout:hover { background: linear-gradient(135deg,#ef4444,#dc2626); color:white; border-color:#ef4444; box-shadow:0 8px 20px rgba(239,68,68,0.3); }
        .passenger-card {
            background: rgba(255,255,255,0.95); backdrop-filter: blur(20px);
            border-radius:20px; padding:30px; margin-bottom:30px; box-shadow:0 20px 60px rgba(0,0,0,0.1); border:1px solid rgba(255,255,255,0.3);
        }
        .passenger-card h2 { color:#1e293b; font-size:24px; margin-bottom:20px; display:flex; align-items:center; gap:12px; border-bottom:2px solid #e2e8f0; padding-bottom:15px;}
        .passenger-card h2 i { color:#6366f1; font-size:22px; }
        .welcome-message, .guest-message { text-align:center; padding:25px; border-radius:16px; margin-bottom:30px; border:1px solid rgba(226,232,240,0.5); background: linear-gradient(135deg,#f8fafc,#e2e8f0);}
        .welcome-icon { width:80px; height:80px; background: linear-gradient(135deg,#6366f1,#8b5cf6); border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-size:36px; margin:0 auto 20px; }
        .driver-card { padding:20px; }
        @media(max-width:768px){ .header-top{ flex-direction:column; align-items:flex-start; } .nav-btn{flex:1; min-width:150px; justify-content:center;} }
    </style>
</head>
<body class="passenger-page">
    <div class="passenger-container">
        <!-- Header -->
        <div class="passenger-header">
            <div class="header-top">
                <div class="logo-container">
                    <div class="logo-icon"><i class="fas fa-taxi"></i></div>
                    <div class="logo-text"><h1>TODA System</h1><p>Find Available Drivers</p></div>
                </div>
                <div class="user-info">
                    <?php if($loggedIn): ?>
                        <span class="user-badge"><i class="fas fa-user"></i> <?php echo $username; ?>
                            <span style="background: rgba(255,255,255,0.2); padding:2px 8px; border-radius:10px; margin-left:5px; font-size:12px;">
                                <?php echo ucfirst($role); ?>
                            </span>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if($loggedIn): ?>
                <div class="welcome-message">
                    <div class="welcome-icon">
                        <?php if($role=='admin'): ?><i class="fas fa-crown"></i>
                        <?php elseif($role=='driver'): ?><i class="fas fa-taxi"></i>
                        <?php else: ?><i class="fas fa-user"></i><?php endif; ?>
                    </div>
                    <h3>Welcome back, <?php echo $username; ?>!</h3>
                    <p>
                        <?php if($role=='admin'): ?>
                            You have administrative access. Manage users, view trip logs, and monitor the system.
                        <?php elseif($role=='driver'): ?>
                            Update your status and view available passengers.
                        <?php else: ?>
                            Find available drivers and request rides.
                        <?php endif; ?>
                    </p>
                </div>
            <?php else: ?>
                <div class="guest-message">
                    <div class="welcome-icon"><i class="fas fa-taxi"></i></div>
                    <h3>Welcome to TODA System</h3>
                    <p>View available drivers. Login to access driver/admin features.</p>
                </div>
            <?php endif; ?>

            <div class="nav-buttons">
                <?php if($loggedIn): ?>
                    <a href="dashboard.php" class="nav-btn primary"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    <?php if(isAdmin()): ?>
                        <a href="user_management.php" class="nav-btn"><i class="fas fa-user-tie"></i> User Management</a>
                        <a href="trip_logs.php" class="nav-btn"><i class="fas fa-clipboard-list"></i> Trip Logs</a>
                    <?php elseif($role=='driver'): ?>
                        <a href="driver_status.php" class="nav-btn"><i class="fas fa-bolt"></i> Update Status</a>
                    <?php endif; ?>
                    <a href="logout.php" class="nav-btn logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                <?php else: ?>
                    <a href="login.php" class="nav-btn primary"><i class="fas fa-sign-in-alt"></i> Login</a>
                    <a href="#drivers" class="nav-btn"><i class="fas fa-taxi"></i> View Drivers</a>
                    <a href="#how-it-works" class="nav-btn"><i class="fas fa-info-circle"></i> How It Works</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Drivers -->
        <div class="passenger-card" id="drivers">
            <h2><i class="fas fa-taxi"></i> Available Drivers
                <span id="driver-count" style="background:#f1f5f9;color:#475569;padding:6px 12px;border-radius:20px;margin-left:15px; display:inline-flex;align-items:center; gap:8px;">
                    <i class="fas fa-users"></i> Loading...
                </span>
            </h2>
            <div id="drivers-container"></div>
        </div>

        <!-- How it works -->
        <div class="passenger-card" id="how-it-works">
            <h2><i class="fas fa-info-circle"></i> How It Works</h2>
            <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap:20px;">
                <div style="background:rgba(248,250,252,0.8);padding:25px;border-radius:16px;text-align:center;border:1px solid rgba(226,232,240,0.5);">
                    <div style="font-size:40px; margin-bottom:15px; color:#6366f1;"><i class="fas fa-search"></i></div>
                    <h4>Find Drivers</h4>
                    <p style="color:#64748b;">View available drivers in real-time</p>
                </div>
                <div style="background:rgba(248,250,252,0.8);padding:25px;border-radius:16px;text-align:center;border:1px solid rgba(226,232,240,0.5);">
                    <div style="font-size:40px; margin-bottom:15px; color:#10b981;"><i class="fas fa-phone"></i></div>
                    <h4>Contact Driver</h4>
                    <p style="color:#64748b;">Get driver contact information</p>
                </div>
                <div style="background:rgba(248,250,252,0.8);padding:25px;border-radius:16px;text-align:center;border:1px solid rgba(226,232,240,0.5);">
                    <div style="font-size:40px; margin-bottom:15px; color:#8b5cf6;"><i class="fas fa-shield-alt"></i></div>
                    <h4>Ride Safe</h4>
                    <p style="color:#64748b;">Track your ride with confidence</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('drivers-container');
            const countElement = document.getElementById('driver-count');

            function loadAvailableDrivers() {
                if(!container) return;

                container.innerHTML = `<div style="text-align:center; color:#64748b; padding:20px;">
                    <i class="fas fa-spinner fa-spin" style="font-size:24px;"></i>
                    <p>Loading available drivers...</p>
                </div>`;

                fetch('ajax/fetch_driver.php')
                .then(res => res.json())
                .then(drivers => {
                    container.innerHTML = '';
                    if(drivers.length > 0){
                        drivers.forEach(driver => {
                            const card = document.createElement('div');
                            card.className = 'driver-card passenger-card';
                            card.innerHTML = `
                                <h4 style="margin:0 0 10px 0;"><i class="fas fa-user-tie"></i> ${driver.fullname}</h4>
                                <p style="margin:0; color:#64748b;">Status: ${driver.status.charAt(0).toUpperCase() + driver.status.slice(1)}</p>
                                <button class="nav-btn primary" style="margin-top:10px;" onclick="requestDriver('${driver.fullname}')">Request Driver</button>
                            `;
                            container.appendChild(card);
                        });
                    } else {
                        container.innerHTML = `<p style="color:#64748b;">No drivers are currently available.</p>`;
                    }
                    if(countElement) countElement.innerHTML = `<i class="fas fa-users"></i> ${drivers.length} available`;
                })
                .catch(err => {
                    container.innerHTML = `<p style="color:#ef4444;">Error loading drivers.</p>`;
                    console.error(err);
                });
            }

            loadAvailableDrivers();
            setInterval(loadAvailableDrivers, 20000);

            window.requestDriver = function(name){ alert(`Request sent to ${name}!`); }
        });
    </script>
</body>
</html>
