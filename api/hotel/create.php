<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE, PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$db_name = "flight_management_system";

$conn = new mysqli($servername, $username, $password, $db_name);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Get the JSON data from the request body
$data = json_decode(file_get_contents("php://input"));

// Validate JSON data and extract fields
$name = isset($data->name) ? $data->name : null;
$city = isset($data->city) ? $data->city : null;
$address = isset($data->address) ? $data->address : null;
$price_per_night = isset($data->price_per_night) ? $data->price_per_night : null;
$available_rooms = isset($data->available_rooms) ? $data->available_rooms : null;

// Validate required fields
if (!$name || !$city || !$address || !$price_per_night || !$available_rooms) {
    echo json_encode(["error" => "Please fill out all required fields"]);
    exit;
}

// Validate numeric fields
if (!is_numeric($price_per_night) || !is_numeric($available_rooms)) {
    echo json_encode(["error" => "Price per Night and Available Rooms must be numeric values"]);
    exit;
}

// Prepare and execute your SQL insert query
$stmt = $conn->prepare('INSERT INTO hotels (name, city, address, price_per_night, available_rooms) VALUES (?, ?, ?, ?, ?)');
$stmt->bind_param('ssssi', $name, $city, $address, $price_per_night, $available_rooms);

try {
    $stmt->execute();
    echo json_encode(["message" => "Hotel created successfully", "status" => "success"]);
} catch (Exception $e) {
    echo json_encode(["error" => "Failed to create hotel: " . $stmt->error]);
}

$conn->close();
?>
