<?php

// header("Access-Control-Allow-Origin: *");
// header("Access-Control-Allow-Headers: Content-Type");
// header("Access-Control-Allow-Methods: POST");

// require_once('conndb.php');

// $response = [];

// function convertDateToMySQLFormat($date)
// {
//     if (!$date) return null;
//     $parts = explode('/', $date);
//     if (count($parts) === 3) {
//         return "{$parts[2]}-{$parts[1]}-{$parts[0]}";
//     }
//     return null;
// }

// try {
//     $documentNo = $_POST['documentNo'] ?? '';
//     if (empty($documentNo)) throw new Exception("ไม่พบ documentNo");

//     $sellDate = convertDateToMySQLFormat($_POST['sellDate'] ?? '');
//     $deliveryDate = convertDateToMySQLFormat($_POST['deliveryDate'] ?? '');

//     // ดึง order_id
//     $stmtOrder = $pdo->prepare("SELECT id FROM sale_order WHERE document_no = ?");
//     $stmtOrder->execute([$documentNo]);
//     $order = $stmtOrder->fetch(PDO::FETCH_ASSOC);

//     if (!$order) throw new Exception("ไม่พบคำสั่งซื้อในระบบ");
//     $order_id = $order['id'];

//     // อัปเดต sale_order
//     $stmt = $pdo->prepare("UPDATE sale_order SET 
//         list_code = ?, sell_date = ?, reference = ?, channel = ?, tax_type = ?, 
//         full_name = ?, customer_code = ?, phone = ?, email = ?, address = ?, 
//         receiver_name = ?, receiver_phone = ?, receiver_email = ?, receiver_address = ?, note = ?, 
//         delivery_date = ?, tracking_no = ?, delivery_type = ?, total_discount = ?, delivery_fee = ?, 
//         final_total_price = ? 
//         WHERE id = ?");
//     $stmt->execute([
//         $_POST['listCode'] ?? '',
//         $sellDate,
//         $_POST['reference'] ?? '',
//         $_POST['channel'] ?? '',
//         $_POST['taxType'] ?? '',
//         $_POST['fullName'] ?? '',
//         $_POST['customerCode'] ?? '',
//         $_POST['phone'] ?? '',
//         $_POST['email'] ?? '',
//         $_POST['address'] ?? '',
//         $_POST['receiverName'] ?? '',
//         $_POST['receiverPhone'] ?? '',
//         $_POST['receiverEmail'] ?? '',
//         $_POST['receiverAddress'] ?? '',
//         $_POST['note'] ?? '',
//         $deliveryDate,
//         $_POST['trackingNo'] ?? '',
//         $_POST['deliveryType'] ?? '',
//         $_POST['totalDiscount'] ?? 0,
//         $_POST['deliveryFee'] ?? 0,
//         $_POST['final_total_price'] ?? 0,
//         $order_id
//     ]);

//     // ⬇️ อัปเดตหรือเพิ่มสินค้าทีละรายการ
//     $products = json_decode($_POST['productList'] ?? '[]', true);
//     foreach ($products as $product) {
//         $stmtCheck = $pdo->prepare("SELECT id FROM sale_order_items WHERE order_id = ? AND pro_id = ?");
//         $stmtCheck->execute([$order_id, $product['pro_id']]);
//         $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);

//         if ($existing) {
//             // update
//             $stmtUpdate = $pdo->prepare("UPDATE sale_order_items SET 
//                 pro_name = ?, sn = ?, qty = ?, unit_price = ?, discount = ?, 
//                 total_price = ?, pro_images = ?, unit = ? 
//                 WHERE id = ?");
//             $stmtUpdate->execute([
//                 $product['pro_erp_title'] ?? '',
//                 $product['pro_sn'] ?? '',
//                 $product['pro_quantity'] ?? 0,
//                 $product['pro_unit_price'] ?? 0,
//                 $product['pro_discount'] ?? 0,
//                 $product['pro_total_price'] ?? 0,
//                 $product['pro_images'] ?? '',
//                 $product['unit'] ?? '',
//                 $existing['id']
//             ]);
//         } else {
//             // insert
//             $stmtInsert = $pdo->prepare("INSERT INTO sale_order_items (
//                 order_id, pro_id, pro_name, sn, qty, unit_price, discount, total_price, pro_images, unit
//             ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
//             $stmtInsert->execute([
//                 $order_id,
//                 $product['pro_id'] ?? 0,
//                 $product['pro_erp_title'] ?? '',
//                 $product['pro_sn'] ?? '',
//                 $product['pro_quantity'] ?? 0,
//                 $product['pro_unit_price'] ?? 0,
//                 $product['pro_discount'] ?? 0,
//                 $product['pro_total_price'] ?? 0,
//                 $product['pro_images'] ?? '',
//                 $product['unit'] ?? ''
//             ]);
//         }
//     }

//     // ⬇️ อัปเดต promotions
//     $promotions = json_decode($_POST['promotions'] ?? '[]', true);
//     foreach ($promotions as $promo) {
//         $stmtCheckPromo = $pdo->prepare("SELECT id FROM sale_order_promotions WHERE order_id = ? AND title = ?");
//         $stmtCheckPromo->execute([$order_id, $promo['title']]);
//         $existingPromo = $stmtCheckPromo->fetch(PDO::FETCH_ASSOC);

//         if (!$existingPromo) {
//             $stmtInsertPromo = $pdo->prepare("INSERT INTO sale_order_promotions (order_id, title) VALUES (?, ?)");
//             $stmtInsertPromo->execute([$order_id, $promo['title']]);
//         }
//     }

//     // ⬇️ อัปเดต gifts
//     $gifts = json_decode($_POST['gifts'] ?? '[]', true);
//     foreach ($gifts as $gift) {
//         $stmtCheckGift = $pdo->prepare("SELECT id FROM sale_order_gifts WHERE order_id = ? AND title = ?");
//         $stmtCheckGift->execute([$order_id, $gift['title']]);
//         $existingGift = $stmtCheckGift->fetch(PDO::FETCH_ASSOC);

