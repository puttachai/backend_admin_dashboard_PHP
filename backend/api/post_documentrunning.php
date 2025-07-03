<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require 'conndb.php'; // เชื่อมต่อ Database

$rawData = json_decode(file_get_contents("php://input"), true);

// ตรวจสอบค่าที่จำเป็น
$requiredFields = ['warehouse_code', 'doc_type'];
$missing = array_filter($requiredFields, fn($key) => empty($rawData[$key]));

if (!empty($missing)) {
    echo json_encode([
        "success" => false,
        "message" => "Missing required fields: " . implode(', ', $missing),
        "postData" => $rawData
    ]);
    exit;
}

// ดึงค่าจาก frontend
$warehouse_code = $rawData['warehouse_code'];
$doc_type = strtoupper($rawData['doc_type']); // doc_typeเช่น SO
$date = new DateTime();
$thai_year = $date->format('Y') + 543;
$today = $thai_year . $date->format('md'); // ปีเดือนวัน เช่น 25680619

$prefix = $warehouse_code . '-' . $doc_type . $today; // เช่น H1-SO25680619

try {
    // ตรวจสอบว่ามี prefix นี้หรือยัง
    $stmt = $pdo->prepare("SELECT * FROM DocumentRunning WHERE prefix = :prefix");
    $stmt->execute([':prefix' => $prefix]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // ถ้ามี → เพิ่ม run_number +1
        $newRunNumber = $existing['run_number'] + 1;
        $formattedRunNumber = str_pad($newRunNumber, 6, '0', STR_PAD_LEFT); // ✅ ทำให้เป็น "000001"

        $updateStmt = $pdo->prepare("UPDATE DocumentRunning SET run_number = :run_number, updated_at = NOW() WHERE prefix = :prefix");
        $updateStmt->execute([
            // ':run_number' => $formattedRunNumber,
            ':run_number' => $newRunNumber,
            ':prefix' => $prefix
        ]);

        echo json_encode([
            "success" => true,
            "message" => "Updated run number to $newRunNumber",
            // "prefix" => $prefix,
            "list_code" => $prefix,
            "doc_number" => $prefix . '-' . $formattedRunNumber 
        ]);
        // "run_number" => $prefix + $newRunNumber,
    } else {

        // $formattedRunNumber = str_pad(1, 6, '0', STR_PAD_LEFT); // "000001"

        // ถ้ายังไม่มี → เพิ่มใหม่ run_number = 1
        $newRunNumber = 1;
        // $newRunNumber = $existing['run_number'] + 1;
        $formattedRunNumber = str_pad($newRunNumber, 6, '0', STR_PAD_LEFT); // ✅ ทำให้เป็น "000001"

        $insertStmt = $pdo->prepare("INSERT INTO DocumentRunning (warehouse_code, doc_type, prefix, run_number) VALUES (:warehouse_code, :doc_type, :prefix, :run_number)");
        $insertStmt->execute([
            ':warehouse_code' => $warehouse_code,
            ':doc_type' => $doc_type,
            ':prefix' => $prefix,
            ':run_number' => $newRunNumber
        ]);

        echo json_encode([
            "success" => true,
            "message" => "Created new document number",
            "list_code" => $prefix,
            "run_number" => $formattedRunNumber,
            "doc_number" => $prefix . '-' . $formattedRunNumber
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}
