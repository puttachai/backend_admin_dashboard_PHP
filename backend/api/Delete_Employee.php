<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Methods: DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// require 'conndb.php';
// require 'conndb.php';
require_once(__DIR__ . '../db/conndb.php');

// รับค่าจาก frontend
$data = json_decode(file_get_contents("php://input"), true);

// ตรวจสอบว่ามี ID และข้อมูลที่ต้องการอัปเดตหรือไม่
if (empty($data['id'])) {
    echo json_encode(["success" => false, "message" => "Missing employee ID"]);
    exit;
}

$id = $data['id'];

try {
    $sql = "DELETE FROM employee WHERE id = :id";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':id' => $id,
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Delete Data SuccessFully",
        "Delete_id" => $id
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}
