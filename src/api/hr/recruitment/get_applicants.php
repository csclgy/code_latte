<?php
ob_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../../config/db.php';

try {
    $stmt = $pdo->query("
    SELECT 
        a.applicant_id,
        a.f_name,
        a.l_name,
        a.m_name,
        a.email,
        a.pos_id,
        a.application_date,
        a.application_status,
        a.result_date,
        a.remarks,
        a.documents_submitted,
        a.interviewed_by,        -- ← add
        p.pos_name
    FROM applicant_tbl a
    LEFT JOIN position_tbl p ON a.pos_id = p.pos_id
    ORDER BY a.application_date DESC
");
    $applicants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $applicants]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>