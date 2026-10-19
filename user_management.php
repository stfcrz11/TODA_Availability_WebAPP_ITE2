<?php
require_once 'config.php';
requireAdmin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!checkCsrf()) {
        $error = "Your session expired. Please try again.";

    // Handle Add User
    } elseif (isset($_POST['add'])) {
        $username   = trim($_POST['username']);
        $password   = $_POST['password'];
        $role       = $_POST['role'];
        $fullname   = trim(isset($_POST['fullname']) ? $_POST['fullname'] : '');
        $contactno  = trim(isset($_POST['contactno']) ? $_POST['contactno'] : '');
        $address    = trim(isset($_POST['address']) ? $_POST['address'] : '');
        $tricycleid = isset($_POST['tricycleid']) ? (int)$_POST['tricycleid'] : 0;

        if ($username === '' || $password === '') {
            $error = "Username and password are required.";
        } elseif (!in_array($role, array('admin', 'driver'), true)) {
            $error = "Invalid role.";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters.";
        } elseif ($role === 'driver' && ($fullname === '' || $tricycleid <= 0)) {
            $error = "Drivers need a full name and an assigned tricycle.";
        } elseif (qRow("SELECT userid FROM users WHERE username=?", array($username))) {
            $error = "Username already exists!";
        } else {
            mysqli_begin_transaction($conn);
            $ok = q("INSERT INTO users (username, password, userrole) VALUES (?, ?, ?)",
                    array($username, password_hash($password, PASSWORD_DEFAULT), $role));
            if ($ok && $role === 'driver') {
                $userid = mysqli_insert_id($conn);
                $ok = q("INSERT INTO driver (userid, fullname, contactno, address, tricycleid) VALUES (?, ?, ?, ?, ?)",
                        array($userid, $fullname, $contactno, $address, $tricycleid));
                if ($ok) {
                    $ok = q("INSERT INTO availability (driverid, status, last_updated) VALUES (?, 'offline', NOW())",
                            array(mysqli_insert_id($conn)));
                }
            }
            if ($ok) {
                mysqli_commit($conn);
                $success = "User added successfully!";
            } else {
                mysqli_rollback($conn);
                $error = "Could not add user: " . mysqli_error($conn);
            }
        }

    // Handle Delete User
    } elseif (isset($_POST['delete'])) {
        $id = (int)$_POST['delete'];
        if ($id === (int)$_SESSION['user_id']) {
            $error = "You cannot delete your own account.";
        } else {
            // driver_status_log and printlogs have no ON DELETE CASCADE, so clear them first
            mysqli_begin_transaction($conn);
            $ok = q("DELETE l FROM driver_status_log l INNER JOIN driver d ON l.driverid = d.driverid WHERE d.userid=?", array($id))
               && q("DELETE FROM printlogs WHERE printed_by=?", array($id))
               && q("DELETE FROM users WHERE userid=?", array($id));
            if ($ok) {
                mysqli_commit($conn);
                $success = "User deleted.";
            } else {
                mysqli_rollback($conn);
                $error = "Could not delete user: " . mysqli_error($conn);
            }
        }
    }
}

$users = q("
    SELECT u.userid, u.username, u.userrole, d.fullname, t.plate_no
    FROM users u
    LEFT JOIN driver d ON d.driverid = (SELECT MIN(d2.driverid) FROM driver d2 WHERE d2.userid = u.userid)
    LEFT JOIN tricycle t ON t.tricycleid = d.tricycleid
    ORDER BY u.userid ASC");
$userCount = ($users instanceof mysqli_result) ? mysqli_num_rows($users) : 0;

$tricycles = q("SELECT tricycleid, plate_no, color FROM tricycle ORDER BY plate_no");

$activePage = 'users';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Management - TODA System</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="assets/style.css">
<script src="assets/script.js" defer></script>
<style>
    .field-wrapper {
        position: relative;
        width: 100%;
    }

    .field-wrapper input,
    .field-wrapper select {
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
    }

    .field-wrapper input:focus,
    .field-wrapper select:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }

    .field-wrapper .input-icon {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: #64748b;
        font-size: 18px;
        z-index: 1;
        pointer-events: none;
    }

    .field-wrapper .toggle-password {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #64748b;
        cursor: pointer;
        font-size: 18px;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        z-index: 1;
    }

    .field-wrapper .toggle-password:hover {
        color: #6366f1;
        background: #f1f5f9;
    }

    .count-pill {
        background: #f1f5f9;
        color: #475569;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-left: 15px;
    }
