<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE, PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$servername = "localhost";
$username = "root";
$password = "";
$db_name = "flight_management_system";

$conn = new mysqli($servername, $username, $password, $db_name);

if ($conn->connect_error) {
    die('connection failed' . $conn->connect_error);
}

$sql = "SELECT * FROM hotels";
$result = $conn->query($sql);

$hotels = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $hotels[] = $row;
    }
}

$response = [
    "hotels" => $hotels,
    "number_of_hotels" => count($hotels)
];

echo json_encode($response);

$conn->close();
?>
