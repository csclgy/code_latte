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

// Auto-set result_date when Hired or Rejected
$result_date = $data['result_date'] ?? null;
if (in_array($data['application_status'] ?? '', ['Hired', 'Rejected']) && empty($result_date)) {
    $result_date = date('Y-m-d');
}

try {
    $stmt = $pdo->prepare("
        UPDATE applicant_tbl SET
            f_name             = :f_name,
            l_name             = :l_name,
            m_name             = :m_name,
            email              = :email,
            vacancy_id         = :vacancy_id,
            application_date   = :application_date,
            application_status = :application_status,
            result_date        = :result_date,
            remarks            = :remarks
        WHERE applicant_id = :applicant_id
    ");

    $stmt->execute([
        ':f_name'             => $data['f_name'],
        ':l_name'             => $data['l_name'],
        ':m_name'             => $data['m_name']             ?? null,
        ':email'              => $data['email']              ?? null,
        ':vacancy_id'         => $data['vacancy_id']         ?? null,
        ':application_date'   => $data['application_date'],
        ':application_status' => $data['application_status'] ?? 'Applied',
        ':result_date'        => $result_date,
        ':remarks'            => $data['remarks']            ?? null,
        ':applicant_id'       => $data['applicant_id'],
    ]);

    ob_clean();
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>