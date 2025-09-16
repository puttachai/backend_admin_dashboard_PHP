<?php 
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

require_once(__DIR__ . '/db/conndb.php');

// ใช้ $_POST แทน เพราะ Vue ส่งเป็น multipart/form-data
$data = $_POST;

// Debug ดูข้อมูล
// var_dump($data); die;

if (empty($data['id'])) {
    echo json_encode(["success" => false, "message" => "Missing employee ID"]);
    exit;
}

$id          = $data['id'];
$full_name   = $data['full_name'] ?? '';
$email       = $data['email'] ?? '';
$telephone   = $data['telephone'] ?? '';
$address     = $data['address'] ?? '';
$department  = $data['department'] ?? '';
$salary      = isset($data['salary']) ? (float)$data['salary'] : 0;
$status      = $data['status'] ?? 'Normal';
$start_work  = !empty($data['start_work']) ? $data['start_work'] : null;

// ฟิลด์เพิ่มเติม
$adminid        = $data['adminid'] ?? null;
$account        = $data['account'] ?? '';
$nickname_admin = $data['nickname_admin'] ?? '';
$customer_id    = $data['customer_id'] ?? null;
$nickname       = $data['nickname'] ?? '';
$contact        = $data['contact'] ?? '';
$mobile         = $data['mobile'] ?? '';
$customer_no    = $data['customer_no'] ?? '';
$sale_no        = $data['sale_no'] ?? '';
$groups         = isset($data['groups']) ? (int)$data['groups'] : null;
$label          = $data['label'] ?? '';
$value          = $data['value'] ?? '';
$level          = isset($data['level']) ? (int)$data['level'] : null;
$customer_name  = $data['customer_name'] ?? '';

$imagePath = null; // <-- ป้องกัน undefined variable



if (!$imagePath) {
    // ถ้าไม่มีการอัปโหลดใหม่ + DB ไม่มีรูป → ใส่ default
    $stmtCheck = $pdo->prepare("SELECT image_path FROM employee WHERE id = :id");
    $stmtCheck->execute([':id' => $id]);
    $currentImage = $stmtCheck->fetchColumn();

    if (empty($currentImage)) {
        $uploadDir = __DIR__ . '/../img/profile/';
        $defaultFile = $uploadDir . 'default.jpg';
        if (file_exists($defaultFile)) {
            $imageName = uniqid() . '_default.jpg';
            $newPath = $uploadDir . $imageName;
            copy($defaultFile, $newPath);
            $imagePath = 'http://localhost:8000/api_admin_dashboard/backend/img/profile/' . $imageName;
            // $imagePath = 'https://api-sale.dpower.co.th/api_admin_dashboard/backend/img/profile/' . $imageName;
        }
    }
}


try {
 $sql = "UPDATE employee SET 
            emp_ids       = :emp_ids,
            full_name     = :full_name,
            email         = :email,
            telephone     = :telephone,
            address       = :address,
            department    = :department,
            salary        = :salary,
            status        = :status,
            start_work    = :start_work,
            adminid       = :adminid,
            account       = :account,
            nickname_admin= :nickname_admin,
            customer_id   = :customer_id,
            nickname      = :nickname,
            contact       = :contact,
            mobile        = :mobile,
            customer_name = :customer_name,
            customer_no   = :customer_no,
            sale_no       = :sale_no,
            `groups`      = :groups,
            label         = :label,
            `value`       = :value,
            `level`       = :level"

            . ($imagePath ? ", image_path = :image_path" : "") . "
        WHERE id = :id";


    $stmt = $pdo->prepare($sql);

    $params = [
        ':id'            => $id,
        ':emp_ids'       => $data['emp_ids'] ?? 0,
        ':full_name'     => $full_name,
        ':email'         => $email,
        ':telephone'     => $telephone,
        ':address'       => $address,
        ':department'    => $department,
        ':salary'        => $salary,
        ':status'        => $status,
        ':start_work'    => $start_work,
        ':adminid'       => $adminid,
        ':account'       => $account,
        ':nickname_admin'=> $nickname_admin,
        ':customer_id'   => $customer_id,
        ':nickname'      => $nickname,
        ':contact'       => $contact,
        ':mobile'        => $mobile,
        ':customer_name' => $contact,
        // ':customer_name' => $customer_name,
        ':customer_no'   => $customer_no,
        ':sale_no'       => $sale_no,
        ':groups'        => $groups,
        ':label'         => $label,
        ':value'         => $value,
        ':level'         => $level
    ];

   
    if ($imagePath) {
        $params[':image_path'] = $imagePath;
    }


    $stmt->execute($params);

    echo json_encode([
        "success" => true,
        "message" => "Update Data Successfully",
        "updated_id" => $id,
        "image" => $imagePath
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}


// // หากมีการอัปโหลดรูปภาพใหม่
// if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
//     $uploadDir = __DIR__ . '/../img/profile/'; ///../../img/profile/
//     $imageName = uniqid() . '_' . basename($_FILES['image']['name']);
//     $imagePathOnServer = $uploadDir . $imageName;

//     if (!is_dir($uploadDir)) {
//         mkdir($uploadDir, 0777, true);
//     }

//     if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePathOnServer)) {
//         $imagePath = 'http://localhost:8000/api_admin_dashboard/backend/img/profile/' . $imageName;
//         // $imagePath = 'https://api-sale.dpower.co.th/api_admin_dashboard/backend/img/profile/' . $imageName;
//     } else {
//         echo json_encode(["success" => false, "message" => "Failed to upload image."]);
//         exit;
//     }
// }