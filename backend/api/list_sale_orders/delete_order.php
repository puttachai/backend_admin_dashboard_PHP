<?php

// ถ้าคำขอเป็น OPTIONS (preflight), ให้ตอบกลับทันที
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *"); // หรือเฉพาะ origin ก็ได้
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    http_response_code(204);
    exit;
}

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");

require_once(__DIR__ . '/../db/conndb.php');

$response = [];

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $orderId = $input['orderId'] ?? 0;

    if (empty($orderId) || !is_numeric($orderId)) {
        throw new Exception("ข้อมูล orderId ไม่ถูกต้อง");
    }

    $pdo->beginTransaction();

    // ตัวอย่าง: ลบข้อมูลในตารางที่อ้างอิง order_id
    // ปรับชื่อ table และ column ตามฐานข้อมูลคุณ
    $stmt1 = $pdo->prepare("DELETE FROM sale_order_items WHERE order_id = ?");
    $stmt1->execute([$orderId]);

    $stmt2 = $pdo->prepare("DELETE FROM sale_order_promotions WHERE order_id = ?");
    $stmt2->execute([$orderId]);

    $stmt3 = $pdo->prepare("DELETE FROM sale_order_gifts WHERE order_id = ?");
    $stmt3->execute([$orderId]);

    // ลบคำสั่งซื้อหลัก
    $stmtMain = $pdo->prepare("DELETE FROM sale_order WHERE id = ?");
    $stmtMain->execute([$orderId]);

    $pdo->commit();

    $response['success'] = true;
    $response['message'] = "ลบรายการคำสั่งซื้อและข้อมูลที่เกี่ยวข้องเรียบร้อยแล้ว";

} catch (Exception $e) {
    $pdo->rollBack();
    $response['success'] = false;
    $response['message'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
}

echo json_encode($response);
