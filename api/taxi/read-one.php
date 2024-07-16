<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require '../../config/db.php';

function validateInput($data) {
    $errors = [];

    if (empty($data["id"])) {
        $errors[] = "ID is required";
    } elseif (!is_numeric($data["id"])) {
        $errors[] = "ID must be a number";
    }

    return $errors;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    // Validate input
    $errors = validateInput($data);
    if (empty($errors)) {
        $id = $data["id"];

        // Prepare and execute
        $stmt = $conn->prepare('SELECT id, company_name, city, phone_number, price_per_km FROM taxis WHERE id = ?');
        if ($stmt) {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($id, $company_name, $city, $phone_number, $price_per_km);

            if ($stmt->num_rows > 0) {
                $stmt->fetch();
                $taxi = [
                    "id" => $id,
                    "company_name" => $company_name,
                    "city" => $city,
                    "phone_number" => $phone_number,
                    "price_per_km" => $price_per_km
                ];
                echo json_encode(["status" => "success", "data" => $taxi]);
            } else {
                echo json_encode(["status" => "error", "message" => "Taxi not found"]);
            }

            // Close statement
            $stmt->close();
        } else {
            echo json_encode(["status" => "error", "message" => $conn->error]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid input", "errors" => $errors]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}

// Close connection
$conn->close();