//         if (!$existingGift) {
//             $stmtInsertGift = $pdo->prepare("INSERT INTO sale_order_gifts (order_id, title, pro_goods_num, pro_image) VALUES (?, ?, ?, ?)");
//             $stmtInsertGift->execute([
//                 $order_id,
//                 $gift['title'],
//                 $gift['pro_goods_num'] ?? 0,
//                 $gift['pro_image'] ?? ''
//             ]);
//         }
//     }

//     $response['success'] = true;
//     $response['message'] = "อัปเดตรายการเรียบร้อยแล้ว";
//     $response['newDocumentNo'] = $documentNo; // ไม่มีการเปลี่ยน documentNo
// } catch (Exception $e) {
//     $response['success'] = false;
//     $response['message'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
// }

// echo json_encode($response);


header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

// 👇 สำคัญมาก: ตอบกลับ OPTIONS ทันที
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}


require_once(__DIR__ . '/../db/conndb.php');
// require_once('conndb.php');
$response = [];

function convertDateToMySQLFormat($date)
{
    if (!$date) return null;
    $parts = explode('/', $date);
    return count($parts) === 3 ? "{$parts[2]}-{$parts[1]}-{$parts[0]}" : null;
}


try {
    $documentNo = $_POST['documentNo'] ?? '';
    if (empty($documentNo)) throw new Exception("ไม่พบ documentNo");

    // ตรวจสอบว่าเอกสารถูกล็อกหรือยัง
    $stmtCheck = $pdo->prepare("SELECT is_locked FROM sale_order WHERE document_no = :documentNo");
    $stmtCheck->bindParam(':documentNo', $documentNo);
    $stmtCheck->execute();
    $docData = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($docData && $docData['is_locked'] == 1) {
        echo json_encode(['success' => false, 'message' => 'เอกสารนี้ถูกล็อกแล้ว ไม่สามารถแก้ไขได้']);
        exit;
    }


    $sellDate = convertDateToMySQLFormat($_POST['sellDate'] ?? '');
    $deliveryDate = convertDateToMySQLFormat($_POST['deliveryDate'] ?? '');

    // หาค่า order_id จาก documentNo
    $stmtOrder = $pdo->prepare("SELECT id FROM sale_order WHERE document_no = ?");
    $stmtOrder->execute([$documentNo]);
    $order = $stmtOrder->fetch(PDO::FETCH_ASSOC);
    if (!$order) throw new Exception("ไม่พบคำสั่งซื้อ");
    $order_id = $order['id'];

    // ✅ เพิ่มโค้ดสำหรับ UPDATE/INSERT delivery address ที่นี่
    $delivery_address = json_decode($_POST['deliveryAddress'] ?? '[]', true);

    if ($delivery_address) {
        // ตรวจสอบว่ามี address ของ order_id นี้อยู่หรือยัง
        $stmtCheck = $pdo->prepare("SELECT id FROM so_delivery_address WHERE order_id = ?");
        $stmtCheck->execute([$order_id]);
        $existingAddress = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($existingAddress) {
            // หากมีข้อมูลแล้ว → ทำการ UPDATE
            $stmtUpdate = $pdo->prepare("
            UPDATE so_delivery_address
            SET
                customer_code = ?,
                customer_id = ?,
                address_line1 = ?,
                address_line2 = ?,
                address_line3 = ?,
                phone = ?,
                zone_code = ?
            WHERE order_id = ?
        ");
            $stmtUpdate->execute([
                $_POST['customerCode'] ?? '',
                $delivery_address['DC_id'] ?? '',
                $delivery_address['DC_add1'] ?? '',
                $delivery_address['DC_add2'] ?? '',
                $delivery_address['DC_add3'] ?? '',
                $delivery_address['DC_tel'] ?? '',
                $delivery_address['DC_zone'] ?? 'ไม่มีข้อมูล',
                $order_id
            ]);
        } else {
            // หากไม่มีข้อมูล → ทำการ INSERT
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
                $delivery_address['DC_zone'] ?? 'ไม่มีข้อมูล',
                $order_id
            ]);
        }
    } else {
        $response['warning'] = 'ไม่มีข้อมูลที่อยู่สำหรับจัดส่ง';
    }



    ////////////////////////////////////////////////////////


    // 👇 STEP: เรียก API เพื่ออัปเดตเอกสาร (เหมือนโค้ดเก่า)
    // $prefix = substr($documentNo, 0, strrpos($documentNo, '-'));

    // // file_get_contents(__DIR__ . '/../update_documentrunning.php');
    // // $updateDocResponse = file_get_contents("http://localhost:86/api_admin_dashboard/backend/api/document_running/update_documentrunning.php", false, stream_context_create([
    // $updateDocResponse = file_get_contents("http://localhost/api_admin_dashboard/backend/api/document_running/update_documentrunning.php", false, stream_context_create([
    //     'http' => [
    //         'method' => 'POST',
    //         'header' => 'Content-Type: application/json',
    //         'content' => json_encode(['prefix' => $prefix])
    //     ]
    // ]));
    
    // // หรือถ้าจะเรียก API จริงๆ ใช้ CURL แทนจะดีกว่า เช่น
    // // $ch = curl_init('http://localhost/api_admin_dashboard/backend/api/update_documentrunning.php');
    // // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // // $response = curl_exec($ch);
    // // curl_close($ch);

    // $updateDocData = json_decode($updateDocResponse, true);
    // if (!$updateDocData['success']) {
    //     throw new Exception($updateDocData['message']);
    // }

    // $newDocumentNo = $updateDocData['doc_number'];

    // // 👇 STEP: อัปเดต documentNo ใหม่ให้ตาราง sale_order
    // $stmtUpdateDoc = $pdo->prepare("UPDATE sale_order SET document_no = ? WHERE id = ?");
    // $stmtUpdateDoc->execute([$newDocumentNo, $order_id]);

    $newDocumentNo = $documentNo;

    ////////////////////////////////////////////////////////

    // อย่าลืมใช้ตัวแปรนี้แทน documentNo เดิมด้านล่าง
    // $documentNo = $newDocumentNo;


    // อัปเดตคำสั่งขายหลัก //delivery_fee = ?,
    $stmt = $pdo->prepare("UPDATE sale_order SET 
        list_code = ?, sell_date = ?, reference = ?, channel = ?, tax_type = ?, 
        full_name = ?, customer_code = ?, phone = ?, email = ?, address = ?, 
        receiver_name = ?, receiver_phone = ?, receiver_email = ?, receiver_address = ?, note = ?, work_detail = ?,
        delivery_date = ?, tracking_no = ?, delivery_type = ?, total_discount = ?, services_total = ?,
        final_total_price = ?,
        price_before_tax = ?, tax_value = ?, price_with_tax = ?,
        vat_visible = ?
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
        $_POST['workDetail'] ?? '',
        $deliveryDate,
        $_POST['trackingNo'] ?? '',
        $_POST['deliveryType'] ?? '',
        $_POST['totalDiscount'] ?? 0,
        $_POST['servicesTotal'] ?? 0,
        // $_POST['deliveryFee'] ?? 0,
        $_POST['final_total_price'] ?? 0,
        //
        $_POST['price_before_tax'] ?? 0,
        $_POST['tax_value'] ?? 0,
        $_POST['price_with_tax'] ?? 0,
        $_POST['vatVisible'] ?? 0,
        $order_id
    ]);


    // ====== UPDATE/INSERT Services ======   
        $services = json_decode($_POST['services'] ?? '[]', true);

        $newServiceIds = [];

        if (!empty($services)) {
            foreach ($services as $service) {

                $serviceId = $service['id'] ?? 0;

                $stmtCheck = $pdo->prepare("SELECT id FROM sale_order_service WHERE order_id = ? AND service_code = ?");
                $stmtCheck->execute([$order_id, $service['service_code'] ?? '']);
                $exist = $stmtCheck->fetch(PDO::FETCH_ASSOC);

                // $total_price = ($service['qty'] ?? 1) * ($service['price'] ?? 0);

                if ($exist) {
                    $stmtUpdate = $pdo->prepare("UPDATE sale_order_service SET 
                        service_code = ?, service_code2 = ?, service_unit = ?, service_psi = ?, service_name = ?, qty = ?, price = ?,  updated_at = NOW()
                        WHERE id = ?");
                    $stmtUpdate->execute([
                        $service['service_code'],
                        $service['service_code2'],
                        $service['service_unit'],
                        $service['service_psi'],
                        $service['service_name'] ?? '',
                        $service['qty'] ?? 1,
                        $service['price'] ?? 0,
                        // $total_price,
                        $exist['id']
                    ]);
                    $newServiceIds[] = $exist['id'];
                } else {
                    $stmtInsert = $pdo->prepare("INSERT INTO sale_order_service 
                        (order_id, service_code, service_code2, service_unit, service_psi, service_name, qty, price, created_at, updated_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
                    $stmtInsert->execute([
                        $order_id,
                        $service['service_code'],
                        $service['service_code2'],
                        $service['service_unit'],
                        $service['service_psi'],
                        $service['service_name'] ?? '',
                        $service['qty'] ?? 1,
                        $service['price'] ?? 0,
                        // $total_price
                    ]);
                     $newServiceIds[] = $pdo->lastInsertId();
                }
            }
        }


    // =============== UPDATE SALE_ORDER_ITEMS ===============
    $products = json_decode($_POST['productList'] ?? '[]', true);
    $newItemIds = [];

    foreach ($products as $product) {
        $itemId = $product['item_id'] ?? 0;

        // $st = isset($product['st']) ? (int)$product['st'] : 0;
         $st = !empty($product['st']) ? 1 : 0;

        if ($itemId > 0) { //452
            // ✏️ UPDATE รายการเดิม
            $stmtUpdate = $pdo->prepare("UPDATE sale_order_items SET 
                pro_id = ?, pro_name = ?, pro_title = ?, pro_goods_sku_text = ?, sn = ?, qty = ?, stock = ?, unit_price = ?, discount = ?, 
                total_price = ?, pro_images = ?, unit = ?, st = ?, pro_activity_id = ? , activity_id = ?, pro_goods_id =?
                WHERE id = ? AND order_id = ?");
            $stmtUpdate->execute([
                $product['pro_sku_price_id'] ?? 0, //
                $product['pro_erp_title'] ?? '',
                $product['pro_title'] ?? '', //
                $product['pro_goods_sku_text'] ?? '', //
                
                $product['pro_sn'] ?? '',
                // $product['pro_quantity'] ?? 0,
                $product['pro_goods_num'] ?? 0,
                $product['stock'] ?? 0,
                $product['pro_unit_price'] ?? 0,
                $product['pro_discount'] ?? 0,
                $product['pro_total_price'] ?? 0,
                $product['pro_image'] ?? '',
                // $product['pro_images'] ?? '',
                $product['pro_units'] ?? '',
                // $product['st'] ?? 0,
                $st,
                $product['pro_activity_id'] ?? 0,
                $product['activity_id'] ?? 0,
                $product['pro_goods_id'] ?? 0,
                $itemId,
                $order_id

                // $product['pro_sku_price_id'] ?? 0,
                // $product['pro_erp_title'] ?? '',
                // $product['pro_title'] ?? '',
                // $product['pro_sn'] ?? '',
                // $product['pro_goods_num'] ?? 0,
                // // $product['pro_quantity'] ?? 0,
                // $product['pro_unit_price'] ?? 0,
                // $product['pro_discount'] ?? 0,
                // $product['pro_total_price'] ?? 0,
                // $product['pro_image'] ?? '',
                // $product['pro_units'] ?? '',
                // $product['pro_activity_id'] ?? 0,
                // $product['activity_id'] ?? 0

            ]);
            $newItemIds[] = $itemId;
        } else {
            // 🆕 INSERT รายการใหม่

            $st = !empty($product['st']) ? 1 : 0;

            $stmtInsert = $pdo->prepare("INSERT INTO sale_order_items (
                order_id, pro_id, pro_goods_sku_text, pro_name, pro_title, sn, qty, stock, unit_price, discount, total_price, pro_images, unit, st, pro_activity_id, activity_id, pro_goods_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmtInsert->execute([
                $order_id,
                $product['pro_sku_price_id'] ?? 0, //
                $product['pro_goods_sku_text'] ?? '',
                $product['pro_erp_title'] ?? '',
                $product['pro_title'] ?? '', //
                $product['pro_sn'] ?? '',
                // $product['pro_quantity'] ?? 0,
                $product['pro_goods_num'] ?? 0,
                $product['stock'] ?? 0,
                $product['pro_unit_price'] ?? 0,
                $product['pro_discount'] ?? 0,
                $product['pro_total_price'] ?? 0,
                $product['pro_image'] ?? '',
                // $product['pro_images'] ?? '',
                $product['pro_units'] ?? '',
                // $product['st'] ?? 0,
                $st,
                $product['pro_activity_id'] ?? 0,
                $product['activity_id'] ?? 0,
                $product['pro_goods_id'] ?? 0,
                // $product['pro_id'] ?? 0,
                // $product['pro_erp_title'] ?? '',
                // $product['pro_sn'] ?? '',
                // $product['pro_quantity'] ?? 0,
                // $product['pro_unit_price'] ?? 0,
                // $product['pro_discount'] ?? 0,
                // $product['pro_total_price'] ?? 0,
                // $product['pro_images'] ?? '',
                // $product['pro_units'] ?? '',
                // $product['pro_activity_id'] ?? 0

             
            ]);
            $newItemIds[] = $pdo->lastInsertId();
        }
    }

    ///////////////////// สำรองพอใช้ได้ /////////////////////////
    // foreach ($products as $product) {
    //     $id = $product['item_id'] ?? null;

    //     if ($id) {
    //         // ✅ มี id แสดงว่าเป็นรายการเดิม → UPDATE
    //         $stmtUpdate = $pdo->prepare("UPDATE sale_order_items SET 
    //             pro_id = ?, pro_name = ?, sn = ?, qty = ?, unit_price = ?, discount = ?, 
    //             total_price = ?, pro_images = ?, unit = ?, pro_activity_id = ?
    //             WHERE id = ? AND order_id = ?");
    //         $stmtUpdate->execute([
    //             $product['pro_id'] ?? 0,
    //             $product['pro_erp_title'] ?? '',
    //             $product['pro_sn'] ?? '',
    //             $product['pro_quantity'] ?? 0,
    //             $product['pro_unit_price'] ?? 0,
    //             $product['pro_discount'] ?? 0,
    //             $product['pro_total_price'] ?? 0,
    //             $product['pro_images'] ?? '',
    //             $product['pro_units'] ?? '',
    //             $product['pro_activity_id'] ?? 0,
    //             $id,
    //             $order_id
    //         ]);
    //         $newItemIds[] = $id;
    //     } else {
    //         // ✅ ไม่มี id → INSERT
    //         $stmtInsert = $pdo->prepare("INSERT INTO sale_order_items (
    //             order_id, pro_id, pro_name, sn, qty, unit_price, discount, total_price, pro_images, unit, pro_activity_id
    //         ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    //         $stmtInsert->execute([
    //             $order_id,
    //             $product['pro_id'] ?? 0,
    //             $product['pro_erp_title'] ?? '',
    //             $product['pro_sn'] ?? '',
    //             $product['pro_quantity'] ?? 0,
    //             $product['pro_unit_price'] ?? 0,
    //             $product['pro_discount'] ?? 0,
    //             $product['pro_total_price'] ?? 0,
    //             $product['pro_images'] ?? '',
    //             $product['pro_units'] ?? '',
    //             $product['pro_activity_id'] ?? 0
    //         ]);
    //         $newItemIds[] = $pdo->lastInsertId();
    //     }
    // }

    ///////////////////// สำรองไม่แนะนำให้ใช้ /////////////////////////
    // foreach ($products as $product) {
    //     // $stmtCheck = $pdo->prepare("SELECT id FROM sale_order_items WHERE order_id = ? AND pro_id = ? AND pro_activity_id = ?");
    //     // // $stmtCheck->execute([$order_id, $product['pro_id']]);
    //     // $stmtCheck->execute([
    //     //     $order_id,
    //     //     $product['pro_id'],
    //     //     $product['pro_activity_id'] ?? null
    //     // ]);
    //     $stmtCheck = $pdo->prepare("SELECT id FROM sale_order_items 
    // WHERE order_id = ? AND pro_id = ? AND pro_activity_id = ? AND unit_price = ? AND total_price = ?");
    //     $stmtCheck->execute([
    //         $order_id,
    //         $product['pro_id'],
    //         $product['pro_activity_id'] ?? 0,
    //         $product['pro_unit_price'] ?? '',
    //         // $product['pro_sn'] ?? '',
    //         $product['pro_total_price'] ?? 0
    //     ]);

    //     $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    //     if ($existing) {
    //         $stmtUpdate = $pdo->prepare("UPDATE sale_order_items SET 
    //             pro_name = ?, sn = ?, qty = ?, unit_price = ?, discount = ?, 
    //             total_price = ?, pro_images = ?, unit = ?, pro_activity_id = ? 
    //             WHERE id = ?");
    //         $stmtUpdate->execute([
    //             $product['pro_erp_title'] ?? '',
    //             $product['pro_sn'] ?? '',
    //             $product['pro_quantity'] ?? 0,
    //             $product['pro_unit_price'] ?? 0,
    //             $product['pro_discount'] ?? 0,
    //             $product['pro_total_price'] ?? 0,
    //             $product['pro_images'] ?? '',
    //             $product['pro_units'] ?? '',
    //             // $product['unit'] ?? '',
    //             $product['pro_activity_id'] ?? null,
    //             $existing['id']

    //         ]);
    //         $newItemIds[] = $existing['id'];
    //     } else {
    //         $stmtInsert = $pdo->prepare("INSERT INTO sale_order_items (
    //             order_id, pro_id, pro_name, sn, qty, unit_price, discount, total_price, pro_images, unit, pro_activity_id
    //         ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    //         $stmtInsert->execute([
    //             $order_id,
    //             $product['pro_id'] ?? 0,
    //             $product['pro_erp_title'] ?? '',
    //             $product['pro_sn'] ?? '',
    //             $product['pro_quantity'] ?? 0,
    //             $product['pro_unit_price'] ?? 0,
    //             $product['pro_discount'] ?? 0,
    //             $product['pro_total_price'] ?? 0,
    //             $product['pro_images'] ?? '',
    //             $product['pro_units'] ?? '',
    //             // $product['unit'] ?? '',
    //             $product['pro_activity_id'] ?? 0
    //         ]);

    //         $newItemIds[] = $pdo->lastInsertId(); // ✅ เก็บ id ที่ insert

    //         $stmtInsert2 = [];

    //         $stmtInsert2 = [
    //             $order_id,
    //             $product['pro_id'] ?? 0,
    //             $product['pro_erp_title'] ?? '',
    //             $product['pro_sn'] ?? '',
    //             $product['pro_quantity'] ?? 0,
    //             $product['pro_unit_price'] ?? 0,
    //             $product['pro_discount'] ?? 0,
    //             $product['pro_total_price'] ?? 0,
    //             $product['pro_images'] ?? '',
    //             $product['pro_units'] ?? '',
    //             // $product['unit'] ?? '',
    //             $product['pro_activity_id'] ?? 0
    //         ];
    //     }
    // }

    // ลบ item ที่ไม่มีในรายการใหม่
    if (!empty($newItemIds)) {
        $idsStr = implode(',', array_map('intval', $newItemIds));
        $pdo->exec("DELETE FROM sale_order_items WHERE order_id = $order_id AND id NOT IN ($idsStr)");
    }

    // ✅ ลบ service ที่ไม่อยู่ใน newServiceIds หรือถ้าไม่มี service เลย → ลบทั้งหมด
    if (empty($newServiceIds)) {
        $pdo->exec("DELETE FROM sale_order_service WHERE order_id = $order_id");
    } else {
        $idsStr = implode(',', array_map('intval', $newServiceIds));
        $pdo->exec("DELETE FROM sale_order_service WHERE order_id = $order_id AND id NOT IN ($idsStr)");
    }

    // =============== PROMOTIONS ===============
    $promotions = json_decode($_POST['promotions'] ?? '[]', true);
    $newPromoIds = [];
    foreach ($promotions as $promo) {
        $stmt = $pdo->prepare("SELECT id FROM sale_order_promotions WHERE order_id = ? AND pro_activity_id = ?");
        $stmt->execute([$order_id, $promo['pro_activity_id']]);
        $exist = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$exist) {
            $stmt = $pdo->prepare("INSERT INTO sale_order_promotions (
                order_id, title, ML_Note, note, st, pro_activity_id, activity_id, pro_sn, pro_goods_id, 
                pro_goods_num, stock, pro_image, pro_sku_price_id, user_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $order_id,
                $promo['title'],
                $promo['ML_Note'] ?? '',
                $promo['note'] ?? '',
                $promo['st'] ?? 0,
                $promo['pro_activity_id'],
                $promo['activity_id'],
                $promo['prosn'] ?? null,
                $promo['pro_goods_id'],
                $promo['pro_goods_num'],
                $promo['stock'] ?? 0,
                $promo['pro_image'],
                $promo['pro_sku_price_id'],
                $promo['user_id']
            ]);
            $newPromoIds[] = $pdo->lastInsertId();
        } else {
            $newPromoIds[] = $exist['id'];
        }
    }

    if (!empty($newPromoIds)) {
        $idsStr = implode(',', array_map('intval', $newPromoIds));
        $pdo->exec("DELETE FROM sale_order_promotions WHERE order_id = $order_id AND id NOT IN ($idsStr)");
    }


    // =============== GIFTS ===============
    $gifts = json_decode($_POST['gifts'] ?? '[]', true);
    $newGiftIds = [];
    foreach ($gifts as $gift) {
        $stmt = $pdo->prepare("SELECT id FROM sale_order_gifts WHERE order_id = ? AND pro_sn = ?");
        $stmt->execute([$order_id, $gift['pro_sn'] ?? $gift['prosn'] ?? '']);
        // $stmt->execute([$order_id, $gift['prosn']]);
        // $stmt = $pdo->prepare("SELECT id FROM sale_order_gifts WHERE order_id = ? AND pro_activity_id = ?");
        // $stmt->execute([$order_id, $gift['pro_activity_id']]);
        $exist = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$exist) {
            $stmt = $pdo->prepare("INSERT INTO sale_order_gifts (
                order_id, pro_sn, pro_goods_sku_text, title, pro_goods_num, stock, pro_image,
                ML_Note, note, st, pro_activity_id, activity_id, pro_goods_id, pro_sku_price_id, user_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $order_id,
                // $gift['prosn'],
                $gift['pro_sn'] ?? $gift['prosn'] ?? '', // <-- ใช้ pro_sn ถ้ามี, ถ้าไม่มีก็ prosn, ถ้าไม่มีเลยเป็น ''
                $gift['pro_goods_sku_text'],
                $gift['title'],
                $gift['pro_goods_num'],
                $gift['stock'] ?? 0,
                $gift['pro_image'],
                $gift['ML_Note'],
                $gift['note'],
                $gift['st'] ?? 0,
                $gift['pro_activity_id'],
                $gift['activity_id'],
                $gift['pro_goods_id'],
                $gift['pro_sku_price_id'],
                $gift['user_id']
            ]);
            $newGiftIds[] = $pdo->lastInsertId();
            
        } else {
            $newGiftIds[] = $exist['id'];
        }
    }

    if (!empty($newGiftIds)) {
        $idsStr = implode(',', array_map('intval', $newGiftIds));
        $pdo->exec("DELETE FROM sale_order_gifts WHERE order_id = $order_id AND id NOT IN ($idsStr)");
    }


     //  ดึงข้อมูล order ล่าสุดและส่งกลับ
    $stmt = $pdo->prepare("SELECT * FROM sale_order WHERE id = ?");
    $stmt->execute([$order_id]);
    $orderData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$orderData) {
        throw new Exception("ไม่พบคำสั่งขายที่เพิ่งบันทึก");
    }

    // แปลงวันที่สำหรับแสดงผล
    $orderData['sell_date']     = convertDateToMySQLFormat($orderData['sell_date']);
    $orderData['delivery_date'] = convertDateToMySQLFormat($orderData['delivery_date']);

    // ดึงสินค้า
    $stmtItems = $pdo->prepare("SELECT * FROM sale_order_items WHERE order_id = ?");
    $stmtItems->execute([$order_id]);
    $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

    // ดึง promotions
    $stmtPromos = $pdo->prepare("SELECT * FROM sale_order_promotions WHERE order_id = ?");
    $stmtPromos->execute([$order_id]);
    $promotions = $stmtPromos->fetchAll(PDO::FETCH_ASSOC);

    // ดึง gifts
    $stmtGifts = $pdo->prepare("SELECT * FROM sale_order_gifts WHERE order_id = ?");
    $stmtGifts->execute([$order_id]);
    $gifts = $stmtGifts->fetchAll(PDO::FETCH_ASSOC);

    // ดึงที่อยู่จัดส่งล่าสุด
    $stmtAddress = $pdo->prepare("SELECT * FROM so_delivery_address WHERE order_id = ? ORDER BY id DESC LIMIT 1");
    $stmtAddress->execute([$order_id]);
    $address = $stmtAddress->fetch(PDO::FETCH_ASSOC);

    // ดึง Services
    $stmtServices = $pdo->prepare("SELECT * FROM sale_order_service WHERE order_id = ?");
    $stmtServices->execute([$order_id]);
    $services = $stmtServices->fetchAll(PDO::FETCH_ASSOC);

    // ประกอบ productList โดยฝัง promotions/gifts ต่อ item (logic เหมือน get_sale_order.php)
    $productList = [];

    foreach ($items as $item) {
        $activityId = $item['pro_activity_id'];
        //  var_dump($item['st']);die; // Debug: ตรวจสอบค่า st
        // ถ้าคอลัมน์ st เก็บเป็น 0/1 หรือ '0'/'1' จะ cast เป็น bool ได้
        $itemSt = (bool)$item['st'];

        // var_dump($itemSt);die; // Debug: ตรวจสอบค่า st

        $matchedPromotions = [];
        $matchedGifts = [];

        if ($itemSt === true) {
            // st === true → match ตาม activity เดียวกัน
            $matchedPromotions = array_values(array_filter($promotions, function ($p) use ($order_id, $activityId, $itemSt) {
                return (int)$p['order_id'] === (int)$order_id
                    && (string)$p['pro_activity_id'] === (string)$activityId
                    && (bool)$p['st'] === $itemSt;
            }));
            $matchedGifts = array_values(array_filter($gifts, function ($g) use ($order_id, $activityId, $itemSt) {
                return (int)$g['order_id'] === (int)$order_id
                    && (string)$g['pro_activity_id'] === (string)$activityId
                    && (bool)$g['st'] === $itemSt;
            }));
        } else {
            // st === false → promotions ไม่เช็ค activity, gifts ต้อง activity != ของ item
            $matchedPromotions = array_values(array_filter($promotions, function ($p) use ($order_id, $itemSt) {
                return (int)$p['order_id'] === (int)$order_id
                    && (bool)$p['st'] === $itemSt;
            }));
            $matchedGifts = array_values(array_filter($gifts, function ($g) use ($order_id, $activityId, $itemSt) {
                return (int)$g['order_id'] === (int)$order_id
                    && (string)$g['pro_activity_id'] != (string)$activityId
                    && (bool)$g['st'] === $itemSt;
            }));
        }

        $productList[] = [
            'id'                  => (int)$item['id'],
            'pro_sku_price_id'    => $item['pro_id'],
            'pro_erp_title'       => ($item['pro_name'] == "0" || empty($item['pro_name'])) ? $item['pro_title'] : $item['pro_name'],
            'pro_title'           => $item['pro_title'],
            'pro_sn'              => $item['sn'],
            'pro_goods_sku_text'  => $item['pro_goods_sku_text'],
            'pro_goods_num'       => $item['qty'],
            'unit_price'          => (float)$item['unit_price'],
            'discount'            => (float)$item['discount'],
            'total_price'         => (float)$item['total_price'],
            'pro_image'           => $item['pro_images'],
            'pro_units'           => $item['unit'],
            'pro_goods_id'        => $item['pro_goods_id'],
            'st'                  => $itemSt,
            'stock'               => $item['stock'],
            'pro_activity_id'     => $activityId,
            'activity_id'         => $item['activity_id'],
            'promotions'          => $matchedPromotions,
            'gifts'               => $matchedGifts,
        ];
    }

    // ✅ ตอบกลับแบบเดียวกับ get_sale_order
    $response['success'] = true;
    $response['message'] = "อัปเดตรายการเรียบร้อยแล้ว";
    $response['data'] = [
        'order'           => $orderData,
        'productList'     => $productList,
        'services'     => $services,
        'deliveryAddress' => $address,
        // 'promotions' => $promotions,
        // 'gifts' => $gifts,
    ];


    // $response['success'] = true;
    // $response['message'] = "อัปเดตรายการเรียบร้อยแล้ว";
    $response['newDocumentNo'] = $newDocumentNo;
    // $response['newDocumentNo'] = $documentNo;
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
}

