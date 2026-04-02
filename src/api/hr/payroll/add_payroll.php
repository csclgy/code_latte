<?php
// src/api/hr/payroll/add_payroll.php
// Inserts a new payroll record into payroll_tbl.
// Net salary is computed server-side to prevent client tampering.
// payroll_status is always set to 'Draft' on creation.
// Finance approval is handled separately via submit_payroll.php.

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed. Use POST.']);
    exit;
}

require_once '../../../config/db.php';

// Accept JSON body or form data
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) $input = $_POST;

// ── REQUIRED FIELDS ──────────────────────────────────────────
$required = ['emp_id', 'payperiod_start', 'payperiod_end', 'base_salary'];
foreach ($required as $field) {
    if (empty($input[$field]) && $input[$field] !== '0') {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => "Missing required field: {$field}"]);
        exit;
    }
}

// ── SANITIZE & CAST ──────────────────────────────────────────
$emp_id           = (int)   $input['emp_id'];
$payperiod_start  =         trim($input['payperiod_start']);
$payperiod_end    =         trim($input['payperiod_end']);
$pay_date         = isset($input['pay_date'])        ? trim($input['pay_date'])        : null;
$base_salary      = (float) $input['base_salary'];
$bonus            = isset($input['bonus'])           ? (float)$input['bonus']          : 0;
$overtime         = isset($input['overtime'])        ? trim($input['overtime'])         : '0';
$deductions_label = isset($input['deductions_label'])? trim($input['deductions_label']): '';
$deduction_total  = isset($input['deduction_total']) ? (float)$input['deduction_total']: 0;

// ── SERVER-SIDE NET SALARY CALCULATION ───────────────────────
// net_salary = base_salary + bonus + overtime - deduction_total
// overtime is stored as varchar(255) in payroll_tbl (your schema),
// so we cast to float for calculation only.
$overtime_amount = (float) $overtime;
$net_salary      = max(0, $base_salary + $bonus + $overtime_amount - $deduction_total);

// ── VALIDATE EMP_ID EXISTS ───────────────────────────────────
try {
    $chk = $pdo->prepare("SELECT emp_id FROM employee_tbl WHERE emp_id = :emp_id LIMIT 1");
    $chk->execute([':emp_id' => $emp_id]);
    if (!$chk->fetch()) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Employee not found.']);
        exit;
    }

    // ── INSERT ────────────────────────────────────────────────
    $stmt = $pdo->prepare("
        INSERT INTO payroll_tbl (
            emp_id,
            payperiod_start,
            payperiod_end,
            base_salary,
            bonus,
            overtime,
            deductions_label,
            deduction_total,
            net_salary,
            payroll_status
        ) VALUES (
            :emp_id,
            :payperiod_start,
            :payperiod_end,
            :base_salary,
            :bonus,
            :overtime,
            :deductions_label,
            :deduction_total,
            :net_salary,
            'Draft'
        )
    ");

    $stmt->execute([
        ':emp_id'           => $emp_id,
        ':payperiod_start'  => $payperiod_start,
        ':payperiod_end'    => $payperiod_end,
        ':base_salary'      => $base_salary,
        ':bonus'            => $bonus,
        ':overtime'         => $overtime,
        ':deductions_label' => $deductions_label,
        ':deduction_total'  => $deduction_total,
        ':net_salary'       => $net_salary,
    ]);

    $new_id = $pdo->lastInsertId();

    echo json_encode([
        'status'     => 'success',
        'message'    => 'Payroll record created successfully.',
        'payroll_id' => (int)$new_id,
        'net_salary' => $net_salary
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}