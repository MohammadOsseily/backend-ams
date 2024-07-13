<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "flight_management_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to fetch all bookings and join with flights to get flight details
$sql = "
    SELECT b.*, f.flight_number, f.departure_time, f.arrival_time, 
           da.name AS departure_airport, aa.name AS arrival_airport
    FROM bookings b
    LEFT JOIN flights f ON b.flight_id = f.id
    LEFT JOIN airports da ON f.departure_airport_id = da.id
    LEFT JOIN airports aa ON f.arrival_airport_id = aa.id
    ORDER BY f.id ASC, b.booking_date ASC
";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $bookings = array();

    // Fetch and organize bookings by flight
    while ($row = $result->fetch_assoc()) {
        $flight_id = $row['flight_id'];

        // Initialize the flight array if it doesn't exist
        if (!isset($bookings[$flight_id])) {
            $bookings[$flight_id] = array(
                'flight_number' => $row['flight_number'],
                'departure_time' => $row['departure_time'],
                'arrival_time' => $row['arrival_time'],
                'departure_airport' => $row['departure_airport'],
                'arrival_airport' => $row['arrival_airport'],
                'bookings' => array()
            );
        }

        // Add booking details to the corresponding flight
        $bookings[$flight_id]['bookings'][] = array(
            'id' => $row['id'],
            'user_id' => $row['user_id'],
            'status' => $row['status'],
            'booking_date' => $row['booking_date']
        );
    }

    // Format the output as required
    $formatted_bookings = array();
    foreach ($bookings as $flight_id => $flight_details) {
        $formatted_bookings[] = array(
            'flight_id' => $flight_id,
            'flight_details' => $flight_details['flight_number'] . ' - ' . 
                                $flight_details['departure_airport'] . ' to ' . 
                                $flight_details['arrival_airport'] . 
                                ' (' . $flight_details['departure_time'] . ' to ' . 
                                $flight_details['arrival_time'] . ')',
            'bookings' => $flight_details['bookings']
        );
    }

    echo json_encode(array("status" => "success", "data" => $formatted_bookings));
} else {
    echo json_encode(array("status" => "success", "data" => [], "message" => "No bookings found"));
}

$conn->close();
?>
