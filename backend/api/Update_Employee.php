<?php 
// header("Access-Control-Allow-Origin: *");
// header("Content-Type: application/json; charset=UTF-8");
// header("Access-Control-Allow-Methods: POST, OPTIONS");
// header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// require_once(__DIR__ . '/db/conndb.php');

// // ใช้ $_POST แทน เพราะ Vue ส่งเป็น multipart/form-data
// $data = $_POST;

// // Debug ดูข้อมูล
// // var_dump($data); die;

// if (empty($data['id'])) {
//     echo json_encode(["success" => false, "message" => "Missing employee ID"]);
//     exit;
// }

// $id          = $data['id'];
// $full_name   = $data['full_name'] ?? '';
// $email       = $data['email'] ?? '';
// $telephone   = $data['telephone'] ?? '';
// $address     = $data['address'] ?? '';
// $department  = $data['department'] ?? '';
// $salary      = isset($data['salary']) ? (float)$data['salary'] : 0;
// $status      = $data['status'] ?? 'Normal';
// $start_work  = !empty($data['start_work']) ? $data['start_work'] : null;

// // ฟิลด์เพิ่มเติม
// $adminid        = $data['adminid'] ?? null;
// $account        = $data['account'] ?? '';
// $nickname_admin = $data['nickname_admin'] ?? '';
// $customer_id    = $data['customer_id'] ?? null;
// $nickname       = $data['nickname'] ?? '';
// $contact        = $data['contact'] ?? '';
// $mobile         = $data['mobile'] ?? '';
// $customer_no    = $data['customer_no'] ?? '';
// $sale_no        = $data['sale_no'] ?? '';
// $groups         = isset($data['groups']) ? (int)$data['groups'] : null;
// $label          = $data['label'] ?? '';
// $value          = $data['value'] ?? '';
// $level          = isset($data['level']) ? (int)$data['level'] : null;
// $customer_name  = $data['customer_name'] ?? '';

// $imagePath = null; // <-- ป้องกัน undefined variable



// if (!$imagePath) {
//     // ถ้าไม่มีการอัปโหลดใหม่ + DB ไม่มีรูป → ใส่ default
//     $stmtCheck = $pdo->prepare("SELECT image_path FROM employee WHERE id = :id");
//     $stmtCheck->execute([':id' => $id]);
//     $currentImage = $stmtCheck->fetchColumn();

//     if (empty($currentImage)) {
//         $uploadDir = __DIR__ . '/../img/profile/';
//         $defaultFile = $uploadDir . 'default.jpg';
//         if (file_exists($defaultFile)) {
//             $imageName = uniqid() . '_default.jpg';
//             $newPath = $uploadDir . $imageName;
//             copy($defaultFile, $newPath);
//             $imagePath = 'http://localhost:8000/api_admin_dashboard/backend/img/profile/' . $imageName;
//             // $imagePath = 'https://api-sale.dpower.co.th/api_admin_dashboard/backend/img/profile/' . $imageName;
//         }
//     }
// }

// // รับข้อมูลลูกค้าจาก Frontend (array JSON)
// $customers = isset($data['customers']) ? json_decode($data['customers'], true) : [];
// $customer_no_list = [];
// $customer_names   = [];

// // var_dump($customers);die;

// if (!empty($customers)) {
//     foreach ($customers as $cust) {
//         $custNo   = $cust['customer_no'] ?? null;
//         $custName = $cust['nickname'] ?? '';

//         if (!$custNo) continue;

//         // var_dump('Test customers ');die;

//         $customer_no_list[] = $custNo;
//         $customer_names[]   = $custName;

//         // insert/update customer
//         $stmtCustomer = $pdo->prepare("
//             INSERT INTO customers (customer_no, customer_name, nickname, contact, mobile, sale_no, `groups`, label, `value`, `level`)
//             VALUES (:customer_no, :customer_name, :nickname, :contact, :mobile, :sale_no, :groups, :label, :value, :level)
//             ON DUPLICATE KEY UPDATE customer_name=VALUES(customer_name)
//         ");
//         $stmtCustomer->execute([
//             ':customer_no'   => $custNo,
//             ':customer_name' => $custName,
//             ':nickname'      => $custName,
//             ':contact'       => $cust['contact'] ?? '',
//             ':mobile'        => $cust['mobile'] ?? '',
//             ':sale_no'       => $cust['sale_no'] ?? '',
//             ':groups'        => $cust['groups'] ?? null,
//             ':label'         => $cust['label'] ?? '',
//             ':value'         => $cust['value'] ?? '',
//             ':level'         => $cust['level'] ?? null,
//         ]);

