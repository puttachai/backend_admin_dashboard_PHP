<?php
// log/activity.php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

// 👇 สำคัญมาก: ตอบกลับ OPTIONS ทันที
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// header("Access-Control-Allow-Origin: *");
// header("Access-Control-Allow-Headers: Content-Type");
// header("Access-Control-Allow-Methods: POST");
// header("Content-Type: application/json; charset=UTF-8");

require_once(__DIR__ . '/../db/conndb.php');

try {
    $data = json_decode(file_get_contents("php://input"), true);

    $account = $data['account'] ?? 'unknown';
    $ip = $data['ip'] ?? 'unknown';
    $activity = $data['activity'] ?? '';
    $page = $data['page'] ?? '';
    $timestamp = $data['timestamp'] ?? date('Y-m-d H:i:s');

    if (!$activity || !$page) {
        throw new Exception("กรุณาระบุ activity และ page");
    }

    $stmt = $pdo->prepare("INSERT INTO activity_logs (account, ip_address, activity, page, timestamp) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$account, $ip, $activity, $page, $timestamp]);

    echo json_encode(["success" => true, "message" => "Log บันทึกเรียบร้อยแล้ว"]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "เกิดข้อผิดพลาด: " . $e->getMessage()]);
}
