<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// เชื่อมต่อฐานข้อมูล
require 'conndb.php';

// รับข้อมูล JSON (จาก Vue FormData)
$data = $_POST;

// ตรวจสอบว่ามีฟิลด์สำคัญหรือไม่
if (empty($data['listCode']) || empty($data['sellDate'])) {
    echo json_encode([
        "success" => false,
        "message" => "Missing required fields (listCode, sellDate)"
    ]);
    exit;
}

try {
    // SQL Insert
    $sql = "INSERT INTO sale_order (
                list_code, sell_date, expire_date, reference, channel, tax_type,
                full_name, customer_code, phone, email, address,
                receiver_name, receiver_phone, receiver_email, receiver_address,
                note, delivery_date, tracking_no, delivery_type,
                total_discount, delivery_fee, product_qty, product_name, discount_qty,
                document_no
            ) VALUES (
                :list_code, :sell_date, :expire_date, :reference, :channel, :tax_type,
                :full_name, :customer_code, :phone, :email, :address,
                :receiver_name, :receiver_phone, :receiver_email, :receiver_address,
                :note, :delivery_date, :tracking_no, :delivery_type,
                :total_discount, :delivery_fee, :product_qty, :product_name, :discount_qty,
                :document_no
            )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':list_code'       => $data['listCode'] ?? '',
        ':sell_date'       => $data['sellDate'] ?? '',
        ':expire_date'     => $data['expireDate'] ?? '',
        ':reference'       => $data['reference'] ?? '',
        ':channel'         => $data['channel'] ?? '',
        ':tax_type'        => $data['taxType'] ?? '',
        ':full_name'       => $data['fullName'] ?? '',
        ':customer_code'   => $data['customerCode'] ?? '',
        ':phone'           => $data['phone'] ?? '',
        ':email'           => $data['email'] ?? '',
        ':address'         => $data['address'] ?? '',
        ':receiver_name'   => $data['receiverName'] ?? '',
        ':receiver_phone'  => $data['receiverPhone'] ?? '',
        ':receiver_email'  => $data['receiverEmail'] ?? '',
        ':receiver_address'=> $data['receiverAddress'] ?? '',
        ':note'            => $data['note'] ?? '',
        ':delivery_date'   => $data['deliveryDate'] ?? '',
        ':tracking_no'     => $data['trackingNo'] ?? '',
        ':delivery_type'   => $data['deliveryType'] ?? '',
        ':total_discount'  => $data['totalDiscount'] ?? '',
        ':delivery_fee'    => $data['deliveryFee'] ?? '',
        ':product_qty'     => $data['productqty'] ?? '',
        ':product_name'    => $data['productName'] ?? '',
        ':discount_qty'    => $data['discountqty'] ?? '',
        ':document_no'     => $data['documentNo'] ?? ''
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Sale order saved successfully",
        "id" => $pdo->lastInsertId()
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}
