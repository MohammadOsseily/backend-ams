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
        $company_name = $data["company_name"];
        $city = $data["city"];
        $phone_number = $data["phone_number"];
        $price_per_km = $data["price_per_km"];

        // Prepare and bind
        $stmt = $conn->prepare('INSERT INTO taxis (company_name, city, phone_number, price_per_km) VALUES (?, ?, ?, ?)');
        if ($stmt) {
            $stmt->bind_param('sssd', $company_name, $city, $phone_number, $price_per_km);

            // Execute statement
            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "Taxi created successfully"]);
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

