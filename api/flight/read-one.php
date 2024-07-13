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


if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "
        SELECT f.*, da.name AS departure_airport, aa.name AS arrival_airport
        FROM flights f
        LEFT JOIN airports da ON f.departure_airport_id = da.id
        LEFT JOIN airports aa ON f.arrival_airport_id = aa.id
        WHERE f.id = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    try {
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $flight = $result->fetch_assoc();
            echo json_encode(["data" => $flight, "status" => "success"]);
        } else {
            echo json_encode(["message" => "No flight found with the given ID", "status" => "error"]);
        }
    } catch (Exception $e) {
        echo json_encode(["error" => $stmt->error, "status" => "error"]);
    }
} else {
    echo json_encode(["message" => "No flight ID provided", "status" => "error"]);
}

$conn->close();
?>
