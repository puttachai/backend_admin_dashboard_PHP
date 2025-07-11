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
    $documentNo = $_POST['documentNo'] ?? '';
    if (empty($documentNo)) throw new Exception("ไม่พบ documentNo");

    $sellDate = convertDateToMySQLFormat($_POST['sellDate'] ?? '');
    $deliveryDate = convertDateToMySQLFormat($_POST['deliveryDate'] ?? '');

    // ดึง order_id
    $stmtOrder = $pdo->prepare("SELECT id FROM sale_order WHERE document_no = ?");
    $stmtOrder->execute([$documentNo]);
    $order = $stmtOrder->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        // ✅ ถ้ายังไม่มี order นี้ ให้สร้างใหม่
        $stmtInsertOrder = $pdo->prepare("INSERT INTO sale_order (
            document_no, list_code, sell_date, reference, channel, tax_type, 
            full_name, customer_code, phone, email, address, 
            receiver_name, receiver_phone, receiver_email, receiver_address, note, 
            delivery_date, tracking_no, delivery_type, total_discount, delivery_fee, 
            final_total_price
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmtInsertOrder->execute([
            $documentNo,
            $_POST['listCode'] ?? '',
            $sellDate,
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
            $deliveryDate,
            $_POST['trackingNo'] ?? '',
            $_POST['deliveryType'] ?? '',
            $_POST['totalDiscount'] ?? 0,
            $_POST['deliveryFee'] ?? 0,
            $_POST['final_total_price'] ?? 0
        ]);

        $order_id = $pdo->lastInsertId(); // เก็บ order_id ที่สร้างใหม่ไว้ใช้ต่อ


        $delivery_address = json_decode($_POST['deliveryAddress'] ?? '[]', true);

        if ($delivery_address) {
            $stmtInsertAddress = $pdo->prepare("INSERT INTO so_delivery_address (
        customer_code, customer_id, address_line1, address_line2, address_line3, phone, zone_code, order_id
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

            $stmtInsertAddress->execute([
                $_POST['customerCode'] ?? '',
                $delivery_address['DC_id'] ?? '',
                $delivery_address['DC_add1'] ?? '',
                $delivery_address['DC_add2'] ?? '',
                $delivery_address['DC_add3'] ?? '',
                $delivery_address['DC_tel'] ?? '',
                $delivery_address['DC_zone'] ?? 'ไม่มีข้อมูล', // zone_code ยังไม่มีส่งมาก็ใส่ค่า default ไปก่อน
                $order_id
            ]);
        } else {
            $response['warning'] = 'ไม่มีข้อมูลที่อยู่สำหรับจัดส่ง';
        }

        /// insert table ที่อยู่จัดส่ง so_delivery_address

        // รับค่าที่อยู่จาก $_POST หรือ JSON (แล้วแต่ที่ frontend ส่งมา)
        // $delivery_address = json_decode($_POST['deliveryAddress'] ?? '[]', true);

        // if ($delivery_address) {
        //     $stmtInsertAddress = $pdo->prepare("INSERT INTO so_delivery_address (
        //         customer_code, customer_id, address_line1, address_line2, address_line3, phone, zone_code, order_id
        //     ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        //     $stmtInsertAddress->execute([
        //         $delivery_address['DC_code'] ?? '',
        //         $delivery_address['DC_id'] ?? '',
        //         $delivery_address['DC_add1'] ?? '',
        //         $delivery_address['DC_add2'] ?? '',
        //         $delivery_address['DC_add3'] ?? '',
        //         $delivery_address['DC_tel'] ?? '',
        //         $delivery_address['DC_zone'] ?? 'ไม่มีข้อมูล',
        //         $order_id
        //     ]);
        // } else {
        //     // ถ้าไม่มี address ส่งมา
        //     $response['warning'] = 'ไม่มีข้อมูลที่อยู่สำหรับจัดส่ง';
        // }


        /////////////////////////////////// xx xx//////////////////////////////////
        // $stmtCheckaddress = $pdo->prepare("SELECT id FROM so_delivery_address WHERE order_id = ?");
        // // $stmtCheck->execute([$order_id, $product['pro_id']]);
        // $stmtCheckaddress->execute([
        //     $order_id,
        // ]);

        // $existing = $stmtCheckaddress->fetch(PDO::FETCH_ASSOC);

        // if($existing){
        //     $stmtInsertIdAddress = $pdo->prepare("UPDATE so_delivery_address SET order_id = ? WHERE id = ?");

        //     $stmtInsertIdAddress->execute([
        //         $order_id,
        //         $existing['id']
        //     ]);
        // }else{
        //     $response['success'] = false;
        //     $response['message'] = "อัปเดตรายการไม่สำเร็จ โปรดกรอกข้อมูลที่อยู่ก่อนทำการ อัปเดท";
        //     return;
        // } 

    } else {
        $order_id = $order['id'];

        // ✅ ทำการอัปเดตคำสั่งขายเดิม
        $stmt = $pdo->prepare("UPDATE sale_order SET 
            list_code = ?, sell_date = ?, reference = ?, channel = ?, tax_type = ?, 
            full_name = ?, customer_code = ?, phone = ?, email = ?, address = ?, 
            receiver_name = ?, receiver_phone = ?, receiver_email = ?, receiver_address = ?, note = ?, 
            delivery_date = ?, tracking_no = ?, delivery_type = ?, total_discount = ?, delivery_fee = ?, 
            final_total_price = ? 
            WHERE id = ?");
        $stmt->execute([
            $_POST['listCode'] ?? '',
            $sellDate,
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
            $deliveryDate,
            $_POST['trackingNo'] ?? '',
            $_POST['deliveryType'] ?? '',
            $_POST['totalDiscount'] ?? 0,
            $_POST['deliveryFee'] ?? 0,
            $_POST['final_total_price'] ?? 0,
            $order_id
        ]);
    }


    // if (!$order) throw new Exception("ไม่พบคำสั่งซื้อในระบบ");
    // $order_id = $order['id'];


    // อัปเดต sale_order
    // $stmt = $pdo->prepare("UPDATE sale_order SET 
    //     list_code = ?, sell_date = ?, reference = ?, channel = ?, tax_type = ?, 
    //     full_name = ?, customer_code = ?, phone = ?, email = ?, address = ?, 
    //     receiver_name = ?, receiver_phone = ?, receiver_email = ?, receiver_address = ?, note = ?, 
    //     delivery_date = ?, tracking_no = ?, delivery_type = ?, total_discount = ?, delivery_fee = ?, 
    //     final_total_price = ? 
    //     WHERE id = ?");
    // $stmt->execute([
    //     $_POST['listCode'] ?? '',
    //     $sellDate,
    //     $_POST['reference'] ?? '',
    //     $_POST['channel'] ?? '',
    //     $_POST['taxType'] ?? '',
    //     $_POST['fullName'] ?? '',
    //     $_POST['customerCode'] ?? '',
    //     $_POST['phone'] ?? '',
    //     $_POST['email'] ?? '',
    //     $_POST['address'] ?? '',
    //     $_POST['receiverName'] ?? '',
    //     $_POST['receiverPhone'] ?? '',
    //     $_POST['receiverEmail'] ?? '',
    //     $_POST['receiverAddress'] ?? '',
    //     $_POST['note'] ?? '',
    //     $deliveryDate,
    //     $_POST['trackingNo'] ?? '',
    //     $_POST['deliveryType'] ?? '',
    //     $_POST['totalDiscount'] ?? 0,
    //     $_POST['deliveryFee'] ?? 0,
    //     $_POST['final_total_price'] ?? 0,
    //     $order_id
    // ]);

    // อัปเดตหรือเพิ่มสินค้า
    $products = json_decode($_POST['productList'] ?? '[]', true);
    foreach ($products as $product) {
        // $stmtCheck = $pdo->prepare("SELECT id FROM sale_order_items WHERE order_id = ? AND pro_id = ? AND pro_activity_id = ?");
        // // $stmtCheck->execute([$order_id, $product['pro_id']]);
        // $stmtCheck->execute([
        //     $order_id,
        //     $product['pro_id'],
        //     $product['pro_activity_id'] ?? null
        // ]);
        $stmtCheck = $pdo->prepare("SELECT id FROM sale_order_items 
    WHERE order_id = ? AND pro_id = ? AND pro_activity_id = ? AND unit_price = ? AND total_price = ?");
        $stmtCheck->execute([
            $order_id,
            $product['pro_id'],
            $product['pro_activity_id'] ?? null,
            $product['pro_unit_price'] ?? '',
            // $product['pro_sn'] ?? '',
            $product['pro_total_price'] ?? 0
        ]);

        $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $stmtUpdate = $pdo->prepare("UPDATE sale_order_items SET 
                pro_name = ?, sn = ?, qty = ?, unit_price = ?, discount = ?, 
                total_price = ?, pro_images = ?, unit = ?, pro_activity_id = ? 
                WHERE id = ?");
            $stmtUpdate->execute([
                $product['pro_erp_title'] ?? '',
                $product['pro_sn'] ?? '',
                $product['pro_quantity'] ?? 0,
                $product['pro_unit_price'] ?? 0,
                $product['pro_discount'] ?? 0,
                $product['pro_total_price'] ?? 0,
                $product['pro_images'] ?? '',
                $product['pro_units'] ?? '',
                // $product['unit'] ?? '',
                $product['pro_activity_id'] ?? null,
                $existing['id']
            ]);
        } else {
            $stmtInsert = $pdo->prepare("INSERT INTO sale_order_items (
                order_id, pro_id, pro_name, sn, qty, unit_price, discount, total_price, pro_images, unit, pro_activity_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmtInsert->execute([
                $order_id,
                $product['pro_id'] ?? 0,
                $product['pro_erp_title'] ?? '',
                $product['pro_sn'] ?? '',
                $product['pro_quantity'] ?? 0,
                $product['pro_unit_price'] ?? 0,
                $product['pro_discount'] ?? 0,
                $product['pro_total_price'] ?? 0,
                $product['pro_images'] ?? '',
                $product['pro_units'] ?? '',
                // $product['unit'] ?? '',
                $product['pro_activity_id'] ?? null
            ]);

            $stmtInsert2 = [];

            $stmtInsert2 = [
                $order_id,
                $product['pro_id'] ?? 0,
                $product['pro_erp_title'] ?? '',
                $product['pro_sn'] ?? '',
                $product['pro_quantity'] ?? 0,
                $product['pro_unit_price'] ?? 0,
                $product['pro_discount'] ?? 0,
                $product['pro_total_price'] ?? 0,
                $product['pro_images'] ?? '',
                $product['pro_units'] ?? '',
                // $product['unit'] ?? '',
                $product['pro_activity_id'] ?? null
            ];
        }
    }

    // อัปเดต promotions
    $promotions = json_decode($_POST['promotions'] ?? '[]', true);
    foreach ($promotions as $promo) {
        $stmtCheckPromo = $pdo->prepare("SELECT id FROM sale_order_promotions WHERE order_id = ? "); //AND title = ?
        $stmtCheckPromo->execute([$order_id]); //, $promo['title']
        $existingPromo = $stmtCheckPromo->fetch(PDO::FETCH_ASSOC);

        if (!$existingPromo) {
            $stmtInsertPromo = $pdo->prepare("INSERT INTO sale_order_promotions (
                order_id, title, ML_Note, note, pro_activity_id, pro_goods_id, 
                pro_goods_num, pro_image, pro_sku_price_id, user_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmtInsertPromo->execute([
                $order_id,
                $promo['title'] ?? '',
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
    }

    // อัปเดต gifts
    $gifts = json_decode($_POST['gifts'] ?? '[]', true);
    foreach ($gifts as $gift) {
        $stmtCheckGift = $pdo->prepare("SELECT id FROM sale_order_gifts WHERE order_id = ? AND title = ?");
        $stmtCheckGift->execute([$order_id, $gift['title']]);
        $existingGift = $stmtCheckGift->fetch(PDO::FETCH_ASSOC);

        if (!$existingGift) {
            $stmtInsertGift = $pdo->prepare("INSERT INTO sale_order_gifts (
                order_id, title, pro_goods_num, pro_image,
                ML_Note, note, pro_activity_id, pro_goods_id, pro_sku_price_id, user_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmtInsertGift->execute([
                $order_id,
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
    }

    $response['success'] = true;
    $response['message'] = "อัปเดตรายการเรียบร้อยแล้ว";
    $response['newDocumentNo'] = $documentNo;
    $response['stmtInsert2'] = $stmtInsert2;
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
