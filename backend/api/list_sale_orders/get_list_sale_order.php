<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET");

require_once(__DIR__ . '/../db/conndb.php');

$page  = isset($_GET['page'])  ? intval($_GET['page'])  : 1;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_clean = preg_replace('/\s+/', ' ', $search);

try {
    $params = [];
    $whereSql = '';
    if ($search_clean !== '') {
        $words = explode(' ', $search_clean);
        $wordConditions = [];
        foreach ($words as $i => $word) {
            $key = ":word$i";
            $word = trim($word);
            if ($word === '') continue;
            $wordConditions[] = "(
                REPLACE(so.document_no,' ','') LIKE $key
                OR REPLACE(so.customer_code,' ','') LIKE $key
                OR REPLACE(so.full_name,' ','') LIKE $key
                OR REPLACE(so.phone,' ','') LIKE $key
                OR REPLACE(e.customer_no,' ','') LIKE $key
                OR REPLACE(e.full_name,' ','') LIKE $key
                OR REPLACE(dc.full_name,' ','') LIKE $key
            )";
            $params[$key] = "%$word%";
        }
        if (count($wordConditions) > 0) {
            $whereSql = " WHERE " . implode(' AND ', $wordConditions);
        }
    }

    // นับจำนวนข้อมูลทั้งหมด
    $totalSql = "
        SELECT COUNT(DISTINCT so.id) AS cnt
        FROM sale_order so
        LEFT JOIN customers c ON so.customer_code = c.customer_no
        LEFT JOIN debt_collector_assignments dca ON c.id = dca.customer_id
        LEFT JOIN debt_collectors dc ON dca.collector_id = dc.id
        LEFT JOIN employee e ON so.customer_code = e.customer_no
        $whereSql
    ";
    $totalStmt = $pdo->prepare($totalSql);
    $totalStmt->execute($params);
    $total = $totalStmt->fetch(PDO::FETCH_ASSOC)['cnt'];

    // 1) เลือก order id สำหรับหน้านี้
        $orderIdSql = "
            SELECT so.id
            FROM sale_order so
            $whereSql
            ORDER BY so.created_at DESC
            LIMIT :limit OFFSET :offset
        ";
        $orderIdStmt = $pdo->prepare($orderIdSql);
        foreach ($params as $k=>$v) $orderIdStmt->bindValue($k,$v);
        $orderIdStmt->bindValue(':limit',$limit,PDO::PARAM_INT);
        $orderIdStmt->bindValue(':offset',$offset,PDO::PARAM_INT);
        $orderIdStmt->execute();
        $orderIds = $orderIdStmt->fetchAll(PDO::FETCH_COLUMN);

        if (!$orderIds) {
            echo json_encode([
                'success'=>true,
                'data'=>['list_order'=>[], 'total'=>(int)$total, 'page'=>$page, 'limit'=>$limit]
            ]);
            exit;
        }

        // 2) ดึงข้อมูลหลัก + collector สำหรับ id เหล่านี้
        $in = implode(',', array_fill(0, count($orderIds), '?'));
        $sql = "
            SELECT 
                so.id AS order_id,
                so.document_no,
                so.list_code, 
                so.full_name AS customer_name,
                so.customer_code, 
                so.phone, 
                so.final_total_price, 
                so.status, so.created_at,
                e.full_name AS employee_name, 
                e.telephone AS employee_phone,
                dc.id AS collector_id, 
                e.emp_ids AS collector_emp_ids,
                dc.full_name AS collector_full_name, 
                dc.telephone AS collector_phone
            FROM sale_order so
                LEFT JOIN customers c ON so.customer_code = c.customer_no
                LEFT JOIN debt_collector_assignments dca ON c.id = dca.customer_id
                LEFT JOIN debt_collectors dc ON dca.collector_id = dc.id
                LEFT JOIN employee e ON so.customer_code = e.customer_no
            WHERE so.id IN ($in)
            ORDER BY so.created_at DESC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($orderIds);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3) Group collector แบบกรองซ้ำ
        $list_order=[];
        foreach($rows as $r){
        $id=$r['order_id'];
        if(!isset($list_order[$id])){
            $list_order[$id]=[
            'id'=>$id,
            'document_no'=>$r['document_no'],
            'list_code'=>$r['list_code'],
            'full_name'=>$r['customer_name'],
            'customer_code'=>$r['customer_code'],
            'phone'=>$r['phone'],
            'final_total_price'=>$r['final_total_price'],
            'status'=>$r['status'],
            'created_at'=>$r['created_at'],
            'employee_name'=>$r['employee_name'],
            'employee_phone'=>$r['employee_phone'],
            'collector_list'=>[]
            ];
        }

        if($r['collector_id']){
            $exists=false;
            foreach($list_order[$id]['collector_list'] as $c){
            if($c['id']==$r['collector_id'] ){ // && $c['emp_ids']==$r['collector_emp_ids']
                $exists=true; break;
            }
            }
            if(!$exists){
            $list_order[$id]['collector_list'][]=[
                'id'=>$r['collector_id'],
                'emp_ids'=>$r['collector_emp_ids'],
                'full_name'=>$r['collector_full_name'],
                'telephone'=>$r['collector_phone']
            ];
            }
        }
        }
        $list_order=array_values($list_order);

        echo json_encode([
        'success'=>true,
        'data'=>[
        'list_order'=>$list_order,
        'total'=>(int)$total,
        'page'=>$page,
        'limit'=>$limit
        ]
        ]);


} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => "error: " . $e->getMessage()
    ]);
}


    // ดึงข้อมูลพร้อม collector
    //  dc.emp_ids AS collector_emp_ids,
    // $sql = "
    //     SELECT 
    //         so.id AS order_id,
    //         ANY_VALUE(so.document_no) AS document_no,
    //         ANY_VALUE(so.list_code) AS list_code,
    //         ANY_VALUE(so.full_name) AS customer_name,
    //         ANY_VALUE(so.customer_code) AS customer_code,
    //         ANY_VALUE(so.phone) AS phone,
    //         ANY_VALUE(so.final_total_price) AS final_total_price,
    //         ANY_VALUE(so.status) AS status,
    //         ANY_VALUE(so.created_at) AS created_at,
    //         ANY_VALUE(dc.id) AS collector_id,            -- เพิ่ม collector_id
    //         ANY_VALUE(e.emp_ids) AS collector_emp_ids,  -- เพิ่ม collector_emp_ids
    //         ANY_VALUE(dc.full_name) AS employee_name,
    //         ANY_VALUE(dc.telephone) AS employee_phone,
    //         ANY_VALUE(dc.telephone) AS collector_phone,
    //         GROUP_CONCAT(DISTINCT dc.full_name SEPARATOR ', ') AS collector_full_name
    //     FROM sale_order so
    //     LEFT JOIN customers c ON so.customer_code = c.customer_no
    //     LEFT JOIN debt_collector_assignments dca ON c.id = dca.customer_id
    //     LEFT JOIN debt_collectors dc ON dca.collector_id = dc.id
    //     LEFT JOIN employee e ON so.customer_code = e.customer_no

    //     $whereSql

    //     GROUP BY so.id
    //     ORDER BY so.created_at DESC
    //     LIMIT :limit OFFSET :offset;
    
    // ";



 // SQL 
    // SELECT 
    //         so.*,
    //         e.full_name AS employee_name,
    //         e.telephone AS employee_phone,
    //         dc.id AS collector_id,
           
    //         dc.full_name AS collector_name,
    //         dc.telephone AS collector_phone
    //     FROM sale_order so
    //     LEFT JOIN customers c ON so.customer_code = c.customer_no
    //     LEFT JOIN debt_collector_assignments dca ON c.id = dca.customer_id
    //     LEFT JOIN debt_collectors dc ON dca.collector_id = dc.id
    //     LEFT JOIN employee e ON so.customer_code = e.customer_no
    //     $whereSql
    //     ORDER BY so.created_at DESC
    //     LIMIT :limit OFFSET :offset


    // $stmt = $pdo->prepare(
    //     "SELECT so.*, so.full_name AS employee_name, so.phone AS employee_phone
    //     FROM sale_order so
    //     LEFT JOIN employee e ON so.customer_code = e.customer_no
    //     $whereSql
    //     ORDER BY so.created_at DESC
    //     LIMIT :limit OFFSET :offset"
    // );


