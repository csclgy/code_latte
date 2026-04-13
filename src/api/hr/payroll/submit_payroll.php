<?php
// src/api/hr/payroll/submit_payroll.php
// Submits a payroll record to the Finance & Accounting department.
//
// What this does:
//   1. Validates the payroll record exists and is in Draft state.
//   2. Inserts a row into fa_payroll_approval with approval_status = 'Pending'.
//   3. Updates payroll_tbl.payroll_status to 'Submitted'.
//
// HR module CREATES the approval request. Finance owns the approval itself.
// This endpoint is the bridge between the two modules.

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

// ── REQUIRED ─────────────────────────────────────────────────
if (empty($input['payroll_id']) && empty($input['payroll_ids'])) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required field: payroll_id or payroll_ids'
    ]);
    exit;
}

// Optional: pass multiple payroll IDs for bulk submit
// If 'payroll_ids' (array) is provided, process all. Otherwise use single 'payroll_id'.
$ids = [];
if (!empty($input['payroll_ids']) && is_array($input['payroll_ids'])) {
    $ids = array_map('intval', $input['payroll_ids']);
} else {
    $ids = [(int)$input['payroll_id']];
}

if (empty($ids)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'No valid payroll IDs provided.']);
    exit;
}

try {
    $pdo->beginTransaction();

    $submitted   = [];
    $skipped     = [];
    $today       = date('Y-m-d');

    // Prepare statements once, execute in loop
    $stmtCheck = $pdo->prepare("
        SELECT p.payroll_id, p.emp_id, p.payroll_status,
               COALESCE(fa.approval_status, 'Draft') AS fa_status,
               fa.payroll_approval_id
        FROM payroll_tbl p
        LEFT JOIN fa_payroll_approval fa ON p.payroll_id = fa.payroll_id
        WHERE p.payroll_id = :payroll_id
        LIMIT 1
    ");

    // fa_payroll_approval columns:
    // payroll_approval_id (AUTO_INCREMENT), payroll_id, approval_status, emp_id, review_date, je_id
    $stmtInsertFA = $pdo->prepare("
        INSERT INTO fa_payroll_approval (
            payroll_id,
            approval_status,
            emp_id,
            review_date,
            je_id
        ) VALUES (
            :payroll_id,
            'Pending',
            :emp_id,
            :review_date,
            NULL
        )
    ");

    $stmtUpdatePayroll = $pdo->prepare("
        UPDATE payroll_tbl
        SET payroll_status = 'Submitted'
        WHERE payroll_id = :payroll_id
    ");

    foreach ($ids as $pid) {
        $stmtCheck->execute([':payroll_id' => $pid]);
        $row = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        // Skip if not found
        if (!$row) {
            $skipped[] = ['payroll_id' => $pid, 'reason' => 'Record not found'];
            continue;
        }

        // Skip if already submitted (fa row exists and is not Draft)
        if ($row['fa_status'] !== 'Draft') {
            $skipped[] = [
                'payroll_id' => $pid,
                'reason'     => "Already {$row['fa_status']} — cannot re-submit"
            ];
            continue;
        }

        // 1. Insert into fa_payroll_approval
        $stmtInsertFA->execute([
            ':payroll_id'  => $pid,
            ':emp_id'      => $row['emp_id'],
            ':review_date' => $today,
        ]);

        // 2. Update payroll_tbl status
        $stmtUpdatePayroll->execute([':payroll_id' => $pid]);

        $submitted[] = $pid;
    }

    $pdo->commit();

    echo json_encode([
        'status'          => 'success',
        'message'         => count($submitted) . ' payroll record(s) submitted to Finance.',
        'submitted_ids'   => $submitted,
        'skipped'         => $skipped
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}