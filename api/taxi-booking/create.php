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

    if (empty($data["user_id"])) {
        $errors[] = "User ID is required";
    } elseif (!is_numeric($data["user_id"])) {
        $errors[] = "User ID must be a number";
    }

    if (empty($data["taxi_id"])) {
        $errors[] = "Taxi ID is required";
    } elseif (!is_numeric($data["taxi_id"])) {
        $errors[] = "Taxi ID must be a number";
    }

    if (empty($data["pick_up_location"])) {
        $errors[] = "Pick-up location is required";
    }

    if (empty($data["drop_off_location"])) {
        $errors[] = "Drop-off location is required";
    }

    if (empty($data["pick_up_time"])) {
        $errors[] = "Pick-up time is required";
    } elseif (!strtotime($data["pick_up_time"])) {
        $errors[] = "Pick-up time must be a valid datetime";
    }

    return $errors;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    // Validate input
    $errors = validateInput($data);
    if (empty($errors)) {
        $user_id = $data["user_id"];
        $taxi_id = $data["taxi_id"];
        $pick_up_location = $data["pick_up_location"];
        $drop_off_location = $data["drop_off_location"];
        $pick_up_time = $data["pick_up_time"];

        // Check if there is already a booking for the same user at the same pick_up_time
        $check_stmt = $conn->prepare('SELECT id FROM taxi_bookings WHERE user_id = ? AND pick_up_time = ?');
        $check_stmt->bind_param('is', $user_id, $pick_up_time);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            echo json_encode(["status" => "error", "message" => "User already has a booking at the same pick-up time"]);
        } else {
            // Prepare and bind
            $stmt = $conn->prepare('INSERT INTO taxi_bookings (user_id, taxi_id, pick_up_location, drop_off_location, pick_up_time) VALUES (?, ?, ?, ?, ?)');
            if ($stmt) {
                $stmt->bind_param('iisss', $user_id, $taxi_id, $pick_up_location, $drop_off_location, $pick_up_time);

                // Execute statement
                if ($stmt->execute()) {
                    echo json_encode(["status" => "success", "message" => "Taxi booking created successfully"]);
                } else {
                    echo json_encode(["status" => "error", "message" => $stmt->error]);
                }

                // Close statement
                $stmt->close();
            } else {
                echo json_encode(["status" => "error", "message" => $conn->error]);
            }
        }

        // Close check statement
        $check_stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid input", "errors" => $errors]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}

// Close connection
$conn->close();