// header("Access-Control-Allow-Origin: *");
// header("Access-Control-Allow-Headers: Content-Type");
// header("Access-Control-Allow-Methods: GET");

// require_once(__DIR__ . '/../db/conndb.php');

// $page  = isset($_GET['page'])  ? intval($_GET['page'])  : 1;
// $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
// $offset = ($page - 1) * $limit;

// $search = isset($_GET['search']) ? trim($_GET['search']) : '';

// // var_dump($search);die;

// try {
//     $params = [];
//     $whereSql = '';
//     if ($search !== '') {
//         // ใช้วงเล็บเพื่อให้ OR ใน WHERE ถูกต้อง
//         $whereSql = " WHERE (so.document_no LIKE :search OR so.customer_code LIKE :search OR so.full_name LIKE :search OR so.phone LIKE :search)";
//         $params[':search'] = "%$search%";
//     }

//     // ดึงจำนวนรวมที่ตรงเงื่อนไข search
//     $totalStmt = $pdo->prepare(
//         "SELECT COUNT(*) AS cnt
//         FROM sale_order so
//         LEFT JOIN employee e ON so.customer_code = e.customer_no
//         $whereSql"
//     );
//     $totalStmt->execute($params);
//     $total = $totalStmt->fetch(PDO::FETCH_ASSOC)['cnt'];

//     // ดึงข้อมูลหน้าที่ พร้อมเงื่อนไข search และ pagination
//     $stmt = $pdo->prepare(
//         "SELECT so.*, so.full_name AS employee_name, so.phone AS employee_phone
//         FROM sale_order so
//         LEFT JOIN employee e ON so.customer_code = e.customer_no
//         $whereSql
//         ORDER BY so.created_at DESC
//         LIMIT :limit OFFSET :offset"
//     );

//     // bind search param ถ้ามี
//     foreach ($params as $key => $value) {
//         $stmt->bindValue($key, $value);
//     }
//     $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
//     $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

//     $stmt->execute();
//     $list_order = $stmt->fetchAll(PDO::FETCH_ASSOC);

//     echo json_encode([
//         'success' => true,
//         'data' => [
//             'list_order' => $list_order,
//             'total' => intval($total),   // total ของ record ที่ตรงกับ search
//             'page' => $page,
//             'limit' => $limit,
//         ]
//     ]);
// } catch (Exception $e) {
//     echo json_encode([
//         'success' => false,
//         'message' => "error: " . $e->getMessage()
//     ]);
// }
