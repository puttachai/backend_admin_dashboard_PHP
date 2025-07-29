<?php

header("Access-Control-Allow-Origin: *"); // หรือระบุ origin เฉพาะแทน * ก็ได้
header("Content-Type: application/json; charset=UTF-8");

// $localhost = 'https://backend2.d-power.online:56916';
// $dbname = 'admin_dashboard';
// $username = 'root';
// $password = '#Dpower123';
// $port = '3306';
$localhost = 'localhost';
$dbname = 'admin_dashboard';
$username = 'root';
$password = '';
$port = '3307';

try {
    $dsn = "mysql:host=$localhost;port=$port;dbname=$dbname;charset=utf8";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ถ้าเชื่อมต่อได้สำเร็จ
    // echo json_encode([
    //     "success" => true,
    //     "message" => "Connection successful"
    // ]);
} catch (PDOException $e) {
    // ถ้าเชื่อมต่อไม่สำเร็จ
    http_response_code(500);
    // echo json_encode([
    //     "success" => false,
    //     "message" => "The connection has failed.: " . $e->getMessage()
    // ]);
    exit;
}
?>
