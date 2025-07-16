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
    $customer_code = $data['DC_code'] ?? '';
    $customer_id = $data['DC_id'] ?? '';
    $address_line1 = $data['DC_add1'] ?? '';
    $address_line2 = $data['DC_add2'] ?? '';
    $address_line3 = $data['DC_add3'] ?? '';
    $phone = $data['DC_tel'] ?? '';
    $zone_code = $data['DC_zone'] ?? 'ไม่มีข้อมูล';

    // ตรวจสอบค่าที่จำเป็น
    if (!$customer_code || !$customer_id || !$address_line1) {
        throw new Exception("กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน");
    }

    // บันทึกข้อมูล
    $stmt = $pdo->prepare("INSERT INTO so_delivery_address (
        customer_code, customer_id, address_line1, address_line2, address_line3, phone, zone_code
    ) VALUES (?, ?, ?, ?, ?, ?, ?)");

    $stmt->execute([
        $customer_code,
        $customer_id,
        $address_line1,
        $address_line2,
        $address_line3,
        $phone,
        $zone_code
    ]);

    $response['success'] = true;
    $response['message'] = 'บันทึกข้อมูลที่อยู่สำเร็จ';
    $response['id'] = $pdo->lastInsertId();

    

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
}

echo json_encode($response);
