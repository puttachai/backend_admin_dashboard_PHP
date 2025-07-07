<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

require_once('conndb.php');

$response = [];

function convertDateToMySQLFormat($date)
{
    if (!$date) return null; // กรณีวันที่ว่าง
    $parts = explode('/', $date); // แยกวันที่ด้วย "/"
    if (count($parts) === 3) {
        return "{$parts[2]}-{$parts[1]}-{$parts[0]}"; // จัดเรียงใหม่เป็น YYYY-MM-DD
    }
    return null; // กรณีรูปแบบไม่ถูกต้อง
}

try {
    $documentNo = $_POST['documentNo'] ?? '';
    if (empty($documentNo)) {
        throw new Exception("ไม่พบ documentNo");
    }

    // ดึง prefix จาก documentNo (เช่น H1-SO25680625 จาก H1-SO25680625-00001)
    $prefix = substr($documentNo, 0, strrpos($documentNo, '-'));

    // เรียก API เพื่ออัปเดต RunNumber และรับ doc_number ใหม่
    $updateDocResponse = file_get_contents("http://localhost/api_admin_dashboard/backend/api/update_documentrunning.php", false, stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => json_encode(['prefix' => $prefix])
        ]
    ]));

    $updateDocData = json_decode($updateDocResponse, true);
    if (!$updateDocData['success']) {
        throw new Exception($updateDocData['message']);
    }

    $newDocumentNo = $updateDocData['doc_number'];

    $sellDate = convertDateToMySQLFormat($_POST['sellDate'] ?? '');
    $deliveryDate = convertDateToMySQLFormat($_POST['deliveryDate'] ?? '');

    // อัปเดตข้อมูลในตาราง sale_order
    // $stmt = $pdo->prepare("UPDATE sale_order SET 
    //     list_code = ?, sell_date = ?, reference = ?, channel = ?, tax_type = ?, 
    //     full_name = ?, customer_code = ?, phone = ?, email = ?, address = ?, 
    //     receiver_name = ?, receiver_phone = ?, receiver_email = ?, receiver_address = ?, note = ?, 
    //     delivery_date = ?, tracking_no = ?, delivery_type = ?, total_discount = ?, delivery_fee = ?, 
    //     final_total_price = ?, document_no = ? 
    //     WHERE document_no = ?");

    $stmt = $pdo->prepare("UPDATE sale_order SET 
    list_code = ?, sell_date = ?, reference = ?, channel = ?, tax_type = ?, 
    full_name = ?, customer_code = ?, phone = ?, email = ?, address = ?, 
    receiver_name = ?, receiver_phone = ?, receiver_email = ?, receiver_address = ?, note = ?, 
    delivery_date = ?, tracking_no = ?, delivery_type = ?, total_discount = ?, delivery_fee = ?, 
    final_total_price = ?, document_no = ? 
    WHERE document_no = ?");

    $stmt->execute([
        $_POST['listCode'] ?? '',
        // $_POST['sellDate'] ?? '',
        // $sellDate, // ใช้วันที่ที่แปลงแล้ว
        convertDateToMySQLFormat($_POST['sellDate'] ?? ''),
        $_POST['reference'] ?? '',
        $_POST['channel'] ?? '',
        $_POST['taxType'] ?? '',
        $_POST['fullName'] ?? '',
        $_POST['customerCode'] ?? '',
        $_POST['phone'] ?? '',
        $_POST['email'] ?? '',
        $_POST['address'] ?? '',
        $_POST['receiverName'] ?? '',
        $_POST['receiverPhone'] ?? '',
        $_POST['receiverEmail'] ?? '',
        $_POST['receiverAddress'] ?? '',
        $_POST['note'] ?? '',
        // $_POST['deliveryDate'] ?? '',
        // $deliveryDate, // ใช้วันที่ที่แปลงแล้ว
        convertDateToMySQLFormat($_POST['deliveryDate'] ?? ''),
        $_POST['trackingNo'] ?? '',
        $_POST['deliveryType'] ?? '',
        $_POST['totalDiscount'] ?? 0,
        $_POST['deliveryFee'] ?? 0,
        $_POST['final_total_price'] ?? 0,
        $newDocumentNo, // ใช้ doc_number ใหม่
        $documentNo // ใช้ doc_number เก่าเป็นเงื่อนไข
    ]);

    // ลบรายการสินค้าเก่าที่เกี่ยวข้องกับ documentNo
    $stmtDelete = $pdo->prepare("DELETE FROM sale_order_items WHERE order_id = (SELECT id FROM sale_order WHERE document_no = ?)");
    $stmtDelete->execute([$newDocumentNo]);

    // เพิ่มรายการสินค้าใหม่
    $productsJson = $_POST['productList'] ?? '[]';
    $products = json_decode($productsJson, true);

    $stmtItem = $pdo->prepare("INSERT INTO sale_order_items (
        order_id, pro_id, pro_name, sn, qty, unit_price, discount, total_price, pro_images, unit
    ) VALUES (
        (SELECT id FROM sale_order WHERE document_no = ?), ?, ?, ?, ?, ?, ?, ?, ?, ?
    )");

    foreach ($products as $product) {
        $stmtItem->execute([
            // $documentNo,
            $newDocumentNo,
            $product['pro_id'] ?? 0,
            $product['pro_erp_title'] ?? '',
            $product['pro_sn'] ?? '',
            $product['pro_quantity'] ?? 0,
            $product['pro_unit_price'] ?? 0,
            $product['pro_discount'] ?? 0,
            $product['pro_total_price'] ?? 0,
            $product['pro_images'] ?? '',
            $product['unit'] ?? ''
        ]);
    }


    // New function
    // ✅ ลบ promotions/gifts เก่าก่อน
    $stmtDeletePromotions = $pdo->prepare("DELETE FROM sale_order_promotions WHERE order_id = (SELECT id FROM sale_order WHERE document_no = ?)");
    $stmtDeleteGifts = $pdo->prepare("DELETE FROM sale_order_gifts WHERE order_id = (SELECT id FROM sale_order WHERE document_no = ?)");
    $stmtDeletePromotions->execute([$newDocumentNo]);
    $stmtDeleteGifts->execute([$newDocumentNo]);

    // ✅ เพิ่มรายการโปรโมชั่นใหม่
    $promotionsJson = $_POST['promotions'] ?? '[]';
    $promotions = json_decode($promotionsJson, true);
    $stmtPromotion = $pdo->prepare("INSERT INTO sale_order_promotions (order_id, title) VALUES ((SELECT id FROM sale_order WHERE document_no = ?), ?)");
    foreach ($promotions as $promo) {
        $stmtPromotion->execute([
            $newDocumentNo,
            $promo['title'] ?? ''
        ]);
    }

    // ✅ เพิ่มรายการของแถมใหม่
    $giftsJson = $_POST['gifts'] ?? '[]';
    $gifts = json_decode($giftsJson, true);
    $stmtGift = $pdo->prepare("INSERT INTO sale_order_gifts (order_id, title, pro_goods_num, pro_image) VALUES ((SELECT id FROM sale_order WHERE document_no = ?), ?, ?, ?)");
    foreach ($gifts as $gift) {
        $stmtGift->execute([
            $newDocumentNo,
            $gift['title'] ?? '',
            $gift['pro_goods_num'] ?? 0,
            $gift['pro_image'] ?? ''
        ]);
    }
    


    // ส่งผลลัพธ์กลับไปยัง Frontend
    $response['success'] = true;
    $response['message'] = "อัปเดตรายการขายเรียบร้อยแล้ว";
    $response['newDocumentNo'] = $newDocumentNo; // ส่ง `documentNo` ใหม่กลับไปยัง Frontend


    // echo json_encode($response);
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
}

echo json_encode($response);

// $response['data'] = [
//     'order' => $order,
//     'productList' => $items,
//     'promotions' => $promotions,
//     'gifts' => $gifts
// ];
