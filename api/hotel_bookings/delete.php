<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$servername = "localhost";
$username = "root";
$password = "";
$db_name = "airplane_db";

$conn = new mysqli($servername, $username, $password, $db_name);

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $user_id = $_POST["user_id"];
    $hotel_id = $_POST["hotel_id"];
    
    $stmt = $conn->prepare('DELETE FROM hotel_bookings WHERE user_id = ? AND hotel_id = ?');
    $stmt->bind_param('ii', $user_id, $hotel_id);

    try {
        $stmt->execute();
               // Update available rooms
               $stmt = $conn->prepare("UPDATE hotels SET available_rooms = available_rooms + 1 WHERE hotel_id = ?");
               $stmt->bind_param("i", $hotel_id);
               $stmt->execute();
       
               echo json_encode(["message" => "Booking cancelled successfully", "status" => "success"]);
           } catch (Exception $e) {
               echo json_encode(["error" => $stmt->error]);
           }
       } else {
           echo json_encode(["error" => "Wrong request method"]);
       }
       
       $conn->close();
       ?>
       