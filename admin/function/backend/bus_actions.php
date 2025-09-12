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
        $bus_name = trim($_POST['bus_name'] ?? '');
        $registration_number = trim($_POST['registration_number'] ?? '');
        $bus_type = trim($_POST['bus_type'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = $_POST['status'] ?? 'Inactive';
        $categories = $_POST['categories'] ?? [];

        if (empty($bus_name) || empty($registration_number) || empty($bus_type)) {
            $response['notif_desc'] = 'Please fill all required fields.';
            echo json_encode($response);
            exit();
        }

        try {
            $stmt_check = $_conn_db->prepare("SELECT COUNT(*) FROM buses WHERE registration_number = ?");
            $stmt_check->execute([$registration_number]);
            if ($stmt_check->fetchColumn() > 0) {
                $response['notif_desc'] = 'A bus with this registration number already exists.';
                echo json_encode($response);
                exit();
            }

            $_conn_db->beginTransaction();

            // SQL now excludes operator_id
            $stmt = $_conn_db->prepare("INSERT INTO buses (bus_name, registration_number, bus_type, description, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$bus_name, $registration_number, $bus_type, $description, $status]);
            $new_bus_id = $_conn_db->lastInsertId();

            if (!empty($categories) && is_array($categories)) {
                $stmt_map = $_conn_db->prepare("INSERT INTO bus_category_map (bus_id, category_id) VALUES (?, ?)");
                foreach ($categories as $category_id) {
                    $stmt_map->execute([$new_bus_id, (int)$category_id]);
                }
            }

            $upload_dir = "uploads/bus_images/";
            if (!is_dir($upload_dir)) { mkdir($upload_dir, 0775, true); }

            if (isset($_FILES['bus_images']) && !empty($_FILES['bus_images']['name'][0])) {
                $stmt_img = $_conn_db->prepare("INSERT INTO bus_images (bus_id, image_path) VALUES (?, ?)");
                $file_count = count($_FILES['bus_images']['name']);
                for ($i = 0; $i < $file_count; $i++) {
                    if ($_FILES['bus_images']['error'][$i] === UPLOAD_ERR_OK) {
                        $tmp_name = $_FILES['bus_images']['tmp_name'][$i];
                        $extension = strtolower(pathinfo($_FILES['bus_images']['name'][$i], PATHINFO_EXTENSION));
                        $new_filename = 'bus_' . $new_bus_id . '_' . time() . '_' . uniqid() . '.' . $extension;
                        if (move_uploaded_file($tmp_name, $upload_dir . $new_filename)) {
                            $stmt_img->execute([$new_bus_id, $new_filename]);
                        }
                    }
                }
            }

            $_conn_db->commit();
            $response = [
                'res' => 'true', 'notif_type' => 'success', 'notif_title' => 'Success',
                'notif_desc' => 'Bus added successfully! You can now manage its seats.',
                'goTo' => 'manage_seats.php?bus_id=' . $new_bus_id
            ];
        } catch (PDOException $e) {
            $_conn_db->rollBack();
            error_log("Add bus error: " . $e->getMessage());
            $response['notif_desc'] = 'A database error occurred. Please check the logs.';
        }
    }
    elseif ($action === 'get_all_categories') {
        try {
            $stmt = $_conn_db->query("SELECT category_id, category_name FROM bus_categories WHERE status = 'Active' ORDER BY category_name");
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response['res'] = 'true';
            $response['categories'] = $categories;
        } catch (PDOException $e) {
            $response['notif_desc'] = 'Could not fetch categories.';
        }
    } elseif ($action === 'add_category') {
        $category_name = trim($_POST['category_name'] ?? '');
        if (empty($category_name)) {
            $response['notif_desc'] = 'Category name cannot be empty.';
        } else {
            try {
                $stmt = $_conn_db->prepare("INSERT INTO bus_categories (category_name) VALUES (?)");
                $stmt->execute([$category_name]);
                $response['res'] = 'true';
                $response['notif_desc'] = 'Category added successfully.';
            } catch (PDOException $e) {
                if ($e->errorInfo[1] == 1062) {
                    $response['notif_desc'] = 'This category already exists.';
                } else {
                    $response['notif_desc'] = 'Database error.';
                }
            }
        }
    } elseif ($action === 'update_category') {
        $category_id = (int)($_POST['category_id'] ?? 0);
        $category_name = trim($_POST['category_name'] ?? '');
        if ($category_id > 0 && !empty($category_name)) {
            try {
                $stmt = $_conn_db->prepare("UPDATE bus_categories SET category_name = ? WHERE category_id = ?");
                $stmt->execute([$category_name, $category_id]);
                $response['res'] = 'true';
                $response['notif_desc'] = 'Category updated successfully.';
            } catch (PDOException $e) {
                $response['notif_desc'] = 'Database error or category already exists.';
            }
        } else {
            $response['notif_desc'] = 'Invalid data provided.';
        }
    } elseif ($action === 'delete_category') {
        $category_id = (int)($_POST['category_id'] ?? 0);
        if ($category_id > 0) {
            try {
                $stmt = $_conn_db->prepare("DELETE FROM bus_categories WHERE category_id = ?");
                $stmt->execute([$category_id]);
                $response['res'] = 'true';
                $response['notif_desc'] = 'Category deleted successfully.';
            } catch (PDOException $e) {
                $response['notif_desc'] = 'Database error.';
            }
        } else {
            $response['notif_desc'] = 'Invalid ID.';
        }
    } elseif ($action === 'update_bus') {
        $bus_id = (int)($_POST['bus_id'] ?? 0);
        $bus_name = trim($_POST['bus_name'] ?? '');
        $registration_number = trim($_POST['registration_number'] ?? '');
        $operator_id =  0;
        $bus_type = trim($_POST['bus_type'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = $_POST['status'] ?? 'Inactive';
        $categories = $_POST['categories'] ?? [];
        $images_to_delete_str = $_POST['images_to_delete'] ?? '';

        // Validación
        if ($bus_id <= 0 || empty($bus_name) || empty($registration_number)) {
            $response['notif_desc'] = 'Datos inválidos. Por favor, rellena todos los campos obligatorios.';
            echo json_encode($response);
            exit();
        }

        try {
            // Comprobar número de registro duplicado, excluyendo el autobús actual
            $stmt_check = $_conn_db->prepare("SELECT COUNT(*) FROM buses WHERE registration_number = ? AND bus_id != ?");
            $stmt_check->execute([$registration_number, $bus_id]);
            if ($stmt_check->fetchColumn() > 0) {
                $response['notif_desc'] = 'Otro autobús con este número de registro ya existe.';
                echo json_encode($response);
                exit();
            }

            $_conn_db->beginTransaction();

            // 1. Actualizar los detalles principales del autobús en la tabla `buses`
            $sql = "UPDATE buses SET 
                        bus_name = ?, registration_number = ?, 
                        bus_type = ?, description = ?, status = ?,
                        updated_at = NOW()
                    WHERE bus_id = ?";
            $stmt = $_conn_db->prepare($sql);
            $stmt->execute([
                $bus_name,
                $registration_number,
                
                $bus_type,
                $description,
                $status,
                $bus_id
            ]);

            // 2. Sincronizar categorías: eliminar las antiguas, insertar las nuevas
            $stmt_delete_cats = $_conn_db->prepare("DELETE FROM bus_category_map WHERE bus_id = ?");
            $stmt_delete_cats->execute([$bus_id]);

            if (!empty($categories) && is_array($categories)) {
                $stmt_insert_cat = $_conn_db->prepare("INSERT INTO bus_category_map (bus_id, category_id) VALUES (?, ?)");
                foreach ($categories as $category_id) {
                    $stmt_insert_cat->execute([$bus_id, (int)$category_id]);
                }
            }

            // 3. Eliminar imágenes marcadas para eliminación
            if (!empty($images_to_delete_str)) {
                $images_to_delete_ids = explode(',', $images_to_delete_str);
                $placeholders = rtrim(str_repeat('?,', count($images_to_delete_ids)), ',');

                // Obtener las rutas de los archivos para poder eliminarlos del servidor
                $stmt_get_paths = $_conn_db->prepare("SELECT image_path FROM bus_images WHERE image_id IN ($placeholders)");
                $stmt_get_paths->execute($images_to_delete_ids);
                $paths_to_unlink = $stmt_get_paths->fetchAll(PDO::FETCH_COLUMN, 0);

                foreach ($paths_to_unlink as $path) {
                    if (file_exists("uploads/bus_images/" . $path)) {
                        unlink("uploads/bus_images/" . $path);
                    }
                }

                // Eliminar los registros de la base de datos
                $stmt_delete_img = $_conn_db->prepare("DELETE FROM bus_images WHERE image_id IN ($placeholders)");
                $stmt_delete_img->execute($images_to_delete_ids);
            }

            // 4. Subir nuevas imágenes (la misma lógica que en add_bus)
            $upload_dir = "uploads/bus_images/";
            if (isset($_FILES['bus_images']) && !empty($_FILES['bus_images']['name'][0])) {
                $stmt_img = $_conn_db->prepare("INSERT INTO bus_images (bus_id, image_path) VALUES (?, ?)");
                $file_count = count($_FILES['bus_images']['name']);
                for ($i = 0; $i < $file_count; $i++) {
                    if ($_FILES['bus_images']['error'][$i] === UPLOAD_ERR_OK) {
                        $tmp_name = $_FILES['bus_images']['tmp_name'][$i];
                        $file_info = pathinfo($_FILES['bus_images']['name'][$i]);
                        $extension = strtolower($file_info['extension']);
                        $new_filename = 'bus_' . $bus_id . '_' . time() . '_' . uniqid() . '.' . $extension;
                        if (move_uploaded_file($tmp_name, $upload_dir . $new_filename)) {
                            $stmt_img->execute([$bus_id, $new_filename]);
                        }
                    }
                }
            }

            $_conn_db->commit();

            $response['res'] = 'true';
            $response['notif_type'] = 'success';
            $response['notif_title'] = 'Success';
            $response['notif_desc'] = 'Bus details updated successfully.';
            $response['goTo'] = 'view_all_buses.php'; // Redirigir a la lista después de la actualización

        } catch (PDOException $e) {
            $_conn_db->rollBack();
            error_log("Update bus error: " . $e->getMessage());
            $response['notif_desc'] = 'Database error updating bus: ' . $e->getMessage();
        }
    } elseif ($action === 'delete_bus') {
        $bus_id = (int)($_POST['bus_id'] ?? 0);
        if ($bus_id <= 0) {
            $response['notif_desc'] = 'Invalid bus ID provided.';
            echo json_encode($response);
            exit();
        }

        try {
            $_conn_db->beginTransaction();

            // Pehle saari seats delete karein
            $stmt_seats = $_conn_db->prepare("DELETE FROM seats WHERE bus_id = ?");
            $stmt_seats->execute([$bus_id]);

            // Phir bus ko delete karein
            $stmt_bus = $_conn_db->prepare("DELETE FROM buses WHERE bus_id = ?");
            $stmt_bus->execute([$bus_id]);

            $_conn_db->commit();

            $response['res'] = 'true';
            $response['notif_type'] = 'success';
            $response['notif_title'] = 'Deleted';
            $response['notif_desc'] = 'The bus and all its seats have been deleted.';
            $response['notif_popup'] = 'false'; // Chhota notification

        } catch (PDOException $e) {
            $_conn_db->rollBack();
            error_log("Delete bus error: " . $e->getMessage());
            $response['notif_desc'] = 'Database error deleting bus: ' . $e->getMessage();
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
                $bus_id,
                $seat_code,
                $deck,
                $seat_type,
                $x_coordinate,
                $y_coordinate,
                $width,
                $height,
                $orientation,
                $base_price,
                $gender_preference,
                $is_bookable,
                $status
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
