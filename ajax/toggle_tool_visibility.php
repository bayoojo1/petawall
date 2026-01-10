<?php
session_start();
require_once __DIR__ . '/../classes/ToolsManagement.php';

// Only admin
// if (empty($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
//     http_response_code(403);
//     echo json_encode(['success' => false]);
//     exit;
// }

if (empty($_POST['tool_name'])) {
    http_response_code(400);
    echo json_encode(['success' => false]);
    exit;
}

$toolName = $_POST['tool_name'];

$toolManagement = new ToolsManagement();
$success = $toolManagement->updateToolVisibility($toolName);

echo json_encode(['success' => $success]);
