<?php
session_start();

// ── SESSION GUARD ──
// Remove the hardcoded $required_page line that was here before.
// Instead, check if it was set by the calling page. 
// If it wasn't set, default to dashboard as a safety measure.
if (!isset($required_page)) {
    $required_page = 'hr_dashboard'; 
}

// ── NOT LOGGED IN ──
if (!isset($_SESSION['emp_id'])) {
    header('Location: ../index.php');
    exit;
}

// ── NO ACCESS ──
// This will now use the $required_page you defined in your module file!
if (!isset($_SESSION['permissions']) || !in_array($required_page, $_SESSION['permissions'])) {
    
    $page_map = [
        'hr_dashboard'   => 'dashboard_hr.php',
        'hr_employees'   => 'employee_management_hr.php',
        'hr_attendance'  => 'attendance_hr.php',
        'hr_payroll'     => 'payroll_hr.php',
        'hr_recruitment' => 'recruitment_hr.php',
    ];

    $first = $_SESSION['permissions'][0] ?? null;
    $redirect = (isset($first) && isset($page_map[$first])) ? $page_map[$first] : '../index.php';
    
    header('Location: ' . $redirect);
    exit;
}