echo json_encode($response);

///////////////////////////////////////////////

    // $products = json_decode($_POST['productList'] ?? '[]', true);
    // $newItemIds = [];
    // foreach ($products as $product) {
    //     $stmt = $pdo->prepare("SELECT id FROM sale_order_items WHERE order_id = ? AND pro_id = ?");
    //     $stmt->execute([$order_id, $product['pro_id']]);
    //     $exist = $stmt->fetch(PDO::FETCH_ASSOC);

    //     if ($exist) {
    //         $stmt = $pdo->prepare("UPDATE sale_order_items SET 
    //             pro_name = ?, sn = ?, qty = ?, unit_price = ?, discount = ?, 
    //             total_price = ?, pro_images = ?, unit = ?, pro_activity_id = ?
    //             WHERE id = ?");
    //         $stmt->execute([
    //             $product['pro_erp_title'] ?? '',
    //             $product['pro_sn'] ?? '',
    //             $product['pro_quantity'] ?? 0,
    //             $product['pro_unit_price'] ?? 0,
    //             $product['pro_discount'] ?? 0,
    //             $product['pro_total_price'] ?? 0,
    //             $product['pro_images'] ?? '',
    //             $product['pro_units'] ?? '',
    //             $product['pro_activity_id'] ?? '', ////////////////////////////////
    //             $exist['id']
    //         ]);
    //         $newItemIds[] = $exist['id'];
    //     } else {
    //         $stmt = $pdo->prepare("INSERT INTO sale_order_items (
    //             order_id, pro_id, pro_name, sn, qty, unit_price, discount, 
    //             total_price, pro_images, unit, pro_activity_id
    //         ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    //         $stmt->execute([
    //             $order_id,
    //             $product['pro_id'],
    //             $product['pro_erp_title'],
    //             $product['pro_sn'],
    //             $product['pro_quantity'],
    //             $product['pro_unit_price'],
    //             $product['pro_discount'],
    //             $product['pro_total_price'],
    //             $product['pro_images'],
    //             $product['pro_units'] || '',
    //             $product['pro_activity_id'] ?? null
    //         ]);
    //         $newItemIds[] = $pdo->lastInsertId();
    //     }
    // }
