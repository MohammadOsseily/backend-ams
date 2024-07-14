<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require '../../../config/db.php';

function validateInput($data) {
    $errors = [];

    if (empty($data["username"])) {
        $errors[] = "Username is required";
    }

    if (empty($data["password"])) {
        $errors[] = "Password is required";
    }

    return $errors;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    // Validate input
    $errors = validateInput($data);
    if (empty($errors)) {
        $username = $data["username"];
        $password = $data["password"];

        // Prepare and execute
        $stmt = $conn->prepare('SELECT id, password, email, first_name, last_name, role FROM users WHERE username = ?');
        if ($stmt) {
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($id, $hashed_password, $email, $first_name, $last_name, $role);

            if ($stmt->num_rows > 0) {
                $stmt->fetch();
                if (password_verify($password, $hashed_password)) {
                    echo json_encode([
                        "status" => "success",
                        "message" => "Login successful",
                        "data" => [
                            "id" => $id,
                            "username" => $username,
                            "email" => $email,
                            "first_name" => $first_name,
                            "last_name" => $last_name,
                            "role" => $role
                        ]
                    ]);
                } else {
                    echo json_encode(["status" => "error", "message" => "Invalid password"]);
                }
            } else {
                echo json_encode(["status" => "error", "message" => "User not found"]);
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