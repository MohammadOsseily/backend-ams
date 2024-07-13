<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$servername = "localhost";
$username = "root";
$password = "";
$db_name = "flight_management_system";

$conn = new mysqli($servername, $username, $password, $db_name);

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $hotel_id = $_POST["hotel_id"];

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Delete related bookings first
        $stmt = $conn->prepare("DELETE FROM hotel_bookings WHERE hotel_id = ?");
        $stmt->bind_param("i", $hotel_id);
        if (!$stmt->execute()) {
            throw new Exception("Failed to delete related bookings: " . $stmt->error);
        }

        // Delete the hotel
        $stmt = $conn->prepare("DELETE FROM hotels WHERE hotel_id = ?");
        $stmt->bind_param("i", $hotel_id);
        if (!$stmt->execute()) {
            throw new Exception("Failed to delete hotel: " . $stmt->error);
        }

        // Commit the transaction
        $conn->commit();
        
        echo json_encode(["status" => "success", "message" => "Hotel deleted successfully"]);
    } catch (Exception $e) {
        // Rollback the transaction if there was an error
        $conn->rollback();
        echo json_encode(["status" => "error", "message" => "Failed to delete hotel. " . $e->getMessage()]);
    } finally {
        $stmt->close();
    }
} else {
    echo json_encode(["status" => "error", "message" => "Wrong request method"]);
}

$conn->close();
?>
