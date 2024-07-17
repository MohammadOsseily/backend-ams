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
    $stmt = $conn->prepare('SELECT id, name, location FROM airports');
    if ($stmt) {
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($id, $name, $location);

        $airports = [];
        while ($stmt->fetch()) {
            $airports[] = [
                "id" => $id,
                "name" => $name,
                "location" => $location
            ];
        }

        echo json_encode(["status" => "success", "data" => $airports]);

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