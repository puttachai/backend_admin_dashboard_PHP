<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// var_dump('asdasdasdta');die;

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

// var_dump('asdasdasdta');die;

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
// New fields 
$adminid = $_POST['adminid'] ?? null;
$account = $_POST['account'] ?? '';
// $password = $_POST['password'] ?? '';  // ใช้ชื่ออื่นแทนกันกับ password พนักงาน
// $crm_password = $_POST['password'] ?? '';  // ใช้ชื่ออื่นแทนกันกับ password พนักงาน
$nickname_admin = $_POST['nickname_admin'] ?? '';
$customer_id = $_POST['customer_id'] ?? null;
$nickname = $_POST['nickname'] ?? '';
$contact = $_POST['contact'] ?? '';
$mobile = $_POST['mobile'] ?? '';
$customer_no = $_POST['customer_no'] ?? '';
$sale_no = $_POST['sale_no'] ?? '';
$groups = $_POST['groups'] ?? null;
$label = $_POST['label'] ?? '';
$value = $_POST['value'] ?? '';
$level = $_POST['level'] ?? null;

// var_dump($sale_no);die;

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
        $imagePath = 'http://localhost:8000/api_admin_dashboard/backend/img/profile/' . $imageName;
        // $imagePath = 'https://api-sale.dpower.co.th/api_admin_dashboard/backend/img/profile/' . $imageName;
    } else {
        echo json_encode(["success" => false, "message" => "Failed to upload image."]);
        exit;
    }
}
try {
    // สร้าง SQL พื้นฐาน
    // $sql = "UPDATE employee SET 
    //             full_name = :full_name,
    //             email = :email,
    //             telephone = :telephone,
    //             address = :address,
    //             department = :department,
    //             salary = :salary,
    //             status = :status,
    //             start_work = :start_work";

    // var_dump('asdasdasdta');die;
   
   $sql = "UPDATE employee SET 
            full_name = :full_name,
            email = :email,
            telephone = :telephone,
            address = :address,
            department = :department,
            salary = :salary,
            status = :status,
            start_work = :start_work,
            adminid = :adminid,
            account = :account,
            -- password = :password,
            -- crm_password = :password,
            nickname_admin = :nickname_admin,
            customer_id = :customer_id,
            nickname = :nickname,
            contact = :contact,
            mobile = :mobile,
            customer_no = :customer_no,
            sale_no = :sale_no,
            groups = :groups,
            label = :label,
            value = :value,
            level = :level";


    // ถ้ามี imagePath ให้เพิ่มเข้าไปใน SQL
    if ($imagePath) {
        $sql .= ", image_path = :image_path";
    }

    $sql .= " WHERE id = :id";

    $stmt = $pdo->prepare($sql);

    // เตรียมค่าที่จะ bind
    // $params = [
    //     ':id' => $id,
    //     ':full_name' => $full_name,
    //     ':email' => $email,
    //     ':telephone' => $telephone,
    //     ':address' => $address,
    //     ':department' => $department,
    //     ':salary' => $salary,
    //     ':status' => $status,
    //     ':start_work' => $start_work
    // ];

    $params = [
    ':id'            => $id,
    ':emp_ids'       => $_POST['emp_ids'] ?? 0,
    ':customer_name' => $_POST['nickname'] ?? '' ?? ($_POST['customer_name']),
    ':full_name'     => $_POST['full_name'] ?? '',
    ':email'         => $_POST['email'] ?? '',
    ':telephone'     => $_POST['telephone'] ?? '',
    ':address'       => $_POST['address'] ?? '',
    ':department'    => $_POST['department'] ?? '',
    ':salary'        => isset($_POST['salary']) ? (float)$_POST['salary'] : 0,
    ':status'        => $_POST['status'] ?? 'Normal',
    ':start_work'    => !empty($_POST['start_work']) ? $_POST['start_work'] : null,
    ':adminid'       => $_POST['adminid'] ?? null,
    ':account'       => $_POST['account'] ?? '',
    ':password'  => $_POST['password'] ?? '',
    ':nickname_admin'=> $_POST['nickname_admin'] ?? '',
    ':customer_id'   => $_POST['customer_id'] ?? null,
    ':nickname'      => $_POST['nickname'] ?? '',
    ':contact'       => $_POST['contact'] ?? '',
    ':mobile'        => $_POST['mobile'] ?? '',
    ':customer_no'   => $_POST['customer_no'] ?? '',
    ':sale_no'       => $_POST['sale_no'] ?? '',
    ':groups'        => isset($_POST['groups']) ? (int)$_POST['groups'] : null,
    ':label'         => $_POST['label'] ?? '',
    ':value'         => $_POST['value'] ?? '',
    ':level'         => isset($_POST['level']) ? (int)$_POST['level'] : null
];

        // var_dump($params);die;

    if ($imagePath) {
        $params[':image_path'] = $imagePath;
    }
// var_dump($params);die;
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