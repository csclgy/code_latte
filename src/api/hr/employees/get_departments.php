<?php
ob_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../../config/db.php';

try {
    $stmt = $pdo->query("
        SELECT dept_id, dept_name 
        FROM department_tbl 
        WHERE dept_status = 'Active'
        ORDER BY dept_name ASC
    ");
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $departments]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>