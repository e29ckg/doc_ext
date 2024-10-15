<?php

// CORS headers
header("Access-Control-Allow-Origin: *"); // หรือคุณสามารถระบุโดเมนที่อนุญาตเช่น https://yourdomain.com แทน *
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// ตรวจสอบคำขอแบบ OPTIONS (สำหรับ preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ตรวจสอบ Authorization Header
$expectedToken = '8vinhYWQYinyaIiJi1nwkY1OAmlOU1tM'; // คุณสามารถตั้งค่าคีย์นี้เป็นคีย์ที่คุณต้องการใช้
$headers = getallheaders();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if ($authHeader !== $expectedToken) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}


date_default_timezone_set("Asia/Bangkok");

include_once './config/database.php';
include_once './objects/payments.php';
include_once './auth/authentication.php';

$database = new Database();
$db = $database->getConnection();

$transaction = new payments($db);

$request_method = $_SERVER["REQUEST_METHOD"];
switch ($request_method) {
    case 'POST':
        // รับข้อมูลจาก body ของ request
        $data = json_decode(file_get_contents('php://input'), true);

        // ตรวจสอบว่ามีการส่ง citizenId มาหรือไม่
        if (isset($data['citizenId'])) {
            $citizenId = $data['citizenId'];

            // ตรวจสอบว่า citizenId มีความยาวเป็น 13 หลัก
            if (preg_match('/^\d{13}$/', $citizenId)) {

                $offset = 0;
                $per_page = 25;
                $search = $citizenId;
                $records = [];
                $stmt = $transaction->readPaginated($offset, $per_page, $search);

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);

                    $transaction_item = array(
                        "vendor_name" => $vendor_name,
                        "amount" => $amount,
                        "effective_date" => convertDateToThaiFormat($effective_date)
                    );

                    array_push($records, $transaction_item);
                }


                // ตัวอย่างการคำนวณ pagination (ในกรณีนี้เป็นการประมวลผลแบบธรรมดา)
                $pagination = [
                    'total' => count($records),
                    'per_page' => 25,
                    'current_page' => 1,
                    'total_pages' => 1,
                    'next_page' => null,
                    'prev_page' => null,
                ];

                // สร้างการตอบกลับเป็น JSON
                $response = [
                    'records' => $records,
                    'pagination' => $pagination,
                ];

                http_response_code(200);
                echo json_encode($response);
            } else {
                // ตอบกลับข้อผิดพลาดหาก citizenId ไม่ถูกต้อง
                http_response_code(400);
                echo json_encode(['error' => 'Invalid citizenId format']);
            }
        } else {
            // ตอบกลับข้อผิดพลาดหากไม่มี citizenId ใน request
            http_response_code(400);
            echo json_encode(['error' => 'Missing citizenId']);
        }

        break;

    default:
        http_response_code(405);
        echo json_encode(array("status" => "error", "message" => "Method not allowed."));
        break;
}

function convertDateToThaiFormat($date)
{
    // สร้าง DateTime จากรูปแบบ "YYYY-MM-DD"
    $dateTime = DateTime::createFromFormat('Y-m-d', $date);

    // กำหนด array ของชื่อเดือนภาษาไทย
    $thaiMonths = [
        "ม.ค.",
        "ก.พ.",
        "มี.ค.",
        "เม.ย.",
        "พ.ค.",
        "มิ.ย.",
        "ก.ค.",
        "ส.ค.",
        "ก.ย.",
        "ต.ค.",
        "พ.ย.",
        "ธ.ค."
    ];

    // แยกปี พ.ศ. ออกจากปี ค.ศ.
    $year = $dateTime->format('Y') + 543; // เพิ่ม 543 เพื่อแปลงเป็น พ.ศ.
    $month = $dateTime->format('n') - 1; // ใช้ 'n' เพื่อนำเดือนแบบไม่ใช้ 0 นำหน้า และลดค่าเพื่อใช้กับ array
    $day = $dateTime->format('j'); // ใช้ 'j' เพื่อนำวันแบบไม่ใช้ 0 นำหน้า

    // สร้างวันที่ในรูปแบบ "DD เดือน พ.ศ."
    $thaiDate = $day . ' ' . $thaiMonths[$month] . ' ' . substr($year, -2);

    return $thaiDate;
}