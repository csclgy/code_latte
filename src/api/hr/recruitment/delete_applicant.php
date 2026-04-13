<?php
ob_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../../config/db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['applicant_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing applicant_id.']);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM applicant_tbl WHERE applicant_id = ?");
    $stmt->execute([$data['applicant_id']]);
    ob_clean();
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>