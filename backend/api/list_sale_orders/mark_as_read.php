<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

require_once(__DIR__ . '/../db/conndb.php');

// รับข้อมูล JSON จาก body
$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['document_no'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing document_no parameter'
    ]);
    exit;
}

$document_no = $input['document_no'];

try {
    $stmt = $pdo->prepare("UPDATE sale_order SET is_read = 1 WHERE document_no = :document_no");
    $stmt->bindValue(':document_no', $document_no);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => "Marked document_no {$document_no} as read"
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => "No record updated, maybe document_no not found or already marked as read"
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => "Error: " . $e->getMessage()
    ]);
}
