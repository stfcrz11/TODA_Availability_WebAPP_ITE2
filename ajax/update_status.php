<?php
// ajax/update_status.php - Driver changes their own availability
require_once '../config.php';
header('Content-Type: application/json');

function fail($code, $message) {
    http_response_code($code);
    echo json_encode(array('success' => false, 'error' => $message));
    exit;
}

if (!isset($_SESSION['user_id'])) fail(401, 'Not logged in');
if (!isDriver()) fail(403, 'Only drivers can update status');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail(405, 'POST required');

$status = isset($_POST['status']) ? $_POST['status'] : '';
if (!isset($STATUSES[$status])) fail(400, 'Invalid status');

$driver = getDriverByUser($_SESSION['user_id']);
if (!$driver) fail(404, 'No driver profile is linked to this account');

if (!setDriverStatus($driver, $status)) fail(500, 'Database error');

echo json_encode(array(
    'success' => true,
    'status'  => $status,
    'label'   => $STATUSES[$status]['label'],
    'badge'   => $STATUSES[$status]['badge'],
));
