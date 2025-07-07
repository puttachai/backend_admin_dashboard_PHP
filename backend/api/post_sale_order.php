<?php
// header("Access-Control-Allow-Origin: *");
// header("Access-Control-Allow-Headers: Content-Type");
// header("Access-Control-Allow-Methods: POST");

// // require 'conndb.php'; // เชื่อมต่อ Database
// require_once('conndb.php');
// // if ($pdo) {
// //     echo json_encode(["message" => "DB connected"]);
// //     exit;
// // }else{
// //      echo json_encode(["message" => "DB not connected"]);
// //     exit;
// // }

// $response = [];

// function convertDateToMySQLFormat($date) {
//     if (!$date) return null; // กรณีวันที่ว่าง
//     $parts = explode('/', $date); // แยกวันที่ด้วย "/"
//     if (count($parts) === 3) {
//         return "{$parts[2]}-{$parts[1]}-{$parts[0]}"; // จัดเรียงใหม่เป็น YYYY-MM-DD
//     }
//     return null; // กรณีรูปแบบไม่ถูกต้อง
// }

// try {

//     $listCode = $_POST['listCode'] ?? '';
//     // $sellDate = $_POST['sellDate'] ?? '';

//     // $expireDate = $_POST['expireDate'] ?? '';
//     $reference = $_POST['reference'] ?? '';
//     $channel = $_POST['channel'] ?? '';
//     $taxType = $_POST['taxType'] ?? '';

//     $fullName = $_POST['fullName'] ?? '';
//     $customerCode = $_POST['customerCode'] ?? '';
//     $phone = $_POST['phone'] ?? '';
//     $email = $_POST['email'] ?? '';
//     $address = $_POST['address'] ?? '';
//     $receiverName = $_POST['receiverName'] ?? '';
//     $receiverPhone = $_POST['receiverPhone'] ?? '';
//     $receiverEmail = $_POST['receiverEmail'] ?? '';
//     $receiverAddress = $_POST['receiverAddress'] ?? '';
//     $note = $_POST['note'] ?? '';
//     // $deliveryDate = $_POST['deliveryDate'] ?? '';
//     $trackingNo = $_POST['trackingNo'] ?? '';
//     $deliveryType = $_POST['deliveryType'] ?? '';  
//     $totalDiscount = $_POST['totalDiscount'] ?? 0;
//     $deliveryFee = $_POST['deliveryFee'] ?? 0;

//     $discountQty = $_POST['discount_qty'] ?? 0;
//     $final_total_price = $_POST['final_total_price'] ?? 0;

//     $productQty = $_POST['pro_quantity'] ?? 0;
//     $productName = $_POST['pro_erp_title'] ?? '';

//     $documentNo = $_POST['documentNo'] ?? '';
//     // $status = $_POST['status'] ?? 'Active';

//     // $sn= prefix.sprintf('%05d',$order_sn_info['run_number'])

//     $sellDate = convertDateToMySQLFormat($_POST['sellDate'] ?? '');
//     $deliveryDate = convertDateToMySQLFormat($_POST['deliveryDate'] ?? '');


//     // INSERT ข้อมูลเอกสารขาย status , ? ,expire_date
//     $stmt = $pdo->prepare("INSERT INTO sale_order (
//         list_code, sell_date, reference, channel, tax_type, full_name, customer_code,
//         phone, email, address, receiver_name, receiver_phone, receiver_email, receiver_address, note,
//         delivery_date, tracking_no, delivery_type, total_discount, delivery_fee, product_qty, product_name, discount_qty, final_total_price, document_no 
//     ) VALUES (
//         ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
//     )");

//     //$status ม $expireDate,
//     $stmt->execute([
//         $listCode, $sellDate, $reference, $channel, $taxType, $fullName, $customerCode,  $phone, $email, $address,
//         $receiverName, $receiverPhone, $receiverEmail, $receiverAddress, $note,
//         $deliveryDate, $trackingNo, $deliveryType, $totalDiscount, $deliveryFee, $productQty, $productName, $discountQty, $final_total_price, $documentNo
//     ]);

//     $orderId = $pdo->lastInsertId(); // ได้ id สำหรับผูกกับสินค้ารายการ

//     // ดึง products จาก JSON ที่ส่งมา
//     $productsJson = $_POST['productList'] ?? '[]';
//     $products = json_decode($productsJson, true);

//     // รับสินค้ารายการ
//     foreach ($products as $product) {
//         $stmtItem = $pdo->prepare("INSERT INTO sale_order_items (
//             order_id, pro_id, pro_name, sn, qty, unit_price, discount, total_price, pro_images, unit
//         ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

//         $stmtItem->execute([
//             $orderId,
//             $product['pro_id'] ?? 0,
//             $product['pro_erp_title'] ?? '',
//             $product['pro_sn'] ?? '',
//             $product['pro_quantity'] ?? 0,
//             $product['pro_unit_price'] ?? 0,
//             $product['pro_discount'] ?? 0,
//             $product['pro_total_price'] ?? 0,
//             $product['pro_images'] ?? '',
//             $product['unit'] ?? '',
//         ]);
//     }


//     $response['success'] = true;
//     $response['message'] = "บันทึกรายการขายเรียบร้อยแล้ว";
// } catch (Exception $e) {
//     $response['success'] = false;
//     $response['message'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
// }

