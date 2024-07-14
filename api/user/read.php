<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Prepare and execute
    $stmt = $conn->prepare('SELECT id, username, email, first_name, last_name, role FROM users');
    if ($stmt) {
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($id, $username, $email, $first_name, $last_name, $role);

        $users = [];
        while ($stmt->fetch()) {
            $users[] = [
                "id" => $id,
                "username" => $username,
                "email" => $email,
                "first_name" => $first_name,
                "last_name" => $last_name,
                "role" => $role
            ];
        }

        echo json_encode(["status" => "success", "data" => $users]);

        // Close statement
        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}

// Close connection
$conn->close();