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
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    $user_id = $data["user_id"];
    $hotel_id = $data["hotel_id"];
    $check_in_date = $data["check_in_date"];
    $check_out_date = $data["check_out_date"];
    $booking_date = date('Y-m-d H:i:s');

    // Backend Validation
    if (strtotime($check_in_date) >= strtotime($check_out_date)) {
        echo json_encode(["status" => "error", "error" => "Check-out date must be later than check-in date."]);
        exit();
    }

    // Check room availability
    $stmt = $conn->prepare("SELECT available_rooms FROM hotels WHERE id = ?");
    $stmt->bind_param("i", $hotel_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $hotel = $result->fetch_assoc();

    if ($hotel['available_rooms'] > 0) {
        // Check for overlapping bookings
        $stmt = $conn->prepare("SELECT * FROM hotel_bookings WHERE hotel_id = ? AND user_id = ? AND ((check_in_date BETWEEN ? AND ?) OR (check_out_date BETWEEN ? AND ?))");
        $stmt->bind_param("iissss", $hotel_id, $user_id, $check_in_date, $check_out_date, $check_in_date, $check_out_date);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            $stmt = $conn->prepare('INSERT INTO hotel_bookings (user_id, hotel_id, check_in_date, check_out_date, booking_date, status) VALUES (?, ?, ?, ?, ?, ?)');
            $status = 'booked';
            $stmt->bind_param('iissss', $user_id, $hotel_id, $check_in_date, $check_out_date, $booking_date, $status);

            try {
                $stmt->execute();
                // Update available rooms
                $stmt = $conn->prepare("UPDATE hotels SET available_rooms = available_rooms - 1 WHERE id = ?");
                $stmt->bind_param("i", $hotel_id);
                $stmt->execute();

                echo json_encode(["message" => "Hotel booked successfully", "status" => "success"]);
            } catch (Exception $e) {
                echo json_encode(["error" => $stmt->error]);
            }
        } else {
            echo json_encode(["error" => "Overlapping booking found"]);
        }
    } else {
        echo json_encode(["error" => "No rooms available"]);
    }
} else {
    echo json_encode(["error" => "Wrong request method"]);
}

$conn->close();
?>
