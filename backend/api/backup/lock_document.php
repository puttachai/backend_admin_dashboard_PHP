<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

require_once('conndb.php');

// รับข้อมูลจาก POST
$data = json_decode(file_get_contents("php://input"), true);
$documentNo = $data['documentNo'] ?? '';

if (!$documentNo) {
    echo json_encode(['success' => false, 'message' => 'ไม่พบหมายเลขเอกสาร']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE sale_order SET is_locked = 1 WHERE document_no = :documentNo");
    $stmt->bindParam(':documentNo', $documentNo);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'ล็อกเอกสารสำเร็จ']);
    } else {
        echo json_encode(['success' => false, 'message' => 'ไม่พบเอกสารนี้ หรือถูกล็อกแล้ว']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
