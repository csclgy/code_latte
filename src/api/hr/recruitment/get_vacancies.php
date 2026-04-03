<?php
ob_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../../config/db.php';

try {
    $stmt = $pdo->query("
        SELECT vacancy_id, job_title, vac_status
        FROM jobvac_tbl
        WHERE vac_status = 'Open'
        ORDER BY job_title ASC
    ");
    $vacancies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $vacancies]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>