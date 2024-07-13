<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "flight_management_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] == "POST" 
    && isset($data['flight_number']) 
    && isset($data['departure_airport']) 
    && isset($data['arrival_airport']) 
    && isset($data['departure_time']) 
    && isset($data['arrival_time']) 
    && isset($data['capacity']) 
    && isset($data['price'])) {
    
    $flight_number = $data['flight_number'];
    $departure_airport = $data['departure_airport'];
    $arrival_airport = $data['arrival_airport'];
    $departure_time = $data['departure_time'];
    $arrival_time = $data['arrival_time'];
    $capacity = $data['capacity'];
    $price = $data['price'];

    // Validate that departure and arrival airports are not the same
    if ($departure_airport == $arrival_airport) {
        echo json_encode(["status" => "error", "message" => "Departure and arrival airports cannot be the same"]);
        exit;
    }

    // Validate that departure time is before arrival time
    if (strtotime($departure_time) >= strtotime($arrival_time)) {
        echo json_encode(["status" => "error", "message" => "Departure time must be before arrival time"]);
        exit;
    }

    // Validate flight number format (alphanumeric and length between 1 and 10)
    if (!preg_match('/^[a-zA-Z0-9]{1,10}$/', $flight_number)) {
        echo json_encode(["status" => "error", "message" => "Invalid flight number format"]);
        exit;
    }

    // Validate capacity (positive integer)
    if (!filter_var($capacity, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])) {
        echo json_encode(["status" => "error", "message" => "Invalid capacity value"]);
        exit;
    }

    // Validate price (positive float)
    if (!filter_var($price, FILTER_VALIDATE_FLOAT) || $price <= 0) {
        echo json_encode(["status" => "error", "message" => "Invalid price value"]);
        exit;
    }

    // Ensure airport IDs exist in the database
    $airport_check_sql = "SELECT id FROM airports WHERE id IN (?, ?)";
    $stmt = $conn->prepare($airport_check_sql);
    $stmt->bind_param('ii', $departure_airport, $arrival_airport);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows < 2) {
        echo json_encode(["status" => "error", "message" => "One or both airport IDs are invalid"]);
        exit;
    }

    // Insert flight into the database
    $sql = "INSERT INTO flights (flight_number, departure_airport_id, arrival_airport_id, departure_time, arrival_time, capacity, price) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('siisssd', $flight_number, $departure_airport, $arrival_airport, $departure_time, $arrival_time, $capacity, $price);

    try {
        $stmt->execute();
        echo json_encode(["status" => "success", "message" => "Flight created"]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => $stmt->error]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request or missing parameters"]);
}

$conn->close();
?>
