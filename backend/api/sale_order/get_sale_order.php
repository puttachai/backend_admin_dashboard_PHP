 <?php
    // header("Access-Control-Allow-Origin: *");
    // header("Access-Control-Allow-Headers: Content-Type");
    // header("Access-Control-Allow-Methods: GET");

    // require_once('conndb.php');

    // $response = [];

    // function convertDateToDisplayFormat($date)
    // {
    //     if (!$date) return null; // กรณีวันที่ว่าง
    //     $parts = explode('-', $date); // แยกวันที่ด้วย "-"
    //     if (count($parts) === 3) {
    //         return "{$parts[2]}/{$parts[1]}/{$parts[0]}"; // จัดเรียงใหม่เป็น DD/MM/YYYY
    //     }
    //     return null; // กรณีรูปแบบไม่ถูกต้อง
    // }

    // try {
    //     $documentNo = $_GET['documentNo'] ?? '';
    //     if (empty($documentNo)) {
    //         throw new Exception("ไม่พบ documentNo");
    //     }

    //     // ดึงข้อมูลจากตาราง sale_order
    //     $stmt = $pdo->prepare("SELECT * FROM sale_order WHERE document_no = ?");
    //     $stmt->execute([$documentNo]);
    //     $order = $stmt->fetch(PDO::FETCH_ASSOC);

    //     if (!$order) {
    //         throw new Exception("ไม่พบข้อมูลสำหรับ documentNo นี้");
    //     }

    //     // แปลงวันที่ในข้อมูล order
    //     $order['sell_date'] = convertDateToDisplayFormat($order['sell_date']);
    //     $order['delivery_date'] = convertDateToDisplayFormat($order['delivery_date']);

    //     // ดึงรายการสินค้า
    //     $stmtItems = $pdo->prepare("SELECT * FROM sale_order_items WHERE order_id = ?");
    //     $stmtItems->execute([$order['id']]);
    //     $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

    //     // เตรียมข้อมูลสินค้า + รวม promotions และ gifts ต่อ item
    //     // $productList = [];

    //     // foreach ($items as $item) {
    //     //     $itemId = $item['id']; // ✅ item_id ในตาราง sale_order_items

    //     //     // ✅ ดึง promotions ของ item นี้
    //     //     $stmtPromo = $pdo->prepare("SELECT * FROM sale_order_promotions WHERE item_id = ?");
    //     //     $stmtPromo->execute([$itemId]);
    //     //     $promotions = $stmtPromo->fetchAll(PDO::FETCH_ASSOC);

    //     //     // ✅ ดึง gifts ของ item นี้
    //     //     $stmtGift = $pdo->prepare("SELECT * FROM sale_order_gifts WHERE item_id = ?");
    //     //     $stmtGift->execute([$itemId]);
    //     //     $gifts = $stmtGift->fetchAll(PDO::FETCH_ASSOC);

    //     //     // ✅ รวมเข้าไปในแต่ละสินค้า
    //     //     $productList[] = array_merge($item, [
    //     //         'promotions' => $promotions,
    //     //         'gifts' => $gifts
    //     //     ]);
    //     // }

    //     // $response['success'] = true;
    //     // $response['data'] = [
    //     //     'order' => $order,
    //     //     'productList' => $productList
    //     // ];

    //     // New Code
    //     // ✅ ดึงโปรโมชั่น
    //     $stmtPromotions = $pdo->prepare("SELECT title FROM sale_order_promotions WHERE order_id = ?");
    //     $stmtPromotions->execute([$order['id']]);
    //     $promotions = $stmtPromotions->fetchAll(PDO::FETCH_ASSOC);

    //     // ✅ ดึงของแถม
    //     $stmtGifts = $pdo->prepare("SELECT title, pro_goods_num, pro_image FROM sale_order_gifts WHERE order_id = ?");
    //     $stmtGifts->execute([$order['id']]);
    //     $gifts = $stmtGifts->fetchAll(PDO::FETCH_ASSOC);


    //     $response['success'] = true;
    //     $response['data'] = [
    //         'order' => $order,
    //         'productList' => $items,
    //         'promotions' => $promotions,
    //         'gifts' => $gifts
    //     ];
    // } catch (Exception $e) {
    //     $response['success'] = false;
    //     $response['message'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
    // }

    // echo json_encode($response);


    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Content-Type");
    header("Access-Control-Allow-Methods: GET");

    // require_once('conndb.php');
    require_once(__DIR__ . '/../db/conndb.php');

    $response = [];

    function convertDateToDisplayFormat($date)
    {
        if (!$date) return null;
        $parts = explode('-', $date);
        if (count($parts) === 3) {
            return "{$parts[2]}/{$parts[1]}/{$parts[0]}";
        }
        return null;
    }

    try {
        $documentNo = $_GET['documentNo'] ?? '';
        // $customerCode = $_GET['customer_code'] ?? '';
        // $customerCode = $_GET['customer_code'] ?? '';

        if (empty($documentNo)) {
            throw new Exception("ไม่พบ documentNo");
        }

        // 1. ดึงข้อมูลหลักจาก sale_order
        $stmt = $pdo->prepare("SELECT * FROM sale_order WHERE document_no = ?");
        $stmt->execute([$documentNo]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            throw new Exception("ไม่พบข้อมูลสำหรับ documentNo นี้");
        }

        $order['sell_date'] = convertDateToDisplayFormat($order['sell_date']);
        $order['delivery_date'] = convertDateToDisplayFormat($order['delivery_date']);

        $orderId = $order['id'];

        // 2. ดึงข้อมูลสินค้า
        $stmtItems = $pdo->prepare("SELECT * FROM sale_order_items WHERE order_id = ?");
        $stmtItems->execute([$orderId]);
        $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

        // 3. ดึงโปรโมชั่น
        $stmtPromos = $pdo->prepare("SELECT * FROM sale_order_promotions WHERE order_id = ?");
        $stmtPromos->execute([$orderId]);
        $promotions = $stmtPromos->fetchAll(PDO::FETCH_ASSOC);

        // 4. ดึงของแถม
        $stmtGifts = $pdo->prepare("SELECT * FROM sale_order_gifts WHERE order_id = ?");
        $stmtGifts->execute([$orderId]);
        $gifts = $stmtGifts->fetchAll(PDO::FETCH_ASSOC);

        // 5. ดึงข้อมูลที่อยู่ (Address)
        $stmtAddress = $pdo->prepare("
            SELECT * 
            FROM so_delivery_address 
            WHERE order_id = :order_id 
            ORDER BY id DESC 
            LIMIT 1
        ");
        // AND customer_code = :customer_code
        //  ':customer_code' => $customerCode
        $stmtAddress->execute([
            ':order_id' => $orderId,
           
        ]);
        $address = $stmtAddress->fetch(PDO::FETCH_ASSOC);

        // 5. ดึงข้อมูลที่อยู่
        // $stmtAddress = $pdo->prepare("SELECT * FROM so_delivery_address WHERE order_id = ? AND document_no = ?");
        // $stmtAddress->execute([$orderId, $documentNo]);
        // // $address = $stmtAddress->fetchAll(PDO::FETCH_ASSOC);
        // $address = $stmtAddress->fetch(PDO::FETCH_ASSOC);


        // รวมข้อมูลเข้า productList
        $productList = [];

        foreach ($items as $item) {
            $activityId = $item['pro_activity_id'] ?? null;

            // ดึงเฉพาะ promotions/gifts ที่ item_id ตรงกับ activityId
            $matchedPromotions = array_filter($promotions, fn($p) => $p['pro_activity_id'] == $activityId && $p['order_id'] == $orderId);
            $matchedGifts = array_filter($gifts, fn($g) => $g['pro_activity_id'] == $activityId && $g['order_id'] == $orderId);

            $productList[] = [
                'id' => $item['id'],
                'pro_sku_price_id' => $item['pro_id'],
                'pro_erp_title' => $item['pro_name'],
                'pro_title' => $item['pro_title'],
                'pro_sn' => $item['sn'],
                'pro_goods_num' => $item['qty'],
                'unit_price' => $item['unit_price'],
                'discount' => $item['discount'],
                'total_price' => $item['total_price'],
                'pro_image' => $item['pro_images'],
                'pro_units' => $item['unit'],
                'pro_goods_id' => $item['pro_goods_id'],
                'st' => $item['st'],
                'stock' => $item['stock'],
                'pro_activity_id' => $activityId,
                'activity_id' => $item['activity_id'],
                // 'activity_id' => $activityId,
                'promotions' => array_values($matchedPromotions),
                'gifts' => array_values($matchedGifts),
            ];
             
        }

        $response['success'] = true;
        $response['data'] = [
            'order' => $order,
            'productList' => $productList,
            // 'deliveryAddress' => $address // เพิ่มข้อมูลที่อยู่ที่ดึงมา
            'deliveryAddress' => $address // เพิ่มข้อมูลที่อยู่ที่ดึงมา
        ];
    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }

    echo json_encode($response);








         // foreach ($items as $item) {
            //     // แปะข้อมูลเพิ่ม
            //     $productList[] = [
            //         'activity_id' => $item['pro_activity_id'], // หรือจะใช้ activity_id ที่ได้จาก promotions ก็ได้
            //         'pro_id' => $item['pro_id'],
            //         'pro_erp_title' => $item['pro_name'],
            //         'pro_sn' => $item['sn'],
            //         'pro_unit_price' => $item['unit_price'],
            //         'pro_quantity' => $item['qty'],
            //         'pro_discount' => $item['discount'],
            //         'pro_total_price' => $item['total_price'],
            //         'pro_images' => $item['pro_images'],
            //         'pro_units' => $item['unit'],
            //         'promotions' => array_values($matchedPromotions),
            //         'gifts' => array_values($matchedGifts),
            //     ];
            // }
        // }

        // ✅ รวมข้อมูลทั้งหมด




    

// header("Access-Control-Allow-Origin: *");
// header("Access-Control-Allow-Headers: Content-Type");
// header("Access-Control-Allow-Methods: GET");

// require_once('conndb.php');

// $response = [];

// function convertDateToDisplayFormat($date)
// {
//     if (!$date) return null;
//     $parts = explode('-', $date);
//     if (count($parts) === 3) {
//         return "{$parts[2]}/{$parts[1]}/{$parts[0]}";
//     }
//     return null;
// }

// try {
//     $documentNo = $_GET['documentNo'] ?? '';
//     if (empty($documentNo)) {
//         throw new Exception("ไม่พบ documentNo");
//     }

//     // ✅ ดึงข้อมูลคำสั่งซื้อจาก sale_order
//     $stmt = $pdo->prepare("SELECT * FROM sale_order WHERE document_no = ?");
//     $stmt->execute([$documentNo]);
//     $order = $stmt->fetch(PDO::FETCH_ASSOC);

//     if (!$order) {
//         throw new Exception("ไม่พบข้อมูลสำหรับ documentNo นี้");
//     }

//     // ✅ แปลงวันที่
//     $order['sell_date'] = convertDateToDisplayFormat($order['sell_date']);
//     $order['delivery_date'] = convertDateToDisplayFormat($order['delivery_date']);

//     // ✅ 1. ดึงรายการสินค้า
//     $stmtItems = $pdo->prepare("SELECT * FROM sale_order_items WHERE order_id = ?");
//     $stmtItems->execute([$order['id']]);
//     $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

//     // ✅ 2. ดึงข้อมูลโปรโมชั่นและของแถม โดยอิงจาก item_id
//     $stmtPromotions = $pdo->prepare("SELECT * FROM sale_order_promotions WHERE order_id = ?");
//     $stmtPromotions->execute([$order['id']]);
//     $promotionsAll = $stmtPromotions->fetchAll(PDO::FETCH_ASSOC);

//     $stmtGifts = $pdo->prepare("SELECT * FROM sale_order_gifts WHERE order_id = ?");
//     $stmtGifts->execute([$order['id']]);
//     $giftsAll = $stmtGifts->fetchAll(PDO::FETCH_ASSOC);

//     // ✅ 3. จัดกลุ่มโปรโมชั่นและของแถมตาม item_id
//     $promotionsMap = [];
//     foreach ($promotionsAll as $p) {
//         $promotionsMap[$p['item_id']][] = $p;
//     }

//     $giftsMap = [];
//     foreach ($giftsAll as $g) {
//         $giftsMap[$g['item_id']][] = $g;
//     }

//     // ✅ 4. รวมโปรโมชั่นและของแถมเข้ากับรายการสินค้า
//     $finalItems = [];
//     foreach ($items as $item) {
//         $item['promotions'] = $promotionsMap[$item['id']] ?? [];
//         $item['gifts'] = $giftsMap[$item['id']] ?? [];
//         $finalItems[] = $item;
//     }

//     // ✅ ส่งข้อมูลกลับ
//     $response['success'] = true;
//     $response['data'] = [
//         'order' => $order,
//         'productList' => $finalItems
//     ];
// } catch (Exception $e) {
//     $response['success'] = false;
//     $response['message'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
// }

// echo json_encode($response);

//     // $response['success'] = true;
//     // $response['data'] = [
//     //     'order' => $order,
//     //     'productList' => $items
//     // ];