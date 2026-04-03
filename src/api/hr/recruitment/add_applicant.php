<?php
ob_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../../config/db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['f_name']) || empty($data['l_name'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'First and last name are required.']);
    exit;
}
if (empty($data['application_date'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Application date is required.']);
    exit;
}

// Check duplicate email
if (!empty($data['email'])) {
    $check = $pdo->prepare("SELECT applicant_id FROM applicant_tbl WHERE email = ?");
    $check->execute([$data['email']]);
    if ($check->fetch()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'error' => 'An applicant with this email already exists.']);
        exit;
    }
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO applicant_tbl (
            f_name, l_name, m_name,
            email, vacancy_id,
            application_date, application_status,
            result_date, remarks
        ) VALUES (
            :f_name, :l_name, :m_name,
            :email, :vacancy_id,
            :application_date, :application_status,
            :result_date, :remarks
        )
    ");

    $stmt->execute([
        ':f_name'             => $data['f_name'],
        ':l_name'             => $data['l_name'],
        ':m_name'             => $data['m_name']             ?? null,
        ':email'              => $data['email']              ?? null,
        ':vacancy_id'         => $data['vacancy_id']         ?? null,
        ':application_date'   => $data['application_date'],
        ':application_status' => $data['application_status'] ?? 'Applied',
        ':result_date'        => $data['result_date']        ?? null,
        ':remarks'            => $data['remarks']            ?? null,
    ]);

    ob_clean();
    echo json_encode(['success' => true, 'applicant_id' => $pdo->lastInsertId()]);
} catch (PDOException $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>