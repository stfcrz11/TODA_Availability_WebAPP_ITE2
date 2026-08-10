<?php
require_once '../config.php'; // Adjust path if needed
header('Content-Type: application/json');

// Make sure session is started
if(session_status() == PHP_SESSION_NONE) session_start();

$result = mysql_query("
    SELECT fullname, status
    FROM driver
    WHERE status='available'
    ORDER BY fullname ASC
");

$data = array();

if($result){
    while($row = mysql_fetch_assoc($result)){
        $data[] = $row;
    }
    echo json_encode($data);
} else {
    // If query fails, return empty array + error message
    echo json_encode([
        'error' => 'DB query failed: ' . mysql_error()
    ]);
}
