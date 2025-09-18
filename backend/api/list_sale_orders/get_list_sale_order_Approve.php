<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET");

require_once(__DIR__ . '/../db/conndb.php');

$page  = isset($_GET['page'])  ? intval($_GET['page'])  : 1;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : '';

$search_clean = preg_replace('/\s+/', ' ', $search);

try {
    $params = [];
    $whereSql = '';

    // --- search filter ---
    if ($search_clean !== '') {
        $words = explode(' ', $search_clean);
        $wordConditions = [];
        foreach ($words as $i => $word) {
            $key = ":word$i";
            $word = trim($word);
            if ($word === '') continue;
            $wordConditions[] = "(REPLACE(so.document_no,' ','') LIKE $key
                                  OR REPLACE(so.customer_code,' ','') LIKE $key
                                  OR REPLACE(so.full_name,' ','') LIKE $key
                                  OR REPLACE(so.phone,' ','') LIKE $key
                                  OR REPLACE(e.customer_no,' ','') LIKE $key
                                  OR REPLACE(e.full_name,' ','') LIKE $key
                                  OR REPLACE(dc.full_name,' ','') LIKE $key)";
            $params[$key] = "%$word%";
        }
        if (count($wordConditions) > 0) {
            $whereSql = " WHERE " . implode(' AND ', $wordConditions);
        }
    }

    // --- status filter ---
    if ($statusFilter !== '') {
        $statusCondition = '';
        if ($statusFilter === 'approved') {
            $statusCondition = "so.status = 'ตรวจสอบเรียบร้อย'";
        } elseif ($statusFilter === 'pending') {
            $statusCondition = "so.status != 'ตรวจสอบเรียบร้อย'";
        }
        if ($statusCondition !== '') {
            $whereSql .= ($whereSql ? ' AND ' : ' WHERE ') . $statusCondition;
        }
    }

    // --- count total ---
    $totalStmt = $pdo->prepare(
        "SELECT COUNT(*) AS cnt
         FROM sale_order so
         LEFT JOIN employee e ON so.customer_code = e.customer_no
         LEFT JOIN customers c ON so.customer_code = c.customer_no
         LEFT JOIN debt_collector_assignments dca ON c.id = dca.customer_id
         LEFT JOIN debt_collectors dc ON dca.collector_id = dc.id
         $whereSql"
    );
    $totalStmt->execute($params);
    $total = intval($totalStmt->fetch(PDO::FETCH_ASSOC)['cnt']);

    // --- fetch paginated orders ---
    // $stmt = $pdo->prepare(
    //     "SELECT so.*, e.full_name AS employee_name, e.telephone AS employee_phone
    //      FROM sale_order so
    //      LEFT JOIN employee e ON so.customer_code = e.customer_no
    //      $whereSql
    //      ORDER BY so.created_at DESC
    //      LIMIT :limit OFFSET :offset"
    // );
    $stmt = $pdo->prepare(
        "SELECT so.*
        FROM sale_order so
        $whereSql
        ORDER BY so.created_at DESC
        LIMIT :limit OFFSET :offset"
    );
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$orders) {
        echo json_encode([
            "success" => true,
            "data" => [
                "list_order" => [],
                "total" => $total,
                "page" => $page,
                "limit" => $limit
            ]
        ]);
        exit;
    }

    // --- ดึง collector list สำหรับ orders ทั้งหมด ---
    $orderIds = array_column($orders, 'id');
    $in = implode(',', array_fill(0, count($orderIds), '?'));
    $collectorSql = "
       SELECT 
            so.id AS order_id,
            dc.id AS collector_id,
            dc.collector_code AS collector_emp_ids,
            dc.full_name AS collector_full_name,
            dc.telephone AS collector_phone
        FROM sale_order so
        LEFT JOIN customers c ON so.customer_code = c.customer_no
        LEFT JOIN debt_collector_assignments dca ON c.id = dca.customer_id
        LEFT JOIN debt_collectors dc ON dca.collector_id = dc.id
        WHERE so.id IN ($in)

    ";
    //  LEFT JOIN employee e ON so.customer_code = e.customer_no
    $stmtCollector = $pdo->prepare($collectorSql);
    $stmtCollector->execute($orderIds);
    $collectorsData = $stmtCollector->fetchAll(PDO::FETCH_ASSOC);

    // จัดกลุ่ม collectors ตาม order_id
    $collectorsMap = [];
    foreach ($collectorsData as $c) {
        $oid = $c['order_id'];
        if (!isset($collectorsMap[$oid])) $collectorsMap[$oid] = [];
        $exists = false;
        foreach ($collectorsMap[$oid] as $existC) {
            if ($existC['id'] == $c['collector_id']) {
                $exists = true;
                break;
            }
        }
        if (!$exists && $c['collector_id']) {
            $collectorsMap[$oid][] = [
                "id" => $c['collector_id'],
                "emp_ids" => $c['collector_emp_ids'],
                "full_name" => $c['collector_full_name'],
                "telephone" => $c['collector_phone']
            ];
        }
    }

    $result = [];

    foreach ($orders as $order) {
        $orderId = $order['id'];

        // --- products ---
        $stmtPro = $pdo->prepare("SELECT * FROM sale_order_items WHERE order_id = :order_id");
        $stmtPro->execute([':order_id' => $orderId]);
        $products = $stmtPro->fetchAll(PDO::FETCH_ASSOC);

        $stmtPromos = $pdo->prepare("SELECT * FROM sale_order_promotions WHERE order_id = :order_id");
        $stmtPromos->execute([':order_id' => $orderId]);
        $promotions = $stmtPromos->fetchAll(PDO::FETCH_ASSOC);

        $stmtGifts = $pdo->prepare("SELECT * FROM sale_order_gifts WHERE order_id = :order_id");
        $stmtGifts->execute([':order_id' => $orderId]);
        $gifts = $stmtGifts->fetchAll(PDO::FETCH_ASSOC);

        foreach ($products as &$p) {
            $activityId = $p['pro_activity_id'];
            $itemSt = (bool)$p['st'];

            $matchedPromotions = [];
            $matchedGifts = [];

            if ($itemSt === true) {
                $matchedPromotions = array_filter($promotions, function ($promo) use ($orderId, $activityId, $itemSt) {
                    return $promo['order_id'] == $orderId &&
                           $promo['pro_activity_id'] == $activityId &&
                           (bool)$promo['st'] === $itemSt;
                });
                $matchedGifts = array_filter($gifts, function ($gift) use ($orderId, $activityId, $itemSt) {
                    return $gift['order_id'] == $orderId &&
                           $gift['pro_activity_id'] == $activityId &&
                           (bool)$gift['st'] === $itemSt;
                });
            } else {
                $matchedPromotions = array_filter($promotions, function ($promo) use ($orderId, $itemSt) {
                    return $promo['order_id'] == $orderId &&
                           (bool)$promo['st'] === $itemSt;
                });
                $matchedGifts = array_filter($gifts, function ($gift) use ($orderId, $activityId, $itemSt) {
                    return $gift['order_id'] == $orderId &&
                           $gift['pro_activity_id'] != $activityId &&
                           (bool)$gift['st'] === $itemSt;
                });
            }

            $p['promotions'] = array_values($matchedPromotions);
            $p['gifts'] = array_values($matchedGifts);
        }

        $stmtService = $pdo->prepare("SELECT * FROM sale_order_service WHERE order_id = :order_id");
        $stmtService->execute([':order_id' => $orderId]);
        $services = $stmtService->fetchAll(PDO::FETCH_ASSOC);

        // --- attach collector_list ---
        $order['collector_list'] = $collectorsMap[$orderId] ?? [];

            // --- collector_list อยู่ข้างนอกระดับเดียวกับ order ---
        $collector_list = $collectorsMap[$orderId] ?? [];

        // เพิ่ม promotions และ gifts ลงใน order
        $order['promotions'] = $products ? $products[0]['promotions'] : [];
        $order['gifts'] = $products ? $products[0]['gifts'] : [];

        $result[] = [
            "order" => $order,
            "collector_list" => $collector_list,  // <-- ตรงนี้อยู่ข้างนอก
            "productList" => $products,
            "services" => $services,
            "deliveryAddress" => false
        ];
    }

    echo json_encode([
        "success" => true,
        "data" => [
            "list_order" => $result,
            "total" => $total,
            "page" => $page,
            "limit" => $limit
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}



// header("Access-Control-Allow-Origin: *");
// header("Access-Control-Allow-Headers: Content-Type");
// header("Access-Control-Allow-Methods: GET");

// require_once(__DIR__ . '/../db/conndb.php');

// $page  = isset($_GET['page'])  ? intval($_GET['page'])  : 1;
// $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
// $offset = ($page - 1) * $limit;

// try {
//     $sql = "SELECT * FROM sale_order ORDER BY id DESC LIMIT :offset, :limit";
//     $stmt = $pdo->prepare($sql);
//     $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
//     $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
//     $stmt->execute();
//     $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

//     $result = [];

//     foreach ($orders as $order) {
//         $orderId = $order['id'];

//         // --- products ---
//         $stmtPro = $pdo->prepare("SELECT * FROM sale_order_items WHERE order_id = :order_id");
//         $stmtPro->execute([':order_id' => $orderId]);
//         $products = $stmtPro->fetchAll(PDO::FETCH_ASSOC);

//         foreach ($products as &$p) {
//             // promotions
//             $stmtPromo = $pdo->prepare("SELECT * FROM sale_order_promotions WHERE order_id = :order_id AND item_id = :item_id");
//             $stmtPromo->execute([':order_id' => $orderId, ':item_id' => $p['id']]);
//             $p['promotions'] = $stmtPromo->fetchAll(PDO::FETCH_ASSOC);

//             // gifts
//             $stmtGift = $pdo->prepare("SELECT * FROM sale_order_gifts WHERE order_id = :order_id AND item_id = :item_id");
//             $stmtGift->execute([':order_id' => $orderId, ':item_id' => $p['id']]);
//             $p['gifts'] = $stmtGift->fetchAll(PDO::FETCH_ASSOC);
//         }

//         // --- services ---
//         $stmtService = $pdo->prepare("SELECT * FROM sale_order_service WHERE order_id = :order_id");
//         $stmtService->execute([':order_id' => $orderId]);
//         $services = $stmtService->fetchAll(PDO::FETCH_ASSOC);

//         // push ออกมาเป็น block
//         $result[] = [
//             'success' => true,
//             "list_order" => [
//                 [
//                     "order" => $order,
//                     "productList" => $products,
//                     "services" => $services,
//                     "deliveryAddress" => false
//                 ]
//             ],
            
//         ];
//     }

//     // echo json_encode($result, JSON_UNESCAPED_UNICODE);

//     echo json_encode([
//         "success" => true,
//         "data" => $result,
//         "total" => count($result),
//         "page" => $page,
//         "limit" => $limit
//     ], JSON_UNESCAPED_UNICODE);

    
// } catch (Exception $e) {
//     echo json_encode([
//         "success" => false,
//         "message" => $e->getMessage()
//     ]);
// }