//         $customerId = $pdo->lastInsertId();

//         // สร้างความสัมพันธ์ employee ↔ customer
//         $stmtAssign = $pdo->prepare("
//             INSERT INTO Debt_Collector_Assignments (collector_id, customer_id)
//             VALUES (:collector_id, :customer_id)
//             ON DUPLICATE KEY UPDATE assigned_date=NOW()
//         ");
//         $stmtAssign->execute([
//             ':collector_id' => $id,
//             ':customer_id'  => $customerId,
//         ]);
//     }
// }

// // เก็บ customer_no และ customer_name เป็น comma-separated สำหรับ employee table
// $customer_no_csv   = implode(',', $customer_no_list);
// $customer_name_csv = implode(',', $customer_names);

// try {
//     $sql = "UPDATE employee SET 
//                 emp_ids        = :emp_ids,
//                 full_name      = :full_name,
//                 email          = :email,
//                 telephone      = :telephone,
//                 address        = :address,
//                 department     = :department,
//                 salary         = :salary,
//                 status         = :status,
//                 start_work     = :start_work,
//                 adminid        = :adminid,
//                 account        = :account,
//                 nickname_admin = :nickname_admin,
//                 customer_no    = :customer_no,
//                 customer_name  = :customer_name"
//             . ($imagePath ? ", image_path = :image_path" : "") . "
//             WHERE id = :id";

//     $stmt = $pdo->prepare($sql);

//     $params = [
//         ':id'             => $id,
//         ':emp_ids'        => $emp_ids,
//         ':full_name'      => $full_name,
//         ':email'          => $email,
//         ':telephone'      => $telephone,
//         ':address'        => $address,
//         ':department'     => $department,
//         ':salary'         => $salary,
//         ':status'         => $status,
//         ':start_work'     => $start_work,
//         ':adminid'        => $adminid,
//         ':account'        => $account,
//         ':nickname_admin' => $nickname_admin,
//         ':customer_no'    => $customer_no_csv,
//         ':customer_name'  => $customer_name_csv,
//     ];

//     if ($imagePath) {
//         $params[':image_path'] = $imagePath;
//     }

//     $stmt->execute($params);

//     echo json_encode([
//         "success" => true,
//         "message" => "Update Data Successfully",
//         "updated_id" => $id,
//         "image" => $imagePath
//     ]);

// } catch (PDOException $e) {
//     http_response_code(500);
//     echo json_encode([
//         "success" => false,
//         "message" => "Database error: " . $e->getMessage()
//     ]);
// }



header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

require_once(__DIR__ . '/db/conndb.php');

$data = $_POST;

if (empty($data['id'])) {
    echo json_encode(["success" => false, "message" => "Missing employee ID"]);
    exit;
}

$id         = $data['id'];
$emp_ids    = $data['emp_ids'] ?? '';
$full_name  = $data['full_name'] ?? '';
$email      = $data['email'] ?? '';
$telephone  = $data['telephone'] ?? '';
$address    = $data['address'] ?? '';
$department = $data['department'] ?? '';
$salary     = isset($data['salary']) ? (float)$data['salary'] : 0;
$status     = $data['status'] ?? 'Normal';
$start_work = !empty($data['start_work']) ? $data['start_work'] : null;

// สำหรับ image
$uploadDir = __DIR__ . '/../img/profile/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

$imagePath = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $imageName = uniqid() . '_' . basename($_FILES['image']['name']);
    $imagePathOnServer = $uploadDir . $imageName;
    if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePathOnServer)) {
        $imagePath = 'http://localhost:8000/api_admin_dashboard/backend/img/profile/' . $imageName;
        // $imagePath = 'https://api-sale.dpower.co.th/api_admin_dashboard/backend/img/profile/' . $imageName;
    }
} else {
    // ถ้าไม่มีรูป → ใช้ default
    $stmtCheck = $pdo->prepare("SELECT image_path FROM employee WHERE id = :id");
    $stmtCheck->execute([':id' => $id]);
    $currentImage = $stmtCheck->fetchColumn();
    if ($currentImage) {
        $imagePath = $currentImage;
    } else {
        $defaultFile = $uploadDir . 'default.jpg';
        if (file_exists($defaultFile)) {
            $imageName = uniqid() . '_default.jpg';
            copy($defaultFile, $uploadDir . $imageName);
            $imagePath = 'http://localhost:8000/api_admin_dashboard/backend/img/profile/' . $imageName;
            // $imagePath = 'https://api-sale.dpower.co.th/api_admin_dashboard/backend/img/profile/' . $imageName;
        }
    }
}

