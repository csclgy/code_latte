<?php
ob_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../../config/db.php';

$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (empty($data['emp_id']) || empty($data['attendance_date']) || empty($data['status'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields: emp_id, attendance_date, status']);
    exit;
}

// Check if attendance already logged for this employee on this date
$check = $pdo->prepare("
    SELECT attendance_id FROM attendance_tbl 
    WHERE emp_id = ? AND attendance_date = ?
");
$check->execute([$data['emp_id'], $data['attendance_date']]);
if ($check->fetch()) {
    http_response_code(409);
    echo json_encode(['success' => false, 'error' => 'Attendance already logged for this employee on this date.']);
    exit;
}

try {
    // Calculate late_minutes if status is Late
    $late_minutes = 0;
    if ($data['status'] === 'Late' && !empty($data['time_in'])) {
        // get employee schedule to calculate how late
        $empStmt = $pdo->prepare("SELECT emp_schedule FROM employee_tbl WHERE emp_id = ?");
        $empStmt->execute([$data['emp_id']]);
        $emp = $empStmt->fetch();

        if ($emp) {
            // scheduled start times
            $scheduleStart = [
                'Morning'   => '06:00',
                'Afternoon' => '14:00',
                'Evening'   => '22:00',
            ];
            $schedule     = $emp['emp_schedule'] ?? 'Morning';
            $expectedTime = $scheduleStart[$schedule] ?? '06:00';

            $expected  = strtotime($expectedTime);
            $actual    = strtotime($data['time_in']);
            $diff      = ($actual - $expected) / 60; // in minutes
            $late_minutes = $diff > 0 ? round($diff) : 0;
        }
    }

    $stmt = $pdo->prepare("
        INSERT INTO attendance_tbl (
            emp_id,
            attendance_date,
            time_in,
            time_out,
            late_minutes,
            status,
            remarks
        ) VALUES (
            :emp_id,
            :attendance_date,
            :time_in,
            :time_out,
            :late_minutes,
            :status,
            :remarks
        )
    ");

    $stmt->execute([
        ':emp_id'          => $data['emp_id'],
        ':attendance_date' => $data['attendance_date'],
        ':time_in'         => $data['time_in']   ?? null,
        ':time_out'        => $data['time_out']  ?? null,
        ':late_minutes'    => $late_minutes,
        ':status'          => $data['status'],
        ':remarks'         => $data['remarks']   ?? null,
    ]);

    echo json_encode(['success' => true, 'attendance_id' => $pdo->lastInsertId()]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>