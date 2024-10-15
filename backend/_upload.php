<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET,HEAD,OPTIONS,POST,PUT");
header("Access-Control-Allow-Headers: Content-Type, Accept");
header("Content-Type: application/json; charset=utf-8");


// โฟลเดอร์ที่ต้องการเก็บไฟล์อัปโหลด
$target_dir = "../uploads/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

// ตรวจสอบว่าผู้ใช้กดปุ่มอัปโหลดหรือไม่
if(isset($_POST["submit"])) {
    // ตรวจสอบว่ามีไฟล์ที่อัปโหลดหรือไม่
    if ($_FILES["fileToUpload"]["size"] == 0) {
        $response = array(
            "status" => "error",
            "message" => "No file was uploaded."
        );
        echo json_encode($response);
        $uploadOk = 0;
        exit;
    }
}

// ตรวจสอบขนาดไฟล์ (เช่น ไม่เกิน 5MB)
if ($_FILES["fileToUpload"]["size"] > 5000000) {
    $response = array(
        "status" => "error",
        "message" => "Sorry, your file is too large."
    );
    echo json_encode($response);
    $uploadOk = 0;
    exit;
}

// อนุญาตเฉพาะไฟล์ .txt เท่านั้น
if($fileType != "txt") {
    $response = array(
        "status" => "error",
        "message" => "Sorry, only .txt files are allowed."
    );
    echo json_encode($response);
    $uploadOk = 0;
    exit;
}

// ตรวจสอบว่า $uploadOk ตั้งค่าเป็น 0 โดยเกิดข้อผิดพลาดบางอย่าง
if ($uploadOk == 0) {
    $response = array(
        "status" => "error",
        "message" => "Sorry, your file was not uploaded."
    );
    echo json_encode($response);
    exit;
// หากทุกอย่างเรียบร้อย ให้ย้ายไฟล์ไปยังโฟลเดอร์ที่กำหนด
} else {
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {

        $filename = $_FILES['fileToUpload']['name'];

        // ตรวจสอบว่าเป็นไฟล์ .txt
        $fileinfo = pathinfo($_FILES['fileToUpload']['name']);
        $name_mode = substr($_FILES['fileToUpload']['name'], 0, 3); // DCT   MCL
        // $fileinfo = pathinfo($filename);
        // $name_mode = substr($filename, 0, 3); // DCT   MCL
        // echo $filename;

        // exit;
        
        if (isset($fileinfo['extension']) && strtolower($fileinfo['extension']) == 'txt') {
            // อ่านเนื้อหาภายในไฟล์
            if ($name_mode !== "DCT" && $name_mode !== "MCL") {
                http_response_code(200);
                $data = array(
                    'status' => 'error',
                    'message' => 'ไม่พบข้อมูล ',
                );
                echo json_encode($data);
                exit();
            }
            
            $content = file_get_contents($target_file, FILE_IGNORE_NEW_LINES);
            $text_utf8 = iconv(mb_detect_encoding($content, mb_detect_order(), true), "UTF-8", $content);

            $data = explode("\n", $text_utf8);
            $arr_lines = array();
            foreach ($data as $line) {
                if (strpos($line, " ") !== false) {
                    $arr_lines[] = $line;
                }
            }            
            
            $datas = array();
            $header = getHeader($arr_lines[0]);

            foreach ($arr_lines as $index => $line) {
                // แยกข้อมูลในบรรทัด
                $data = explode(" ", $line);
                $arr_data = array();

                foreach ($data as $value) {
                    $trimmed_value = trim($value); // ลบช่องว่างที่ติดอยู่ด้านหน้าและด้านหลังของข้อความ
                    if ($trimmed_value !== "") { // ตรวจสอบว่าข้อมูลไม่เป็นช่องว่าง
                        $arr_data[] = $trimmed_value;
                    }
                }

                if ($index % 2 === 1) {
                    $datas[] = getDatas($arr_data, $header);
                }
            }

            // ข้อมูลที่ต้องการส่งกลับ
            http_response_code(200);
            $data = array(
                'status' => 'success',
                'header' => $header,
                'datas' => $datas,
                'message' => 'Data received successfully'
            );
            echo json_encode($data);
            exit();

        } else {
            http_response_code(200);
            $data = array(
                'status' => 'error',
                'message' => 'กรุณาอัปโหลดไฟล์ .txt เท่านั้น'
            );
            echo json_encode($data);
            exit();
        }

        
    } else {
        $response = array(
            "status" => "error",
            "message" => "Sorry, there was an error uploading your file."
        );
        echo json_encode($response);
    }
}


