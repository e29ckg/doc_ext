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
include_once './objects/users.php';
// include_once './auth/authentication.php';

$database = new Database();
$db = $database->getConnection();

$Users = new Users($db);

$request_method = $_SERVER["REQUEST_METHOD"];
switch ($request_method) {
    case 'GET':

        if (isset($_GET['id'])) {
            $Users->id = intval($_GET['id']);
            $Users->readOne();
            if ($Users->id !== null) {
                $Users_arr = array(
                    "id" => $Users->id,
                    "uid" => $Users->uid,
                    "card_id" => $Users->card_id
                );
                http_response_code(200);
                echo json_encode($Users_arr);
                exit;
            } else {
                http_response_code(404);
                echo json_encode(array("status" => "error", "message" => "Users not found."));
                exit;
            }
        }

        if (isset($_GET['card_id'])) {
            $Users->card_id = $_GET['card_id'];
            $Users->readOneCardId();
            if ($Users->id !== null) {
                $Users_arr = array(
                    "id" => $Users->id,
                    "uid" => $Users->uid,
                    "card_id" => $Users->card_id,
                    "created" => $Users->created,
                    "updated" => $Users->updated
                );
                http_response_code(200);
                echo json_encode($Users_arr);
                exit;
            } else {
                http_response_code(404);
                echo json_encode(array("status" => "error", "message" => "Users not found."));
                exit;
            }
        }

        if (isset($_GET['uid'])) {
            $Users->uid = $_GET['uid'];
            $Users->read_uid();
            if ($Users->id !== null) {
                $Users_arr = array(
                    "id" => $Users->id,
                    "uid" => $Users->uid,
                    "card_id" => $Users->card_id
                );
                http_response_code(200);
                echo json_encode($Users_arr);
                exit;
            } else {
                http_response_code(404);
                echo json_encode(array("status" => "error", "message" => "Users not found."));
                exit;
            }
        }

        // $Users = $Users->readAll();
        // http_response_code(200);
        // echo json_encode(array("status" => "success", "users" => $Users));

        http_response_code(404);
        echo json_encode(array("status" => "error", "message" => "Users not found."));

        break;

    case 'POST':

        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data)) {

            if (!empty($data->uid)) {
                $Users->uid = $data->uid;

                if ($Users->create_uid()) {
                    http_response_code(201);
                    echo json_encode(array("status" => "success", "message" => "Users were created.", "id" => $Users->id));
                    exit;
                } else {
                    http_response_code(503);
                    echo json_encode(array("status" => "error", "message" => "Unable to create Users."));
                    exit;
                }
            }

        } else {
            http_response_code(400);
            echo json_encode(array("status" => "error", "message" => "Unable to create Users. No data received."));
            exit;
        }
        break;


    case 'PUT':

        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data)) {
            if (
                // !empty($data->id) &&
                !empty($data->uid) &&
                !empty($data->card_id)
            ) {
                // $Users->id = $data->id;
                $Users->uid = $data->uid;
                $Users->card_id = $data->card_id;

                if ($Users->update()) {
                    http_response_code(200);
                    echo json_encode(array("status" => "success", "message" => "Users was updated.", "data" => $data));
                } else {
                    http_response_code(503);
                    echo json_encode(array("status" => "error", "message" => "Unable to update Users."));
                }
            } else {
                http_response_code(400);
                echo json_encode(array("status" => "error", "message" => "Unable to update Users. Data is incomplete."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("status" => "error", "message" => "Unable to create Users. No data received."));
            exit;
        }
        break;

    case 'DELETE':

        $data = json_decode(file_get_contents("php://input"));

        if (isset($data->id)) {
            $Users->id = intval($data->id);
            if ($Users->delete()) {
                http_response_code(200);
                echo json_encode(array("status" => "success", "message" => "Users was deleted."));
                exit;
            } else {
                http_response_code(503);
                echo json_encode(array("status" => "error", "message" => "Unable to delete Users."));
                exit;
            }
        } else {
            http_response_code(400);
            echo json_encode(array("status" => "error", "message" => "Unable to delete Users. ID is missing."));
            // exit;
        }
        // if (isset($_GET['id'])) {
        //     $Users->id = intval($_GET['id']);
        //     if ($Users->delete()) {
        //         http_response_code(200);
        //         echo json_encode(array("status"=>"success","message" => "Users was deleted."));
        //         exit;
        //     } else {
        //         http_response_code(503);
        //         echo json_encode(array("status"=>"error","message" => "Unable to delete Users."));
        //         exit;
        //     }
        // } else {
        //     http_response_code(400);
        //     echo json_encode(array("status"=>"error","message" => "Unable to delete Users. ID is missing."));
        //     // exit;
        // }

        break;

    default:
        http_response_code(405);
        echo json_encode(array("status" => "error", "message" => "Method not allowed."));
        // exit;
        break;
}
