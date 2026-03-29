<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../config/db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['emp_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing emp_id.']);
    exit;
}

try {
    // If password is provided, update it too — otherwise leave it unchanged
    if (!empty($data['user_password'])) {
        $stmt = $pdo->prepare("
            UPDATE employee_tbl SET
                emp_fname         = :fname,
                emp_lname         = :lname,
                emp_mname         = :mname,
                emp_email         = :email,
                emp_contact       = :contact,
                emp_address       = :address,
                emp_age           = :age,
                dept_id           = :dept_id,
                pos_id            = :pos_id,
                emp_date_hired    = :date_hired,
                emp_status        = :status,
                emp_schedule      = :schedule,
                emp_working_hours = :working_hours,
                user_name         = :username,
                user_password     = :password
            WHERE emp_id = :emp_id
        ");
        $stmt->bindValue(':password', password_hash($data['user_password'], PASSWORD_DEFAULT));
    } else {
        $stmt = $pdo->prepare("
            UPDATE employee_tbl SET
                emp_fname         = :fname,
                emp_lname         = :lname,
                emp_mname         = :mname,
                emp_email         = :email,
                emp_contact       = :contact,
                emp_address       = :address,
                emp_age           = :age,
                dept_id           = :dept_id,
                pos_id            = :pos_id,
                emp_date_hired    = :date_hired,
                emp_status        = :status,
                emp_schedule      = :schedule,
                emp_working_hours = :working_hours,
                user_name         = :username
            WHERE emp_id = :emp_id
        ");
    }

    $stmt->bindValue(':fname',         $data['emp_fname']);
    $stmt->bindValue(':lname',         $data['emp_lname']);
    $stmt->bindValue(':mname',         $data['emp_mname']         ?? '');
    $stmt->bindValue(':email',         $data['emp_email']);
    $stmt->bindValue(':contact',       $data['Emp_contact']       ?? '');
    $stmt->bindValue(':address',       $data['emp_address']       ?? '');
    $stmt->bindValue(':age',           $data['emp_age']           ?? null);
    $stmt->bindValue(':dept_id',       $data['dept_id']);
    $stmt->bindValue(':pos_id',        $data['pos_id']);
    $stmt->bindValue(':date_hired',    $data['emp_date_hired']    ?? null);
    $stmt->bindValue(':status',        $data['emp_status']        ?? 'Active');
    $stmt->bindValue(':schedule',      $data['emp_schedule']      ?? 'Morning');
    $stmt->bindValue(':working_hours', $data['emp_working_hours'] ?? 8);
    $stmt->bindValue(':username',      $data['user_name']);
    $stmt->bindValue(':emp_id',        $data['emp_id']);

    $stmt->execute();
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>