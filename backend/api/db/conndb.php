<?php
header("Access-Control-Allow-Origin: *"); // หรือระบุ origin เฉพาะแทน * ก็ได้
header("Content-Type: application/json; charset=UTF-8");

// $localhost = 'localhost';
// $dbname = 'admin_dashboard';
// $username = 'root';
// $password = '#Dpower123';
// $port = '3307';
$localhost = 'mysql';
$dbname = 'admin_dashboard';
$username = 'root';
$password = 'root';
// $port = '3307';

try {
    $dsn = "mysql:host=$localhost;dbname=$dbname;charset=utf8";
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
      echo json_encode(["success"=>false, "message"=>$e->getMessage()]);
    // echo json_encode([
    //     "success" => false,
    //     "message" => "The connection has failed.: " . $e->getMessage()
    // ]);
    exit;
}
?>
