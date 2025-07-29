<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

require_once(__DIR__ . '/../db/conndb.php');
$response = [];

try {

    $input = json_decode(file_get_contents("php://input"), true);
    $documentNo = $input['documentNo'] ?? '';
    $status = $input['status'] ?? 'ตรวจสอบเรียบร้อย';

    // $documentNo = $_POST['documentNo'] ?? '';
    // $status = $_POST['status'] ?? 'ตรวจสอบเรียบร้อย';

    if (empty($documentNo)) {
        throw new Exception("ไม่พบเลขที่เอกสาร (documentNo)");
    }

    // ตรวจสอบเอกสารมีอยู่ไหม
    $stmt = $pdo->prepare("SELECT id FROM sale_order WHERE document_no = ?");
    $stmt->execute([$documentNo]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception("ไม่พบเอกสารในระบบ");
    }

    // อัปเดตสถานะ
    $stmtUpdate = $pdo->prepare("UPDATE sale_order SET status = ? WHERE document_no = ?");
    $stmtUpdate->execute([$status, $documentNo]);

    $response['success'] = true;
    $response['message'] = "อัปเดตสถานะเรียบร้อยแล้ว";
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
}

echo json_encode($response);
