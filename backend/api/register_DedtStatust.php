<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once(__DIR__ . '/db/conndb.php');



// ตรวจสอบฟิลด์ที่จำเป็น
$requiredFields = ['emp_ids', 'fullName', 'password', 'phone', 'customer_no'];
$missing = array_filter($requiredFields, fn($key) => empty($_POST[$key]));

if (!empty($missing)) {
    echo json_encode([
        "success" => false,
        "message" => "Lack of information: " . implode(', ', $missing),
        "postData" => $_POST
    ]);
    exit;
}


// กำหนดโฟลเดอร์เก็บรูป
$uploadDir = __DIR__ . '/../img/profile/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// ค่า default
$imageName = null;
$imagePath = null;

// ตรวจสอบว่ามีการอัปโหลดรูปเข้ามาหรือไม่
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    // กรณีมีการอัปโหลดไฟล์
    $imageName = uniqid() . '_' . basename($_FILES['image']['name']);
    $imagePathOnServer = $uploadDir . $imageName;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePathOnServer)) {
        $imagePath = 'http://localhost:8000/api_admin_dashboard/backend/img/profile/' . $imageName;
        // $imagePath = 'https://api-sale.dpower.co.th/api_admin_dashboard/backend/img/profile/' . $imageName;
    }
} else {
    // ❌ กรณีไม่ได้อัปโหลด → copy default.jpg มาเป็นไฟล์ใหม่
    $defaultFile = $uploadDir . 'default.jpg';
    if (file_exists($defaultFile)) {
        $imageName = uniqid() . '_default.jpg';
        $newPath = $uploadDir . $imageName;

        // copy ไฟล์ default.jpg ไปที่ไฟล์ใหม่
        copy($defaultFile, $newPath);

        $imagePath = 'http://localhost:8000/api_admin_dashboard/backend/img/profile/' . $imageName;
        //  $imagePath = 'https://api-sale.dpower.co.th/api_admin_dashboard/backend/img/profile/' . $imageName;
    } else {
        // fallback ถ้าไม่มี default.jpg จริง ๆ
        $imageName = "default.jpg";
        $imagePath = 'http://localhost:8000/api_admin_dashboard/backend/img/profile/' . $imageName;
        // $imagePath = 'https://api-sale.dpower.co.th/api_admin_dashboard/backend/img/profile/' . $imageName;
    }
}



// รับค่าจากฟอร์ม
$emp_ids      = $_POST['emp_ids'];
$full_name    = $_POST['fullName'];
$email        = $_POST['email'] ?? '';
$password_raw = $_POST['password'];
$password_hashed = hash('sha512', $password_raw);
$telephone    = $_POST['phone'] ?? '';
$address      = $_POST['address'] ?? '';
$department   = $_POST['department'] ?? '';
$salary       = isset($_POST['salary']) && $_POST['salary'] !== '' ? (float)$_POST['salary'] : 0;
$status       = $_POST['status'] ?? 'Normal';
$start_work   = !empty($_POST['start_work']) ? $_POST['start_work'] : null;
$customer_no  = $_POST['customer_no'];


// $customer = json_decode($_POST['customer'], true);
// รับ customer (ถ้ามี) หรือ null ถ้าไม่มี
// $customer = isset($_POST['customer']) ? json_decode($_POST['customer'], true) : null;
$customers = isset($_POST['customers']) ? json_decode($_POST['customers'], true) : [];

// var_dump($customers);die;

// $customer_name = $customers['nickname'] ?? '';
// $nickname      = $customers['nickname'] ?? '';
// $contact       = $customers['contact'] ?? '';
// $mobile        = $customers['mobile'] ?? '';
// $sale_no       = $customers['sale_no'] ?? '';
// $groups        = $customers['groups'] ?? null;
// $label         = $customers['label'] ?? '';
// $value         = $customers['value'] ?? '';
// $level         = $customers['level'] ?? null;

$customer_names = [];
$nicknames      = [];
$contacts       = [];
$mobiles        = [];
$sale_nos       = [];
$groups_list    = [];
$labels         = [];
$values         = [];
$levels         = [];

