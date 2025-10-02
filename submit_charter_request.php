<?php
// submit_charter_request.php (AJAX Version)

// Set the header to indicate a JSON response
header('Content-Type: application/json');

include_once('admin/function/_db.php');

// Prepare a response array
$response = [
    'status' => 'error',
    'message' => 'An unknown error occurred.'
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim(filter_input(INPUT_POST, 'name'));
    $mobile = trim(filter_input(INPUT_POST, 'mobile'));
    $from_location = trim(filter_input(INPUT_POST, 'from_location'));
    $to_location = trim(filter_input(INPUT_POST, 'to_location'));
    $journey_date = trim(filter_input(INPUT_POST, 'journey_date'));
    $trip_type = trim(filter_input(INPUT_POST, 'trip_type'));
    $return_date = trim(filter_input(INPUT_POST, 'return_date'));
    $message = trim(filter_input(INPUT_POST, 'message'));

    // --- Validation ---
    if (empty($name) || empty($mobile) || empty($from_location) || empty($to_location) || empty($journey_date) || empty($trip_type)) {
        $response['message'] = 'Please fill out all required fields.';
        echo json_encode($response);
        exit();
    }
    if ($trip_type === 'Round-Trip' && empty($return_date)) {
        $response['message'] = 'Please select a return date for a round trip.';
        echo json_encode($response);
        exit();
    }
    if ($trip_type === 'One-Way') {
        $return_date = null;
    }

    // --- Save to Database ---
    try {
        $stmt = $_conn_db->prepare( // Use your DB connection variable
            "INSERT INTO charter_inquiries (customer_name, customer_mobile, from_location, to_location, journey_date, trip_type, return_date, message) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$name, $mobile, $from_location, $to_location, $journey_date, $trip_type, $return_date, $message]);
        
        // --- If execution reaches here, it was successful ---
        $response['status'] = 'success';
        $response['message'] = 'Thank You! Your request has been sent. We will contact you shortly.';

        // Optional: Send an email notification to the admin here (using PHPMailer if configured)

    } catch (PDOException $e) {
        error_log("Charter Inquiry DB Error: " . $e->getMessage());
        $response['message'] = 'A database error occurred. Could not save your request.';
    }
} else {
    $response['message'] = 'Invalid request method.';
}

// Send the JSON response back to the JavaScript
echo json_encode($response);
exit();
?>