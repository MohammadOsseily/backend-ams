<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE, PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

require '../../config/db.php';

$sql = "
    SELECT
        flights.id,
        flights.flight_number,
        flights.departure_airport_id,
        departure_airport.name AS departure_airport_name,
        flights.arrival_airport_id,
        arrival_airport.name AS arrival_airport_name,
        flights.departure_time,
        flights.arrival_time,
        flights.price,
        flights.capacity
    FROM flights
    JOIN airports AS departure_airport ON flights.departure_airport_id = departure_airport.id
    JOIN airports AS arrival_airport ON flights.arrival_airport_id = arrival_airport.id;
";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $flights = array();
    while ($row = $result->fetch_assoc()) {
        $flights[] = $row;
    }
    echo json_encode(["flights" => $flights]);
} else {
    echo json_encode(["message" => "No flights found"]);
}

$conn->close();
?>
