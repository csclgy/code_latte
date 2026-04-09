<?php
// $host   = '192.168.1.184';
// $db     = 'mis_coffee';
// $user   = 'student';
// $pass   = '1234'; 

$host   = 'localhost';
$db     = 'mis_coffee';
$user   = 'root';
$pass   = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}
?>