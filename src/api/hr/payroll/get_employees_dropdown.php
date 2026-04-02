<?php
// src/api/hr/payroll/get_employees_dropdown.php
// Returns active employees for the <select> dropdown in the payroll modal.
// Only returns employees with emp_status = 'Active' (or all if ?all=1).
// Reuses the same endpoint pattern as src/api/hr/attendance/get_employees_dropdown.php
// but scoped for payroll use (includes working hours and position info).

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../../../config/db.php';

try {
    $all = isset($_GET['all']) && $_GET['all'] == '1';

    $sql = "
        SELECT
            e.emp_id,
            e.emp_fname,
            e.emp_mname,
            e.emp_lname,
            e.emp_email,
            e.emp_status,
            e.emp_working_hours,
            e.emp_schedule,
            d.dept_name,
            p.pos_name
        FROM employee_tbl e
        LEFT JOIN department_tbl d ON e.dept_id = d.dept_id
        LEFT JOIN position_tbl   p ON e.pos_id  = p.post_id
    ";

    if (!$all) {
        $sql .= " WHERE e.emp_status = 'Active'";
    }

    $sql .= " ORDER BY e.emp_lname ASC, e.emp_fname ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format for dropdown: full name + metadata
    $formatted = array_map(function ($e) {
        return [
            'emp_id'         => (int) $e['emp_id'],
            'full_name'      => trim($e['emp_fname'] . ' ' . $e['emp_mname'] . ' ' . $e['emp_lname']),
            'emp_email'      => $e['emp_email'],
            'emp_status'     => $e['emp_status'],
            'working_hours'  => (int) $e['emp_working_hours'],
            'schedule'       => $e['emp_schedule'],
            'department'     => $e['dept_name']  ?? '—',
            'position'       => $e['pos_name']   ?? '—',
        ];
    }, $employees);

    echo json_encode([
        'status'    => 'success',
        'count'     => count($formatted),
        'employees' => $formatted
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}