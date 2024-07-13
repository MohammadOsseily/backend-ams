<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$db_name = "flight_management_system";

$conn = new mysqli($servername, $username, $password, $db_name);

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

if ($_SERVER['REQUEST_METHOD'] == "GET" && isset($_GET['id'])) {
    $id = $_GET["id"];

    // Prepare and execute the SQL query
    $stmt = $conn->prepare("SELECT * FROM hotel_bookings WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $booking = $result->fetch_assoc();
        echo json_encode(["data" => $booking, "status" => "success"]);
    } else {
        echo json_encode(["data" => null, "status" => "not_found"]);
    }
} else {
    echo json_encode(["error" => "Invalid request parameters", "status" => "error"]);
}

$conn->close();
?>
