<?php
// src/api/hr/payroll/get_payroll_computation.php
//
// Given an emp_id, payperiod_start, and payperiod_end this endpoint:
//   1. Pulls emp_basic_salary and emp_working_hours from employee_tbl
//   2. Counts attendance days (Present + Late) within the cut-off from attendance_tbl
//   3. Computes prorated base salary for the cut-off
//   4. Auto-computes SSS, PhilHealth, Pag-IBIG contributions
//   5. Auto-computes withholding tax (TRAIN Law) based on MONTHLY equivalent salary
//
// GET params: emp_id, payperiod_start (YYYY-MM-DD), payperiod_end (YYYY-MM-DD)

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../../../config/db.php';

// ── REQUIRED PARAMS ───────────────────────────────────────────
$emp_id          = isset($_GET['emp_id'])          ? (int)trim($_GET['emp_id'])          : 0;
$payperiod_start = isset($_GET['payperiod_start']) ? trim($_GET['payperiod_start'])       : '';
$payperiod_end   = isset($_GET['payperiod_end'])   ? trim($_GET['payperiod_end'])         : '';

if (!$emp_id || !$payperiod_start || !$payperiod_end) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required params: emp_id, payperiod_start, payperiod_end']);
    exit;
}

try {
    // ── 1. GET EMPLOYEE BASE SALARY & WORKING HOURS ───────────
    $stmtEmp = $pdo->prepare("
        SELECT emp_basic_salary, emp_working_hours
        FROM employee_tbl
        WHERE emp_id = :emp_id
        LIMIT 1
    ");
    $stmtEmp->execute([':emp_id' => $emp_id]);
    $emp = $stmtEmp->fetch(PDO::FETCH_ASSOC);

    if (!$emp) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Employee not found.']);
        exit;
    }

    $monthly_salary  = (float) $emp['emp_basic_salary'];
    $working_hours   = (int)   $emp['emp_working_hours']; // hours per day (e.g. 8)

    // ── 2. COUNT WORKING DAYS IN THE FULL CUT-OFF MONTH ───────
    // We use the number of weekdays (Mon–Sat, or Mon–Fri depending on company)
    // For simplicity we count Mon–Sat as working days (6-day work week typical in PH)
    // Change to Mon–Fri if your company is 5-day
    $start_dt = new DateTime($payperiod_start);
    $end_dt   = new DateTime($payperiod_end);

    $total_working_days_in_cutoff = 0;
    $cursor = clone $start_dt;
    while ($cursor <= $end_dt) {
        $dow = (int)$cursor->format('N'); // 1=Mon … 7=Sun
        if ($dow <= 6) { // Mon–Sat
            $total_working_days_in_cutoff++;
        }
        $cursor->modify('+1 day');
    }

    // ── 3. COUNT DAYS EMPLOYEE ACTUALLY ATTENDED IN CUT-OFF ───
    // Count Present and Late as paid days; Absent is unpaid
    $stmtAtt = $pdo->prepare("
        SELECT COUNT(*) AS days_attended
        FROM attendance_tbl
        WHERE emp_id           = :emp_id
          AND attendance_date  >= :start
          AND attendance_date  <= :end
          AND status           IN ('Present', 'Late')
    ");
    $stmtAtt->execute([
        ':emp_id' => $emp_id,
        ':start'  => $payperiod_start,
        ':end'    => $payperiod_end,
    ]);
    $attRow       = $stmtAtt->fetch(PDO::FETCH_ASSOC);
    $days_attended = (int)($attRow['days_attended'] ?? 0);

    // ── 4. COMPUTE PRORATED BASE SALARY FOR CUT-OFF ───────────
    // daily_rate = monthly_salary / total_working_days_in_cutoff
    // cutoff_base_salary = daily_rate × days_attended
    $daily_rate          = $total_working_days_in_cutoff > 0
        ? round($monthly_salary / $total_working_days_in_cutoff, 4)
        : 0;
    $cutoff_base_salary  = round($daily_rate * $days_attended, 2);

    // ── 5. CONTRIBUTIONS (based on MONTHLY salary, deducted per cut-off) ─
    // All contribution tables are monthly. For semi-monthly payroll (2 cut-offs/month)
    // deduct HALF per cut-off. For monthly payroll, deduct full amount.
    // Detect semi-monthly: if cut-off spans ≤ 16 days it is likely semi-monthly.
    $cutoff_days_span = $start_dt->diff($end_dt)->days + 1;
    $is_semi_monthly  = $cutoff_days_span <= 16;
    $divisor          = $is_semi_monthly ? 2 : 1;

    // ── SSS (employee share, 4.5% of MSC) ────────────────────
    // MSC brackets (monthly salary → monthly SSS employee share)
    $sss_table = [
        [0,       4249.99,  180.00],
        [4250,    4749.99,  202.50],
        [4750,    5249.99,  225.00],
        [5250,    5749.99,  247.50],
        [5750,    6249.99,  270.00],
        [6250,    6749.99,  292.50],
        [6750,    7249.99,  315.00],
        [7250,    7749.99,  337.50],
        [7750,    8249.99,  360.00],
        [8250,    8749.99,  382.50],
        [8750,    9249.99,  405.00],
        [9250,    9749.99,  427.50],
        [9750,   10249.99,  450.00],
        [10250,  10749.99,  472.50],
        [10750,  11249.99,  495.00],
        [11250,  11749.99,  517.50],
        [11750,  12249.99,  540.00],
        [12250,  12749.99,  562.50],
        [12750,  13249.99,  585.00],
        [13250,  13749.99,  607.50],
        [13750,  14249.99,  630.00],
        [14250,  14749.99,  652.50],
        [14750,  15249.99,  675.00],
        [15250,  15749.99,  697.50],
        [15750,  16249.99,  720.00],
        [16250,  16749.99,  742.50],
        [16750,  17249.99,  765.00],
        [17250,  17749.99,  787.50],
        [17750,  18249.99,  810.00],
        [18250,  18749.99,  832.50],
        [18750,  19249.99,  855.00],
        [19250,  19749.99,  877.50],
        [19750,  PHP_FLOAT_MAX, 900.00],
    ];

    $monthly_sss = 900.00; // default to max
    foreach ($sss_table as [$min, $max, $share]) {
        if ($monthly_salary >= $min && $monthly_salary <= $max) {
            $monthly_sss = $share;
            break;
        }
    }
    $cutoff_sss = round($monthly_sss / $divisor, 2);

    // ── PhilHealth (2.5% employee share, min ₱250, max ₱2,500 monthly) ──
    $monthly_philhealth = min(max(round($monthly_salary * 0.025, 2), 250.00), 2500.00);
    $cutoff_philhealth  = round($monthly_philhealth / $divisor, 2);

    // ── Pag-IBIG (2% of salary, max ₱100 monthly employee share) ─────
    $monthly_pagibig = $monthly_salary <= 1500
        ? round($monthly_salary * 0.01, 2)
        : min(round($monthly_salary * 0.02, 2), 100.00);
    $cutoff_pagibig = round($monthly_pagibig / $divisor, 2);

    // ── 6. WITHHOLDING TAX (TRAIN Law, based on MONTHLY taxable income) ─
    // Taxable monthly income = monthly_salary - monthly_sss - monthly_philhealth - monthly_pagibig
    $monthly_taxable = $monthly_salary - $monthly_sss - $monthly_philhealth - $monthly_pagibig;
    $monthly_taxable = max(0, $monthly_taxable);

    // TRAIN Law monthly tax table (effective 2023 onwards)
    $monthly_tax = 0;
    if ($monthly_taxable <= 20833) {
        $monthly_tax = 0;
    } elseif ($monthly_taxable <= 33332) {
        $monthly_tax = ($monthly_taxable - 20833) * 0.20;
    } elseif ($monthly_taxable <= 66666) {
        $monthly_tax = 2500 + ($monthly_taxable - 33333) * 0.25;
    } elseif ($monthly_taxable <= 166666) {
        $monthly_tax = 10833.33 + ($monthly_taxable - 66667) * 0.30;
    } elseif ($monthly_taxable <= 666666) {
        $monthly_tax = 40833.33 + ($monthly_taxable - 166667) * 0.32;
    } else {
        $monthly_tax = 200833.33 + ($monthly_taxable - 666667) * 0.35;
    }

    $cutoff_tax = round($monthly_tax / $divisor, 2);

    // ── 7. TOTAL DEDUCTIONS & NET SALARY ─────────────────────
    $total_deductions  = $cutoff_sss + $cutoff_philhealth + $cutoff_pagibig + $cutoff_tax;
    $net_salary        = max(0, round($cutoff_base_salary - $total_deductions, 2));

    // ── RESPONSE ──────────────────────────────────────────────
    echo json_encode([
        'status'  => 'success',
        'computation' => [
            // Salary
            'monthly_salary'              => $monthly_salary,
            'daily_rate'                  => round($daily_rate, 2),
            'total_working_days_in_cutoff'=> $total_working_days_in_cutoff,
            'days_attended'               => $days_attended,
            'cutoff_base_salary'          => $cutoff_base_salary,

            // Contributions (per cut-off)
            'sss'                         => $cutoff_sss,
            'philhealth'                  => $cutoff_philhealth,
            'pag_ibig'                    => $cutoff_pagibig,
            'tax'                         => $cutoff_tax,
            'deduction_total'             => round($total_deductions, 2),
            'net_salary'                  => $net_salary,

            // Monthly equivalents (for display reference)
            'monthly_sss'                 => $monthly_sss,
            'monthly_philhealth'          => $monthly_philhealth,
            'monthly_pagibig'             => $monthly_pagibig,
            'monthly_tax'                 => round($monthly_tax, 2),
            'monthly_taxable_income'      => round($monthly_taxable, 2),

            // Meta
            'is_semi_monthly'             => $is_semi_monthly,
            'cutoff_days_span'            => $cutoff_days_span,
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}