<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$servername = "localhost";
$username = "root";
$password = "";
$db_name = "flight_management_system";

$conn = new mysqli($servername, $username, $password, $db_name);

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $conn->prepare("SELECT name, price_per_night, available_rooms, city, address FROM hotels WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $hotel = $result->fetch_assoc();
        echo json_encode(["status" => "success", "data" => $hotel]);
    } else {
        echo json_encode(["status" => "error", "message" => "Hotel not found"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No hotel ID provided"]);
}

$conn->close();
?>
