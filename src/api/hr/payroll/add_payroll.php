<?php
// src/api/hr/payroll/add_payroll.php
// Inserts a new payroll record into payroll_tbl.
// Accepts individual deduction columns: sss, philhealth, pag-ibig, tax
// deduction_total and net_salary are computed SERVER-SIDE.
// payroll_status is always 'Draft' on creation.

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed. Use POST.']);
    exit;
}

require_once '../../../config/db.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) $input = $_POST;

// ── REQUIRED FIELDS ──────────────────────────────────────────
$required = ['emp_id', 'payperiod_start', 'payperiod_end', 'base_salary'];
foreach ($required as $field) {
    if (!isset($input[$field]) || $input[$field] === '') {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => "Missing required field: {$field}"]);
        exit;
    }
}

// ── SANITIZE ─────────────────────────────────────────────────
$emp_id          = (int)    $input['emp_id'];
$payperiod_start =           trim($input['payperiod_start']);
$payperiod_end   =           trim($input['payperiod_end']);
$base_salary     = (float)  $input['base_salary'];
$bonus           = isset($input['bonus'])      ? (float)$input['bonus']      : 0;
$overtime        = isset($input['overtime'])   ? trim($input['overtime'])     : '0';

// Individual deductions (auto-computed by frontend via get_payroll_computation.php)
$sss             = isset($input['sss'])        ? (float)$input['sss']        : 0;
$philhealth      = isset($input['philhealth']) ? (float)$input['philhealth'] : 0;
$pag_ibig        = isset($input['pag_ibig'])   ? (float)$input['pag_ibig']   : 0;
$tax             = isset($input['tax'])        ? (float)$input['tax']        : 0;

// ── SERVER-SIDE TOTALS ────────────────────────────────────────
$overtime_amount  = (float)$overtime;
$deduction_total  = round($sss + $philhealth + $pag_ibig + $tax, 2);
$net_salary       = max(0, round($base_salary + $bonus + $overtime_amount - $deduction_total, 2));

// ── VALIDATE EMP EXISTS ───────────────────────────────────────
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
            sss,
            philhealth,
            `pag-ibig`,
            tax,
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
            :sss,
            :philhealth,
            :pag_ibig,
            :tax,
            :deduction_total,
            :net_salary,
            'Draft'
        )
    ");

    $stmt->execute([
        ':emp_id'          => $emp_id,
        ':payperiod_start' => $payperiod_start,
        ':payperiod_end'   => $payperiod_end,
        ':base_salary'     => $base_salary,
        ':bonus'           => $bonus,
        ':overtime'        => $overtime,
        ':sss'             => $sss,
        ':philhealth'      => $philhealth,
        ':pag_ibig'        => $pag_ibig,
        ':tax'             => $tax,
        ':deduction_total' => $deduction_total,
        ':net_salary'      => $net_salary,
    ]);

    echo json_encode([
        'status'          => 'success',
        'message'         => 'Payroll record created successfully.',
        'payroll_id'      => (int)$pdo->lastInsertId(),
        'deduction_total' => $deduction_total,
        'net_salary'      => $net_salary,
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}