</style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="content-header">
            <h2><i class="fas fa-user-tie"></i> User Management</h2>
            <p>Manage system users and their permissions</p>
        </div>

        <div class="card">
            <h3><i class="fas fa-user-plus"></i> Add New User</h3>

            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="alert-icon fas fa-exclamation-circle"></i>
                <span><?php echo h($error); ?></span>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="alert-icon fas fa-check-circle"></i>
                <span><?php echo h($success); ?></span>
            </div>
            <?php endif; ?>

            <form method="post" style="max-width: 500px;">
                <input type="hidden" name="csrf" value="<?php echo csrfToken(); ?>">

                <div class="form-group">
                    <label for="username"><i class="fas fa-user"></i> Username</label>
                    <div class="field-wrapper">
                        <input type="text" name="username" id="username" required placeholder="Enter username">
                        <span class="input-icon"><i class="fas fa-user"></i></span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password"><i class="fas fa-key"></i> Password</label>
                    <div class="field-wrapper">
                        <input type="password" name="password" id="password" required minlength="6" placeholder="At least 6 characters">
                        <span class="input-icon"><i class="fas fa-key"></i></span>
                        <button type="button" class="toggle-password" data-tooltip="Show password"><i class="fas fa-eye"></i></button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="role"><i class="fas fa-user-tag"></i> User Role</label>
                    <div class="field-wrapper">
                        <select name="role" id="role" required>
                            <option value="driver">Driver</option>
                            <option value="admin">Administrator</option>
                        </select>
                        <span class="input-icon"><i class="fas fa-user-tag"></i></span>
                    </div>
                </div>

                <div id="driver-fields">
                    <div class="form-group">
                        <label for="fullname"><i class="fas fa-id-card"></i> Driver Full Name</label>
                        <div class="field-wrapper">
                            <input type="text" name="fullname" id="fullname" placeholder="e.g. Juan Dela Cruz">
                            <span class="input-icon"><i class="fas fa-id-card"></i></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="contactno"><i class="fas fa-phone"></i> Contact No.</label>
                        <div class="field-wrapper">
                            <input type="text" name="contactno" id="contactno" placeholder="09XXXXXXXXX">
                            <span class="input-icon"><i class="fas fa-phone"></i></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="address"><i class="fas fa-map-marker-alt"></i> Address</label>
                        <div class="field-wrapper">
                            <input type="text" name="address" id="address" placeholder="Barangay, City">
                            <span class="input-icon"><i class="fas fa-map-marker-alt"></i></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="tricycleid"><i class="fas fa-car"></i> Tricycle</label>
                        <div class="field-wrapper">
                            <select name="tricycleid" id="tricycleid">
                                <?php while ($tricycles && $t = mysqli_fetch_assoc($tricycles)): ?>
                                <option value="<?php echo (int)$t['tricycleid']; ?>"><?php echo h($t['plate_no'] . ($t['color'] ? ' (' . $t['color'] . ')' : '')); ?></option>
                                <?php endwhile; ?>
                            </select>
                            <span class="input-icon"><i class="fas fa-car"></i></span>
                        </div>
                    </div>
                </div>

                <button type="submit" name="add" class="btn">
                    <i class="fas fa-user-plus"></i>
                    <span>Add User</span>
                </button>
            </form>
        </div>

        <div class="card">
            <h3><i class="fas fa-users"></i> All Users <span class="count-pill"><i class="fas fa-users"></i> <?php echo $userCount; ?> users</span></h3>
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
                        <?php while ($users && $row = mysqli_fetch_assoc($users)): ?>
                        <tr>
                            <td><strong>#<?php echo (int)$row['userid']; ?></strong></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 44px; height: 44px; background: linear-gradient(135deg, #6366f1, #8b5cf6); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 16px;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div>
                                        <div style="font-weight: 600; color: #1e293b;"><?php echo h($row['username']); ?></div>
                                        <div style="font-size: 12px; color: #64748b;">
                                            <?php if ($row['fullname']): ?>
                                                <i class="fas fa-id-card"></i> <?php echo h($row['fullname']); ?>
                                                <?php if ($row['plate_no']): ?> · <i class="fas fa-car"></i> <?php echo h($row['plate_no']); ?><?php endif; ?>
                                            <?php elseif ($row['userrole'] === 'driver'): ?>
                                                <i class="fas fa-exclamation-triangle"></i> No driver profile
                                            <?php else: ?>
                                                <i class="fas fa-crown"></i> System account
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if ($row['userrole'] === 'admin'): ?>
                                <span class="badge badge-primary"><i class="fas fa-crown"></i> Administrator</span>
                                <?php else: ?>
                                <span class="badge badge-success"><i class="fas fa-taxi"></i> Driver</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ((int)$row['userid'] === (int)$_SESSION['user_id']): ?>
                                    <span style="color: #64748b; font-size: 14px;">(you)</span>
                                <?php else: ?>
                                <form method="post" style="display: inline;" onsubmit="return confirm('Delete user <?php echo h(addslashes($row['username'])); ?>? This also removes their driver profile and trip logs.');">
                                    <input type="hidden" name="csrf" value="<?php echo csrfToken(); ?>">
                                    <button type="submit" name="delete" value="<?php echo (int)$row['userid']; ?>" class="btn" style="background: #ef4444; padding: 8px 16px; font-size: 14px;">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const role = document.getElementById('role');
        const driverFields = document.getElementById('driver-fields');
        const fullname = document.getElementById('fullname');

        function syncDriverFields() {
            const isDriver = role.value === 'driver';
            driverFields.style.display = isDriver ? '' : 'none';
            fullname.required = isDriver;
        }
        role.addEventListener('change', syncDriverFields);
        syncDriverFields();
    });
    </script>
</body>
</html>
