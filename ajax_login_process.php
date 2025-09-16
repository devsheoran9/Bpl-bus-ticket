<?php 
require "./admin/function/_db.php"; 

header('Content-Type: application/json');

// Default response
$response = ['success' => false, 'message' => 'Invalid credentials.'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_identifier = $_POST['login_identifier'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($login_identifier) || empty($password)) {
        $response['message'] = 'Please enter both email/mobile and password.';
        echo json_encode($response);
        exit();
    }

    try {
        $stmt = $_conn_db->prepare("SELECT id, username, email, mobile_no, password, status FROM users WHERE mobile_no = :mobile OR email = :email");
        $stmt->execute([':mobile' => $login_identifier, ':email' => $login_identifier]);

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (password_verify($password, $user['password'])) {
                if ($user['status'] != 1) {
                    $response['message'] = "Your account is not active. Please contact support.";
                } else {
                    // Login successful, set session variables
                    $_SESSION['loggedin'] = true;
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['mobile'] = $user['mobile_no'];

                    // Update response to be successful
                    $response['success'] = true;
                    $response['message'] = 'Login successful!';
                    // Send user data back to the JavaScript to update the form
                    $response['user'] = [
                        'username' => $user['username'],
                        'email' => $user['email'],
                        'mobile_no' => $user['mobile_no']
                    ];
                }
            } else {
                // Password does not match
                $response['message'] = "Invalid credentials. Please try again.";
            }
        } else {
            // User not found
            $response['message'] = "Invalid credentials. Please try again.";
        }
    } catch (PDOException $e) {
        // Database error
        error_log("AJAX Login Error: " . $e->getMessage());
        $response['message'] = "A server error occurred. Please try again later.";
    }
} else {
    $response['message'] = "Invalid request method.";
}

echo json_encode($response);
exit();