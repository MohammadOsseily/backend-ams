<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require '../../config/db.php';

$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : die();

$query = "
    SELECT
        b.id,
        b.flight_id,
        b.status,
        b.booking_date
    FROM
        bookings b
    WHERE
        b.user_id = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    $bookings = array();

    while ($row = $result->fetch_assoc()) {
        $booking_item = array(
            "id" => $row['id'],
            "flight_id" => $row['flight_id'],
            "status" => $row['status'],
            "booking_date" => $row['booking_date']
        );
        array_push($bookings, $booking_item);
    }

    http_response_code(200);
    echo json_encode(array("bookings" => $bookings));
} else {
    http_response_code(404);
    echo json_encode(array("message" => "No bookings found."));
}

$stmt->close();
$conn->close();
?>
