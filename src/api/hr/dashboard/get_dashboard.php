<?php
// src/api/hr/dashboard/get_dashboard.php

ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../../config/db.php';

try {

    $today       = date('Y-m-d');
    $currentYear = (int) date('Y');
    $currentMon  = (int) date('n');

    $monthParam   = isset($_GET['month'])         ? trim($_GET['month'])               : date('Y-m');
    $monthsBack   = isset($_GET['months_back'])   ? max(1, (int)$_GET['months_back'])  : 6;
    $summaryLimit = isset($_GET['summary_limit']) ? max(1, (int)$_GET['summary_limit']): 10;

    $monthStart = $monthParam . '-01';
    $monthEnd   = date('Y-m-t', strtotime($monthStart));

    /* ── Build posMap dynamically from position_tbl ── */
    $posMap = [];
    try {
        $posRows = $pdo->query("SELECT post_id, pos_name FROM position_tbl")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($posRows as $p) {
            $posMap[(int)$p['post_id']] = $p['pos_name'];
        }
    } catch (PDOException $e) {
        $posMap = [1 => 'Barista', 2 => 'Cashier', 3 => 'Manager', 4 => 'Kitchen Staff'];
    }

    /* ════════════════════════════════════════════════════════
       1. STAT CARDS
    ════════════════════════════════════════════════════════ */

    $totalStaff = (int) $pdo->query("
        SELECT COUNT(*) FROM employee_tbl WHERE emp_status = 'Active'
    ")->fetchColumn();

    $presentToday = 0;
    try {
        $stmtPresent = $pdo->prepare("
            SELECT COUNT(*)
            FROM   attendance_tbl a
            LEFT JOIN leave_tbl l
                   ON a.emp_id = l.emp_id
                  AND :today1 BETWEEN l.date_start AND l.date_end
                  AND l.leave_status = 'Approved'
            WHERE  a.attendance_date = :today2
              AND  a.status = 'Present'
              AND  l.leave_id IS NULL
        ");
        $stmtPresent->execute([':today1' => $today, ':today2' => $today]);
        $presentToday = (int) $stmtPresent->fetchColumn();
    } catch (PDOException $e) {}

    $stmtPay = $pdo->prepare("
        SELECT COALESCE(SUM(net_salary), 0)
        FROM   payroll_tbl
        WHERE  payperiod_start >= :ms
          AND  payperiod_end   <= :me
    ");
    $stmtPay->execute([':ms' => $monthStart, ':me' => $monthEnd]);
    $monthlyPayroll = (float) $stmtPay->fetchColumn();

    $totalApplicants = 0;
    try {
        $totalApplicants = (int) $pdo->query("SELECT COUNT(*) FROM applicant_tbl")->fetchColumn();
    } catch (PDOException $e) {}

    $statCards = [
        'total_staff'      => $totalStaff,
        'present_today'    => $presentToday,
        'monthly_payroll'  => $monthlyPayroll,
        'total_applicants' => $totalApplicants,
    ];

    /* ════════════════════════════════════════════════════════
       2. MONTHLY ATTENDANCE
    ════════════════════════════════════════════════════════ */

    $monthList = [];
    for ($i = $monthsBack - 1; $i >= 0; $i--) {
        $ts = mktime(0, 0, 0, $currentMon - $i, 1, $currentYear);
        $monthList[] = [
            'year'  => (int) date('Y', $ts),
            'month' => (int) date('n', $ts),
            'label' => date('M', $ts),
        ];
    }

    $fromDate = date('Y-m-01', mktime(0, 0, 0, $currentMon - ($monthsBack - 1), 1, $currentYear));

    $monthlyAttendance = [];
    try {
        $stmtMonthly = $pdo->prepare("
            SELECT
                YEAR(a.attendance_date)  AS yr,
                MONTH(a.attendance_date) AS mo,
                CASE
                    WHEN l.leave_id IS NOT NULL AND l.leave_status = 'Approved' THEN 'On Leave'
                    ELSE a.status
                END AS att_status,
                COUNT(*) AS cnt
            FROM attendance_tbl a
            JOIN employee_tbl e ON a.emp_id = e.emp_id
            LEFT JOIN leave_tbl l
                   ON a.emp_id = l.emp_id
                  AND a.attendance_date BETWEEN l.date_start AND l.date_end
            WHERE  a.attendance_date >= :from_date
            GROUP  BY yr, mo, att_status
            ORDER  BY yr, mo
        ");
        $stmtMonthly->execute([':from_date' => $fromDate]);

        $monthlyIndex = [];
        foreach ($stmtMonthly->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $monthlyIndex[$row['yr']][$row['mo']][$row['att_status']] = (int) $row['cnt'];
        }

        foreach ($monthList as $m) {
            $b = $monthlyIndex[$m['year']][$m['month']] ?? [];
            $monthlyAttendance[] = [
                'label'    => $m['label'],
                'year'     => $m['year'],
                'month'    => $m['month'],
                'present'  => $b['Present']  ?? 0,
                'absent'   => $b['Absent']   ?? 0,
                'late'     => $b['Late']     ?? 0,
                'on_leave' => $b['On Leave'] ?? 0,
            ];
        }
    } catch (PDOException $e) {
        foreach ($monthList as $m) {
            $monthlyAttendance[] = [
                'label' => $m['label'], 'year' => $m['year'], 'month' => $m['month'],
                'present' => 0, 'absent' => 0, 'late' => 0, 'on_leave' => 0,
            ];
        }
    }

    /* ════════════════════════════════════════════════════════
       3. TODAY'S STATUS
    ════════════════════════════════════════════════════════ */

    $todaysStatus = [];
    try {
        $stmtStatus = $pdo->prepare("
            SELECT
                e.emp_id,
                e.emp_fname,
                e.emp_lname,
                e.emp_schedule,
                e.emp_working_hours,
                e.pos_id,
                CASE
                    WHEN l.leave_id      IS NOT NULL AND l.leave_status = 'Approved' THEN 'On Leave'
                    WHEN a.attendance_id IS NULL                                      THEN 'Absent'
                    ELSE a.status
                END AS att_status,
                a.time_in,
                a.time_out,
                a.late_minutes,
                a.remarks
            FROM employee_tbl e
            LEFT JOIN attendance_tbl a
                   ON e.emp_id = a.emp_id
                  AND a.attendance_date = :today1
            LEFT JOIN leave_tbl l
                   ON e.emp_id = l.emp_id
                  AND :today2 BETWEEN l.date_start AND l.date_end
                  AND l.leave_status = 'Approved'
            WHERE  e.emp_status = 'Active'
            ORDER  BY e.emp_lname, e.emp_fname
        ");
        $stmtStatus->execute([':today1' => $today, ':today2' => $today]);

        $todaysStatus = array_map(function ($row) use ($posMap) {
            return [
                'emp_id'            => (int) $row['emp_id'],
                'emp_fname'         =>       $row['emp_fname'],
                'emp_lname'         =>       $row['emp_lname'],
                'emp_schedule'      =>       $row['emp_schedule'],
                'emp_working_hours' =>       $row['emp_working_hours'],
                'pos_id'            => (int) $row['pos_id'],
                'emp_role'          => $posMap[$row['pos_id']] ?? 'Other',
                'att_status'        =>       $row['att_status'],
                'time_in'           =>       $row['time_in'],
                'time_out'          =>       $row['time_out'],
                'late_minutes'      =>       $row['late_minutes'],
                'remarks'           =>       $row['remarks'],
            ];
        }, $stmtStatus->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {}

    /* ════════════════════════════════════════════════════════
       4. PAYROLL BY ROLE
       KEY FIX: payroll_tbl uses post_id (not pos_id).
       Join: payroll_tbl.post_id → position_tbl.post_id
       to get role name and sum totals per position.
    ════════════════════════════════════════════════════════ */

    $stmtByRole = $pdo->prepare("
        SELECT
            p.post_id,
            pos.pos_name                   AS role,
            COALESCE(SUM(p.net_salary), 0) AS total_net_salary
        FROM payroll_tbl p
        JOIN employee_tbl e   ON p.emp_id  = e.emp_id
        JOIN position_tbl pos ON p.post_id = pos.post_id
        WHERE  p.payperiod_start >= :ms
          AND  p.payperiod_end   <= :me
          AND  e.emp_status = 'Active'
        GROUP  BY p.post_id, pos.pos_name
        ORDER  BY total_net_salary DESC
    ");
    $stmtByRole->execute([':ms' => $monthStart, ':me' => $monthEnd]);

    $payrollByRole = array_map(function ($r) {
        return [
            'post_id'          => (int)   $r['post_id'],
            'role'             =>          $r['role'],
            'total_net_salary' => (float) $r['total_net_salary'],
        ];
    }, $stmtByRole->fetchAll(PDO::FETCH_ASSOC));

    /* ════════════════════════════════════════════════════════
       5. PAYROLL SUMMARY
       KEY FIX: Join payroll_tbl.post_id → position_tbl.post_id
       Use payroll_tbl.payroll_status directly (no fa table).
    ════════════════════════════════════════════════════════ */

    $stmtSummary = $pdo->prepare("
        SELECT
            p.payroll_id,
            p.emp_id,
            e.emp_fname,
            e.emp_lname,
            p.post_id,
            pos.pos_name      AS emp_role,
            p.payperiod_start,
            p.payperiod_end,
            p.base_salary,
            p.bonus,
            p.overtime,
            p.deductions_label,
            p.deduction_total,
            p.net_salary,
            p.payroll_status
        FROM payroll_tbl p
        JOIN employee_tbl e   ON p.emp_id  = e.emp_id
        JOIN position_tbl pos ON p.post_id = pos.post_id
        WHERE  p.payperiod_start >= :ms
          AND  p.payperiod_end   <= :me
        ORDER  BY p.payroll_id DESC
        LIMIT  :lim
    ");
    $stmtSummary->bindValue(':ms',  $monthStart,   PDO::PARAM_STR);
    $stmtSummary->bindValue(':me',  $monthEnd,     PDO::PARAM_STR);
    $stmtSummary->bindValue(':lim', $summaryLimit, PDO::PARAM_INT);
    $stmtSummary->execute();

    $payrollSummary = array_map(function ($row) {
        return [
            'payroll_id'       => (int)   $row['payroll_id'],
            'emp_id'           => (int)   $row['emp_id'],
            'emp_fname'        =>         $row['emp_fname'],
            'emp_lname'        =>         $row['emp_lname'],
            'emp_role'         =>         $row['emp_role'],
            'payperiod_start'  =>         $row['payperiod_start'],
            'payperiod_end'    =>         $row['payperiod_end'],
            'base_salary'      => (float) $row['base_salary'],
            'bonus'            => (float) $row['bonus'],
            'overtime'         => (float) $row['overtime'],
            'deductions_label' =>         $row['deductions_label'],
            'deduction_total'  => (float) $row['deduction_total'],
            'net_salary'       => (float) $row['net_salary'],
            'payroll_status'   =>         $row['payroll_status'],
        ];
    }, $stmtSummary->fetchAll(PDO::FETCH_ASSOC));

    /* ════════════════════════════════════════════════════════
       FINAL RESPONSE
    ════════════════════════════════════════════════════════ */

    echo json_encode([
        'status'             => 'success',
        'generated_at'       => date('c'),
        'filters'            => [
            'month'       => $monthParam,
            'month_start' => $monthStart,
            'month_end'   => $monthEnd,
            'months_back' => $monthsBack,
        ],
        'stat_cards'         => $statCards,
        'monthly_attendance' => $monthlyAttendance,
        'todays_status'      => $todaysStatus,
        'payroll_by_role'    => $payrollByRole,
        'payroll_summary'    => $payrollSummary,
    ], JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage(),
    ]);
}