<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
require_once __DIR__ . '/../../../config/db.php';

try {
    $stmt = $pdo->query("
        SELECT 
            emp_id,
            Emp_fname    AS emp_fname,
            Emp_lname    AS emp_lname,
            emp_mname,
            emp_email,
            Emp_contact  AS emp_contact,
            emp_address,
            emp_age,
            emp_date_hired,
            emp_status,
            Emp_schedule    AS emp_schedule,
            Emp_working_hours AS emp_working_hours,
            User_name    AS emp_username,
            dept_id,
            Pos_id       AS pos_id
        FROM employee_tbl 
        ORDER BY emp_id ASC
    ");
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $employees]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>