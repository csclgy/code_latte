<?php
// src/api/hr/payroll/update_payroll.php
// Updates a Draft payroll record with individual deduction columns.
// GUARD: Only Draft records (fa_status = 'Draft') can be edited.

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

if (empty($input['payroll_id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required field: payroll_id']);
    exit;
}

$payroll_id = (int)$input['payroll_id'];

try {
    // ── CHECK RECORD EXISTS & IS STILL DRAFT ─────────────────
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
    if ($row['fa_status'] !== 'Draft') {
        http_response_code(403);
        echo json_encode([
            'status'  => 'error',
            'message' => "Cannot edit. This record is '{$row['fa_status']}' in Finance."
        ]);
        exit;
    }

    // ── SANITIZE ──────────────────────────────────────────────
    $payperiod_start = isset($input['payperiod_start']) ? trim($input['payperiod_start']) : null;
    $payperiod_end   = isset($input['payperiod_end'])   ? trim($input['payperiod_end'])   : null;
    $base_salary     = isset($input['base_salary'])     ? (float)$input['base_salary']    : null;
    $bonus           = isset($input['bonus'])           ? (float)$input['bonus']          : 0;
    $overtime        = isset($input['overtime'])        ? trim($input['overtime'])         : '0';
    $sss             = isset($input['sss'])             ? (float)$input['sss']            : 0;
    $philhealth      = isset($input['philhealth'])      ? (float)$input['philhealth']     : 0;
    $pag_ibig        = isset($input['pag_ibig'])        ? (float)$input['pag_ibig']       : 0;
    $tax             = isset($input['tax'])             ? (float)$input['tax']            : 0;

    if (is_null($base_salary)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing required field: base_salary']);
        exit;
    }

    // ── SERVER-SIDE TOTALS ────────────────────────────────────
    $overtime_amount = (float)$overtime;
    $deduction_total = round($sss + $philhealth + $pag_ibig + $tax, 2);
    $net_salary      = max(0, round($base_salary + $bonus + $overtime_amount - $deduction_total, 2));

    // ── UPDATE ────────────────────────────────────────────────
    $params = [':payroll_id' => $payroll_id];
    $set    = [
        'base_salary     = :base_salary',
        'bonus           = :bonus',
        'overtime        = :overtime',
        'sss             = :sss',
        'philhealth      = :philhealth',
        '`pag-ibig`      = :pag_ibig',
        'tax             = :tax',
        'deduction_total = :deduction_total',
        'net_salary      = :net_salary',
        "payroll_status  = 'Draft'",
    ];
    $params[':base_salary']     = $base_salary;
    $params[':bonus']           = $bonus;
    $params[':overtime']        = $overtime;
    $params[':sss']             = $sss;
    $params[':philhealth']      = $philhealth;
    $params[':pag_ibig']        = $pag_ibig;
    $params[':tax']             = $tax;
    $params[':deduction_total'] = $deduction_total;
    $params[':net_salary']      = $net_salary;

    if ($payperiod_start) { $set[] = 'payperiod_start = :payperiod_start'; $params[':payperiod_start'] = $payperiod_start; }
    if ($payperiod_end)   { $set[] = 'payperiod_end   = :payperiod_end';   $params[':payperiod_end']   = $payperiod_end; }

    $sql  = "UPDATE payroll_tbl SET " . implode(', ', $set) . " WHERE payroll_id = :payroll_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode([
        'status'          => 'success',
        'message'         => 'Payroll record updated successfully.',
        'payroll_id'      => $payroll_id,
        'deduction_total' => $deduction_total,
        'net_salary'      => $net_salary,
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}