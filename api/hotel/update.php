<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require '../../config/db.php';

$data = json_decode(file_get_contents("php://input"));

if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($data->id)) {
    $id = $data->id;
    $name = $data->name;
    $price_per_night = $data->price_per_night;
    $available_rooms = $data->available_rooms;
    $city = $data->city;
    $address = $data->address;

    // Validate required fields
if (!$name || !$city || !$address || !$price_per_night || !$available_rooms) {
    echo json_encode(["error" => "Please fill out all required fields"]);
    exit;
}

// Validate numeric fields
if (!is_numeric($price_per_night) || $price_per_night <= 0 || !is_numeric($available_rooms) || $available_rooms <= 0) {
    echo json_encode(["error" => "Price per Night and Available Rooms must be positive numeric values"]);
    exit;
}

    // Update hotel in the database
    $sql = "
        UPDATE hotels
        SET
            name = ?,
            price_per_night = ?,
            available_rooms = ?,
            city = ?,
            address = ?
        WHERE
            id = ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssdssi', $name, $price_per_night, $available_rooms, $city, $address, $id);

    try {
        $stmt->execute();
        echo json_encode(["status" => "success", "message" => "Hotel updated"]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Failed to update hotel: " . $stmt->error]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method or missing hotel_id"]);
}

$conn->close();
?>
