<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE, PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require '../../config/db.php';

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