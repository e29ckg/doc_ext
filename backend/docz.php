<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

date_default_timezone_set("Asia/Bangkok");

include_once './config/database.php';
include_once './objects/docz.php';

$database = new Database();
$db = $database->getConnection();

$docz = new Docz($db);

$request_method = $_SERVER["REQUEST_METHOD"];
switch ($request_method) {
    case 'GET':

        // Pagination parameters
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 25;
        $search = isset($_GET['search_text']) ? $_GET['search_text'] : '';


        // Calculate offset
        $offset = ($page - 1) * $per_page;

        $stmt = $docz->readPaginated($offset, $per_page, $search);
        $num = $stmt->rowCount();

        if ($num > 0) {
            $doczs_arr = array();
            $doczs_arr["records"] = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);

                $docz_item = array(
                    "id" => $id,
                    "name" => $name,
                    "file" => $file,
                    "r_number" => $r_number,
                    "r_date" => $r_date,
                    "doc_speed" => $doc_speed,
                    "doc_form_number" => $doc_form_number,
                    "doc_date" => $doc_date,
                    "doc_form" => $doc_form,
                    "doc_to" => $doc_to,
                    "created" => $created
                );

                array_push($doczs_arr["records"], $docz_item);
            }
            // Get total rows for pagination
            $total_rows = $docz->countAll($search);
            $total_pages = ceil($total_rows / $per_page);

            $doczs_arr["pagination"] = array(
                "total" => $total_rows,
                "per_page" => $per_page,
                "current_page" => $page,
                "total_pages" => $total_pages,
                "next_page" => $page < $total_pages ? $page + 1 : null,
                "prev_page" => $page > 1 ? $page - 1 : null
            );


            http_response_code(200);
            echo json_encode($doczs_arr);
        } else {
            http_response_code(404);
            echo json_encode(array("status" => "error", "message" => "No doczs found."));
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