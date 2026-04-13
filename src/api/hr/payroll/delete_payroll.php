<?php
// src/api/hr/payroll/delete_payroll.php
// Deletes a payroll record from payroll_tbl.
// GUARD: Only Draft records (no fa_payroll_approval row) can be deleted.
//        If FA already has an approval row for this payroll_id, deletion is blocked.

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, DELETE');

$method = $_SERVER['REQUEST_METHOD'];
if (!in_array($method, ['POST', 'DELETE'])) {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed. Use POST or DELETE.']);
    exit;
}

require_once '../../../config/db.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) $input = $_POST;

// Also support ?payroll_id=N via GET for DELETE requests
if (empty($input['payroll_id']) && isset($_GET['payroll_id'])) {
    $input['payroll_id'] = $_GET['payroll_id'];
}

if (empty($input['payroll_id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required field: payroll_id']);
    exit;
}

$payroll_id = (int)$input['payroll_id'];

try {
    // ── CHECK RECORD EXISTS ───────────────────────────────────
    $chk = $pdo->prepare("
        SELECT p.payroll_id, p.payroll_status,
               COALESCE(fa.approval_status, 'Draft') AS fa_status
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

    // ── GUARD: Block deletion if submitted to Finance ─────────
    if ($row['fa_status'] !== 'Draft') {
        http_response_code(403);
        echo json_encode([
            'status'  => 'error',
            'message' => "Cannot delete. This payroll record has already been '{$row['fa_status']}' by Finance."
        ]);
        exit;
    }

    // ── DELETE ────────────────────────────────────────────────
    // Hard delete from payroll_tbl.
    // Safe because no fa_payroll_approval row exists at this point.
    $del = $pdo->prepare("DELETE FROM payroll_tbl WHERE payroll_id = :payroll_id");
    $del->execute([':payroll_id' => $payroll_id]);

    echo json_encode([
        'status'     => 'success',
        'message'    => 'Payroll record deleted successfully.',
        'payroll_id' => $payroll_id
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}