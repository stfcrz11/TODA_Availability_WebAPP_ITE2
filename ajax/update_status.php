<?php
require_once '../config.php';
checkLogin();
if($_SESSION['userrole']!='driver') exit;

if(isset($_POST['status'])){
    $status = e($_POST['status']);
    mysql_query("UPDATE driver SET status='$status' WHERE userid=".$_SESSION['userid']);
    echo json_encode(['success'=>true]);
}
?>
