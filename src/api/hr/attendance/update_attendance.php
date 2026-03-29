<?php
ob_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../../config/db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['attendance_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing attendance_id.']);
    exit;
}

try {
    // Recalculate late_minutes if status is Late
    $late_minutes = 0;
    if ($data['status'] === 'Late' && !empty($data['time_in'])) {
        $empStmt = $pdo->prepare("SELECT emp_schedule FROM employee_tbl WHERE emp_id = ?");
        $empStmt->execute([$data['emp_id']]);
        $emp = $empStmt->fetch();

        if ($emp) {
            $scheduleStart = [
                'Morning'   => '06:00',
                'Afternoon' => '14:00',
                'Evening'   => '22:00',
            ];
            $schedule     = $emp['emp_schedule'] ?? 'Morning';
            $expectedTime = $scheduleStart[$schedule] ?? '06:00';

            $expected     = strtotime($expectedTime);
            $actual       = strtotime($data['time_in']);
            $diff         = ($actual - $expected) / 60;
            $late_minutes = $diff > 0 ? round($diff) : 0;
        }
    }

    $stmt = $pdo->prepare("
        UPDATE attendance_tbl SET
            emp_id          = :emp_id,
            attendance_date = :attendance_date,
            time_in         = :time_in,
            time_out        = :time_out,
            late_minutes    = :late_minutes,
            status          = :status,
            remarks         = :remarks
        WHERE attendance_id = :attendance_id
    ");

    $stmt->execute([
        ':emp_id'          => $data['emp_id'],
        ':attendance_date' => $data['attendance_date'],
        ':time_in'         => $data['time_in']        ?? null,
        ':time_out'        => $data['time_out']       ?? null,
        ':late_minutes'    => $late_minutes,
        ':status'          => $data['status'],
        ':remarks'         => $data['remarks']        ?? null,
        ':attendance_id'   => $data['attendance_id'],
    ]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>