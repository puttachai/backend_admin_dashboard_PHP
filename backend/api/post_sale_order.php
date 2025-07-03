<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

// require 'conndb.php'; // เชื่อมต่อ Database
require_once('conndb.php');
// if ($pdo) {
//     echo json_encode(["message" => "DB connected"]);
//     exit;
// }else{
//      echo json_encode(["message" => "DB not connected"]);
//     exit;
// }

$response = [];

function convertDateToMySQLFormat($date) {
    if (!$date) return null; // กรณีวันที่ว่าง
    $parts = explode('/', $date); // แยกวันที่ด้วย "/"
    if (count($parts) === 3) {
        return "{$parts[2]}-{$parts[1]}-{$parts[0]}"; // จัดเรียงใหม่เป็น YYYY-MM-DD
    }
    return null; // กรณีรูปแบบไม่ถูกต้อง
}

try {
    
    $listCode = $_POST['listCode'] ?? '';
    // $sellDate = $_POST['sellDate'] ?? '';

    // $expireDate = $_POST['expireDate'] ?? '';
    $reference = $_POST['reference'] ?? '';
    $channel = $_POST['channel'] ?? '';
    $taxType = $_POST['taxType'] ?? '';

    $fullName = $_POST['fullName'] ?? '';
    $customerCode = $_POST['customerCode'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $address = $_POST['address'] ?? '';
    $receiverName = $_POST['receiverName'] ?? '';
    $receiverPhone = $_POST['receiverPhone'] ?? '';
    $receiverEmail = $_POST['receiverEmail'] ?? '';
    $receiverAddress = $_POST['receiverAddress'] ?? '';
    $note = $_POST['note'] ?? '';
    // $deliveryDate = $_POST['deliveryDate'] ?? '';
    $trackingNo = $_POST['trackingNo'] ?? '';
    $deliveryType = $_POST['deliveryType'] ?? '';  
    $totalDiscount = $_POST['totalDiscount'] ?? 0;
    $deliveryFee = $_POST['deliveryFee'] ?? 0;

    $discountQty = $_POST['discount_qty'] ?? 0;
    $final_total_price = $_POST['final_total_price'] ?? 0;

    $productQty = $_POST['pro_quantity'] ?? 0;
    $productName = $_POST['pro_erp_title'] ?? '';
   
    $documentNo = $_POST['documentNo'] ?? '';
    // $status = $_POST['status'] ?? 'Active';

    // $sn= prefix.sprintf('%05d',$order_sn_info['run_number'])

    $sellDate = convertDateToMySQLFormat($_POST['sellDate'] ?? '');
    $deliveryDate = convertDateToMySQLFormat($_POST['deliveryDate'] ?? '');


    // INSERT ข้อมูลเอกสารขาย status , ? ,expire_date
    $stmt = $pdo->prepare("INSERT INTO sale_order (
        list_code, sell_date, reference, channel, tax_type, full_name, customer_code,
        phone, email, address, receiver_name, receiver_phone, receiver_email, receiver_address, note,
        delivery_date, tracking_no, delivery_type, total_discount, delivery_fee, product_qty, product_name, discount_qty, final_total_price, document_no 
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
    )");

    //$status ม $expireDate,
    $stmt->execute([
        $listCode, $sellDate, $reference, $channel, $taxType, $fullName, $customerCode,  $phone, $email, $address,
        $receiverName, $receiverPhone, $receiverEmail, $receiverAddress, $note,
        $deliveryDate, $trackingNo, $deliveryType, $totalDiscount, $deliveryFee, $productQty, $productName, $discountQty, $final_total_price, $documentNo
    ]);

    $orderId = $pdo->lastInsertId(); // ได้ id สำหรับผูกกับสินค้ารายการ

    // ดึง products จาก JSON ที่ส่งมา
    $productsJson = $_POST['productList'] ?? '[]';
    $products = json_decode($productsJson, true);

    // รับสินค้ารายการ
    foreach ($products as $product) {
        $stmtItem = $pdo->prepare("INSERT INTO sale_order_items (
            order_id, pro_id, pro_name, sn, qty, unit_price, discount, total_price, pro_images, unit
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmtItem->execute([
            $orderId,
            $product['pro_id'] ?? 0,
            $product['pro_erp_title'] ?? '',
            $product['pro_sn'] ?? '',
            $product['pro_quantity'] ?? 0,
            $product['pro_unit_price'] ?? 0,
            $product['pro_discount'] ?? 0,
            $product['pro_total_price'] ?? 0,
            $product['pro_images'] ?? '',
            $product['unit'] ?? '',
        ]);
    }

    // foreach ($_POST as $key => $value) {
    //     if (preg_match('/products\[(\d+)\]\[pro_name\]/', $key, $matches)) {
    //         $i = $matches[1];
    //         $pro_name = $_POST["products"][$i]["pro_name"] ?? '';
    //         $qty = $_POST["products"][$i]["qty"] ?? 0;
    //         $unit_price = $_POST["products"][$i]["pro_unit_price"] ?? 0;
    //         $discount = $_POST["products"][$i]["discount"] ?? 0;
    //         $pro_images = $_POST["products"][$i]["pro_images"] ?? '';

    //         $stmtItem = $pdo->prepare("INSERT INTO sale_order_items (
    //             order_id, pro_name, qty, unit_price, discount, pro_images
    //         ) VALUES (?, ?, ?, ?, ?, ?)");

    //         $stmtItem->execute([
    //             $orderId, $pro_name, $qty, $unit_price, $discount, $pro_images
    //         ]);
    //     }
    // }

    $response['success'] = true;
    $response['message'] = "บันทึกรายการขายเรียบร้อยแล้ว";
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
}

echo json_encode($response);
