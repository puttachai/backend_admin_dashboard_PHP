<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET");

// require_once('conndb.php');
require_once(__DIR__ . '/../db/conndb.php');

$response = [];

try {

    $stmt = $pdo->prepare('SELECT * FROM sale_order');
    $stmt->execute(); 
    $list_order = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response['success'] = true;
    $response['data'] = [
        'list_order' => $list_order
    ];
    

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
}


header('Content-Type: application/json');
echo json_encode($response);



