<?php
// bus_actions.php
global $_conn_db;
include_once('../_db.php'); // आपकी DB कनेक्शन और sanitize_input फ़ंक्शन - सुनिश्चित करें कि यह पाथ सही है

header('Content-Type: application/json'); // JSON रिस्पॉन्स के लिए हेडर सेट करें

$response = [
    'res' => 'false',
    'notif_type' => 'error',
    'notif_title' => 'Error',
    'notif_desc' => 'An unknown error occurred.', // यह डिफ़ॉल्ट एरर डिस्क्रिप्शन है
    'notif_popup' => 'true',
    'goTo' => ''
];

if (isset($_POST['action']) || isset($_GET['action'])) {
    // GET या POST से action को प्राप्त करें
    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    if ($action === 'add_bus') {
        // --- एक नई बस जोड़ने को संभालें ---
        $bus_name = $_POST['bus_name'] ?? '';
        $registration_number = $_POST['registration_number'] ?? '';
        $operator_id = (int)($_POST['operator_id'] ?? 0);
        $bus_type = $_POST['bus_type'] ?? '';
        $total_seats = (int)($_POST['total_seats'] ?? 0); 
        $seater_seats = (int)($_POST['seater_seats'] ?? 0);
        $sleeper_seats = (int)($_POST['sleeper_seats'] ?? 0);
        $description = $_POST['description'] ?? '';
        $status = $_POST['status'] ?? 'Inactive';
        
        $amenities = null; // JSON डेटा को यहाँ से प्रोसेस किया जा सकता है, यदि आवश्यक हो
      
        if (empty($bus_name) || empty($registration_number) || $operator_id <= 0 || empty($bus_type)) {
            $response['notif_desc'] = 'Bus name, registration number, operator, and type are required.';
            echo json_encode($response);
            exit();
        }

        $target_dir = "uploads/bus_images/";
        $bus_image_path = null;
        if (isset($_FILES['bus_image']) && $_FILES['bus_image']['error'] === UPLOAD_ERR_OK) {
            if (!is_dir($target_dir)) {
                if (!mkdir($target_dir, 0775, true)) {
                    $response['notif_desc'] = 'Failed to create upload directory.';
                    echo json_encode($response);
                    exit();
                }
            }
            $original_name = basename($_FILES['bus_image']['name']);
            $sanitized_name = preg_replace('/[^a-zA-Z0-9._-]/', '_', $original_name);
            $image_name = uniqid() . '_' . $sanitized_name;

            $target_file = $target_dir . $image_name;

            if (move_uploaded_file($_FILES['bus_image']['tmp_name'], $target_file)) {
                $bus_image_path = $target_file;
            } else {
                $response['notif_desc'] = 'Failed to upload bus image.';
                echo json_encode($response);
                exit();
            }
        }

        try {
            $stmt = $_conn_db->prepare("SELECT COUNT(*) FROM buses WHERE registration_number = ?");
            $stmt->execute([$registration_number]);
            if ($stmt->fetchColumn() > 0) {
                $response['notif_desc'] = 'A bus with this registration number already exists.';
                echo json_encode($response);
                exit();
            }

            $stmt = $_conn_db->prepare(
                "INSERT INTO buses (bus_name, registration_number, operator_id, bus_type, total_seats, seater_seats, sleeper_seats, amenities, bus_image, description, status, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())"
            );
            $stmt->execute([
                $bus_name, $registration_number, $operator_id, $bus_type, $total_seats,
                $seater_seats, $sleeper_seats, $amenities, $bus_image_path, $description, $status
            ]);

            $new_bus_id = $_conn_db->lastInsertId();

            $response['res'] = 'true';
            $response['notif_type'] = 'success';
            $response['notif_title'] = 'Success';
            $response['notif_desc'] = 'Bus added successfully! Redirecting to seat layout setup.';
            $response['notif_popup'] = 'true';
            $response['goTo'] = 'manage_seats.php?bus_id=' . $new_bus_id; 
            
        } catch (PDOException $e) {
            error_log("Add bus error: " . $e->getMessage());
            $response['notif_desc'] = 'Database error: ' . $e->getMessage();
        }

    } elseif ($action === 'add_seat') {
        // --- एक नई सीट जोड़ने को संभालें ---
        $bus_id = (int)($_POST['bus_id'] ?? 0);
        $seat_code = $_POST['seat_code'] ?? '';
        $deck = $_POST['deck'] ?? '';
        $seat_type = $_POST['seat_type'] ?? 'SEATER';
        $x_coordinate = (int)($_POST['x_coordinate'] ?? 0);
        $y_coordinate = (int)($_POST['y_coordinate'] ?? 0);
        $width = (int)($_POST['width'] ?? 40);
        $height = (int)($_POST['height'] ?? 40);
        $orientation = $_POST['orientation'] ?? 'VERTICAL';
        $base_price = (float)($_POST['base_price'] ?? 0.00);
        $gender_preference = $_POST['gender_preference'] ?? 'ANY';
        // फिक्स: बूलियन में फिर 0/1 पूर्णांक में स्पष्ट रूप से बदलें
        $is_bookable_bool = filter_var($_POST['is_bookable'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $is_bookable = $is_bookable_bool ? 1 : 0; // सुनिश्चित करें कि 0 या 1 पूर्णांक का उपयोग किया जाता है
        $status = $_POST['status'] ?? 'AVAILABLE';

        if ($bus_id <= 0 || empty($seat_code) || empty($deck)) {
            $response['notif_desc'] = 'Missing required seat data.';
            echo json_encode($response);
            exit();
        }

        try {
            $stmt = $_conn_db->prepare("SELECT COUNT(*) FROM seats WHERE bus_id = ? AND seat_code = ?");
            $stmt->execute([$bus_id, $seat_code]);
            if ($stmt->fetchColumn() > 0) {
                $response['notif_desc'] = 'Seat code "' . $seat_code . '" already exists for this bus.';
                echo json_encode($response);
                exit();
            }

            $stmt = $_conn_db->prepare(
                "INSERT INTO seats (bus_id, seat_code, deck, seat_type, x_coordinate, y_coordinate, width, height, orientation, base_price, gender_preference, is_bookable, status, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())"
            );
            $stmt->execute([
                $bus_id, $seat_code, $deck, $seat_type, $x_coordinate, $y_coordinate, $width, $height,
                $orientation, $base_price, $gender_preference, $is_bookable, $status
            ]);
            
            $new_seat_id = $_conn_db->lastInsertId();

            $response['res'] = 'true';
            $response['notif_type'] = 'success';
            $response['notif_title'] = 'Success';
            $response['notif_desc'] = 'Seat added successfully!';
            $response['new_seat_id'] = $new_seat_id;
            $response['notif_popup'] = 'false'; // यह एक पृष्ठभूमि ऑपरेशन है, पॉपअप की आवश्यकता नहीं है
            
        } catch (PDOException $e) {
            error_log("Add seat error: " . $e->getMessage());
            $response['notif_desc'] = 'Database error adding seat: ' . $e->getMessage();
        }

    } elseif ($action === 'update_seat' || $action === 'update_seat_position') {
        // --- मौजूदा सीट या सिर्फ उसकी स्थिति को अपडेट करना संभालें ---
        $seat_id = (int)($_POST['seat_id'] ?? 0); 
        $bus_id = (int)($_POST['bus_id'] ?? 0); 

        if ($seat_id <= 0 || $bus_id <= 0) {
            $response['notif_desc'] = 'Missing required seat or bus ID for update.';
            echo json_encode($response);
            exit();
        }

        $update_fields = [];
        $update_values = [];

        // हमेशा `updated_at` अपडेट करें
        $update_fields[] = "updated_at = NOW()";

        // मोडल एडिट से फ़ील्ड्स (पूर्ण 'update_seat' क्रिया)
        if ($action === 'update_seat') {
            $update_fields[] = "base_price = ?";
            $update_values[] = (float)($_POST['base_price'] ?? 0.00);
            
            $update_fields[] = "gender_preference = ?";
            $update_values[] = $_POST['gender_preference'] ?? 'ANY';
            
            // फिक्स: बूलियन में फिर 0/1 पूर्णांक में स्पष्ट रूप से बदलें
            $is_bookable_bool = filter_var($_POST['is_bookable'] ?? true, FILTER_VALIDATE_BOOLEAN);
            $update_fields[] = "is_bookable = ?";
            $update_values[] = $is_bookable_bool ? 1 : 0; // सुनिश्चित करें कि 0 या 1 पूर्णांक का उपयोग किया जाता है
            
            $update_fields[] = "orientation = ?";
            $update_values[] = $_POST['orientation'] ?? 'VERTICAL';
            
            $update_fields[] = "width = ?";
            $update_values[] = (int)($_POST['width'] ?? 40);
            
            $update_fields[] = "height = ?";
            $update_values[] = (int)($_POST['height'] ?? 40);
            
            $update_fields[] = "status = ?";
            $update_values[] = $_POST['status'] ?? 'AVAILABLE';
        }

        // स्थिति अपडेट के लिए फ़ील्ड्स (ड्रैग से या मोडल से यदि इसमें स्थिति शामिल है)
        if (isset($_POST['x_coordinate'])) {
            $update_fields[] = "x_coordinate = ?";
            $update_values[] = (int)($_POST['x_coordinate'] ?? 0);
        }
        if (isset($_POST['y_coordinate'])) {
            $update_fields[] = "y_coordinate = ?";
            $update_values[] = (int)($_POST['y_coordinate'] ?? 0);
        }

        if (empty($update_fields)) {
            $response['notif_desc'] = 'No valid fields to update.';
            echo json_encode($response);
            exit();
        }

        try {
            $sql = "UPDATE seats SET " . implode(', ', $update_fields) . " WHERE seat_id = ? AND bus_id = ?";
            $update_values[] = $seat_id;
            $update_values[] = $bus_id;

            $stmt = $_conn_db->prepare($sql);
            $stmt->execute($update_values);

            $response['res'] = 'true';
            $response['notif_type'] = 'success';
            $response['notif_title'] = 'Success';
            $response['notif_desc'] = 'Seat updated successfully!';
            $response['notif_popup'] = 'false'; // यह एक पृष्ठभूमि ऑपरेशन है, पॉपअप की आवश्यकता नहीं है
            
        } catch (PDOException $e) {
            error_log("Update seat error: " . $e->getMessage());
            $response['notif_desc'] = 'Database error updating seat: ' . $e->getMessage();
        }

    } elseif ($action === 'delete_seat') {
        // --- एक सीट हटाने को संभालें ---
        $seat_id = (int)($_POST['seat_id'] ?? 0);
        $bus_id = (int)($_POST['bus_id'] ?? 0); // सुरक्षा जांच के लिए bus_id जोड़ा गया

        if ($seat_id <= 0 || $bus_id <= 0) { // दोनों की जांच करें
            $response['notif_desc'] = 'Missing seat ID or Bus ID for deletion.';
            echo json_encode($response);
            exit();
        }

        try {
            // सुरक्षा के लिए WHERE क्लॉज में bus_id जोड़ें
            $stmt = $_conn_db->prepare("DELETE FROM seats WHERE seat_id = ? AND bus_id = ?");
            $stmt->execute([$seat_id, $bus_id]);

            $response['res'] = 'true';
            $response['notif_type'] = 'success';
            $response['notif_title'] = 'Success';
            $response['notif_desc'] = 'Seat deleted successfully!';
            $response['notif_popup'] = 'false'; // यह एक पृष्ठभूमि ऑपरेशन है, पॉपअप की आवश्यकता नहीं है
            
        } catch (PDOException $e) {
            error_log("Delete seat error: " . $e->getMessage());
            $response['notif_desc'] = 'Database error deleting seat: ' . $e->getMessage();
        }

    } elseif ($action === 'get_bus_seats') {
        // --- एक बस के लिए सभी सीटों को लाना संभालें ---
        // GET अनुरोध से bus_id प्राप्त करें
        $bus_id = (int)($_GET['bus_id'] ?? 0);

        if ($bus_id <= 0) {
            $response['notif_desc'] = 'Invalid Bus ID provided for fetching seats.';
            echo json_encode($response);
            exit();
        }

        try {
            // सुसंगत रेंडरिंग ऑर्डर के लिए डेक और फिर कोऑर्डिनेट्स द्वारा ऑर्डर किया गया
            $stmt = $_conn_db->prepare("SELECT * FROM seats WHERE bus_id = ? ORDER BY deck DESC, y_coordinate ASC, x_coordinate ASC");
            $stmt->execute([$bus_id]);
            $seats = $stmt->fetchAll(PDO::FETCH_ASSOC); // एसोसिएटिव एरे के रूप में लाएं

            // **महत्वपूर्ण फिक्स:** यहाँ सफलता के लिए डिफ़ॉल्ट एरर नोटिफिकेशन को ओवरराइट करें
            $response['res'] = 'true';
            $response['seats'] = $seats; // सीटों का डेटा यहाँ जोड़ा गया है
            $response['notif_type'] = 'success'; 
            $response['notif_title'] = 'Success';
            $response['notif_desc'] = 'Seats fetched successfully.';
            $response['notif_popup'] = 'false'; // यह एक पृष्ठभूमि ऑपरेशन है, पॉपअप की आवश्यकता नहीं है

        } catch (PDOException $e) {
            error_log("Get bus seats error: " . $e->getMessage());
            $response['notif_desc'] = 'Database error fetching seats: ' . $e->getMessage();
            // यदि कोई डेटाबेस एरर होती है, तो $response['res'] = 'false' और एरर नोटिफिकेशन अपने आप बनी रहेंगी।
        }
    }
}

// यदि कोई वैध 'action' प्रदान नहीं किया गया है, तो डिफ़ॉल्ट एरर रिस्पॉन्स भेजा जाएगा
echo json_encode($response);
?>