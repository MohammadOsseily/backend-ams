<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . '/../vendor/autoload.php';
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../config');
$dotenv->load();

require '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $departure_city = $input['departure_city'];
    $preferred_city = $input['city'];
    $preferred_date = $input['date'];
    $preferred_budget = floatval($input['budget']);
    $num_days = intval($input['days']);
    $date_range_start = date('Y-m-d', strtotime($preferred_date . ' -3 days'));
    $date_range_end = date('Y-m-d', strtotime($preferred_date . ' +3 days'));

    $response = [];

    $departure_airport_query = "SELECT id FROM airports WHERE location = ?";
    $stmt = $conn->prepare($departure_airport_query);
    $stmt->bind_param("s", $departure_city);
    $stmt->execute();
    $departure_airport_result = $stmt->get_result();

    $arrival_airport_query = "SELECT id FROM airports WHERE location = ?";
    $stmt = $conn->prepare($arrival_airport_query);
    $stmt->bind_param("s", $preferred_city);
    $stmt->execute();
    $arrival_airport_result = $stmt->get_result();

    if ($departure_airport_result->num_rows > 0 && $arrival_airport_result->num_rows > 0) {
        $departure_airport = $departure_airport_result->fetch_assoc();
        $arrival_airport = $arrival_airport_result->fetch_assoc();
        $departure_airport_id = $departure_airport['id'];
        $arrival_airport_id = $arrival_airport['id'];

        $flights_query = "
            SELECT * FROM flights
            WHERE departure_airport_id = ? AND arrival_airport_id = ? 
            AND departure_time BETWEEN ? AND ? 
            AND capacity > 0
        ";
        $stmt = $conn->prepare($flights_query);
        $stmt->bind_param("iiss", $departure_airport_id, $arrival_airport_id, $date_range_start, $date_range_end);
        $stmt->execute();
        $flights_result = $stmt->get_result();

        if ($flights_result->num_rows > 0) {
            $flights = $flights_result->fetch_all(MYSQLI_ASSOC);
            $response['flights'] = $flights;
        } else {
            $response['flights'] = [];
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No airport found in one or both specified cities.']);
        exit();
    }

    $hotels_query = "
        SELECT * FROM hotels
        WHERE city = ? AND price_per_night <= ? AND available_rooms > 0
    ";
    $stmt = $conn->prepare($hotels_query);
    $stmt->bind_param("sd", $preferred_city, $preferred_budget);
    $stmt->execute();
    $hotels_result = $stmt->get_result();

    if ($hotels_result->num_rows > 0) {
        $hotels = $hotels_result->fetch_all(MYSQLI_ASSOC);
        $response['hotels'] = $hotels;
    } else {
        $response['hotels'] = [];
    }

    $taxis_query = "
        SELECT * FROM taxis
        WHERE city = ? AND price_per_km <= ?
    ";
    $stmt = $conn->prepare($taxis_query);
    $stmt->bind_param("sd", $preferred_city, $preferred_budget);
    $stmt->execute();
    $taxis_result = $stmt->get_result();

    if ($taxis_result->num_rows > 0) {
        $taxis = $taxis_result->fetch_all(MYSQLI_ASSOC);
        $response['taxis'] = $taxis;
    } else {
        $response['taxis'] = [];
    }

    $valid_trips = [];
    foreach ($response['flights'] as $flight) {
        foreach ($response['hotels'] as $hotel) {
            foreach ($response['taxis'] as $taxi) {
                $hotel_total_cost = $hotel['price_per_night'] * $num_days;
                $taxi_total_cost = $taxi['price_per_km'] * 10 * $num_days;
                $total_cost = $flight['price'] + $hotel_total_cost + $taxi_total_cost;
                if ($total_cost <= $preferred_budget) {
                    $valid_trips[] = [
                        'flight' => $flight,
                        'hotel' => $hotel,
                        'taxi' => $taxi,
                        'total_cost' => $total_cost
                    ];
                }
            }
        }
    }

    if (!empty($valid_trips)) {
        $response['valid_trips'] = $valid_trips;

        $trip_plan_prompt = "
        You are a travel planner AI. I need you to create a detailed trip plan based on the following options and preferences:

        Preferences:
        - Departure City: $departure_city
        - Destination City: $preferred_city
        - Preferred Departure Date: $preferred_date , 3 days before or after this date are valid and you can recommend them
        - Number of Days: $num_days
        - Budget: $preferred_budget

        Flight Options:\n";

        foreach ($valid_trips as $index => $trip) {
            $trip_plan_prompt .= "Option " . ($index + 1) . ":
            - Flight Number: " . $trip['flight']['flight_number'] . "
            - Departure Time: " . $trip['flight']['departure_time'] . "
            - Arrival Time: " . $trip['flight']['arrival_time'] . "
            - Price: $" . $trip['flight']['price'] . "\n";
        }

        $trip_plan_prompt .= "\nHotel Options:\n";
        foreach ($valid_trips as $index => $trip) {
            $trip_plan_prompt .= "Option " . ($index + 1) . ":
            - Hotel Name: " . $trip['hotel']['name'] . "
            - Address: " . $trip['hotel']['address'] . "
            - Price per Night: $" . $trip['hotel']['price_per_night'] . "\n";
        }

        $trip_plan_prompt .= "\nTaxi Options:\n";
        foreach ($valid_trips as $index => $trip) {
            $trip_plan_prompt .= "Option " . ($index + 1) . ":
            - Taxi Service: " . $trip['taxi']['company_name'] . "
            - Price per KM: $" . $trip['taxi']['price_per_km'] . "\n";
        }

        $trip_plan_prompt .= "
        Based on the above options, create a detailed trip plan. The plan should include:
        - A daily itinerary with recommended activities, places to visit, and estimated costs for each day.
        - Calculate the total cost of the trip, ensuring it stays within the budget.
        - Include recommendations for popular attractions, restaurants, and activities in the destination city based on your knowledge.
        - Always give numbers don't just say X$ for something that is estimated, you can mention that it is estimated but give a close number.
        - Check for flights going back from the destination city to the departure city on the day after your last planned day. You can use this flight to return, adding its cost to the total. If there's a flight within a range of 3 days before or after that date, consider adjusting your trip length accordingly to include this return option in your total cost. If no such flight is available, consider alternative dates or airlines for your return journey.
        - Do not say what option you chose like Option 1, say the flight number instead.
        - Do not forge to count the price of the hotel every day.
        ";

        $openai_api_key = $_ENV['API_KEY'];
        $openai_payload = json_encode([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a travel planner AI.'],
                ['role' => 'user', 'content' => $trip_plan_prompt],
            ],
            'max_tokens' => 750
        ]);

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $openai_api_key
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $openai_payload);

        $openai_response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to call OpenAI API: ' . curl_error($ch)]);
            exit();
        }

        curl_close($ch);

        $openai_result = json_decode($openai_response, true);

        if (isset($openai_result['choices']) && isset($openai_result['choices'][0]['message']['content'])) {
            $response['trip_plan'] = $openai_result['choices'][0]['message']['content'];
        } else {
            $response['trip_plan'] = "Failed to generate trip plan.";
        }
    } else {
        $response['message'] = "No valid trips found within the specified budget.";
    }

    echo json_encode($response);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
