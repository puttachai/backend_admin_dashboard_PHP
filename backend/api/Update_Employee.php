<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// require 'conndb.php';
require_once(__DIR__ . '/db/conndb.php');

// รับค่าจาก frontend
$data = json_decode(file_get_contents("php://input"), true);

// ตรวจสอบว่ามี ID และข้อมูลที่ต้องการอัปเดตหรือไม่
// รับค่าจาก form-data โดยใช้ $_POST และ $_FILES
if (empty($_POST['id'])) {
    echo json_encode(["success" => false, "message" => "Missing employee ID"]);
    exit;
}

$id = $_POST['id'];
$full_name = $_POST['full_name'] ?? '';
$email = $_POST['email'] ?? '';
$telephone = $_POST['telephone'] ?? '';
$address = $_POST['address'] ?? '';
$department = $_POST['department'] ?? '';
// $salary = $_POST['salary'] ?? '';
$salary = isset($_POST['salary']) && $_POST['salary'] !== '' 
    ? (float)$_POST['salary'] 
    : 0;
$status = $_POST['status'] ?? '';
// $start_work = $_POST['start_work'] ?? '';
$start_work = !empty($_POST['start_work']) ? $_POST['start_work'] : null;

// เพิ่มก่อน imagePath prepare SQL
$imagePath = null;

// หากมีการอัปโหลดรูปภาพใหม่
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../img/profile/'; ///../../img/profile/
    $imageName = uniqid() . '_' . basename($_FILES['image']['name']);
    $imagePathOnServer = $uploadDir . $imageName;

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePathOnServer)) {
        // $imagePath = 'http://localhost:8000/api_admin_dashboard/backend/img/profile/' . $imageName;
        $imagePath = 'https://api-sale.dpower.co.th/api_admin_dashboard/backend/img/profile/' . $imageName;
    } else {
        echo json_encode(["success" => false, "message" => "Failed to upload image."]);
        exit;
    }
}
try {
    // สร้าง SQL พื้นฐาน
    $sql = "UPDATE employee SET 
                full_name = :full_name,
                email = :email,
                telephone = :telephone,
                address = :address,
                department = :department,
                salary = :salary,
                status = :status,
                start_work = :start_work";

    // ถ้ามี imagePath ให้เพิ่มเข้าไปใน SQL
    if ($imagePath) {
        $sql .= ", image_path = :image_path";
    }

    $sql .= " WHERE id = :id";

    $stmt = $pdo->prepare($sql);

    // เตรียมค่าที่จะ bind
    $params = [
        ':id' => $id,
        ':full_name' => $full_name,
        ':email' => $email,
        ':telephone' => $telephone,
        ':address' => $address,
        ':department' => $department,
        ':salary' => $salary,
        ':status' => $status,
        ':start_work' => $start_work
    ];

    if ($imagePath) {
        $params[':image_path'] = $imagePath;
    }

    $stmt->execute($params);

    echo json_encode([
        "success" => true,
        "message" => "Update Data Successfully",
        "updated_id" => $id
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}