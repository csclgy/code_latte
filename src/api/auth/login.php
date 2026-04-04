<?php
ob_start();
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../config/db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['user_name']) || empty($data['user_password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Username and password are required.']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            e.emp_id,
            e.emp_fname,
            e.emp_lname,
            e.emp_email,
            e.emp_status,
            e.user_name,
            e.user_password,
            e.dept_id,
            d.dept_name,
            p.pos_name
        FROM employee_tbl e
        LEFT JOIN department_tbl d ON e.dept_id = d.dept_id
        LEFT JOIN position_tbl   p ON e.pos_id  = p.pos_id
        WHERE e.user_name = ?
        LIMIT 1
    ");
    $stmt->execute([$data['user_name']]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);

    // User not found
    if (!$employee) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Invalid username or password.']);
        exit;
    }

    // Check if account is active
    if ($employee['emp_status'] !== 'Active') {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Your account is inactive. Please contact your administrator.']);
        exit;
    }

    // Verify password
    if (!password_verify($data['user_password'], $employee['user_password'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Invalid username or password.']);
        exit;
    }

    // Store session
    $_SESSION['emp_id']    = $employee['emp_id'];
    $_SESSION['emp_fname'] = $employee['emp_fname'];
    $_SESSION['emp_lname'] = $employee['emp_lname'];
    $_SESSION['user_name'] = $employee['user_name'];
    $_SESSION['dept_id']   = $employee['dept_id'];
    $_SESSION['dept_name'] = $employee['dept_name'];
    $_SESSION['pos_name']  = $employee['pos_name'];
    $_SESSION['logged_in'] = true;

    // Redirect based on department
    $redirect = match((int)$employee['dept_id']) {
        1 => '/hrm_module/pages/dashboard_hr.php',      // HR
        2 => '/hrm_module/pages/dashboard_inv.php',     // Inventory
        default => '/hrm_module/pages/dashboard_hr.php' // fallback
    };

    ob_clean();
    echo json_encode([
        'success'   => true,
        'emp_id'    => $employee['emp_id'],
        'name'      => $employee['emp_fname'] . ' ' . $employee['emp_lname'],
        'dept_id'   => $employee['dept_id'],
        'dept_name' => $employee['dept_name'],
        'pos_name'  => $employee['pos_name'],
        'redirect'  => $redirect,
    ]);

} catch (PDOException $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>