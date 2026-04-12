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
    // ── Step 1: Get applicant data ──
    $stmt = $pdo->prepare("
        SELECT 
            a.applicant_id,
            a.f_name,
            a.l_name,
            a.m_name,
            a.email,
            a.pos_id,
            a.application_date
        FROM applicant_tbl a
        WHERE a.applicant_id = ?
    ");
    $stmt->execute([$data['applicant_id']]);
    $applicant = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$applicant) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Applicant not found.']);
        exit;
    }

    // ── Step 2: Check if already in employee_tbl ──
    if (!empty($applicant['email'])) {
        $check = $pdo->prepare("SELECT emp_id FROM employee_tbl WHERE emp_email = ?");
        $check->execute([$applicant['email']]);
        if ($check->fetch()) {
            $updateStmt = $pdo->prepare("
                UPDATE applicant_tbl 
                SET application_status = 'Hired', result_date = ?
                WHERE applicant_id = ?
            ");
            $updateStmt->execute([date('Y-m-d'), $data['applicant_id']]);
            ob_clean();
            echo json_encode([
                'success'        => true,
                'message'        => 'Status updated. Employee already exists in the system.',
                'already_exists' => true,
            ]);
            exit;
        }
    }

    // ── Step 3: Generate default username ──
    $defaultUsername = strtolower(
        substr($applicant['f_name'], 0, 1) .
        str_replace(' ', '', $applicant['l_name'])
    );

    // check if username already exists, append number if so
    $usernameCheck = $pdo->prepare("SELECT emp_id FROM employee_tbl WHERE user_name = ?");
    $usernameCheck->execute([$defaultUsername]);
    if ($usernameCheck->fetch()) {
        $defaultUsername = $defaultUsername . rand(10, 99);
    }

    $defaultPassword = password_hash($defaultUsername, PASSWORD_DEFAULT);

    // ── Step 4: Insert into employee_tbl with ALL columns ──
    $insertStmt = $pdo->prepare("
        INSERT INTO employee_tbl (
            emp_fname,
            emp_lname,
            emp_mname,
            emp_email,
            dept_id,
            pos_id,
            emp_address,
            emp_contact,
            emp_date_hired,
            emp_status,
            emp_age,
            emp_working_hours,
            emp_schedule,
            user_name,
            user_password,
            emp_basic_salary,
            emp_contactemergency,
            emp_contactemergencynum,
            emp_SSS,
            emp_TIN,
            emp_Philhealth,
            emp_Pagibig,
            emp_type
        ) VALUES (
            :emp_fname,
            :emp_lname,
            :emp_mname,
            :emp_email,
            :dept_id,
            :pos_id,
            :emp_address,
            :emp_contact,
            :emp_date_hired,
            :emp_status,
            :emp_age,
            :emp_working_hours,
            :emp_schedule,
            :user_name,
            :user_password,
            :emp_basic_salary,
            :emp_contactemergency,
            :emp_contactemergencynum,
            :emp_SSS,
            :emp_TIN,
            :emp_Philhealth,
            :emp_Pagibig,
            :emp_type
        )
    ");

    $insertStmt->execute([
        ':emp_fname'                => $applicant['f_name'],
        ':emp_lname'                => $applicant['l_name'],
        ':emp_mname'                => $applicant['m_name']  ?? '',
        ':emp_email'                => $applicant['email']   ?? '',
        ':dept_id'                  => $data['dept_id']      ?? 1,
        ':pos_id'                   => $applicant['pos_id']  ?? null,
        ':emp_address'              => '',
        ':emp_contact'              => '',
        ':emp_date_hired'           => date('Y-m-d'),
        ':emp_status'               => 'Active',
        ':emp_age'                  => 0,
        ':emp_working_hours'        => 8,
        ':emp_schedule'             => 'Morning',
        ':user_name'                => $defaultUsername,
        ':user_password'            => $defaultPassword,
        ':emp_basic_salary'         => 0.00,
        ':emp_contactemergency'     => '',
        ':emp_contactemergencynum'  => 0,
        ':emp_SSS'                  => '',
        ':emp_TIN'                  => '',
        ':emp_Philhealth'           => '',
        ':emp_Pagibig'              => '',
        ':emp_type'                 => 'Regular',
    ]);

    $newEmpId = $pdo->lastInsertId();

    // ── Step 5: Update applicant status to Hired ──
    $updateStmt = $pdo->prepare("
        UPDATE applicant_tbl 
        SET application_status = 'Hired', result_date = ?
        WHERE applicant_id = ?
    ");
    $updateStmt->execute([date('Y-m-d'), $data['applicant_id']]);

    ob_clean();
    echo json_encode([
        'success'          => true,
        'message'          => 'Applicant hired and added to employee list!',
        'emp_id'           => $newEmpId,
        'default_username' => $defaultUsername,
        'default_password' => $defaultUsername,
        'already_exists'   => false,
    ]);

} catch (PDOException $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>