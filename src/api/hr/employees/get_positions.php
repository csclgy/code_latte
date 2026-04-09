<?php
ob_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../../config/db.php';

try {
    $stmt = $pdo->query("
        SELECT pos_id, pos_name 
        FROM position_tbl 
        WHERE pos_status = 'Active'
        ORDER BY pos_name ASC
    ");
    $positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $positions]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>