///////////////////////////////////////////////


// header("Access-Control-Allow-Origin: *");
// header("Access-Control-Allow-Headers: Content-Type");
// header("Access-Control-Allow-Methods: POST");

// require_once('conndb.php');

// $response = [];

// function convertDateToMySQLFormat($date)
// {
//     if (!$date) return null; // กรณีวันที่ว่าง
//     $parts = explode('/', $date); // แยกวันที่ด้วย "/"
//     if (count($parts) === 3) {
//         return "{$parts[2]}-{$parts[1]}-{$parts[0]}"; // จัดเรียงใหม่เป็น YYYY-MM-DD
//     }
//     return null; // กรณีรูปแบบไม่ถูกต้อง
// }

// try {
//     $documentNo = $_POST['documentNo'] ?? '';
//     if (empty($documentNo)) {
//         throw new Exception("ไม่พบ documentNo");
//     }

//     // ดึง prefix จาก documentNo (เช่น H1-SO25680625 จาก H1-SO25680625-00001)
//     $prefix = substr($documentNo, 0, strrpos($documentNo, '-'));

//     // เรียก API เพื่ออัปเดต RunNumber และรับ doc_number ใหม่
//     $updateDocResponse = file_get_contents("http://localhost/api_admin_dashboard/backend/api/update_documentrunning.php", false, stream_context_create([
//         'http' => [
//             'method' => 'POST',
//             'header' => 'Content-Type: application/json',
//             'content' => json_encode(['prefix' => $prefix])
//         ]
//     ]));

