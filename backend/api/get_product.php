<?php
// อนุญาตให้ทุกต้นทางเรียก API ได้ (ใน development)
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

require 'conndb.php';

try {
    // ดึงข้อมูล product ทั้งหมด
    $stmt = $pdo->prepare("SELECT * FROM products");
    $stmt->execute();
    $productsList = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($productsList as &$product) {
        // ดึงภาพที่เกี่ยวข้องกับ product จากตาราง product_images
        $imageStmt = $pdo->prepare("SELECT pro_images FROM products WHERE id = ?");
        $imageStmt->execute([$product['id']]);
        $images = $imageStmt->fetchAll(PDO::FETCH_COLUMN);

        // แปลงเป็น array ของ objects
        $product['images'] = array_map(function ($imgPath) {
            return ['imagePath' => $imgPath];
        }, $images);
    }

    echo json_encode($productsList, JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
