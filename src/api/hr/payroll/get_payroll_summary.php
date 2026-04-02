<?php
// src/api/hr/payroll/get_payroll_summary.php
// Returns aggregated payroll statistics for the stat cards on payroll_hr.php.
// Optionally filterable by pay period dates via GET params:
//   ?period_start=2026-03-01&period_end=2026-03-15

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../../../config/db.php';

try {
    $period_start = isset($_GET['period_start']) ? trim($_GET['period_start']) : null;
    $period_end   = isset($_GET['period_end'])   ? trim($_GET['period_end'])   : null;

    // Build optional period filter
    $where  = '';
    $params = [];
    if ($period_start && $period_end) {
        $where = "WHERE p.payperiod_start >= :period_start AND p.payperiod_end <= :period_end";
        $params[':period_start'] = $period_start;
        $params[':period_end']   = $period_end;
    }

    // ── GRAND TOTALS ─────────────────────────────────────────
    $stmtTotal = $pdo->prepare("
        SELECT
            COUNT(*)            AS total_records,
            SUM(p.net_salary)   AS total_net_salary,
            SUM(p.base_salary)  AS total_base_salary,
            SUM(p.bonus)        AS total_bonus,
            SUM(p.deduction_total) AS total_deductions
        FROM payroll_tbl p
        {$where}
    ");
    $stmtTotal->execute($params);
    $totals = $stmtTotal->fetch(PDO::FETCH_ASSOC);

    // ── BREAKDOWN BY FA STATUS ────────────────────────────────
    $stmtBreakdown = $pdo->prepare("
        SELECT
            COALESCE(fa.approval_status, 'Draft') AS fa_status,
            COUNT(*)                               AS record_count,
            SUM(p.net_salary)                      AS net_salary_sum
        FROM payroll_tbl p
        LEFT JOIN fa_payroll_approval fa ON p.payroll_id = fa.payroll_id
        {$where}
        GROUP BY COALESCE(fa.approval_status, 'Draft')
    ");
    $stmtBreakdown->execute($params);
    $breakdown_rows = $stmtBreakdown->fetchAll(PDO::FETCH_ASSOC);

    // Map to key-value for easy JS consumption
    $breakdown = [
        'Draft'     => ['count' => 0, 'net_salary' => 0],
        'Pending'   => ['count' => 0, 'net_salary' => 0],
        'Submitted' => ['count' => 0, 'net_salary' => 0],
        'Approved'  => ['count' => 0, 'net_salary' => 0],
        'Rejected'  => ['count' => 0, 'net_salary' => 0],
    ];
    foreach ($breakdown_rows as $row) {
        $key = $row['fa_status'];
        if (isset($breakdown[$key])) {
            $breakdown[$key] = [
                'count'      => (int)   $row['record_count'],
                'net_salary' => (float) $row['net_salary_sum']
            ];
        }
    }

    // ── COMBINED SUBMITTED (Submitted + Approved) ─────────────
    $submitted_total_count  = $breakdown['Submitted']['count']      + $breakdown['Approved']['count'];
    $submitted_total_salary = $breakdown['Submitted']['net_salary'] + $breakdown['Approved']['net_salary'];

    // ── DRAFT + REJECTED (still on HR side) ──────────────────
    $draft_total_count  = $breakdown['Draft']['count']      + $breakdown['Rejected']['count'];
    $draft_total_salary = $breakdown['Draft']['net_salary'] + $breakdown['Rejected']['net_salary'];

    echo json_encode([
        'status'  => 'success',
        'summary' => [
            'total_records'           => (int)   $totals['total_records'],
            'total_net_salary'        => (float) $totals['total_net_salary'],
            'total_base_salary'       => (float) $totals['total_base_salary'],
            'total_bonus'             => (float) $totals['total_bonus'],
            'total_deductions'        => (float) $totals['total_deductions'],
            'submitted_to_finance'    => [
                'count'      => $submitted_total_count,
                'net_salary' => $submitted_total_salary
            ],
            'draft_or_rejected'       => [
                'count'      => $draft_total_count,
                'net_salary' => $draft_total_salary
            ],
            'breakdown_by_fa_status'  => $breakdown
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}