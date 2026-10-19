<?php
// ajax/fetch_drivers.php - Public list of drivers whose latest status is "available"
require_once '../config.php';
header('Content-Type: application/json');

$result = q("
    SELECT d.fullname, d.contactno, t.plate_no, a.status, a.last_updated
    FROM driver d
    " . LATEST_STATUS_JOIN . "
    LEFT JOIN tricycle t ON t.tricycleid = d.tricycleid
    WHERE a.status = 'available'
    ORDER BY d.fullname ASC
");

if (!($result instanceof mysqli_result)) {
    http_response_code(500);
    echo json_encode(array('error' => 'DB query failed: ' . mysqli_error($conn)));
    exit;
}

$data = array();
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}
echo json_encode($data);
