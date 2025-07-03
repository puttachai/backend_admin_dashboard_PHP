<?php
// ✅ แก้ไข: เพิ่ม Header สำหรับ CORS
header("Access-Control-Allow-Origin: *"); // หรือใส่ origin เช่น http://localhost:5173
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

require 'conndb.php'; // เชื่อมต่อผ่าน PDO แล้วในไฟล์นี้

$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!isset($data['account']) || !isset($data['password'])) {
    echo json_encode(["success" => false, "message" => "Missing email or password"]);
    exit;
}

$email = $data['account'];
$password = $data['password'];

// เข้ารหัสด้วย SHA-512
$hashedPassword = hash('sha512', $password);

try {
    $sql = "SELECT * FROM employee WHERE email = :email AND password = :password";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':email' => $email,
        ':password' => $hashedPassword
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo json_encode([
            "success" => true,
            "userId" => $user['id'],
            "email" => $user['email'],
            "image_path" => $user['image_path'], // ← เพิ่มตรงนี้
            "message" => "Login successful"
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Incorrect email or password"]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
}
?>
