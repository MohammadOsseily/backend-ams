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

    if (empty($data["username"])) {
        $errors[] = "Username is required";
    }

    if (empty($data["password"])) {
        $errors[] = "Password is required";
    }

    if (empty($data["email"])) {
        $errors[] = "Email is required";
    } elseif (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    if (empty($data["first_name"])) {
        $errors[] = "First name is required";
    }

    if (empty($data["last_name"])) {
        $errors[] = "Last name is required";
    }

    if (empty($data["role"])) {
        $errors[] = "Role is required";
    }

    return $errors;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    // Validate input
    $errors = validateInput($data);
    if (empty($errors)) {
        $username = $data["username"];
        $password = password_hash($data["password"], PASSWORD_BCRYPT); // Hash the password
        $email = $data["email"];
        $first_name = $data["first_name"];
        $last_name = $data["last_name"];
        $role = $data["role"];

        // Prepare and bind
        $stmt = $conn->prepare('INSERT INTO users (username, password, email, first_name, last_name, role) VALUES (?, ?, ?, ?, ?, ?)');
        if ($stmt) {
            $stmt->bind_param('ssssss', $username, $password, $email, $first_name, $last_name, $role);

            // Execute statement
            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "User created successfully"]);
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