// echo json_encode($response);


header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

require_once('conndb.php');

$response = [];

function convertDateToMySQLFormat($date)
{
    if (!$date) return null;
    $parts = explode('/', $date);
    if (count($parts) === 3) {
        return "{$parts[2]}-{$parts[1]}-{$parts[0]}";
    }
    return null;
}

try {
    $listCode = $_POST['listCode'] ?? '';
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
    $trackingNo = $_POST['trackingNo'] ?? '';
    $deliveryType = $_POST['deliveryType'] ?? '';
    $totalDiscount = $_POST['totalDiscount'] ?? 0;
    $deliveryFee = $_POST['deliveryFee'] ?? 0;
    $discountQty = $_POST['discount_qty'] ?? 0;
    $final_total_price = $_POST['final_total_price'] ?? 0;
    $productQty = $_POST['pro_quantity'] ?? 0;
    $productName = $_POST['pro_erp_title'] ?? '';
    $documentNo = $_POST['documentNo'] ?? '';

    $sellDate = convertDateToMySQLFormat($_POST['sellDate'] ?? '');
    $deliveryDate = convertDateToMySQLFormat($_POST['deliveryDate'] ?? '');

    $stmt = $pdo->prepare("INSERT INTO sale_order (
        list_code, sell_date, reference, channel, tax_type, full_name, customer_code,
        phone, email, address, receiver_name, receiver_phone, receiver_email, receiver_address, note,
        delivery_date, tracking_no, delivery_type, total_discount, delivery_fee, product_qty, product_name, discount_qty, final_total_price, document_no
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
    )");

    $stmt->execute([
        $listCode,
        $sellDate,
        $reference,
        $channel,
        $taxType,
        $fullName,
        $customerCode,
        $phone,
        $email,
        $address,
        $receiverName,
        $receiverPhone,
        $receiverEmail,
        $receiverAddress,
        $note,
        $deliveryDate,
        $trackingNo,
        $deliveryType,
        $totalDiscount,
        $deliveryFee,
        $productQty,
        $productName,
        $discountQty,
        $final_total_price,
        $documentNo
    ]);

    $orderId = $pdo->lastInsertId();

    // สินค้า 
    $productsJson = $_POST['productList'] ?? '[]';
    $products = json_decode($productsJson, true);

    foreach ($products as $product) {

        $activityId = $product['pro_activity_id'] ?? null;

        $stmtItem = $pdo->prepare("INSERT INTO sale_order_items (
            order_id, pro_id, pro_name, sn, qty, unit_price, discount, total_price, pro_images, unit, pro_activity_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

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
            $activityId         // ใส่ค่า pro_activity_id เข้า column ใหม่ที่เพิ่งเพิ่ม
        ]);
    }


    // โปรโมชั่น
    // 💡 Insert promotions
    $promotionsJson = $_POST['promotions'] ?? '[]';
    $promotions = json_decode($promotionsJson, true);
    foreach ($promotions as $promo) {
        // $stmtPromo = $pdo->prepare("INSERT INTO sale_order_promotions (order_id, title) VALUES (?, ?)");
        $stmtPromo = $pdo->prepare("INSERT INTO sale_order_promotions (
            order_id, title, ML_Note, note, pro_activity_id, pro_goods_id, 
            pro_goods_num, pro_image, pro_sku_price_id, user_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        // $stmtPromo->execute([$orderId, $promo['title'] ?? '']);
        $stmtPromo->execute([
            $orderId,
            $promo['title'],
            $promo['ML_Note'] ?? '',
            $promo['note'] ?? '',
            $promo['pro_activity_id'] ?? null,
            $promo['pro_goods_id'] ?? null,
            $promo['pro_goods_num'] ?? null,
            $promo['pro_image'] ?? null,
            $promo['pro_sku_price_id'] ?? null,
            $promo['user_id'] ?? null,
        ]);
    }

    // ของแถม
    // 💡 Insert gifts
    $giftsJson = $_POST['gifts'] ?? '[]';
    $gifts = json_decode($giftsJson, true);
    foreach ($gifts as $gift) {
        // $stmtGift = $pdo->prepare("INSERT INTO sale_order_gifts (order_id, title, pro_goods_num, pro_image) VALUES (?, ?, ?, ?)");
        $stmtGift = $pdo->prepare("INSERT INTO sale_order_gifts (
            order_id, title, pro_goods_num, pro_image,
            ML_Note, note, pro_activity_id, pro_goods_id, pro_sku_price_id, user_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtGift->execute([
            $orderId,
            // $gift['title'] ?? '',
            // $gift['pro_goods_num'] ?? 0,
            // $gift['pro_image'] ?? ''
            $gift['title'],
            $gift['pro_goods_num'] ?? 0,
            $gift['pro_image'] ?? '',
            $gift['ML_Note'] ?? '',
            $gift['note'] ?? '',
            $gift['pro_activity_id'] ?? null,
            $gift['pro_goods_id'] ?? null,
            $gift['pro_sku_price_id'] ?? null,
            $gift['user_id'] ?? null,
        ]);
    }

    $response['success'] = true;
    $response['message'] = "บันทึกรายการขายเรียบร้อยแล้ว";

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
}

echo json_encode($response);



// backup
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
