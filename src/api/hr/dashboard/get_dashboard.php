<?php
ob_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../../config/db.php';

try {
    $today     = date('Y-m-d');
    $thisMonth = date('Y-m');

    // ── 1. STAT CARDS ──
    $staffStmt = $pdo->query("
        SELECT COUNT(*) FROM employee_tbl WHERE emp_status = 'Active'
    ");
    $totalStaff = (int)$staffStmt->fetchColumn();

    $presentStmt = $pdo->prepare("
        SELECT COUNT(*) FROM attendance_tbl 
        WHERE attendance_date = ? AND status = 'Present'
    ");
    $presentStmt->execute([$today]);
    $presentToday = (int)$presentStmt->fetchColumn();

    $payrollStmt = $pdo->prepare("
        SELECT COALESCE(SUM(net_salary), 0)
        FROM payroll_tbl
        WHERE DATE_FORMAT(payperiod_start, '%Y-%m') = ?
    ");
    $payrollStmt->execute([$thisMonth]);
    $monthlyPayroll = (float)$payrollStmt->fetchColumn();

    $appStmt = $pdo->query("SELECT COUNT(*) FROM applicant_tbl");
    $totalApplicants = (int)$appStmt->fetchColumn();

    // ── 2. MONTHLY ATTENDANCE (last 6 months) ──
    $monthlyAtt = [];
    for ($i = 5; $i >= 0; $i--) {
        $monthDate  = date('Y-m', strtotime("-$i months"));
        $monthLabel = date('M',   strtotime("-$i months"));

        $attStmt = $pdo->prepare("
            SELECT 
                SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) AS present,
                SUM(CASE WHEN status = 'Absent'  THEN 1 ELSE 0 END) AS absent,
                SUM(CASE WHEN status = 'Late'    THEN 1 ELSE 0 END) AS late
            FROM attendance_tbl
            WHERE DATE_FORMAT(attendance_date, '%Y-%m') = ?
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
    // JOIN position_tbl to get role name dynamically
    $todayStmt = $pdo->prepare("
        SELECT 
            e.emp_id,
            e.emp_fname,
            e.emp_lname,
            e.emp_schedule,
            p.pos_name AS emp_role,
            COALESCE(
                CASE 
                    WHEN l.leave_id IS NOT NULL AND l.leave_status = 'Approved' THEN 'On Leave'
                    ELSE a.status
                END,
                'Absent'
            ) AS att_status
        FROM employee_tbl e
        LEFT JOIN position_tbl p
            ON e.pos_id = p.pos_id
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
    // JOIN position_tbl to get role name dynamically
    $roleStmt = $pdo->prepare("
        SELECT 
            p.pos_name AS role,
            COALESCE(SUM(pr.net_salary), 0) AS total_net_salary
        FROM payroll_tbl pr
        JOIN employee_tbl e  ON pr.emp_id  = e.emp_id
        JOIN position_tbl p  ON e.pos_id   = p.pos_id
        WHERE DATE_FORMAT(pr.payperiod_start, '%Y-%m') = ?
        GROUP BY e.pos_id, p.pos_name
        ORDER BY total_net_salary DESC
    ");
    $roleStmt->execute([$thisMonth]);
    $payrollByRole = $roleStmt->fetchAll(PDO::FETCH_ASSOC);

    // ── 5. PAYROLL SUMMARY ──
    // JOIN position_tbl to get role name dynamically
    $summaryStmt = $pdo->prepare("
        SELECT 
            e.emp_fname,
            e.emp_lname,
            p.pos_name      AS emp_role,
            pr.net_salary,
            pr.payperiod_start,
            pr.payperiod_end,
            pr.payroll_status
        FROM payroll_tbl pr
        JOIN employee_tbl e ON pr.emp_id = e.emp_id
        JOIN position_tbl p ON e.pos_id  = p.pos_id
        WHERE DATE_FORMAT(pr.payperiod_start, '%Y-%m') = ?
        ORDER BY pr.payroll_id DESC
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