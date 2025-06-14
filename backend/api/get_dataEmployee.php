<?php
// อนุญาตให้ทุกต้นทางเรียก API ได้ (ใน development)
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

require 'conndb.php';

try {
    // ดึงข้อมูล employee ทั้งหมด
    $stmt = $pdo->prepare("SELECT * FROM employee");
    $stmt->execute();
    $employeeList = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($employeeList as &$employee) {
        // ดึงภาพที่เกี่ยวข้องกับ employee จากตาราง employee_images
        $imageStmt = $pdo->prepare("SELECT image_path FROM employee WHERE emp_ids = ?");
        $imageStmt->execute([$employee['id']]);
        $images = $imageStmt->fetchAll(PDO::FETCH_COLUMN);

        // แปลงเป็น array ของ objects
        $employee['images'] = array_map(function ($imgPath) {
            return ['imagePath' => $imgPath];
        }, $images);
    }

    echo json_encode($employeeList, JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
