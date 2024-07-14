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
    
    if (empty($data["company_name"])) {
        $errors[] = "Company name is required";
    }
    
    if (empty($data["city"])) {
        $errors[] = "City is required";
    }
    
    if (empty($data["phone_number"])) {
        $errors[] = "Phone number is required";
    } elseif (!preg_match('/^[0-9-]+$/', $data["phone_number"])) {
        $errors[] = "Phone number can only contain digits and hyphens";
    }
    
    if (empty($data["price_per_km"])) {
        $errors[] = "Price per km is required";
    } elseif (!is_numeric($data["price_per_km"])) {
        $errors[] = "Price per km must be a number";
    }

    return $errors;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    // Validate input
    $errors = validateInput($data);
    if (empty($errors)) {
        $id = $data["id"];
        $company_name = $data["company_name"];
        $city = $data["city"];
        $phone_number = $data["phone_number"];
        $price_per_km = $data["price_per_km"];

        // Prepare and bind
        $stmt = $conn->prepare('UPDATE taxis SET company_name = ?, city = ?, phone_number = ?, price_per_km = ? WHERE id = ?');
        if ($stmt) {
            $stmt->bind_param('sssdi', $company_name, $city, $phone_number, $price_per_km, $id);

            // Execute statement
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo json_encode(["status" => "success", "message" => "Taxi updated successfully"]);
                } else {
                    echo json_encode(["status" => "error", "message" => "Taxi not found or no changes made"]);
                }
            } else {
                echo json_encode(["status" => "error", "message" => $stmt->error]);
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