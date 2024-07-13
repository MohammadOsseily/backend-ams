<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE, PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


$servername = "localhost";
$username = "root";
$password = "";
$dbname = "flight_management_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT id, name FROM airports";
$result = $conn->query($sql);

$airports = array();

while ($row = $result->fetch_assoc()) {
    $airports[] = array(
        "id" => $row['id'],
        "name" => $row['name']
    );
}

echo json_encode(array("status" => "success", "airports" => $airports));

$conn->close();
?>
