<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// // Handle preflight request
// if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
//     http_response_code(200);
//     exit();
// }

require 'conndb.php'; // เชื่อมต่อฐานข้อมูล

$rawData = json_decode(file_get_contents("php://input"), true);
// ตรวจสอบว่ารับค่ามาครบหรือไม่
$requiredFields = ['full_name', 'email', 'password'];
$missing = array_filter($requiredFields, fn($key) => empty($rawData[$key]));

if (!empty($missing)) {
    echo json_encode([
        "success" => false,
        "message" => "Lack of information: " . implode(', ', $missing),
        "postData" => $_POST
    ]);
    exit;
}

// รับค่าจากฟอร์ม
$emp_ids = $rawData['emp_ids'];
$full_name = $rawData['full_name'] ?? '';
$email = $rawData['email'] ?? '';
$password_raw = $rawData['password'] ?? '';
$password_hashed = hash('sha512', $password_raw); // เข้ารหัส password

try {
    $sql = "INSERT INTO employee (emp_ids, full_name, email, password) 
            VALUES (:emp_ids, :full_name, :email, :password)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':emp_ids' => $emp_ids,
        ':full_name' => $full_name,
        ':email' => $email,
        ':password' => $password_hashed
    ]);

    $lastInsertId = $pdo->lastInsertId();

    echo json_encode([
        "success" => true,
        "message" => "Employee registered successfully. ID: $lastInsertId"
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}