//     $updateDocData = json_decode($updateDocResponse, true);
//     if (!$updateDocData['success']) {
//         throw new Exception($updateDocData['message']);
//     }

//     $newDocumentNo = $updateDocData['doc_number'];

//     $sellDate = convertDateToMySQLFormat($_POST['sellDate'] ?? '');
//     $deliveryDate = convertDateToMySQLFormat($_POST['deliveryDate'] ?? '');

//     // อัปเดตข้อมูลในตาราง sale_order
//     // $stmt = $pdo->prepare("UPDATE sale_order SET 
//     //     list_code = ?, sell_date = ?, reference = ?, channel = ?, tax_type = ?, 
//     //     full_name = ?, customer_code = ?, phone = ?, email = ?, address = ?, 
//     //     receiver_name = ?, receiver_phone = ?, receiver_email = ?, receiver_address = ?, note = ?, 
//     //     delivery_date = ?, tracking_no = ?, delivery_type = ?, total_discount = ?, delivery_fee = ?, 
//     //     final_total_price = ?, document_no = ? 
//     //     WHERE document_no = ?");

//     $stmt = $pdo->prepare("UPDATE sale_order SET 
//     list_code = ?, sell_date = ?, reference = ?, channel = ?, tax_type = ?, 
//     full_name = ?, customer_code = ?, phone = ?, email = ?, address = ?, 
//     receiver_name = ?, receiver_phone = ?, receiver_email = ?, receiver_address = ?, note = ?, 
//     delivery_date = ?, tracking_no = ?, delivery_type = ?, total_discount = ?, delivery_fee = ?, 
//     final_total_price = ?, document_no = ? 
//     WHERE document_no = ?");

