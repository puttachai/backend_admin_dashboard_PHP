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
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

require_once('conndb.php');
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

    // อัปเดตคำสั่งขายหลัก
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

    // =============== UPDATE SALE_ORDER_ITEMS ===============
    $products = json_decode($_POST['productList'] ?? '[]', true);
    $newItemIds = [];
    foreach ($products as $product) {
        $stmt = $pdo->prepare("SELECT id FROM sale_order_items WHERE order_id = ? AND pro_id = ?");
        $stmt->execute([$order_id, $product['pro_id']]);
        $exist = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($exist) {
            $stmt = $pdo->prepare("UPDATE sale_order_items SET 
                pro_name = ?, sn = ?, qty = ?, unit_price = ?, discount = ?, 
                total_price = ?, pro_images = ?, unit = ?, pro_activity_id = ?
                WHERE id = ?");
            $stmt->execute([
                $product['pro_erp_title'] ?? '',
                $product['pro_sn'] ?? '',
                $product['pro_quantity'] ?? 0,
                $product['pro_unit_price'] ?? 0,
                $product['pro_discount'] ?? 0,
                $product['pro_total_price'] ?? 0,
                $product['pro_images'] ?? '',
                $product['unit'] ?? '',
                $product['pro_activity_id'] ?? null,
                $exist['id']
            ]);
            $newItemIds[] = $exist['id'];
        } else {
            $stmt = $pdo->prepare("INSERT INTO sale_order_items (
                order_id, pro_id, pro_name, sn, qty, unit_price, discount, 
                total_price, pro_images, unit, pro_activity_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $order_id,
                $product['pro_id'],
                $product['pro_erp_title'],
                $product['pro_sn'],
                $product['pro_quantity'],
                $product['pro_unit_price'],
                $product['pro_discount'],
                $product['pro_total_price'],
                $product['pro_images'],
                $product['unit'],
                $product['pro_activity_id'] ?? null
            ]);
            $newItemIds[] = $pdo->lastInsertId();
        }
    }

    // ลบ item ที่ไม่มีในรายการใหม่
    if (!empty($newItemIds)) {
        $idsStr = implode(',', array_map('intval', $newItemIds));
        $pdo->exec("DELETE FROM sale_order_items WHERE order_id = $order_id AND id NOT IN ($idsStr)");
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
                order_id, title, ML_Note, note, pro_activity_id, pro_goods_id, 
                pro_goods_num, pro_image, pro_sku_price_id, user_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $order_id,
                $promo['title'],
                $promo['ML_Note'] ?? '',
                $promo['note'] ?? '',
                $promo['pro_activity_id'],
                $promo['pro_goods_id'],
                $promo['pro_goods_num'],
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
        $stmt = $pdo->prepare("SELECT id FROM sale_order_gifts WHERE order_id = ? AND pro_activity_id = ?");
        $stmt->execute([$order_id, $gift['pro_activity_id']]);
        $exist = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$exist) {
            $stmt = $pdo->prepare("INSERT INTO sale_order_gifts (
                order_id, title, pro_goods_num, pro_image,
                ML_Note, note, pro_activity_id, pro_goods_id, pro_sku_price_id, user_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $order_id,
                $gift['title'],
                $gift['pro_goods_num'],
                $gift['pro_image'],
                $gift['ML_Note'],
                $gift['note'],
                $gift['pro_activity_id'],
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

    $response['success'] = true;
    $response['message'] = "อัปเดตรายการเรียบร้อยแล้ว";
    $response['newDocumentNo'] = $documentNo;
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
}

echo json_encode($response);



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
