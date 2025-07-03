<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET");

require_once('conndb.php');

$response = [];

function convertDateToDisplayFormat($date) {
    if (!$date) return null; // กรณีวันที่ว่าง
    $parts = explode('-', $date); // แยกวันที่ด้วย "-"
    if (count($parts) === 3) {
        return "{$parts[2]}/{$parts[1]}/{$parts[0]}"; // จัดเรียงใหม่เป็น DD/MM/YYYY
    }
    return null; // กรณีรูปแบบไม่ถูกต้อง
}

try {
    $documentNo = $_GET['documentNo'] ?? '';
    if (empty($documentNo)) {
        throw new Exception("ไม่พบ documentNo");
    }

    // ดึงข้อมูลจากตาราง sale_order
    $stmt = $pdo->prepare("SELECT * FROM sale_order WHERE document_no = ?");
    $stmt->execute([$documentNo]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception("ไม่พบข้อมูลสำหรับ documentNo นี้");
    }

    // แปลงวันที่ในข้อมูล order
    $order['sell_date'] = convertDateToDisplayFormat($order['sell_date']);
    $order['delivery_date'] = convertDateToDisplayFormat($order['delivery_date']);

    // ดึงรายการสินค้า
    $stmtItems = $pdo->prepare("SELECT * FROM sale_order_items WHERE order_id = ?");
    $stmtItems->execute([$order['id']]);
    $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

    $response['success'] = true;
    $response['data'] = [
        'order' => $order,
        'productList' => $items
    ];
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
}

echo json_encode($response);