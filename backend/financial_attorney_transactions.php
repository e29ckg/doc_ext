<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(204);
    exit();
}

date_default_timezone_set("Asia/Bangkok");

include_once './config/database.php';
include_once './objects/financial_attorney_transactions.php';
// include_once './auth/authentication.php';

$database = new Database();
$db = $database->getConnection();

$transaction = new FinancialAttorneyTransactions($db);

$request_method = $_SERVER["REQUEST_METHOD"];
switch ($request_method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $transaction->id = intval($_GET['id']);
            $transaction->readOne();
            if ($transaction->code_pf !== null) {
                $transaction_arr = array(
                    "id" => $transaction->id,
                    "code_pf" => $transaction->code_pf,
                    "version" => $transaction->version,
                    "codeH" => $transaction->codeH,
                    "code" => $transaction->code,
                    "account" => $transaction->account,
                    "amount" => $transaction->amount,
                    "date_now" => $transaction->date_now,
                    "vendor_name" => $transaction->vendor_name,
                    "effective_date" => $transaction->effective_date,
                    "bene_ref" => $transaction->bene_ref,
                    "personal_id" => $transaction->personal_id,
                    "created" => $transaction->created,
                    "updated" => $transaction->updated,
                    "edit" => false
                );
                http_response_code(200);
                echo json_encode($transaction_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("status" => "error", "message" => "Transaction not found."));
            }
        } else {
            // Pagination parameters
            $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
            $per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 10;
            $search = isset($_GET['search']) ? $_GET['search'] : '';

            // Calculate offset
            $offset = ($page - 1) * $per_page;

            $stmt = $transaction->readPaginated($offset, $per_page, $search);
            $num = $stmt->rowCount();

            if ($num > 0) {
                $transactions_arr = array();
                $transactions_arr["records"] = array();

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);

                    $transaction_item = array(
                        "id" => $id,
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
                        "created" => $created,
                        "updated" => $updated,
                        "edit" => false
                    );

                    array_push($transactions_arr["records"], $transaction_item);
                }

                // Get total rows for pagination
                $total_rows = $transaction->countAll($search);
                $total_pages = ceil($total_rows / $per_page);

                $transactions_arr["pagination"] = array(
                    "total" => $total_rows,
                    "per_page" => $per_page,
                    "current_page" => $page,
                    "total_pages" => $total_pages,
                    "next_page" => $page < $total_pages ? $page + 1 : null,
                    "prev_page" => $page > 1 ? $page - 1 : null
                );

                http_response_code(200);
                echo json_encode($transactions_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("status" => "success", "message" => "No transactions found."));
            }
        }
        break;

    case 'POST':
        // ตรวจสอบการรับรองตัวตนก่อนดำเนินการ
        // if (!authenticate()) {
        //     http_response_code(401);
        //     echo json_encode(array("message" => "Unauthorized access."));
        //     exit();
        // }
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data)) {
            // สร้างธุรกรรมสำหรับแต่ละรายการ
            $created_ids = [];
            foreach ($data as $item) {
                if (
                    !empty($item->code_pf) &&
                    !empty($item->version) &&
                    !empty($item->codeH) &&
                    !empty($item->code) &&
                    !empty($item->account) &&
                    !empty($item->amount) &&
                    !empty($item->date_now) &&
                    !empty($item->vendor_name) &&
                    !empty($item->effective_date) &&
                    !empty($item->bene_ref) &&
                    !empty($item->personal_id)
                ) {
                    $transaction->code_pf = $item->code_pf;
                    $transaction->version = $item->version;
                    $transaction->codeH = $item->codeH;
                    $transaction->code = $item->code;
                    $transaction->account = $item->account;
                    $transaction->amount = $item->amount;
                    $transaction->date_now = $item->date_now;
                    $transaction->vendor_name = $item->vendor_name;
                    $transaction->effective_date = $item->effective_date;
                    $transaction->bene_ref = $item->bene_ref;
                    $transaction->personal_id = $item->personal_id;

                    if ($transaction->create()) {
                        // เก็บ ID ที่สร้างล่าสุด
                        $created_ids[] = $transaction->id;
                    }
                }
            }

            if (!empty($created_ids)) {
                // ส่งข้อความยืนยันพร้อมกับ ID ล่าสุดที่สร้างขึ้น
                http_response_code(201);
                echo json_encode(array("status" => "success", "message" => "Transactions were created.", "last_inserted_ids" => $created_ids));
            } else {
                http_response_code(503);
                echo json_encode(array("status" => "error", "message" => "Unable to create transactions."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("status" => "error", "message" => "Unable to create transactions. No data received."));
        }
        break;


    case 'PUT':
        // ตรวจสอบการรับรองตัวตนก่อนดำเนินการ
        // if (!authenticate()) {
        //     http_response_code(401);
        //     echo json_encode(array("message" => "Unauthorized access."));
        //     exit();
        // }

        $data = json_decode(file_get_contents("php://input"));

        if (
            isset($_GET['id']) &&
            !empty($data->code_pf) &&
            !empty($data->version) &&
            !empty($data->codeH) &&
            !empty($data->code) &&
            !empty($data->account) &&
            !empty($data->amount) &&
            !empty($data->date_now) &&
            !empty($data->vendor_name) &&
            !empty($data->effective_date) &&
            !empty($data->bene_ref) &&
            !empty($data->personal_id)
        ) {
            $transaction->id = $_GET['id'];
            $transaction->code_pf = $data->code_pf;
            $transaction->version = $data->version;
            $transaction->codeH = $data->codeH;
            $transaction->code = $data->code;
            $transaction->account = $data->account;
            $transaction->amount = $data->amount;
            $transaction->date_now = $data->date_now;
            $transaction->vendor_name = $data->vendor_name;
            $transaction->effective_date = $data->effective_date;
            $transaction->bene_ref = $data->bene_ref;
            $transaction->personal_id = $data->personal_id;

            if ($transaction->update()) {
                http_response_code(200);
                echo json_encode(array("status" => "success", "message" => "Transaction was updated.", "data" => $data));
            } else {
                http_response_code(503);
                echo json_encode(array("status" => "error", "message" => "Unable to update transaction."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("status" => "error", "message" => "Unable to update transaction. Data is incomplete."));
        }
        break;

    case 'DELETE':
        // ตรวจสอบการรับรองตัวตนก่อนดำเนินการ
        //  if (!authenticate()) {
        //     http_response_code(401);
        //     echo json_encode(array("message" => "Unauthorized access."));
        //     exit();
        // }
        if (isset($_GET['id'])) {
            $transaction->id = intval($_GET['id']);
            if ($transaction->delete()) {
                http_response_code(200);
                echo json_encode(array("status" => "success", "message" => "Transaction was deleted."));
            } else {
                http_response_code(503);
                echo json_encode(array("status" => "error", "message" => "Unable to delete transaction."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("status" => "error", "message" => "Unable to delete transaction. ID is missing."));
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(array("status" => "error", "message" => "Method not allowed."));
        break;
}
?>