// รับข้อมูลลูกค้าหลายร้าน
$customers = isset($data['customers']) ? json_decode($data['customers'], true) : [];

// var_dump($customers);die;

$customer_no_list = [];
$customer_name_list = [];

try {
    $pdo->beginTransaction();

    // 1️⃣ เพิ่ม/อัปเดต debt_collectors
    $stmtCollector = $pdo->prepare("
        INSERT INTO debt_collectors (collector_code, full_name, email, telephone, address, department, salary, status, start_work, image_path)
        VALUES (:collector_code, :full_name, :email, :telephone, :address, :department, :salary, :status, :start_work, :image_path)
        ON DUPLICATE KEY UPDATE 
            full_name = VALUES(full_name),
            email = VALUES(email),
            telephone = VALUES(telephone),
            address = VALUES(address),
            department = VALUES(department),
            salary = VALUES(salary),
            status = VALUES(status),
            start_work = VALUES(start_work),
            image_path = VALUES(image_path)
    ");
    $stmtCollector->execute([
        ':collector_code' => $emp_ids,
        ':full_name'      => $full_name,
        ':email'          => $email,
        ':telephone'      => $telephone,
        ':address'        => $address,
        ':department'     => $department,
        ':salary'         => $salary,
        ':status'         => $status,
        ':start_work'     => $start_work,
        ':image_path'     => $imagePath,
    ]);

    // var_dump($stmtCollector);die;

    // var_dump('dasdas');die;

    // ✅ ดึง collector_id จริง
    $stmtGetCollectorId = $pdo->prepare("SELECT id FROM debt_collectors WHERE collector_code = :collector_code LIMIT 1");
    $stmtGetCollectorId->execute([':collector_code' => $emp_ids]);
    $collectorId = $stmtGetCollectorId->fetchColumn();

    // var_dump($collectorId);die;

    // 2️⃣ ดึง customer_no ปัจจุบันของ employee
    $stmtCurrent = $pdo->prepare("SELECT customer_no FROM employee WHERE id = :id");
    $stmtCurrent->execute([':id' => $id]);
    $currentCustomerCsv = $stmtCurrent->fetchColumn();
    $currentCustomers = $currentCustomerCsv ? explode(',', $currentCustomerCsv) : [];

    

    // 3️⃣ เตรียม customer_no ใหม่
    $newCustomerNos = [];
    foreach ($customers as $cust) {
        if (!empty($cust['customer_no'])) $newCustomerNos[] = $cust['customer_no'];
    }
    // var_dump('asdad');die;

    // 4️⃣ หาลูกค้าที่ถูกลบ
    $removedCustomerNos = array_diff($currentCustomers, $newCustomerNos);

    

    // 5️⃣ ลบความสัมพันธ์ใน Debt_Collector_Assignments
    if (!empty($removedCustomerNos)) {
        $inQuery = implode(',', array_fill(0, count($removedCustomerNos), '?'));
        $stmtDelAssignments = $pdo->prepare("
            DELETE FROM Debt_Collector_Assignments 
            WHERE collector_id = ? AND customer_id IN (
                SELECT id FROM customers WHERE customer_no IN ($inQuery)
            )
        ");
        $stmtDelAssignments->execute(array_merge([$collectorId], $removedCustomerNos));
    }

    // var_dump($removedCustomerNos);

    // 6️⃣ ลบลูกค้าที่ไม่มี collector ใดเชื่อมต่อ
    if (!empty($removedCustomerNos) && is_array($removedCustomerNos)) {
        // รีเซ็ต index
        $removedCustomerNos = array_values($removedCustomerNos);

        // แปลงค่าเป็นสตริง (ถ้าจำเป็น)
        $removedCustomerNos = array_map('strval', $removedCustomerNos);

        $inQuery = implode(',', array_fill(0, count($removedCustomerNos), '?'));

        $sql = "
            DELETE FROM customers
            WHERE customer_no IN ($inQuery)
            AND id NOT IN (SELECT customer_id FROM Debt_Collector_Assignments)
        ";
        $stmtDelCustomers = $pdo->prepare($sql);
        $stmtDelCustomers->execute($removedCustomerNos);
    }

    // if (!empty($removedCustomerNos)) {
    //     $inQuery = implode(',', array_fill(0, count($removedCustomerNos), '?'));
    //     $stmtDelCustomers = $pdo->prepare("
    //         DELETE FROM customers
    //         WHERE customer_no IN ($inQuery)
    //         AND id NOT IN (SELECT customer_id FROM Debt_Collector_Assignments)
    //     ");
    //     $stmtDelCustomers->execute($removedCustomerNos);
    // }
    

    // 7️⃣ เพิ่ม/อัปเดตลูกค้าใหม่และสร้างความสัมพันธ์
    $stmtGetCustomerId = $pdo->prepare("SELECT id FROM customers WHERE customer_no = :customer_no LIMIT 1");
    $lastCustomerId = null;

    

    foreach ($customers as $cust) {

        $custNo   = $cust['customer_no'] ?? null;
        $custName = $cust['nickname'] ?? '';
        if (!$custNo) continue;

        $customer_no_list[] = $custNo;
        $customer_name_list[] = $custName;

        $stmtCustomer = $pdo->prepare("
            INSERT INTO customers (customer_no, customer_name, nickname, contact, mobile, sale_no, `groups`, label, `value`, `level`)
            VALUES (:customer_no, :customer_name, :nickname, :contact, :mobile, :sale_no, :groups, :label, :value, :level)
            ON DUPLICATE KEY UPDATE 
                customer_name = VALUES(customer_name),
                nickname = VALUES(nickname),
                contact = VALUES(contact),
                mobile = VALUES(mobile),
                sale_no = VALUES(sale_no)
        ");
        $stmtCustomer->execute([
            ':customer_no'   => $custNo,
            ':customer_name' => $custName,
            ':nickname'      => $custName,
            ':contact'       => $cust['contact'] ?? '',
            ':mobile'        => $cust['mobile'] ?? '',
            ':sale_no'       => $cust['sale_no'] ?? '',
            ':groups'        => $cust['groups'] ?? null,
            ':label'         => $cust['label'] ?? '',
            ':value'         => $cust['value'] ?? '',
            ':level'         => $cust['level'] ?? null,
        ]);

        // ดึง customer_id จริง
        $stmtGetCustomerId->execute([':customer_no' => $custNo]);
        $customerId = $stmtGetCustomerId->fetchColumn();
        $lastCustomerId = $customerId;

        // Update/Insert ความสัมพันธ์
        $stmtAssign = $pdo->prepare("
            INSERT IGNORE INTO Debt_Collector_Assignments (collector_id, customer_id, assigned_date)
            VALUES (:collector_id, :customer_id, NOW())
        ");
        $stmtAssign->execute([
            ':collector_id' => $collectorId,
            ':customer_id'  => $customerId,
        ]);
    }

    // 8️⃣ อัปเดต employee
    $customer_no_csv   = implode(',', $customer_no_list);
    $customer_name_csv = implode(',', $customer_name_list);

    $stmtEmployee = $pdo->prepare("
        UPDATE employee SET
            emp_ids       = :emp_ids,
            full_name     = :full_name,
            email         = :email,
            telephone     = :telephone,
            address       = :address,
            department    = :department,
            salary        = :salary,
            status        = :status,
            start_work    = :start_work,
            customer_no   = :customer_no,
            customer_name = :customer_name,
            image_path    = :image_path
        WHERE id = :id
    ");
    $stmtEmployee->execute([
        ':id'            => $id,
        ':emp_ids'       => $emp_ids,
        ':full_name'     => $full_name,
        ':email'         => $email,
        ':telephone'     => $telephone,
        ':address'       => $address,
        ':department'    => $department,
        ':salary'        => $salary,
        ':status'        => $status,
        ':start_work'    => $start_work,
        ':customer_no'   => $customer_no_csv,
        ':customer_name' => $customer_name_csv,
        ':image_path'    => $imagePath,
    ]);

    // var_dump($stmtEmployee);die;

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Update employee, collector, and customers successfully',
        'collector_id' => $collectorId,
        'last_customer_id' => $lastCustomerId,
        'image_used' => $imagePath
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => "Database error: " . $e->getMessage()
    ]);
}



// header("Access-Control-Allow-Origin: *");
// header("Content-Type: application/json; charset=UTF-8");
// header("Access-Control-Allow-Methods: POST, OPTIONS");
// header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// require_once(__DIR__ . '/db/conndb.php');

// $data = $_POST;

// if (empty($data['id'])) {
//     echo json_encode(["success" => false, "message" => "Missing employee ID"]);
//     exit;
// }

// $id         = $data['id'];
// $emp_ids    = $data['emp_ids'] ?? '';
// $full_name  = $data['full_name'] ?? '';
// $email      = $data['email'] ?? '';
// $telephone  = $data['telephone'] ?? '';
// $address    = $data['address'] ?? '';
// $department = $data['department'] ?? '';
// $salary     = isset($data['salary']) ? (float)$data['salary'] : 0;
// $status     = $data['status'] ?? 'Normal';
// $start_work = !empty($data['start_work']) ? $data['start_work'] : null;

// // สำหรับ image
// $uploadDir = __DIR__ . '/../img/profile/';
// if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

// $imagePath = null;
// if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
//     $imageName = uniqid() . '_' . basename($_FILES['image']['name']);
//     $imagePathOnServer = $uploadDir . $imageName;
//     if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePathOnServer)) {
//         $imagePath = 'http://localhost:8000/api_admin_dashboard/backend/img/profile/' . $imageName;
//     }
// } else {
//     // ถ้าไม่มีรูป → ใช้ default
//     $stmtCheck = $pdo->prepare("SELECT image_path FROM employee WHERE id = :id");
//     $stmtCheck->execute([':id' => $id]);
//     $currentImage = $stmtCheck->fetchColumn();
//     if ($currentImage) {
//         $imagePath = $currentImage;
//     } else {
//         $defaultFile = $uploadDir . 'default.jpg';
//         if (file_exists($defaultFile)) {
//             $imageName = uniqid() . '_default.jpg';
//             copy($defaultFile, $uploadDir . $imageName);
//             $imagePath = 'http://localhost:8000/api_admin_dashboard/backend/img/profile/' . $imageName;
//         }
//     }
// }

// // รับข้อมูลลูกค้าหลายร้าน
// $customers = isset($data['customers']) ? json_decode($data['customers'], true) : [];

// $customer_no_list = [];
// $customer_name_list = [];

// try {
//     $pdo->beginTransaction();

//     // 1️⃣ เพิ่ม/อัปเดต debt_collectors
// $stmtCollector = $pdo->prepare("
//     INSERT INTO debt_collectors (collector_code, full_name, email, telephone, address, department, salary, status, start_work, image_path)
//     VALUES (:collector_code, :full_name, :email, :telephone, :address, :department, :salary, :status, :start_work, :image_path)
//     ON DUPLICATE KEY UPDATE 
//         full_name = VALUES(full_name),
//         email = VALUES(email),
//         telephone = VALUES(telephone),
//         address = VALUES(address),
//         department = VALUES(department),
//         salary = VALUES(salary),
//         status = VALUES(status),
//         start_work = VALUES(start_work),
//         image_path = VALUES(image_path)
// ");
// $stmtCollector->execute([
//     ':collector_code' => $emp_ids,
//     ':full_name'      => $full_name,
//     ':email'          => $email,
//     ':telephone'      => $telephone,
//     ':address'        => $address,
//     ':department'     => $department,
//     ':salary'         => $salary,
//     ':status'         => $status,
//     ':start_work'     => $start_work,
//     ':image_path'     => $imagePath,
// ]);


// // ✅ ดึง collector_id จริง
// $stmtGetCollectorId = $pdo->prepare("SELECT id FROM debt_collectors WHERE collector_code = :collector_code LIMIT 1");
// $stmtGetCollectorId->execute([':collector_code' => $emp_ids]);
// $collectorId = $stmtGetCollectorId->fetchColumn();

// // 2️⃣ อัปเดต/เพิ่มลูกค้าและสร้างความสัมพันธ์
// $lastCustomerId = null;
// foreach ($customers as $cust) {
//     $custNo   = $cust['customer_no'] ?? null;
//     $custName = $cust['nickname'] ?? '';
//     if (!$custNo) continue;

//     $customer_no_list[] = $custNo;
//     $customer_name_list[] = $custName;

//     $stmtCustomer = $pdo->prepare("
//         INSERT INTO customers (customer_no, customer_name, nickname, contact, mobile, sale_no, `groups`, label, `value`, `level`)
//         VALUES (:customer_no, :customer_name, :nickname, :contact, :mobile, :sale_no, :groups, :label, :value, :level)
//         ON DUPLICATE KEY UPDATE 
//             customer_name = VALUES(customer_name),
//             nickname = VALUES(nickname),
//             contact = VALUES(contact),
//             mobile = VALUES(mobile),
//             sale_no = VALUES(sale_no)
//     ");
//     $stmtCustomer->execute([
//         ':customer_no'   => $custNo,
//         ':customer_name' => $custName,
//         ':nickname'      => $custName,
//         ':contact'       => $cust['contact'] ?? '',
//         ':mobile'        => $cust['mobile'] ?? '',
//         ':sale_no'       => $cust['sale_no'] ?? '',
//         ':groups'        => $cust['groups'] ?? null,
//         ':label'         => $cust['label'] ?? '',
//         ':value'         => $cust['value'] ?? '',
//         ':level'         => $cust['level'] ?? null,
//     ]);

    
//     // ✅ ดึง customer_id จริง
//     $customerId = $pdo->lastInsertId(); // <-- ใช้ ID ที่เพิ่ง insert
//     if (!$customerId) {
//         // ถ้า update → ดึง ID ด้วย SELECT
//         $stmtGetCustomerId->execute([':customer_no' => $custNo]);
//         $customerId = $stmtGetCustomerId->fetchColumn();
//     }

//     $stmtAssign = $pdo->prepare("
//         INSERT IGNORE INTO Debt_Collector_Assignments (collector_id, customer_id, assigned_date)
//         VALUES (:collector_id, :customer_id, NOW())
//     ");
//     $stmtAssign->execute([
//         ':collector_id' => $collectorId,
//         ':customer_id'  => $customerId,
//     ]);

// }

//     // 3️⃣ อัปเดต employee
//     $customer_no_csv = implode(',', $customer_no_list);
//     $customer_name_csv = implode(',', $customer_name_list);

//     $stmtEmployee = $pdo->prepare("
//         UPDATE employee SET
//             emp_ids       = :emp_ids,
//             full_name     = :full_name,
//             email         = :email,
//             telephone     = :telephone,
//             address       = :address,
//             department    = :department,
//             salary        = :salary,
//             status        = :status,
//             start_work    = :start_work,
//             customer_no   = :customer_no,
//             customer_name = :customer_name,
//             image_path    = :image_path
//         WHERE id = :id
//     ");
//     $stmtEmployee->execute([
//         ':id'            => $id,
//         ':emp_ids'       => $emp_ids,
//         ':full_name'     => $full_name,
//         ':email'         => $email,
//         ':telephone'     => $telephone,
//         ':address'       => $address,
//         ':department'    => $department,
//         ':salary'        => $salary,
//         ':status'        => $status,
//         ':start_work'    => $start_work,
//         ':customer_no'   => $customer_no_csv,
//         ':customer_name' => $customer_name_csv,
//         ':image_path'    => $imagePath,
//     ]);

//     $pdo->commit();

//     echo json_encode([
//         'success' => true,
//         'message' => 'Update employee, collector, and customers successfully',
//         'collector_id' => $collectorId,
//         'last_customer_id' => $lastCustomerId,
//         'image_used' => $imagePath
//     ]);

// } catch (PDOException $e) {
//     $pdo->rollBack();
//     http_response_code(500);
//     echo json_encode([
//         'success' => false,
//         'message' => "Database error: " . $e->getMessage()
//     ]);
// }




///////////////////////////////////////////////////////////////////////////////////

// // หากมีการอัปโหลดรูปภาพใหม่
// if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
//     $uploadDir = __DIR__ . '/../img/profile/'; ///../../img/profile/
//     $imageName = uniqid() . '_' . basename($_FILES['image']['name']);
//     $imagePathOnServer = $uploadDir . $imageName;

//     if (!is_dir($uploadDir)) {
//         mkdir($uploadDir, 0777, true);
//     }

//     if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePathOnServer)) {
//         $imagePath = 'http://localhost:8000/api_admin_dashboard/backend/img/profile/' . $imageName;
//         // $imagePath = 'https://api-sale.dpower.co.th/api_admin_dashboard/backend/img/profile/' . $imageName;
//     } else {
//         echo json_encode(["success" => false, "message" => "Failed to upload image."]);
//         exit;
//     }
// }