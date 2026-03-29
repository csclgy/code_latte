<?php
ob_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../../config/db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['attendance_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing attendance_id.']);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM attendance_tbl WHERE attendance_id = ?");
    $stmt->execute([$data['attendance_id']]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>