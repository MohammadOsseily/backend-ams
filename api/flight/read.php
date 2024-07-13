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


$sql = "
    SELECT
        id,
        flight_number,
        departure_airport_id,
        arrival_airport_id,
        departure_time,
        arrival_time,
        price,
        capacity
    FROM flights;
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
