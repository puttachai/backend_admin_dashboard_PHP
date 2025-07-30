<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// require 'conndb.php';
require_once(__DIR__ . '../db/conndb.php');

try {
    // ดึง department ไม่ซ้ำ
    $sql = "SELECT department, COUNT(*) AS total
                FROM employee
                GROUP BY department
                ORDER BY total DESC";
                
    // $sql = "SELECT DISTINCT department FROM employee ORDER BY department ASC";
    $stmt = $pdo->query($sql);
    // $departments = $stmt->fetchAll(PDO::FETCH_COLUMN); // ดึงเฉพาะค่าคอลัมน์เดียว
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "departments" => $result
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}
