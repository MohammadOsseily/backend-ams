<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require '../../config/db.php';

$data = json_decode(file_get_contents("php://input"));

if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($data->user_id) && isset($data->flight_id)) {
    $user_id = $data->user_id;
    $flight_id = $data->flight_id;
    $booking_date = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("SELECT departure_time, capacity FROM flights WHERE id = ?");
    $stmt->bind_param("i", $flight_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $flight = $result->fetch_assoc();

    if (new DateTime($flight['departure_time']) < new DateTime()) {
        echo json_encode(["status" => "error", "message" => "Cannot book a flight that has already departed"]);
        exit();
    }

    if ($flight['capacity'] <= 0) {
        echo json_encode(["status" => "error", "message" => "No available seats for this flight"]);
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM bookings WHERE user_id = ? AND flight_id = ?");
    $stmt->bind_param("ii", $user_id, $flight_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "User has already booked this flight"]);
        exit();
    }

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("INSERT INTO bookings (user_id, flight_id, status, booking_date) VALUES (?, ?, 'booked', ?)");
        $stmt->bind_param("iis", $user_id, $flight_id, $booking_date);
        $stmt->execute();

        $stmt = $conn->prepare("UPDATE flights SET capacity = capacity - 1 WHERE id = ?");
        $stmt->bind_param("i", $flight_id);
        $stmt->execute();

        $conn->commit();
        echo json_encode(["status" => "success", "message" => "Flight booked successfully"]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(["status" => "error", "message" => $stmt->error]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method or missing parameters"]);
}

$conn->close();
?>
