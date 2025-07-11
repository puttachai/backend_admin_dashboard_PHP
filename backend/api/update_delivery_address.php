<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");
header('Content-Type: application/json');

require_once('conndb.php');

$response = [];

try {
    // 👇 รับ raw JSON แล้ว decode
    $rawData = file_get_contents("php://input");
    $data = json_decode($rawData, true);

    // รับค่าจาก POST
    $id = $data['id'] ?? null;  // ใช้ id ของแถวที่ต้องการอัปเดต
    $customer_code = $data['DC_code'] ?? '';
    $customer_id = $data['DC_id'] ?? '';
    $address_line1 = $data['DC_add1'] ?? '';
    $address_line2 = $data['DC_add2'] ?? '';
    $address_line3 = $data['DC_add3'] ?? '';
    $phone = $data['DC_tel'] ?? '';
    $zone_code = $data['DC_zone'] ?? 'ไม่มีข้อมูล';

    // ตรวจสอบค่าที่จำเป็น
    if (!$id || !$customer_code || !$customer_id || !$address_line1) {
        throw new Exception("กรุณาระบุ ID และข้อมูลที่จำเป็นให้ครบถ้วน");
    }

    // ตรวจสอบว่าข้อมูลที่จะแก้ไขมีอยู่จริง
    $checkStmt = $pdo->prepare("SELECT id FROM so_delivery_address WHERE id = ?");
    $checkStmt->execute([$id]);
    if ($checkStmt->rowCount() === 0) {
        throw new Exception("ไม่พบข้อมูลที่อยู่ที่ต้องการแก้ไข");
    }

    // อัปเดตข้อมูล
    $stmt = $pdo->prepare("
        UPDATE so_delivery_address 
        SET 
            customer_code = ?, 
            customer_id = ?, 
            address_line1 = ?, 
            address_line2 = ?, 
            address_line3 = ?, 
            phone = ?, 
            zone_code = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $customer_code,
        $customer_id,
        $address_line1,
        $address_line2,
        $address_line3,
        $phone,
        $zone_code,
        $id
    ]);

    // ✅ ดึงข้อมูลที่ถูกอัปเดตกลับไป
    $stmt = $pdo->prepare("SELECT * FROM so_delivery_address WHERE id = ?");
    $stmt->execute([$id]);
    $updatedAddress = $stmt->fetch(PDO::FETCH_ASSOC);

    $response['success'] = true;
    $response['message'] = 'อัปเดตข้อมูลที่อยู่สำเร็จ';
    $response['data'] = $updatedAddress;

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
}

echo json_encode($response);
