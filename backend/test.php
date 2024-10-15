<?php
// ตั้งค่า Header ให้ส่งข้อมูลเป็น JSON
header('Content-Type: application/json');

// รับค่า Secret Token จากตัวแปรสภาพแวดล้อม
$authToken = getenv('SECRET_TOKEN');

// การตรวจสอบการยืนยันตัวตน
$headers = apache_request_headers();
$clientToken = isset($headers['Authorization']) ? $headers['Authorization'] : '';

// ตรวจสอบว่า Token ที่ได้รับตรงกับ Token ที่กำหนดหรือไม่
if ($clientToken !== $authToken) {
    $response = [
        'status' => 'error',
        'message' => 'Unauthorized'
    ];
    echo json_encode($response);
    http_response_code(401); // ส่งสถานะ HTTP 401 Unauthorized
    exit;
}

// รับข้อมูล POST ที่ถูกส่งมา
$postData = file_get_contents('php://input');

// แปลงข้อมูลจาก JSON เป็น Array
$data = json_decode($postData, true);

// ตรวจสอบว่าข้อมูลถูกต้องหรือไม่
if ($data === null) {
    // กรณีข้อมูลไม่ถูกต้องหรือล้มเหลวในการแปลง JSON
    $response = [
        'status' => 'error',
        'message' => 'Invalid JSON received'
    ];
} else {
    // กรณีข้อมูลถูกต้อง
    $response = [
        'status' => 'success',
        'received_data' => $data
    ];
}

// ส่งข้อมูลตอบกลับไปในรูปแบบ JSON
echo json_encode($response);
