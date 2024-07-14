<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Prepare and execute
    $stmt = $conn->prepare('SELECT id, company_name, city, phone_number, price_per_km FROM taxis');
    if ($stmt) {
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($id, $company_name, $city, $phone_number, $price_per_km);

        $taxis = [];
        while ($stmt->fetch()) {
            $taxis[] = [
                "id" => $id,
                "company_name" => $company_name,
                "city" => $city,
                "phone_number" => $phone_number,
                "price_per_km" => $price_per_km
            ];
        }

        echo json_encode(["status" => "success", "data" => $taxis]);

        // Close statement
        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}

// Close connection
$conn->close();