foreach ($customers as $cust) {
    if (!empty($cust['nickname']))  $customer_names[] = $cust['nickname'];
    if (!empty($cust['nickname']))  $nicknames[]      = $cust['nickname'];
    if (!empty($cust['contact']))   $contacts[]       = $cust['contact'];
    if (!empty($cust['mobile']))    $mobiles[]        = $cust['mobile'];
    if (!empty($cust['sale_no']))   $sale_nos[]       = $cust['sale_no'];
    if (isset($cust['groups']))     $groups_list[]    = $cust['groups'];
    if (!empty($cust['label']))     $labels[]         = $cust['label'];
    if (!empty($cust['value']))     $values[]         = $cust['value'];
    if (isset($cust['level']))      $levels[]         = $cust['level'];
}

// รวมเป็นสตริงคั่นด้วยคอมมา
$customer_name = implode(', ', $customer_names);
$nickname      = implode(', ', $nicknames);
$contact       = implode(', ', $contacts);
$mobile        = implode(', ', $mobiles);
$sale_no       = implode(', ', $sale_nos);
$groups        = implode(', ', $groups_list);
$label         = implode(', ', $labels);
$value         = implode(', ', $values);
$level         = implode(', ', $levels);

// var_dump($customer_name);die;

// ถ้ามี customers
if ($customers) {
    // $customer_no = $customer['customer_no'] ?? null;ฃ
     $customer_no = implode(',', array_column($customers, 'customer_no')); // เก็บ customer_no เป็น string comma-separated
} else {
    $customer_no = null; // ไม่มีลูกค้า → เซ็ตเป็น null
}


