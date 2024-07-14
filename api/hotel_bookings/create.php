<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(204);
    exit();
}

require '../../config/db.php';

$data = json_decode(file_get_contents("php://input"));

if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($data->user_id) && isset($data->hotel_id) && isset($data->check_in_date) && isset($data->check_out_date)) {
    $user_id = $data->user_id;
    $hotel_id = $data->hotel_id;
    $check_in_date = $data->check_in_date;
    $check_out_date = $data->check_out_date;
    $booking_date = date('Y-m-d H:i:s');

    if (new DateTime($check_in_date) < new DateTime()) {
        echo json_encode(["status" => "error", "message" => "Check-in date cannot be in the past"]);
        exit();
    }

    if (new DateTime($check_out_date) <= new DateTime($check_in_date)) {
        echo json_encode(["status" => "error", "message" => "Check-out date must be later than check-in date"]);
        exit();
    }

    $stmt = $conn->prepare("SELECT available_rooms FROM hotels WHERE id = ?");
    $stmt->bind_param("i", $hotel_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $hotel = $result->fetch_assoc();

    if (!$hotel || $hotel['available_rooms'] <= 0) {
        echo json_encode(["status" => "error", "message" => "No available rooms for this hotel"]);
        exit();
    }

    // Check for overlapping bookings
    $stmt = $conn->prepare("
        SELECT * FROM hotel_bookings 
        WHERE hotel_id = ? 
          AND user_id = ?
          AND ((check_in_date <= ? AND check_out_date >= ?) 
           OR (check_in_date <= ? AND check_out_date >= ?)
           OR (check_in_date >= ? AND check_out_date <= ?))
    ");
    $stmt->bind_param("iissssss", $hotel_id, $user_id, $check_in_date, $check_in_date, $check_out_date, $check_out_date, $check_in_date, $check_out_date);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "User has an overlapping booking for these dates"]);
        exit();
    }

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("INSERT INTO hotel_bookings (user_id, hotel_id, check_in_date, check_out_date, booking_date, status) VALUES (?, ?, ?, ?, ?, 'booked')");
        $stmt->bind_param("iisss", $user_id, $hotel_id, $check_in_date, $check_out_date, $booking_date);
        $stmt->execute();

        if ($stmt->error) {
            throw new Exception($stmt->error);
        }

        $stmt = $conn->prepare("UPDATE hotels SET available_rooms = available_rooms - 1 WHERE id = ?");
        $stmt->bind_param("i", $hotel_id);
        $stmt->execute();

        if ($stmt->error) {
            throw new Exception($stmt->error);
        }

        $conn->commit();
        echo json_encode(["status" => "success", "message" => "Hotel booked successfully"]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method or missing parameters"]);
}

$conn->close();
?>