//     $stmt->execute([
//         $_POST['listCode'] ?? '',
//         // $_POST['sellDate'] ?? '',
//         // $sellDate, // ใช้วันที่ที่แปลงแล้ว
//         convertDateToMySQLFormat($_POST['sellDate'] ?? ''),
//         $_POST['reference'] ?? '',
//         $_POST['channel'] ?? '',
//         $_POST['taxType'] ?? '',
//         $_POST['fullName'] ?? '',
//         $_POST['customerCode'] ?? '',
//         $_POST['phone'] ?? '',
//         $_POST['email'] ?? '',
//         $_POST['address'] ?? '',
//         $_POST['receiverName'] ?? '',
//         $_POST['receiverPhone'] ?? '',
//         $_POST['receiverEmail'] ?? '',
//         $_POST['receiverAddress'] ?? '',
//         $_POST['note'] ?? '',
//         // $_POST['deliveryDate'] ?? '',
//         // $deliveryDate, // ใช้วันที่ที่แปลงแล้ว
//         convertDateToMySQLFormat($_POST['deliveryDate'] ?? ''),
//         $_POST['trackingNo'] ?? '',
//         $_POST['deliveryType'] ?? '',
//         $_POST['totalDiscount'] ?? 0,
//         $_POST['deliveryFee'] ?? 0,
//         $_POST['final_total_price'] ?? 0,
//         $newDocumentNo, // ใช้ doc_number ใหม่
//         $documentNo // ใช้ doc_number เก่าเป็นเงื่อนไข
//     ]);

