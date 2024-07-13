<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE, PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header('Content-Type: application/json');

require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == "GET" && isset($_GET['user_id']) && isset($_GET['flight_id'])) {
    $user_id = $_GET["user_id"];
    $flight_id = $_GET["flight_id"];

    $sql = "SELECT * FROM bookings WHERE user_id = ? AND flight_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $user_id, $flight_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $booking = $result->fetch_assoc();
        echo json_encode(["data" => $booking, "status" => "success"]);
    } else {
        echo json_encode(["data" => null, "status" => "not_found"]);
    }
} else {
    echo json_encode(["error" => "Invalid request parameters", "status" => "error"]);
}

$conn->close();
?>
