<?php
// src/api/hr/payroll/get_payroll.php
// Returns payroll records for HR view with payroll_status

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../../../config/db.php';

try {
    $params = [];
    $where = ["1=1"]; // Base condition
    
    // Optional filters
    if (!empty($_GET['period_start'])) {
        $where[] = "p.payperiod_start >= :period_start";
        $params[':period_start'] = $_GET['period_start'];
    }
    if (!empty($_GET['period_end'])) {
        $where[] = "p.payperiod_end <= :period_end";
        $params[':period_end'] = $_GET['period_end'];
    }
    if (!empty($_GET['emp_id'])) {
        $where[] = "p.emp_id = :emp_id";
        $params[':emp_id'] = (int)$_GET['emp_id'];
    }
    // Filter by payroll_status (from payroll_tbl)
    if (!empty($_GET['payroll_status'])) {
        $where[] = "p.payroll_status = :payroll_status";
        $params[':payroll_status'] = $_GET['payroll_status'];
    }
    
    $sql = "
        SELECT 
            p.payroll_id,
            p.emp_id,
            CONCAT(e.emp_fname, ' ', e.emp_lname) as emp_name,
            d.dept_name,
            pos.pos_name,
            p.payperiod_start,
            p.payperiod_end,
            p.base_salary,
            p.bonus,
            p.overtime,
            p.deduction_total,
            p.deductions_label,
            p.net_salary,
            p.payroll_status,
            fa.approval_status as fa_status
        FROM payroll_tbl p
        JOIN employee_tbl e ON p.emp_id = e.emp_id
        LEFT JOIN department_tbl d ON e.dept_id = d.dept_id
        LEFT JOIN position_tbl pos ON e.pos_id = pos.pos_id
        LEFT JOIN fa_payroll_approval fa ON p.payroll_id = fa.payroll_id
        WHERE " . implode(" AND ", $where) . "
        ORDER BY p.payperiod_start DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'records' => $records
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}