//     // ลบรายการสินค้าเก่าที่เกี่ยวข้องกับ documentNo
//     $stmtDelete = $pdo->prepare("DELETE FROM sale_order_items WHERE order_id = (SELECT id FROM sale_order WHERE document_no = ?)");
//     $stmtDelete->execute([$newDocumentNo]);

//     // เพิ่มรายการสินค้าใหม่
//     $productsJson = $_POST['productList'] ?? '[]';
//     $products = json_decode($productsJson, true);

//     $stmtItem = $pdo->prepare("INSERT INTO sale_order_items (
//         order_id, pro_id, pro_name, sn, qty, unit_price, discount, total_price, pro_images, unit
//     ) VALUES (
//         (SELECT id FROM sale_order WHERE document_no = ?), ?, ?, ?, ?, ?, ?, ?, ?, ?
//     )");

//     foreach ($products as $product) {
//         $stmtItem->execute([
//             // $documentNo,
//             $newDocumentNo,
//             $product['pro_id'] ?? 0,
//             $product['pro_erp_title'] ?? '',
//             $product['pro_sn'] ?? '',
//             $product['pro_quantity'] ?? 0,
//             $product['pro_unit_price'] ?? 0,
//             $product['pro_discount'] ?? 0,
//             $product['pro_total_price'] ?? 0,
//             $product['pro_images'] ?? '',
//             $product['unit'] ?? ''
//         ]);
//     }


