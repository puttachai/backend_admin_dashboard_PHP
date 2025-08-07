<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

// require 'conndb.php';
require_once(__DIR__ . '../db/conndb.php');

// ตรวจสอบว่ามีฟิลด์จำเป็นครบหรือไม่
$requiredFields = ['emp_ids', 'fullName', 'password', 'phone', 'customer_no'];
// $requiredFields = ['emp_ids', 'fullName', 'password', 'phone'];
// $requiredFields = ['emp_ids', 'fullName', 'email', 'password', 'phone', 'address', 'department', 'salary', 'status', 'start_work'];
$missing = array_filter($requiredFields, fn($key) => empty($_POST[$key]));

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    $missing[] = 'image';
}

// if (!empty($missing)) {
//     // echo json_encode(["success" => false, "message" => "ขาดข้อมูล: " . implode(', ', array_keys($missing))]); //ขาดข้อมูล
//     exit;
// }

if (!empty($missing)) {
    echo json_encode([
        "success" => false,
        "message" => "Lack of information: " . implode(', ', $missing), // แสดงชื่อฟิลด์ที่ขาด
        "postData" => $_POST, // ดูข้อมูลที่ถูกส่งมาจริง
        "imageStatus" => isset($_FILES['image']) ? $_FILES['image']['error'] : 'no image'
    ]);
    exit;
}

// กำหนดโฟลเดอร์ที่เก็บรูปภาพในเครื่อง
$uploadDir = __DIR__ . '/../img/profile/'; // ./img/profile/ & /../../img/profile/ 
$imageName = uniqid() . '_' . basename($_FILES['image']['name']);
$imagePathOnServer = $uploadDir . $imageName;

// ตรวจสอบว่าสร้างโฟลเดอร์ไว้แล้ว
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePathOnServer)) {
    // สร้าง URL สำหรับเก็บลงฐานข้อมูล
    $imagePath = 'http://localhost/api_admin_dashboard/backend/img/profile/' . $imageName;
    // $imagePath = 'http://localhost/api_admin_dashboard/backend/img/profile/' . $imageName;
} else {
    echo json_encode(["success" => false, "message" => "Failed to upload image."]);
    exit;
}

// รับค่าจากฟอร์ม
$emp_ids = $_POST['emp_ids'];
$full_name = $_POST['fullName'];
$email = $_POST['email'];
$password_raw = $_POST['password'];
$password_hashed = hash('sha512', $password_raw);
$telephone = $_POST['phone'];
$address = $_POST['address'];
$department = $_POST['department'];
$salary = $_POST['salary'];
$status = $_POST['status'];
$start_work = $_POST['start_work'];
$customer_no = $_POST['customer_no'];

try {
    $sql = "INSERT INTO employee (
                emp_ids, image_path, full_name, email, password, telephone, address, department, salary, status, start_work , customer_no
            ) VALUES (
                :emp_ids, :image_path, :full_name, :email, :password, :telephone, :address, :department, :salary, :status, :start_work, :customer_no
            )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':emp_ids' => $emp_ids,
        ':image_path' => $imagePath ? $imagePath : '',
        ':full_name' => $full_name,
        ':email' => $email ? $email : '',
        ':password' => $password_hashed,
        ':telephone' => $telephone ? $telephone : '',
        ':address' => $address ? $address : '',
        ':department' => $department ? $department : '',
        ':salary' => $salary ? $salary : 0,
        ':status' => $status ? $status : 'Normal',
        ':start_work' => $start_work, 
        ':customer_no' => $customer_no, 
    ]);
    
    $lastInsertId = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Data added successfully. The ID of the data.:' . $lastInsertId
    ]);
    exit; // ✅ เพิ่มความชัวร์ว่า PHP หยุดแค่นี้

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}


 // $lastInsertId = $pdo->lastInsertId();

    // echo json_encode(["success" => true, "message" => "เพิ่มพนักงานเรียบร้อยแล้ว"]);
    //เพิ่มข้อมูลสำเร็จ ไอดีของมูล
    // echo json_encode([
    //     'success' => true,
    //     'message' => 'Data added successfully. The ID of the data.: ' . $lastInsertId 
    //   ]);
    // exit; // ✅ เพิ่มความชัวร์ว่า PHP หยุดแค่นี้