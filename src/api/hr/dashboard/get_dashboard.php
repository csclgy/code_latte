<?php
ob_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../../config/db.php';

try {
    $today     = date('Y-m-d');
    $thisMonth = date('Y-m');
    $thisYear  = date('Y');

    // ── 1. STAT CARDS ──

    // Total active staff
    $staffStmt = $pdo->query("
        SELECT COUNT(*) AS total_staff 
        FROM employee_tbl 
        WHERE emp_status = 'Active'
    ");
    $totalStaff = (int)$staffStmt->fetchColumn();

    // Present today
    $presentStmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM attendance_tbl 
        WHERE attendance_date = ? AND status = 'Present'
    ");
    $presentStmt->execute([$today]);
    $presentToday = (int)$presentStmt->fetchColumn();

    // Monthly payroll (current month net salary sum)
    $payrollStmt = $pdo->prepare("
        SELECT COALESCE(SUM(net_salary), 0) AS monthly_payroll
        FROM payroll_tbl
        WHERE DATE_FORMAT(payperiod_start, '%Y-%m') = ?
    ");
    $payrollStmt->execute([$thisMonth]);
    $monthlyPayroll = (float)$payrollStmt->fetchColumn();

    // Total applicants
    $appStmt = $pdo->query("SELECT COUNT(*) FROM applicant_tbl");
    $totalApplicants = (int)$appStmt->fetchColumn();

    // ── 2. MONTHLY ATTENDANCE (last 6 months) ──
    $monthlyAtt = [];
    for ($i = 5; $i >= 0; $i--) {
        $monthDate  = date('Y-m', strtotime("-$i months"));
        $monthLabel = date('M',   strtotime("-$i months"));

        $attStmt = $pdo->prepare("
            SELECT 
                SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) AS present,
                SUM(CASE WHEN a.status = 'Absent'  THEN 1 ELSE 0 END) AS absent,
                SUM(CASE WHEN a.status = 'Late'    THEN 1 ELSE 0 END) AS late
            FROM attendance_tbl a
            WHERE DATE_FORMAT(a.attendance_date, '%Y-%m') = ?
        ");
        $attStmt->execute([$monthDate]);
        $row = $attStmt->fetch(PDO::FETCH_ASSOC);

        $monthlyAtt[] = [
            'label'   => $monthLabel,
            'present' => (int)($row['present'] ?? 0),
            'absent'  => (int)($row['absent']  ?? 0),
            'late'    => (int)($row['late']    ?? 0),
        ];
    }

    // ── 3. TODAY'S STATUS ──
    // Get all active employees and their attendance for today
    $todayStmt = $pdo->prepare("
        SELECT 
            e.emp_id,
            e.emp_fname        AS emp_fname,
            e.emp_lname        AS emp_lname,
            e.emp_schedule     AS emp_schedule,
            CASE e.Pos_id
                WHEN 1 THEN 'Barista'
                WHEN 2 THEN 'Cashier'
                WHEN 3 THEN 'Kitchen Staff'
                WHEN 4 THEN 'Supervisor'
                ELSE '—'
            END AS emp_role,
            COALESCE(
                CASE 
                    WHEN l.leave_id IS NOT NULL AND l.leave_status = 'Approved' THEN 'On Leave'
                    ELSE a.status
                END,
                'Absent'
            ) AS att_status
        FROM employee_tbl e
        LEFT JOIN attendance_tbl a 
            ON e.emp_id = a.emp_id AND a.attendance_date = ?
        LEFT JOIN leave_tbl l 
            ON e.emp_id = l.emp_id 
            AND ? BETWEEN l.date_start AND l.date_end
            AND l.leave_status = 'Approved'
        WHERE e.emp_status = 'Active'
        ORDER BY e.emp_fname ASC
    ");
    $todayStmt->execute([$today, $today]);
    $todaysStatus = $todayStmt->fetchAll(PDO::FETCH_ASSOC);

    // ── 4. PAYROLL BY ROLE ──
    $roleStmt = $pdo->prepare("
        SELECT 
            CASE e.pos_id
                WHEN 1 THEN 'Barista'
                WHEN 2 THEN 'Cashier'
                WHEN 3 THEN 'Kitchen Staff'
                WHEN 4 THEN 'Supervisor'
                ELSE 'Other'
            END AS role,
            COALESCE(SUM(p.net_salary), 0) AS total_net_salary
        FROM payroll_tbl p
        JOIN employee_tbl e ON p.emp_id = e.emp_id
        WHERE DATE_FORMAT(p.payperiod_start, '%Y-%m') = ?
        GROUP BY e.pos_id
        ORDER BY total_net_salary DESC
    ");
    $roleStmt->execute([$thisMonth]);
    $payrollByRole = $roleStmt->fetchAll(PDO::FETCH_ASSOC);

    // ── 5. PAYROLL SUMMARY (latest records) ──
    $summaryStmt = $pdo->prepare("
        SELECT 
            e.emp_fname        AS emp_fname,
            e.emp_lname        AS emp_lname,
            CASE e.Pos_id
                WHEN 1 THEN 'Barista'
                WHEN 2 THEN 'Cashier'
                WHEN 3 THEN 'Kitchen Staff'
                WHEN 4 THEN 'Supervisor'
                ELSE '—'
            END AS emp_role,
            p.net_salary,
            p.payperiod_start,
            p.payperiod_end,
            p.payroll_status
        FROM payroll_tbl p
        JOIN employee_tbl e ON p.emp_id = e.emp_id
        WHERE DATE_FORMAT(p.payperiod_start, '%Y-%m') = ?
        ORDER BY p.payroll_id DESC
        LIMIT 10
    ");
    $summaryStmt->execute([$thisMonth]);
    $payrollSummary = $summaryStmt->fetchAll(PDO::FETCH_ASSOC);

    // ── RESPONSE ──
    ob_clean();
    echo json_encode([
        'status'  => 'success',
        'stat_cards' => [
            'total_staff'      => $totalStaff,
            'present_today'    => $presentToday,
            'monthly_payroll'  => $monthlyPayroll,
            'total_applicants' => $totalApplicants,
        ],
        'monthly_attendance' => $monthlyAtt,
        'todays_status'      => $todaysStatus,
        'payroll_by_role'    => $payrollByRole,
        'payroll_summary'    => $payrollSummary,
    ]);

} catch (PDOException $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>