// if ($_SERVER['REQUEST_METHOD'] == 'POST') {
//     if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
//         $filename = $_FILES['file']['tmp_name'];

//         // ตรวจสอบว่าเป็นไฟล์ .txt
//         $fileinfo = pathinfo($_FILES['file']['name']);
//         $name_mode = substr($_FILES['file']['name'], 0, 3); // DCT   MCL
        
//         if (isset($fileinfo['extension']) && strtolower($fileinfo['extension']) == 'txt') {
//             // อ่านเนื้อหาภายในไฟล์
//             if ($name_mode !== "DCT" && $name_mode !== "MCL") {
//                 http_response_code(200);
//                 $data = array(
//                     'status' => 'error',
//                     'message' => 'ไม่พบข้อมูล ',
//                 );
//                 echo json_encode($data);
//                 exit();
//             }
            
//             $content = file_get_contents($filename, FILE_IGNORE_NEW_LINES);
//             $text_utf8 = iconv(mb_detect_encoding($content, mb_detect_order(), true), "UTF-8", $content);

//             $data = explode("\n", $text_utf8);
//             $arr_lines = array();
//             foreach ($data as $line) {
//                 if (strpos($line, " ") !== false) {
//                     $arr_lines[] = $line;
//                 }
//             }            
            
//             $datas = array();
//             $header = getHeader($arr_lines[0]);

//             foreach ($arr_lines as $index => $line) {
//                 // แยกข้อมูลในบรรทัด
//                 $data = explode(" ", $line);
//                 $arr_data = array();

//                 foreach ($data as $value) {
//                     $trimmed_value = trim($value); // ลบช่องว่างที่ติดอยู่ด้านหน้าและด้านหลังของข้อความ
//                     if ($trimmed_value !== "") { // ตรวจสอบว่าข้อมูลไม่เป็นช่องว่าง
//                         $arr_data[] = $trimmed_value;
//                     }
//                 }

//                 if ($index % 2 === 1) {
//                     $datas[] = getDatas($arr_data, $header);
//                 }
//             }

//             // ข้อมูลที่ต้องการส่งกลับ
//             http_response_code(200);
//             $data = array(
//                 'status' => 'success',
//                 'header' => $header,
//                 'datas' => $datas,
//                 'message' => 'Data received successfully'
//             );
//             echo json_encode($data);
//             exit();

//         } else {
//             http_response_code(200);
//             $data = array(
//                 'status' => 'error',
//                 'message' => 'กรุณาอัปโหลดไฟล์ .txt เท่านั้น'
//             );
//             echo json_encode($data);
//             exit();
//         }
//     } else {
//         http_response_code(200);
//         $data = array(
//             'status' => 'error',
//             'message' => 'เกิดข้อผิดพลาดในการอัปโหลดไฟล์'
//         );
//         echo json_encode($data);
//         exit();
//     }
// } else {
//     http_response_code(200);
//     $data = array(
//         'status' => 'error',
//         'message' => 'วิธีการร้องขอไม่ถูกต้อง'
//     );
//     echo json_encode($data);
//     exit();
// }

function getHeader($arr_lines) {
    $version = null;

    $data = $arr_lines;
    $data = explode(" ", $data);

    $arr_data = array();    
    
    foreach ($data as $value) {
        $trimmed_value = trim($value); // ลบช่องว่างที่ติดอยู่ด้านหน้าและด้านหลังของข้อความ
        if ($trimmed_value !== "") { // ตรวจสอบว่าข้อมูลไม่เป็นช่องว่าง
            $arr_data[] = $trimmed_value;
        }
    }
    
    $version = substr($arr_data[0], -3);
    $code_pf = substr($arr_data[0], 0, 4);
    $code = "";
    $debit_account = "";
    $court = "ศาลจังหวัดกาญจนบุรี";
    $amount = "";
    $date_now = "";
    
    if ($version === "130" && $code_pf === "HDCT") {
        $code = $arr_data[0];
        $debit_account = $arr_data[2];
        $court = $arr_data[5];        
        $amount = $arr_data[3];
        $amount = intval($amount);
        $amount = number_format($amount / 100, 0, '.', ',');
        $date_now = $arr_data[4];
        $date = DateTime::createFromFormat('ymd', $date_now);
        $date_now = $date->format('Y-m-d');

    } elseif ($version === '130' && $code_pf === "HMCL") {
        $code = $arr_data[0];
        $amount = $arr_data[2];
        $amount = substr($amount, 18, 19);
        $amount = intval($amount);
        $amount = number_format($amount, 0, '.', ',');
        $date_now = $arr_data[1];
        $date = DateTime::createFromFormat('d-m-Y', $date_now);
        $date_now = $date->format('Y-m-d');

    } elseif ($version === '129' && $code_pf === "HMCL") {
        $code = $arr_data[0];
        $amount = $arr_data[2];
        $amount = substr($amount, 18, 19);
        $amount = intval($amount);
        $amount = number_format($amount, 0, '.', ',');
        $date_now = $arr_data[1];
        $date = DateTime::createFromFormat('d-m-Y', $date_now);
        $date_now = $date->format('Y-m-d');
    }

    return [
        "status" => "success",
        "version" => $version,
        'code_pf' => $code_pf,
        'code' => $code,
        'debit_account' => $debit_account,
        'amount' => $amount,
        'date_now' => $date_now,
        'court' => $court,
    ];    
}