try {

    $pdo->beginTransaction();

    // 1️⃣ เพิ่มพนักงานใน debt_collectors
    $stmtCollector = $pdo->prepare("
        INSERT INTO debt_collectors (collector_code, full_name, email, password, telephone, address, department, salary, status, start_work, image_path)
        VALUES (:collector_code, :full_name, :email, :password, :telephone, :address, :department, :salary, :status, :start_work, :image_path)
        ON DUPLICATE KEY UPDATE full_name=VALUES(full_name)
    ");
    $stmtCollector->execute([
        ':collector_code' => $emp_ids,
        ':full_name'      => $full_name,
        ':email'          => $email,
        ':password'       => $password_hashed,
        ':telephone'      => $telephone,
        ':address'        => $address,
        ':department'     => $department,
        ':salary'         => $salary,
        ':status'         => $status,
        ':start_work'     => $start_work,
        ':image_path'     => $imagePath,
    ]);
    $collectorId = $pdo->lastInsertId();


    $customerId = null; // ✅ ประกาศค่าเริ่มต้นก่อนใช้
    // 2️⃣ เพิ่มลูกค้าใน customers และความสัมพันธ์ collector ↔ customer
    if (!empty($customers)) {

        foreach ($customers as $cust) {
            $custNo = $cust['customer_no'] ?? null;
            $custName = $cust['nickname'] ?? '';
            $contact  = $cust['contact'] ?? '';
            $mobile   = $cust['mobile'] ?? '';
            $sale_no  = $cust['sale_no'] ?? '';
            $groups   = $cust['groups'] ?? null;
            $label    = $cust['label'] ?? '';
            $value    = $cust['value'] ?? '';
            $level    = $cust['level'] ?? null;

            // เพิ่มลูกค้าใน customers
            $stmtCustomer = $pdo->prepare("
                INSERT INTO customers (customer_no, customer_name, nickname, contact, mobile, sale_no, `groups`, label, value, level)
                VALUES (:customer_no, :customer_name, :nickname, :contact, :mobile, :sale_no, :groups, :label, :value, :level)
                ON DUPLICATE KEY UPDATE customer_name=VALUES(customer_name)
            ");
            $stmtCustomer->execute([
                ':customer_no'   => $custNo,
                ':customer_name' => $custName,
                ':nickname'      => $custName,
                ':contact'       => $contact,
                ':mobile'        => $mobile,
                ':sale_no'       => $sale_no,
                ':groups'        => $groups,
                ':label'         => $label,
                ':value'         => $value,
                ':level'         => $level,
            ]);
            $customerId = $pdo->lastInsertId();

            // เพิ่มความสัมพันธ์ collector ↔ customer
            $stmtAssign = $pdo->prepare("
                INSERT INTO Debt_Collector_Assignments (collector_id, customer_id)
                VALUES (:collector_id, :customer_id)
                ON DUPLICATE KEY UPDATE assigned_date=NOW()
            ");
            $stmtAssign->execute([
                ':collector_id' => $collectorId,
                ':customer_id'  => $customerId,
            ]);
        }
    }


    // 1.5️⃣ เพิ่มข้อมูลเข้า employee
    $stmtEmployee = $pdo->prepare("
        INSERT INTO employee (emp_ids, customer_name, full_name, email, password, telephone, address, department, salary, status, start_work, image_path, customer_no)
        VALUES (:emp_ids, :customer_name, :full_name, :email, :password, :telephone, :address, :department, :salary, :status, :start_work, :image_path, :customer_no)
        ON DUPLICATE KEY UPDATE full_name=VALUES(full_name)
    ");
    $stmtEmployee->execute([
        ':emp_ids'      => $emp_ids,
        ':customer_name' => $customer_name,
        ':full_name'    => $full_name,
        ':email'        => $email,
        ':password'     => $password_hashed,
        ':telephone'    => $telephone,
        ':address'      => $address,
        ':department'   => $department,
        ':salary'       => $salary,
        ':status'       => $status,
        ':start_work'   => $start_work,
        ':image_path'   => $imagePath,
        ':customer_no'  => $customer_no,
    ]);
    $employeeId = $pdo->lastInsertId();

    $pdo->commit();

    // var_dump('Test');die; // Debug line to check if the script is being executed

    echo json_encode([
        'success' => true,
        'message' => 'Employee and customer added successfully',
        'collector_id' => $collectorId,
        'customer_id'  => $customerId,
        'image_used'   => $imagePath
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}



// กำหนดโฟลเดอร์เก็บรูป
// $uploadDir = __DIR__ . '/../img/profile/';
// if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

// // จัดการรูป
// $imageName = null;
// $imagePath = null;
// if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
//     $imageName = uniqid() . '_' . basename($_FILES['image']['name']);
//     $imagePathOnServer = $uploadDir . $imageName;
//     if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePathOnServer)) {
//         $imagePath = 'https://api-sale.dpower.co.th/api_admin_dashboard/backend/img/profile/' . $imageName;
//     }
// } else {
//     $defaultFile = $uploadDir . 'default.jpg';
//     $imageName = uniqid() . '_default.jpg';
//     $newPath = $uploadDir . $imageName;
//     if (file_exists($defaultFile)) copy($defaultFile, $newPath);
//     $imagePath = 'https://api-sale.dpower.co.th/api_admin_dashboard/backend/img/profile/' . $imageName;
// }




    // // 2️⃣ เพิ่มลูกค้าใน customers
    // $stmtCustomer = $pdo->prepare("
    //     INSERT INTO customers (customer_no, customer_name, nickname, contact, mobile, sale_no, `groups`, label, value, level)
    //     VALUES (:customer_no, :customer_name, :nickname, :contact, :mobile, :sale_no, :groups, :label, :value, :level)
    //     ON DUPLICATE KEY UPDATE customer_name=VALUES(customer_name)
    // ");
    // $stmtCustomer->execute([
    //     ':customer_no'   => $customer_no,
    //     ':customer_name' => $customer_name,
    //     ':nickname'      => $nickname,
    //     ':contact'       => $contact,
    //     ':mobile'        => $mobile,
    //     ':sale_no'       => $sale_no,
    //     ':groups'        => $groups,
    //     ':label'         => $label,
    //     ':value'         => $value,
    //     ':level'         => $level,
    // ]);
    // $customerId = $pdo->lastInsertId();

    // // 3️⃣ เพิ่มความสัมพันธ์ใน Debt_Collector_Assignments
    // $stmtAssign = $pdo->prepare("
    //     INSERT INTO Debt_Collector_Assignments (collector_id, customer_id)
    //     VALUES (:collector_id, :customer_id)
    //     ON DUPLICATE KEY UPDATE assigned_date=NOW()
    // ");
    // $stmtAssign->execute([
    //     ':collector_id' => $collectorId,
    //     ':customer_id'  => $customerId,
    // ]);
    
    // var_dump($customer_name);die; // Debug line to check if customerId is set correctly
