<?php
require_once 'config.php';
checkLogin();

if(!isAdmin()){
    die("Access denied. Admins only.");
}

// Handle Add User
if(isset($_POST['add'])){
    $username = e($_POST['username']);
    $password = hash('sha256', e($_POST['password']));
    $role = e($_POST['role']);

    $check = mysql_query("SELECT * FROM users WHERE username='$username'");
    if(mysql_num_rows($check) > 0){
        $error = "Username already exists!";
    } else {
        mysql_query("INSERT INTO users(username,password,userrole) VALUES('$username','$password','$role')");
        $success = "User added successfully!";
    }
}

// Handle Delete User
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    mysql_query("DELETE FROM users WHERE userid=$id");
    header("Location: user_management.php");
    exit;
}

// Fetch all users
$users = mysql_query("SELECT userid, username, userrole FROM users ORDER BY userid ASC");
?>

<!DOCTYPE html>
<html>
<head>
<title>User Management - TODA System</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="assets/style.css">
<script src="assets/script.js" defer></script>
<style>
    /* Fix for password input in user management */
    .password-field-wrapper {
        position: relative;
        width: 100%;
    }
    
    .password-field-wrapper input {
        width: 100%;
        padding: 16px 20px 16px 50px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        font-size: 16px;
        transition: all 0.3s ease;
        background: white;
        color: #1e293b;
    }
    
    .password-field-wrapper input:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }
    
    .password-field-wrapper .input-icon {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: #64748b;
        font-size: 18px;
        z-index: 1;
    }
    
    .password-field-wrapper .toggle-password {
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
        z-index: 1;
        transition: all 0.3s ease;
    }
    
    .password-field-wrapper .toggle-password:hover {
        color: #6366f1;
        background: #f1f5f9;
    }
    
    /* Fix for username input */
    .username-field-wrapper {
        position: relative;
        width: 100%;
    }
    
    .username-field-wrapper input {
        width: 100%;
        padding: 16px 20px 16px 50px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        font-size: 16px;
        transition: all 0.3s ease;
        background: white;
        color: #1e293b;
    }
    
    .username-field-wrapper input:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }
    
    .username-field-wrapper .input-icon {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: #64748b;
        font-size: 18px;
        z-index: 1;
    }
    
    /* Fix for role select */
    .role-field-wrapper {
        position: relative;
        width: 100%;
    }
    
    .role-field-wrapper select {
        width: 100%;
        padding: 16px 20px 16px 50px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        font-size: 16px;
        transition: all 0.3s ease;
        background: white;
        color: #1e293b;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
    }
    
    .role-field-wrapper select:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }
    
    .role-field-wrapper .input-icon {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: #64748b;
        font-size: 18px;
        z-index: 1;
    }
    
    /* Remove the default input-with-icon styles that might conflict */
    .input-with-icon {
        display: none;
    }
