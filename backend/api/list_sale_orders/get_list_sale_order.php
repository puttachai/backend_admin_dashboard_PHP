<?php 

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET");

require_once(__DIR__ . '/../db/conndb.php');

$page  = isset($_GET['page'])  ? intval($_GET['page'])  : 1;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// ลบช่องว่างส่วนเกินจาก input search
$search_clean = preg_replace('/\s+/', ' ', $search);

try {
    $params = [];
    $whereSql = '';
    if ($search_clean !== '') {
        // ใช้คำเต็มใน search โดยแยกคำและจับเป็น word boundary
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
                                  OR REPLACE(e.full_name,' ','') LIKE $key)";
            $params[$key] = "%$word%";
        }
        if (count($wordConditions) > 0) {
            $whereSql = " WHERE " . implode(' AND ', $wordConditions);
        }
    }

    // ดึงจำนวนรวมที่ตรงเงื่อนไข search
    $totalStmt = $pdo->prepare(
        "SELECT COUNT(*) AS cnt
        FROM sale_order so
        LEFT JOIN employee e ON so.customer_code = e.customer_no
        $whereSql"
    );
    $totalStmt->execute($params);
    $total = $totalStmt->fetch(PDO::FETCH_ASSOC)['cnt'];

    // ดึงข้อมูลหน้าที่ พร้อมเงื่อนไข search และ pagination
     $stmt = $pdo->prepare(
        "SELECT so.*, e.full_name AS employee_name, e.telephone AS employee_phone
        FROM sale_order so
        LEFT JOIN employee e ON so.customer_code = e.customer_no
        $whereSql
        ORDER BY so.created_at DESC
        LIMIT :limit OFFSET :offset"
    );
    // $stmt = $pdo->prepare(
    //     "SELECT so.*, so.full_name AS employee_name, so.phone AS employee_phone
    //     FROM sale_order so
    //     LEFT JOIN employee e ON so.customer_code = e.customer_no
    //     $whereSql
    //     ORDER BY so.created_at DESC
    //     LIMIT :limit OFFSET :offset"
    // );

    // bind params
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();
    $list_order = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => [
            'list_order' => $list_order,
            'total' => intval($total),
            'page' => $page,
            'limit' => $limit,
        ]
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => "error: " . $e->getMessage()
    ]);
}



// header("Access-Control-Allow-Origin: *");
// header("Access-Control-Allow-Headers: Content-Type");
// header("Access-Control-Allow-Methods: GET");

// require_once(__DIR__ . '/../db/conndb.php');

// $page  = isset($_GET['page'])  ? intval($_GET['page'])  : 1;
// $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
// $offset = ($page - 1) * $limit;

// $search = isset($_GET['search']) ? trim($_GET['search']) : '';

// // var_dump($search);die;

// try {
//     $params = [];
//     $whereSql = '';
//     if ($search !== '') {
//         // ใช้วงเล็บเพื่อให้ OR ใน WHERE ถูกต้อง
//         $whereSql = " WHERE (so.document_no LIKE :search OR so.customer_code LIKE :search OR so.full_name LIKE :search OR so.phone LIKE :search)";
//         $params[':search'] = "%$search%";
//     }

//     // ดึงจำนวนรวมที่ตรงเงื่อนไข search
//     $totalStmt = $pdo->prepare(
//         "SELECT COUNT(*) AS cnt
//         FROM sale_order so
//         LEFT JOIN employee e ON so.customer_code = e.customer_no
//         $whereSql"
//     );
//     $totalStmt->execute($params);
//     $total = $totalStmt->fetch(PDO::FETCH_ASSOC)['cnt'];

//     // ดึงข้อมูลหน้าที่ พร้อมเงื่อนไข search และ pagination
//     $stmt = $pdo->prepare(
//         "SELECT so.*, so.full_name AS employee_name, so.phone AS employee_phone
//         FROM sale_order so
//         LEFT JOIN employee e ON so.customer_code = e.customer_no
//         $whereSql
//         ORDER BY so.created_at DESC
//         LIMIT :limit OFFSET :offset"
//     );

//     // bind search param ถ้ามี
//     foreach ($params as $key => $value) {
//         $stmt->bindValue($key, $value);
//     }
//     $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
//     $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

//     $stmt->execute();
//     $list_order = $stmt->fetchAll(PDO::FETCH_ASSOC);

//     echo json_encode([
//         'success' => true,
//         'data' => [
//             'list_order' => $list_order,
//             'total' => intval($total),   // total ของ record ที่ตรงกับ search
//             'page' => $page,
//             'limit' => $limit,
//         ]
//     ]);
// } catch (Exception $e) {
//     echo json_encode([
//         'success' => false,
//         'message' => "error: " . $e->getMessage()
//     ]);
// }
