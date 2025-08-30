<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET");

require_once(__DIR__ . '/../db/conndb.php');

$response = [];

try {

    $codes = ['4320101_1','ACC0002','ACC0005']; //, 'ACC0007', 'ACC0019'

    // สร้าง placeholders สำหรับ PDO

    $placeholders = implode(',', array_fill(0, count($codes), '?'));

    // ดึงข้อมูลทั้งหมดจากตาราง mst_so_service
    $stmt = $pdo->prepare("SELECT id, service_code, service_name, qty, price FROM mst_so_service  WHERE service_code IN ($placeholders) ORDER BY id ASC");
    $stmt->execute($codes);
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response['success'] = true;
    $response['data'] = $services;
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
}

echo json_encode($response);