</style>
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
            <a href="user_management.php" class="nav-item active">
                <i class="fas fa-user-tie"></i>
                <span>User Management</span>
            </a>
            <a href="trip_logs.php" class="nav-item">
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
                <i class="fas fa-user-tie"></i>
                User Management
            </h2>
            <p>Manage system users and their permissions</p>
        </div>

        <div class="card">
            <h3><i class="fas fa-user-plus"></i> Add New User</h3>
            
            <?php if(isset($error)): ?>
            <div class="alert alert-danger">
                <i class="alert-icon fas fa-exclamation-circle"></i>
                <span><?php echo $error; ?></span>
            </div>
            <?php endif; ?>
            
            <?php if(isset($success)): ?>
            <div class="alert alert-success">
                <i class="alert-icon fas fa-check-circle"></i>
                <span><?php echo $success; ?></span>
            </div>
            <?php endif; ?>

            <form method="post" style="max-width: 500px;">
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i>
                        Username
                    </label>
                    <div class="username-field-wrapper">
                        <input type="text" 
                               name="username" 
                               id="username" 
                               required 
                               placeholder="Enter username">
                        <span class="input-icon">
                            <i class="fas fa-user"></i>
                        </span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-key"></i>
                        Password
                    </label>
                    <div class="password-field-wrapper">
                        <input type="password" 
                               name="password" 
                               id="password" 
                               required 
                               placeholder="Enter password">
                        <span class="input-icon">
                            <i class="fas fa-key"></i>
                        </span>
                        <button type="button" 
                                class="toggle-password"
                                onclick="togglePassword('password')"
                                data-tooltip="Show password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="role">
                        <i class="fas fa-user-tag"></i>
                        User Role
                    </label>
                    <div class="role-field-wrapper">
                        <select name="role" id="role" required>
                            <option value="admin">Administrator</option>
                            <option value="driver">Driver</option>
                        </select>
                        <span class="input-icon">
                            <i class="fas fa-user-tag"></i>
                        </span>
                    </div>
                </div>

                <button type="submit" name="add" class="btn">
                    <i class="fas fa-user-plus"></i>
                    <span>Add User</span>
                </button>
            </form>
        </div>

        <div class="card">
            <h3><i class="fas fa-users"></i> All Users</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th><i class="fas fa-id-card"></i> ID</th>
                            <th><i class="fas fa-user"></i> Username</th>
                            <th><i class="fas fa-user-tag"></i> Role</th>
                            <th><i class="fas fa-cog"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysql_fetch_assoc($users)): ?>
                        <tr>
                            <td><strong>#<?php echo $row['userid']; ?></strong></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="
                                        width: 44px;
                                        height: 44px;
                                        background: linear-gradient(135deg, #6366f1, #8b5cf6);
                                        border-radius: 50%;
                                        display: flex;
                                        align-items: center;
                                        justify-content: center;
                                        color: white;
                                        font-weight: 600;
                                        font-size: 16px;
                                    ">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div>
                                        <div style="font-weight: 600; color: #1e293b;">
                                            <?php echo htmlspecialchars($row['username']); ?>
                                        </div>
                                        <div style="font-size: 12px; color: #64748b;">
                                            <i class="fas fa-calendar-alt"></i>
                                            Joined recently
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if($row['userrole'] == 'admin'): ?>
                                <span class="badge badge-primary">
                                    <i class="fas fa-crown"></i>
                                    Administrator
                                </span>
                                <?php else: ?>
                                <span class="badge badge-success">
                                    <i class="fas fa-taxi"></i>
                                    Driver
                                </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 10px;">
                                    <a href="?delete=<?php echo $row['userid']; ?>" 
                                       class="btn"
                                       onclick="return confirm('Delete user <?php echo htmlspecialchars($row['username']); ?>?')"
                                       style="background: #ef4444; padding: 8px 16px; font-size: 14px;">
                                        <i class="fas fa-trash"></i>
                                        Delete
                                    </a>
                                    <button class="btn" style="padding: 8px 16px; font-size: 14px; background: #3b82f6;"
                                            onclick="editUser(<?php echo $row['userid']; ?>)">
                                        <i class="fas fa-edit"></i>
                                        Edit
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
    // Simple password toggle function
    function togglePassword(inputId) {
        const passwordInput = document.getElementById(inputId);
        const toggleButton = document.querySelector(`[onclick="togglePassword('${inputId}')"]`);
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
    
    function editUser(userId) {
        showToast('<i class="fas fa-info-circle"></i> Edit feature coming soon!', 'info');
    }
    
    // Update user count
    document.addEventListener('DOMContentLoaded', function() {
        const userCount = document.querySelectorAll('tbody tr').length;
        const countElement = document.createElement('div');
        countElement.innerHTML = `<i class="fas fa-users"></i> ${userCount} users`;
        countElement.style.cssText = `
            background: #f1f5f9;
            color: #475569;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-left: 15px;
        `;
        
        const header = document.querySelector('.card h3');
        if (header) {
            header.appendChild(countElement);
        }
        
        // Add focus effects
        const inputs = document.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.style.borderColor = '#6366f1';
                this.style.boxShadow = '0 0 0 4px rgba(99, 102, 241, 0.1)';
            });
            
            input.addEventListener('blur', function() {
                this.style.borderColor = '#e2e8f0';
                this.style.boxShadow = 'none';
            });
        });
    });
    </script>
</body>
</html>