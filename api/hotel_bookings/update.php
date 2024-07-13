<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$db_name = "flight_management_system";

// Establish database connection
$conn = new mysqli($servername, $username, $password, $db_name);

// Check for connection errors
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Get raw POST data and decode JSON
    $json_data = json_decode(file_get_contents("php://input"), true);

    // Extract data from JSON
    $id = isset($json_data["id"]) ? $json_data["id"] : null;
    $user_id = isset($json_data["user_id"]) ? $json_data["user_id"] : null;
    $hotel_id = isset($json_data["hotel_id"]) ? $json_data["hotel_id"] : null;
    $check_in_date = isset($json_data["check_in_date"]) ? $json_data["check_in_date"] : null;
    $check_out_date = isset($json_data["check_out_date"]) ? $json_data["check_out_date"] : null;
    $status = isset($json_data["status"]) ? $json_data["status"] : null;

    // Validate required fields
    if (!$id || !$user_id || !$hotel_id || !$check_in_date || !$check_out_date || !$status) {
        echo json_encode(["status" => "error", "message" => "All fields are required"]);
        exit;
    }

    // Validate check-out date is later than check-in date
    if (strtotime($check_in_date) >= strtotime($check_out_date)) {
        echo json_encode(["status" => "error", "message" => "Check-out date must be later than check-in date"]);
        exit;
    }

    // Check if the booking ID exists
    $stmt = $conn->prepare("SELECT * FROM hotel_bookings WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo json_encode(["status" => "error", "message" => "Booking with ID $id not found"]);
        exit;
    }

    // Update the booking in the database
    $stmt = $conn->prepare('UPDATE hotel_bookings SET user_id = ?, hotel_id = ?, check_in_date = ?, check_out_date = ?, status = ? WHERE id = ?');
    $stmt->bind_param('iisssi', $user_id, $hotel_id, $check_in_date, $check_out_date, $status, $id);

    // Execute the update statement
    try {
        $stmt->execute();
        echo json_encode(["status" => "success", "message" => "Booking updated successfully"]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Failed to update booking: " . $stmt->error]);
    }

    // Close the statement
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Wrong request method"]);
}

// Close the database connection
$conn->close();
?>
