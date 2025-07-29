<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once(__DIR__ . '/../db/conndb.php');

$response = [];

try {
    // รับ documentNo จาก query string
    $documentNo = $_GET['documentNo'] ?? '';

    if (empty($documentNo)) {
        throw new Exception("กรุณาระบุ documentNo");
    }

    // ดึงข้อมูล voucherNo จากตาราง sale_order
    $stmt = $pdo->prepare("SELECT status FROM sale_order WHERE document_no = ?");
    $stmt->execute([$documentNo]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && !empty($row['status'])) {
        $response['success'] = true;
        $response['status'] = $row['status'];
    } else {
        $response['success'] = true;
        $response['status'] = null; // ยังไม่ได้อนุมัติ
    }
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
}

echo json_encode($response);