function getDatas($arr_lines, $header) {
    $code_pf = $header["code_pf"];
    $version = $header["version"];
    $codeH = $header["code"];
    $code = "";
    $account = "";
    $amount = "";
    $date_now = $header["date_now"];
    $vendor_name = "";
    $effective_date = $header["date_now"];
    $bene_ref = "";
    $personal_id = "";

    if ($version === "130" && $code_pf === "HDCT") {
        $code = $arr_lines[0];
        $account = $arr_lines[1];
        $amount = $arr_lines[2];
        $amount = intval($amount);
        $amount = number_format($amount / 100, 0, '.', ',');
        $date_now = $arr_lines[3];
        $date = DateTime::createFromFormat('ymd', $date_now);
        $date_now = $date->format('Y-m-d');
        $vendor_name = $arr_lines[4] . ' ' . $arr_lines[5];
        $effective_date = $arr_lines[6];
        $effective_date = substr($effective_date, 0, 6);
        $effective_date = DateTime::createFromFormat('ymd', $effective_date);
        $effective_date = $effective_date->format('Y-m-d');
        $bene_ref = $arr_lines[6];
        $bene_ref = substr($bene_ref, 9);
        $personal_id = substr($arr_lines[8], 0, 13);        

    } else if ($version === "130" && $code_pf === "HMCL") {
        $code = $arr_lines[0];
        for ($i = 7; $i < count($arr_lines); $i++) {
            if (strlen($arr_lines[$i]) === 30) {
                $account = $arr_lines[$i];    
                $bene_ref = $arr_lines[$i];    
            }    
            if (strlen($arr_lines[$i]) === 20) {
                $personal_id = $arr_lines[$i]; 
            }    
        }
        $account = substr($account, 10, 10);
        $bene_ref = substr($bene_ref, 20);
        $amount = $arr_lines[1];
        $amount = substr($amount, 0, 13);
        $amount = intval($amount);
        $amount = number_format($amount, 0, '.', ',');
        $vendor_name = substr($arr_lines[1], 13) . ' ' . $arr_lines[2];
        $personal_id = substr($personal_id, 0, 13);

    } else if ($version === "129" && $code_pf === "HMCL") {
        $code = $arr_lines[0];
        for ($i = 7; $i < count($arr_lines); $i++) {
            if (strlen($arr_lines[$i]) === 30) {
                $account = $arr_lines[$i];    
                $bene_ref = $arr_lines[$i];    
            }    
            if (strlen($arr_lines[$i]) === 20 || strlen($arr_lines[$i]) === 16) {
                $personal_id = $arr_lines[$i]; 
            }    
        }
        $account = substr($account, 10, 10);
        $bene_ref = substr($bene_ref, 20);
        $amount = $arr_lines[1];
        $amount = substr($amount, 0, 13);
        $amount = intval($amount);
        $amount = number_format($amount, 0, '.', ',');
        $vendor_name = substr($arr_lines[1], 13) . ' ' . $arr_lines[2];
        $personal_id = substr($personal_id, 0, 13);
    }

    return [
        "code_pf" => $code_pf,
        "version" => $version,
        "codeH" => $codeH,
        "code" => $code,
        "account" => $account,
        "amount" => $amount,
        "date_now" => $date_now,
        "vendor_name" => $vendor_name,
        "effective_date" => $effective_date,
        "bene_ref" => $bene_ref,
        "personal_id" => $personal_id,
        "act" => "insert"
        // "datas" => $arr_lines,
    ];
}
?>