//     // New function
//     // ✅ ลบ promotions/gifts เก่าก่อน
//     $stmtDeletePromotions = $pdo->prepare("DELETE FROM sale_order_promotions WHERE order_id = (SELECT id FROM sale_order WHERE document_no = ?)");
//     $stmtDeleteGifts = $pdo->prepare("DELETE FROM sale_order_gifts WHERE order_id = (SELECT id FROM sale_order WHERE document_no = ?)");
//     $stmtDeletePromotions->execute([$newDocumentNo]);
//     $stmtDeleteGifts->execute([$newDocumentNo]);

//     // ✅ เพิ่มรายการโปรโมชั่นใหม่
//     $promotionsJson = $_POST['promotions'] ?? '[]';
//     $promotions = json_decode($promotionsJson, true);
//     $stmtPromotion = $pdo->prepare("INSERT INTO sale_order_promotions (order_id, title) VALUES ((SELECT id FROM sale_order WHERE document_no = ?), ?)");
//     foreach ($promotions as $promo) {
//         $stmtPromotion->execute([
//             $newDocumentNo,
//             $promo['title'] ?? ''
//         ]);
//     }

//     // ✅ เพิ่มรายการของแถมใหม่
//     $giftsJson = $_POST['gifts'] ?? '[]';
//     $gifts = json_decode($giftsJson, true);
//     $stmtGift = $pdo->prepare("INSERT INTO sale_order_gifts (order_id, title, pro_goods_num, pro_image) VALUES ((SELECT id FROM sale_order WHERE document_no = ?), ?, ?, ?)");
//     foreach ($gifts as $gift) {
//         $stmtGift->execute([
//             $newDocumentNo,
//             $gift['title'] ?? '',
//             $gift['pro_goods_num'] ?? 0,
//             $gift['pro_image'] ?? ''
//         ]);
//     }
    


//     // ส่งผลลัพธ์กลับไปยัง Frontend
//     $response['success'] = true;
//     $response['message'] = "อัปเดตรายการขายเรียบร้อยแล้ว";
//     $response['newDocumentNo'] = $newDocumentNo; // ส่ง `documentNo` ใหม่กลับไปยัง Frontend


//     // echo json_encode($response);
// } catch (Exception $e) {
//     $response['success'] = false;
//     $response['message'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
// }

// echo json_encode($response);

// // $response['data'] = [
// //     'order' => $order,
// //     'productList' => $items,
// //     'promotions' => $promotions,
// //     'gifts' => $gifts
// // ];
