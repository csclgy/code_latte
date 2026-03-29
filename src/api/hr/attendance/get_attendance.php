<?php
ob_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../../config/db.php';

try {
    $stmt = $pdo->query("
        SELECT 
            a.attendance_id,
            a.emp_id,
            a.attendance_date,
            a.time_in,
            a.time_out,
            a.late_minutes,
            a.remarks,
            e.emp_fname      AS emp_fname,
            e.emp_lname      AS emp_lname,
            e.emp_schedule   AS emp_schedule,
            e.pos_id         AS pos_id,
            CASE e.Pos_id
                WHEN 1 THEN 'Barista'
                WHEN 2 THEN 'Cashier'
                WHEN 3 THEN 'Kitchen Staff'
                WHEN 4 THEN 'Supervisor'
                ELSE '—'
            END AS emp_role,
            CASE 
                WHEN l.leave_id IS NOT NULL AND l.leave_status = 'Approved' THEN 'On Leave'
                ELSE a.status
            END AS status
        FROM attendance_tbl a
        JOIN employee_tbl e ON a.emp_id = e.emp_id
        LEFT JOIN leave_tbl l 
            ON a.emp_id = l.emp_id 
            AND a.attendance_date BETWEEN l.date_start AND l.date_end
        ORDER BY a.attendance_date DESC
    ");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $records]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>