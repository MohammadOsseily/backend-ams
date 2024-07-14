<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    $id = $data["id"];

    // Validate required fields
    if (!$id) {
        echo json_encode(["status" => "error", "message" => "Please provide id"]);
        exit;
    }

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Get hotel_id from the booking
        $stmt = $conn->prepare("SELECT hotel_id FROM hotel_bookings WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $booking = $result->fetch_assoc();

        if (!$booking) {
            throw new Exception("Booking not found");
        }

        $hotel_id = $booking['hotel_id'];

        // Delete the specific booking
        $stmt = $conn->prepare("DELETE FROM hotel_bookings WHERE id = ?");
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            throw new Exception("Failed to delete booking: " . $stmt->error);
        }

        // Update available rooms
        $stmt = $conn->prepare("UPDATE hotels SET available_rooms = available_rooms + 1 WHERE id = ?");
        $stmt->bind_param("i", $hotel_id);
        if (!$stmt->execute()) {
            throw new Exception("Failed to update available rooms: " . $stmt->error);
        }

        // Commit the transaction
        $conn->commit();

        echo json_encode(["status" => "success", "message" => "Booking cancelled successfully"]);
    } catch (Exception $e) {
        // Rollback the transaction if there was an error
        $conn->rollback();
        echo json_encode(["status" => "error", "message" => "Failed to cancel booking. " . $e->getMessage()]);
    } finally {
        $stmt->close();
    }
} else {
    echo json_encode(["status" => "error", "message" => "Wrong request method"]);
}

$conn->close();
?>
