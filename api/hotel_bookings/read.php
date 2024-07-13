<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$db_name = "flight_management_system";

$conn = new mysqli($servername, $username, $password, $db_name);

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    // Prepare and execute the SQL query
    $sql = "SELECT hb.id as booking_id, hb.user_id, hb.hotel_id, hb.check_in_date, hb.check_out_date, hb.booking_date, hb.status, 
                   h.name as hotel_name, h.city, h.address, h.price_per_night, h.available_rooms 
            FROM hotel_bookings hb 
            JOIN hotels h ON hb.hotel_id = h.id 
            ORDER BY h.id, hb.booking_date";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $bookings = [];
        while ($row = $result->fetch_assoc()) {
            $hotel_id = $row['hotel_id'];
            if (!isset($bookings[$hotel_id])) {
                $bookings[$hotel_id] = [
                    'hotel' => [
                        'hotel_id' => $row['hotel_id'],
                        'hotel_name' => $row['hotel_name'],
                        'city' => $row['city'],
                        'address' => $row['address'],
                        'price_per_night' => $row['price_per_night'],
                        'available_rooms' => $row['available_rooms']
                    ],
                    'bookings' => []
                ];
            }
            $bookings[$hotel_id]['bookings'][] = [
                'booking_id' => $row['booking_id'],
                'user_id' => $row['user_id'],
                'check_in_date' => $row['check_in_date'],
                'check_out_date' => $row['check_out_date'],
                'booking_date' => $row['booking_date'],
                'status' => $row['status']
            ];
        }
        echo json_encode(["data" => array_values($bookings), "status" => "success"]);
    } else {
        echo json_encode(["data" => [], "status" => "not_found"]);
    }
} else {
    echo json_encode(["error" => "Invalid request method", "status" => "error"]);
}

$conn->close();
?>
