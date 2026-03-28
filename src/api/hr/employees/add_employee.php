<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../config/db.php';

$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required = ['emp_fname', 'emp_lname', 'emp_email', 'dept_id', 'pos_id', 'user_name', 'user_password'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => "Missing required field: $field"]);
        exit;
    }
}

// Check if username already exists
$check = $pdo->prepare("SELECT emp_id FROM employee_tbl WHERE user_name = ?");
$check->execute([$data['user_name']]);
if ($check->fetch()) {
    http_response_code(409);
    echo json_encode(['success' => false, 'error' => 'Username already exists.']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO employee_tbl (
            emp_fname, emp_lname, emp_mname, emp_email,
            emp_contact, emp_address, emp_age,
            dept_id, pos_id,
            emp_date_hired, emp_status,
            emp_schedule, emp_working_hours,
            user_name, user_password
        ) VALUES (
            :fname, :lname, :mname, :email,
            :contact, :address, :age,
            :dept_id, :pos_id,
            :date_hired, :status,
            :schedule, :working_hours,
            :username, :password
        )
    ");

    $stmt->execute([
        ':fname'         => $data['emp_fname'],
        ':lname'         => $data['emp_lname'],
        ':mname'         => $data['emp_mname']        ?? '',
        ':email'         => $data['emp_email'],
        ':contact'       => $data['emp_contact']      ?? '',
        ':address'       => $data['emp_address']      ?? '',
        ':age'           => $data['emp_age']          ?? null,
        ':dept_id'       => $data['dept_id'],
        ':pos_id'        => $data['pos_id'],
        ':date_hired'    => $data['emp_date_hired']   ?? null,
        ':status'        => $data['emp_status']       ?? 'Active',
        ':schedule'      => $data['emp_schedule']     ?? 'Morning',
        ':working_hours' => $data['emp_working_hours'] ?? 8,
        ':username'      => $data['user_name'],
        ':password'      => password_hash($data['user_password'], PASSWORD_DEFAULT),
    ]);

    echo json_encode(['success' => true, 'emp_id' => $pdo->lastInsertId()]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>