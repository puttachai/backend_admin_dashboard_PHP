<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET");

// require_once('conndb.php');
require_once(__DIR__ . '/../db/conndb.php');

// $response = [];

$page  = isset($_GET['page'])  ? intval($_GET['page'])  : 1;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$offset = ($page - 1) * $limit;

try {
    // ดึงจำนวนรวม
    $totalStmt = $pdo->query('SELECT COUNT(*) AS cnt FROM sale_order');
    $total = $totalStmt->fetch(PDO::FETCH_ASSOC)['cnt'];

    // ดึงข้อมูลเฉพาะหน้าที่ // id // DESC // ASC
    $stmt = $pdo->prepare(
        'SELECT * FROM sale_order ORDER BY created_at DESC LIMIT :limit OFFSET :offset'
    );
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $list_order = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => [
            'list_order' => $list_order,
            'total' => intval($total)
        ]
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => "error: " . $e->getMessage()
    ]);
}

// try {

//     $stmt = $pdo->prepare('SELECT * FROM sale_order');
//     $stmt->execute(); 
//     $list_order = $stmt->fetchAll(PDO::FETCH_ASSOC);

//     $response['success'] = true;
//     $response['data'] = [
//         'list_order' => $list_order
//     ];
    

// } catch (Exception $e) {
//     $response['success'] = false;
//     $response['message'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
// }


// header('Content-Type: application/json');
// echo json_encode($response);



