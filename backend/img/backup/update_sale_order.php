<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

require_once('conndb.php');

$response = [];

try {
    $documentNo = $_POST['documentNo'] ?? '';
    if (empty($documentNo)) {
        throw new Exception("ไม่พบ documentNo");
    }

    // อัปเดตข้อมูลในตาราง sale_order
    $stmt = $pdo->prepare("UPDATE sale_order SET 
        list_code = ?, sell_date = ?, reference = ?, channel = ?, tax_type = ?, 
        full_name = ?, customer_code = ?, phone = ?, email = ?, address = ?, 
        receiver_name = ?, receiver_phone = ?, receiver_email = ?, receiver_address = ?, note = ?, 
        delivery_date = ?, tracking_no = ?, delivery_type = ?, total_discount = ?, delivery_fee = ?, 
        final_total_price = ? 
        WHERE document_no = ?");

    $stmt->execute([
        $_POST['listCode'] ?? '',
        $_POST['sellDate'] ?? '',
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
        $_POST['deliveryDate'] ?? '',
        $_POST['trackingNo'] ?? '',
        $_POST['deliveryType'] ?? '',
        $_POST['totalDiscount'] ?? 0,
        $_POST['deliveryFee'] ?? 0,
        $_POST['final_total_price'] ?? 0,
        $documentNo
    ]);

    // ลบรายการสินค้าเก่าที่เกี่ยวข้องกับ documentNo
    $stmtDelete = $pdo->prepare("DELETE FROM sale_order_items WHERE order_id = (SELECT id FROM sale_order WHERE document_no = ?)");
    $stmtDelete->execute([$documentNo]);

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
            $documentNo,
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

    $response['success'] = true;
    $response['message'] = "อัปเดตรายการขายเรียบร้อยแล้ว";
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
}

echo json_encode($response);