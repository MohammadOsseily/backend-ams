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
        $stmt = $conn->prepare('SELECT id, name, location FROM airports WHERE id = ?');
        if ($stmt) {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($id, $name, $location);

            if ($stmt->num_rows > 0) {
                $stmt->fetch();
                $airport = [
                    "id" => $id,
                    "name" => $name,
                    "location" => $location
                ];
                echo json_encode(["status" => "success", "data" => $airport]);
            } else {
                echo json_encode(["status" => "error", "message" => "Airport not found"]);
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