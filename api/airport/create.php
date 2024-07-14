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

    if (empty($data["name"])) {
        $errors[] = "Name is required";
    }
    
    if (empty($data["location"])) {
        $errors[] = "Location is required";
    }

    return $errors;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    // Validate input
    $errors = validateInput($data);
    if (empty($errors)) {
        $name = $data["name"];
        $location = $data["location"];

        // Prepare and bind
        $stmt = $conn->prepare('INSERT INTO airports (name, location) VALUES (?, ?)');
        if ($stmt) {
            $stmt->bind_param('ss', $name, $location);

            // Execute statement
            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "Airport created successfully"]);
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