<?php
// config.php - Add redirect after login
session_start();

define('DB_HOST','127.0.0.1');
define('DB_USER','root');
define('DB_PASS','');
define('DB_NAME','toda_system');

$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
if(!$link){
    die('DB connection error');
}

mysql_select_db(DB_NAME) or die('DB select error');

// Secure input
function e($str){
    return htmlspecialchars(mysql_real_escape_string($str));
}

// Login check
function checkLogin(){
    if(!isset($_SESSION['user_id'])){
        header("Location: login.php");
        exit;
    }
}

// Admin check
function isAdmin(){
    return (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
}

// Redirect to passenger view after login
function redirectAfterLogin(){
    header("Location: index.php");
    exit;
}