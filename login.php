<?php
require_once 'config.php';

// If already logged in, redirect to dashboard
if(isset($_SESSION['user_id'])){
    header("Location: dashboard.php");
    exit;
}

$error = '';
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if($username=='' || $password==''){
        $error = "Username and password required.";
    } else {
        $user = qRow("SELECT userid, username, password, userrole FROM users WHERE username=? LIMIT 1", array($username));
        $valid = false;
        if($user){
            if(password_verify($password, $user['password'])){
                $valid = true;
            } elseif(hash_equals($user['password'], hash('sha256', $password))){
                // Legacy SHA-256 hash: accept once, then upgrade to password_hash()
                $valid = true;
                q("UPDATE users SET password=? WHERE userid=?", array(password_hash($password, PASSWORD_DEFAULT), (int)$user['userid']));
            }
        }

        if($valid){
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$user['userid'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = strtolower($user['userrole']);

            header("Location: dashboard.php");
            exit;
        }
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TODA System - Login</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="assets/style.css">
<style>
    body.login-page {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        padding: 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        margin: 0;
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    }
    
    .login-container {
        width: 100%;
        max-width: 450px;
    }
    
    .login-box {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(20px);
        border-radius: 24px;
        padding: 50px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        text-align: center;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }
    
    .login-logo {
        width: 90px;
        height: 90px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        color: white;
        font-size: 40px;
        box-shadow: 0 10px 30px rgba(99, 102, 241, 0.4);
    }
    
    .login-title {
        color: #1e293b;
        margin-bottom: 10px;
        font-size: 28px;
        font-weight: 700;
    }
    
    .login-subtitle {
        color: #64748b;
        font-size: 15px;
        margin-bottom: 30px;
    }
    
    .login-form {
        text-align: left;
        margin-top: 30px;
    }
    
    .login-footer {
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #e2e8f0;
        color: #64748b;
        font-size: 14px;
        text-align: center;
    }
    
    .form-group {
        margin-bottom: 25px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 10px;
        color: #1e293b;
        font-weight: 600;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .form-group label i {
        font-size: 16px;
        color: #6366f1;
    }
    
    .input-wrapper {
        position: relative;
        width: 100%;
    }
    
    .input-wrapper input {
        width: 100%;
        padding: 16px 50px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        font-size: 16px;
        transition: all 0.3s ease;
        background: white;
        color: #1e293b;
    }
    
    .input-wrapper input:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }
    
    .input-wrapper .input-icon {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: #64748b;
        font-size: 18px;
    }
    
    .input-wrapper .toggle-password {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #64748b;
        cursor: pointer;
        font-size: 18px;
        padding: 8px;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .input-wrapper .toggle-password:hover {
        color: #6366f1;
        background: #f1f5f9;
    }
    
    .login-btn {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white;
        border: none;
        padding: 16px 32px;
        border-radius: 12px;
        cursor: pointer;
        font-size: 16px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        width: 100%;
        margin-top: 20px;
    }
    
    .login-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
    }
    
    .back-btn {
        background: rgba(255, 255, 255, 0.9);
        border: 2px solid #e2e8f0;
        color: #1e293b;
        padding: 16px 32px;
        border-radius: 12px;
        cursor: pointer;
        font-size: 16px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        width: 100%;
        margin-top: 15px;
        text-decoration: none;
        text-align: center;
    }
    
    .back-btn:hover {
        background: #f1f5f9;
        transform: translateY(-2px);
        border-color: #6366f1;
    }
    
    .alert {
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 15px;
        border-left: 4px solid transparent;
    }
    
    .alert-danger {
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.3);
        color: #ef4444;
        border-left-color: #ef4444;
    }
    
    .alert-icon {
        font-size: 24px;
        flex-shrink: 0;
    }
</style>
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-logo">
                <i class="fas fa-taxi"></i>
            </div>
            
            <h2 class="login-title">TODA System Login</h2>
            <p class="login-subtitle">
                <i class="fas fa-shield-alt" style="margin-right: 8px;"></i>
                Access your dashboard and management tools
            </p>

            <?php if($error): ?>
            <div class="alert alert-danger">
                <i class="alert-icon fas fa-exclamation-triangle"></i>
                <span><?php echo h($error); ?></span>
            </div>
            <?php endif; ?>

            <form method="post" class="login-form">
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i>
                        Username
                    </label>
                    <div class="input-wrapper">
                        <input type="text" 
                               name="username" 
                               id="username" 
                               required 
                               placeholder="Enter your username">
                        <span class="input-icon">
                            <i class="fas fa-user"></i>
                        </span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Password
                    </label>
                    <div class="input-wrapper">
                        <input type="password" 
                               name="password" 
                               id="password" 
                               required 
                               placeholder="Enter your password">
                        <span class="input-icon">
                            <i class="fas fa-lock"></i>
                        </span>
                        <button type="button" 
                                class="toggle-password"
                                onclick="togglePassword()"
                                data-tooltip="Show password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Login to Dashboard</span>
                </button>
            </form>

            <a href="index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Passenger View</span>
            </a>

            <div class="login-footer">
                <p>
                    <i class="fas fa-info-circle" style="margin-right: 8px;"></i>
                    Driver and admin access only. Passengers can view drivers without login.
                </p>
            </div>
        </div>
    </div>

    <script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleButton = document.querySelector('.toggle-password');
        const icon = toggleButton.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.className = 'fas fa-eye-slash';
            toggleButton.setAttribute('data-tooltip', 'Hide password');
        } else {
            passwordInput.type = 'password';
            icon.className = 'fas fa-eye';
            toggleButton.setAttribute('data-tooltip', 'Show password');
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            
            if (!username || !password) {
                e.preventDefault();
                alert('Please fill in all fields');
                return false;
            }
        });
    });
    </script>
</body>
</html>