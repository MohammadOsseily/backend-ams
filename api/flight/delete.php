<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "flight_management_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($data['id'])) {
    $id = $data['id'];

    // Validate id (ensure it's a positive integer)
    if (!filter_var($id, FILTER_VALIDATE_INT) || $id <= 0) {
        echo json_encode(["status" => "error", "message" => "Invalid flight ID"]);
        exit;
    }

    $sql = "DELETE FROM flights WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);

    try {
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            echo json_encode(["status" => "success", "message" => "Flight deleted"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Flight not found or already deleted"]);
        }
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => $stmt->error]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method or missing id"]);
}

$conn->close();
?>
