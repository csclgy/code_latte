<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$username = isset($data['user_name'])     ? trim($data['user_name'])  : '';
$password = isset($data['user_password']) ? $data['user_password']    : '';

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'error' => 'Username and password are required.']);
    exit;
}

// ── FETCH USER (Updated to use PDO $pdo) ──
try {
    $stmt = $pdo->prepare("SELECT emp_id, user_name, user_password, user_access FROM employee_tbl WHERE user_name = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'Invalid username or password.']);
        exit;
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    exit;
}

// ── PASSWORD VERIFICATION ──
$stored   = $user['user_password'];
$verified = false;

if (strpos($stored, '$2y$') === 0 || strpos($stored, '$2a$') === 0) {
    $verified = password_verify($password, $stored);        // bcrypt
} elseif (strlen($stored) === 32) {
    $verified = (md5($password) === $stored);               // MD5
} elseif (strlen($stored) === 40) {
    $verified = (sha1($password) === $stored);              // SHA1
} else {
    $verified = ($password === $stored);                    // plain text fallback
}

if (!$verified) {
    echo json_encode(['success' => false, 'error' => 'Invalid username or password.']);
    exit;
}

// ── ACCESS MAP ──
$access_map = [
    'HR_admin'      => ['hr_dashboard', 'hr_employees', 'hr_attendance', 'hr_payroll', 'hr_recruitment'],
    'HR_staff'   => ['hr_dashboard','hr_employees', 'hr_attendance', 'hr_recruitment'],
    'HR_payroll' => ['hr_dashboard', 'hr_payroll'],
];

$access = $user['user_access'];

if (!array_key_exists($access, $access_map)) {
    echo json_encode(['success' => false, 'error' => 'Access denied.']);
    exit;
}

// ── SET SESSION ──
$_SESSION['emp_id']      = $user['emp_id'];
$_SESSION['user_name']   = $user['user_name'];
$_SESSION['user_access'] = $access;
$_SESSION['permissions'] = $access_map[$access];

echo json_encode([
    'success'     => true,
    'user_access' => $access,
    'permissions' => $_SESSION['permissions'],
    'redirect'    => '../hrm_module/pages/dashboard_hr.php'
]);
exit;