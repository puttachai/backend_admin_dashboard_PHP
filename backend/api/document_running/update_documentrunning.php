<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once(__DIR__ . '/../db/conndb.php');
// require 'conndb.php'; // เชื่อมต่อ Database

$rawData = json_decode(file_get_contents("php://input"), true);

// ตรวจสอบค่าที่จำเป็น
$prefix = $rawData['prefix'] ?? '';
if (empty($prefix)) {
    echo json_encode([
        "success" => false,
        "message" => "Missing required field: prefix"
    ]);
    exit;
}

try {
    // ตรวจสอบว่ามี prefix นี้หรือไม่
    $stmt = $pdo->prepare("SELECT * FROM DocumentRunning WHERE prefix = :prefix");
    $stmt->execute([':prefix' => $prefix]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$existing) {
        throw new Exception("ไม่พบ prefix นี้ในระบบ");
    }

    // เพิ่มค่า run_number
    $newRunNumber = $existing['run_number'] + 1;
    $formattedRunNumber = str_pad($newRunNumber, 5, '0', STR_PAD_LEFT); // ✅ ทำให้เป็น "000002"

    // อัปเดต run_number ในฐานข้อมูล
    $updateStmt = $pdo->prepare("UPDATE DocumentRunning SET run_number = :run_number, updated_at = NOW() WHERE prefix = :prefix");
    $updateStmt->execute([
        ':run_number' => $newRunNumber,
        ':prefix' => $prefix
    ]);

    // ส่งกลับ doc_number ใหม่
    echo json_encode([
        "success" => true,
        "message" => "Updated run number to $newRunNumber",
        "doc_number" => $prefix . '-' . $formattedRunNumber
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}