<?php
// อนุญาตให้ทุกต้นทางเรียก API ได้ (ใน development)
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

// require 'conndb.php';
require_once(__DIR__ . '../db/conndb.php');

try {
    // ดึงพนักงานที่สมัครล่าสุด 5 คน (เรียงจากวันเริ่มงานมากไปน้อย)
    $stmt = $pdo->prepare("SELECT full_name, telephone, department, start_work FROM employee ORDER BY start_work DESC LIMIT 5");
    $stmt->execute();
    $latestEmployees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($latestEmployees, JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
