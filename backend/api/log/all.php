<?php
// log/all.php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json; charset=UTF-8");

require_once(__DIR__ . '/../db/conndb.php');

try {
    $stmt = $pdo->query("SELECT * FROM activity_logs ORDER BY timestamp DESC");
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "logs" => $logs]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "เกิดข้อผิดพลาด: " . $e->getMessage()]);
}
