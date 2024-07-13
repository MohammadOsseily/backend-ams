<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Include database and object files
include_once '../config/database.php';
include_once '../objects/hotel_booking.php';

// Instantiate database and hotel booking object
$database = new Database();
$db = $database->getConnection();

// Initialize object
$hotel_booking = new HotelBooking($db);

// Get hotel_booking_id from URL parameter
$hotel_booking->hotel_booking_id = isset($_GET['hotel_booking_id']) ? $_GET['hotel_booking_id'] : die();

// Read the details of the hotel booking
$hotel_booking->readOne();

if ($hotel_booking->hotel_booking_id != null) {
    // Create array
    $hotel_booking_arr = array(
        "hotel_booking_id" => $hotel_booking->hotel_booking_id,
        "user_id" => $hotel_booking->user_id,
        "hotel_id" => $hotel_booking->hotel_id,
        "check_in_date" => $hotel_booking->check_in_date,
        "check_out_date" => $hotel_booking->check_out_date,
        "booking_date" => $hotel_booking->booking_date,
        "status" => $hotel_booking->status
    );

    // Set response code - 200 OK
    http_response_code(200);

    // Make it json format
    echo json_encode(array("status" => "success", "data" => $hotel_booking_arr));
} else {
    // Set response code - 404 Not found
    http_response_code(404);

    // Tell the user hotel booking does not exist
    echo json_encode(array("status" => "error", "message" => "Hotel booking does not exist."));
}
?>
