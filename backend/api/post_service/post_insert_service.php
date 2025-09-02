<?php
header("Content-Type: application/json; charset=UTF-8");

require_once(__DIR__ . '/../db/conndb.php');

$response = [];

try {
    // ---- 1. รับค่าจาก JSON body ----
    $input = json_decode(file_get_contents('php://input'), true);
    $service_code = $input['ServiceCode'] ?? null;

    if (!$service_code) {
        throw new Exception("กรุณาส่ง service_code");
    }

    // ---- 2. ยิง API ไปหา ServicesControllers ----
    $apiUrl = "http://host.docker.internal:5005/api/ServicesControllers";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);

    // ส่งแบบ form-data (x-www-form-urlencoded)
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        "ServiceCode" => $service_code
    ]));

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Accept: application/json",
        "Content-Type: application/x-www-form-urlencoded", // ใช้ form-urlencoded
        "Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiJKd3RTdWJqZWN0IiwianRpIjoiMzAxM2JiZDktYjM0NC00MjFkLWIyZGItYzczMDBjY2QyMWJmIiwiVXNlck5hbWUiOiJEUG93ZXIxIiwiVXNlcklEIjoiMiIsIlJvbGVzIjoiVXNlciIsIlBlcm1pc3Npb25zIjoiIiwiTWVudVBlcm1pc3Npb25zIjoiIiwiZXhwIjoxNzU2NzE2NTYyLCJpc3MiOiJKd3RJc3N1ZXIiLCJhdWQiOiJKd3RBdWRpZW5jZSJ9.-KHGR5qBhYAW0k881FmtMhe-a9cCk6UhTXccLfA_xjU"
    ]);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        throw new Exception("cURL Error: " . curl_error($ch));
    }
    curl_close($ch);

    $servicesData = json_decode($result, true);
    if (!$servicesData) {
        throw new Exception("ไม่สามารถ decode ข้อมูลจาก API ได้: $result");
    }

    // var_dump($servicesData);die;

    // ---- 3. Loop ข้อมูลมา insert/update ----
foreach ($servicesData as $service) {

    $code = $service['service_code'] ?? '';
    $code = trim(preg_replace('/[\r\n\t]+/', '', $code));
    $name = trim($service['service_name'] ?? '');
    $code2 = trim($service['service_code2'] ?? '');
    $code2 = trim(preg_replace('/[\r\n\t]+/', '', $code2));
    $unit = trim($service['service_unit'] ?? '');
    $psi = trim($service['service_psi'] ?? '');

    // var_dump('Check services:' . $code . $name . $code2 . $unit . $psi);die;
    // if ($code === '') {
    //     // ถ้าไม่มี code → ข้าม
    //     continue;
    // }

    // ตรวจสอบว่ามี service_code อยู่แล้ว
    $stmtCheck = $pdo->prepare("SELECT id FROM mst_so_service WHERE service_code = ?");
    $stmtCheck->execute([$code]);
    $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // ถ้ามีอยู่แล้ว → ข้ามไปตัวถัดไป
        continue;
    }

    // ถ้าไม่มี → ทำการ insert
    $stmtIns = $pdo->prepare("INSERT INTO mst_so_service 
                                (service_code, service_code2, service_unit, service_psi, service_name, qty, price)
                                VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmtIns->execute([
        $code,
        $code2 ?: $code,
        $unit,
        $psi,
        $name,
        $service['qty'] ?? 1,
        $service['price'] ?? 100
    ]);
}



    // ---- 4. ดึงข้อมูลออกมาเพื่อส่งกลับ ----
    $stmtServices = $pdo->prepare("SELECT * FROM mst_so_service ");
    $stmtServices->execute();
    $services = $stmtServices->fetchAll(PDO::FETCH_ASSOC);

    // var_dump('Check services:' . $services);die;

    $response['success'] = true;
    $response['message'] = "อัปเดตรายการเรียบร้อยแล้ว";
    $response['data'] = ['services' => $services];

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
