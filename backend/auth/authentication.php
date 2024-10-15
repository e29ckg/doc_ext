<?php

// require_once '../vendor/autoload.php';
// use \Firebase\JWT\JWT;

// class Authentication {
    
//     private $key = "your_secret_key"; // กำหนดคีย์สำหรับสร้างและตรวจสอบ Token

//     public function generateToken($data) {
//         $issued_at = time(); // กำหนดเวลาที่ Token ถูกสร้าง
//         $expiration_time = $issued_at + (60 * 60); // กำหนดเวลาหมดอายุของ Token ให้เป็นหนึ่งชั่วโมง

//         // กำหนดข้อมูลใน Token
//         $payload = array(
//             "iss" => "your_domain_name",
//             "iat" => $issued_at,
//             "exp" => $expiration_time,
//             "data" => $data
//         );

//         // สร้าง Token
//         $jwt = JWT::encode($payload, $this->key);

//         return $jwt; // คืนค่า Token ที่สร้าง
//     }

//     public function validateToken($jwt) {
//         try {
//             // ตรวจสอบ Token
//             $decoded = JWT::decode($jwt, $this->key, array('HS256'));
//             return $decoded; // คืนค่าข้อมูลถูกต้องถ้า Token ถูกต้อง
//         } catch (Exception $e) {
//             return false; // คืนค่า false ถ้าเกิดข้อผิดพลาดในการตรวจสอบ Token
//         }
//     }
// }

?>
