<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

require_once(__DIR__ . '/db/conndb.php');

try {
    // ดึงข้อมูล employee + ลูกค้าที่ assign ผ่าน Debt_Collector_Assignments
    $stmt = $pdo->prepare("
        SELECT 
            e.id AS emp_id,
            e.emp_ids,
            e.full_name,
            e.email,
            e.telephone,
            e.address,
            e.department,
            e.salary,
            e.status,
            e.start_work,
            e.image_path,
            c.customer_no,
            c.nickname,
            c.contact,
            c.mobile AS customer_mobile
        FROM employee e
        LEFT JOIN debt_collectors dc ON e.emp_ids = dc.collector_code
        LEFT JOIN Debt_Collector_Assignments dca ON dc.id = dca.collector_id
        LEFT JOIN customers c ON dca.customer_id = c.id
        ORDER BY e.id DESC
    ");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // แปลงข้อมูลให้ employee 1 คนมีลูกค้าเป็น array
    $employees = [];
    foreach ($rows as $row) {
        $empId = $row['emp_id'];

        if (!isset($employees[$empId])) {
            $employees[$empId] = [
                'id' => $row['emp_id'],
                'emp_ids' => $row['emp_ids'],
                'full_name' => $row['full_name'],
                'email' => $row['email'],
                'telephone' => $row['telephone'],
                'address' => $row['address'],
                'department' => $row['department'],
                'salary' => $row['salary'],
                'status' => $row['status'],
                'start_work' => $row['start_work'],
                'image_path' => $row['image_path'],
                'customers' => [], // เก็บลูกค้าเป็น array
            ];
        }

        // ถ้ามีลูกค้า assign
        if (!empty($row['customer_no'])) {
            $employees[$empId]['customers'][] = [
                'customer_no' => $row['customer_no'],
                'nickname' => $row['nickname'],
                'contact' => $row['contact'],
                'customer_mobile' => $row['customer_mobile'],
            ];
        }
    }

    // แปลง associative array เป็น indexed array
    $employees = array_values($employees);

    echo json_encode($employees, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}




// // อนุญาตให้ทุกต้นทางเรียก API ได้ (ใน development)
// header('Access-Control-Allow-Origin: *');
// header('Content-Type: application/json; charset=utf-8');

// // require 'conndb.php';

// require_once(__DIR__ . '/db/conndb.php');
// // require_once(__DIR__ . '../db/conndb.php');

// try {
//     // ดึงข้อมูล employee ทั้งหมด
//     $stmt = $pdo->prepare("SELECT * FROM employee");
//     $stmt->execute();
//     $employeeList = $stmt->fetchAll(PDO::FETCH_ASSOC);

//     foreach ($employeeList as &$employee) {
//         // ดึงภาพที่เกี่ยวข้องกับ employee จากตาราง employee_images
//         $imageStmt = $pdo->prepare("SELECT image_path FROM employee WHERE emp_ids = ?");
//         $imageStmt->execute([$employee['id']]);
//         $images = $imageStmt->fetchAll(PDO::FETCH_COLUMN);

//         // แปลงเป็น array ของ objects
//         $employee['images'] = array_map(function ($imgPath) {
//             return ['imagePath' => $imgPath];
//         }, $images);
//     }

//     echo json_encode($employeeList, JSON_UNESCAPED_UNICODE);
// } catch (PDOException $e) {
//     echo json_encode(['error' => $e->getMessage()]);
// }
