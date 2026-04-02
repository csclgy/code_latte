<?php
// src/api/hr/payroll/update_payroll.php
// Updates an existing payroll record in payroll_tbl.
// GUARD: Only Draft records can be updated.
//        Once submitted to Finance (fa_payroll_approval), the record is locked.

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, PUT');

$method = $_SERVER['REQUEST_METHOD'];
if (!in_array($method, ['POST', 'PUT'])) {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed. Use POST or PUT.']);
    exit;
}

require_once '../../../config/db.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) $input = $_POST;

// ── REQUIRED ─────────────────────────────────────────────────
if (empty($input['payroll_id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required field: payroll_id']);
    exit;
}

$payroll_id = (int)$input['payroll_id'];

try {
    // ── CHECK RECORD EXISTS & IS STILL DRAFT ─────────────────
    // We check both payroll_tbl.payroll_status AND fa_payroll_approval.approval_status.
    // If FA has already taken it (Submitted/Approved/Rejected), block the edit.
    $chk = $pdo->prepare("
        SELECT p.payroll_status, COALESCE(fa.approval_status, 'Draft') AS fa_status
        FROM payroll_tbl p
        LEFT JOIN fa_payroll_approval fa ON p.payroll_id = fa.payroll_id
        WHERE p.payroll_id = :payroll_id
        LIMIT 1
    ");
    $chk->execute([':payroll_id' => $payroll_id]);
    $row = $chk->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Payroll record not found.']);
        exit;
    }

    // Lock check — HR cannot edit once Finance has received it
    if ($row['fa_status'] !== 'Draft') {
        http_response_code(403);
        echo json_encode([
            'status'  => 'error',
            'message' => "Cannot edit. This payroll record is currently '{$row['fa_status']}' in Finance."
        ]);
        exit;
    }

    // ── SANITIZE ──────────────────────────────────────────────
    $payperiod_start  = isset($input['payperiod_start'])  ? trim($input['payperiod_start'])  : null;
    $payperiod_end    = isset($input['payperiod_end'])    ? trim($input['payperiod_end'])    : null;
    $base_salary      = isset($input['base_salary'])      ? (float)$input['base_salary']     : null;
    $bonus            = isset($input['bonus'])            ? (float)$input['bonus']           : 0;
    $overtime         = isset($input['overtime'])         ? trim($input['overtime'])          : '0';
    $deductions_label = isset($input['deductions_label']) ? trim($input['deductions_label']) : '';
    $deduction_total  = isset($input['deduction_total'])  ? (float)$input['deduction_total'] : 0;

    if (is_null($base_salary)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing required field: base_salary']);
        exit;
    }

    // ── RECALCULATE NET SALARY SERVER-SIDE ────────────────────
    $overtime_amount = (float)$overtime;
    $net_salary      = max(0, $base_salary + $bonus + $overtime_amount - $deduction_total);

    // ── BUILD DYNAMIC SET CLAUSE ──────────────────────────────
    $set    = [];
    $params = [':payroll_id' => $payroll_id];

    if ($payperiod_start) { $set[] = 'payperiod_start = :payperiod_start';   $params[':payperiod_start']  = $payperiod_start; }
    if ($payperiod_end)   { $set[] = 'payperiod_end   = :payperiod_end';     $params[':payperiod_end']    = $payperiod_end; }

    $set[] = 'base_salary      = :base_salary';      $params[':base_salary']      = $base_salary;
    $set[] = 'bonus            = :bonus';            $params[':bonus']            = $bonus;
    $set[] = 'overtime         = :overtime';         $params[':overtime']         = $overtime;
    $set[] = 'deductions_label = :deductions_label'; $params[':deductions_label'] = $deductions_label;
    $set[] = 'deduction_total  = :deduction_total';  $params[':deduction_total']  = $deduction_total;
    $set[] = 'net_salary       = :net_salary';       $params[':net_salary']       = $net_salary;
    $set[] = "payroll_status   = 'Draft'";           // keep as Draft on edit

    $sql  = "UPDATE payroll_tbl SET " . implode(', ', $set) . " WHERE payroll_id = :payroll_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode([
        'status'     => 'success',
        'message'    => 'Payroll record updated successfully.',
        'payroll_id' => $payroll_id,
        'net_salary' => $net_salary
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}