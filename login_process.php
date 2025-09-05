<?php
session_start();
require 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mobile_no = $_POST['mobile_no'];
    $password = $_POST['password'];

    // Find user by mobile number and check if their status is active (e.g., status = 1)
    $stmt = $conn->prepare("SELECT id, username, password, status FROM users WHERE mobile_no = ?");
    $stmt->bind_param("s", $mobile_no);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $username, $hashed_password, $user_status);
        $stmt->fetch();

        // Verify password AND check if the user account is active
        if (password_verify($password, $hashed_password)) {

            if ($user_status != 1) { // Checks the main 'users' table status
                // Account is disabled or banned
                echo "Your account is not active. Please contact support.";
                header("refresh:3;url=login.php");
                exit();
            }

            // Correct password and active account, start session
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;

            // Generate a unique token for this specific login session
            $token = bin2hex(random_bytes(32));
            $ip_address = $_SERVER['REMOTE_ADDR'];

            // =================================================================
            // ** NEW AND IMPORTANT LINE **
            // We must save this unique token in the session itself.
            $_SESSION['login_token'] = $token;
            // =================================================================

            // Insert the new token into the database with status 1 (active)
            $token_stmt = $conn->prepare("INSERT INTO users_login_token (user_id, token, ip_address, status) VALUES (?, ?, ?, 1)");
            $token_stmt->bind_param("iss", $id, $token, $ip_address);
            $token_stmt->execute();
            $token_stmt->close();

            // Redirect to the homepage
            header("Location: index.php");
            exit();
        } else {
            // Incorrect password
            echo "Invalid mobile number or password.";
            header("refresh:2;url=login.php");
            exit();
        }
    } else {
        // User not found
        echo "Invalid mobile number or password.";
        header("refresh:2;url=login.php");
        exit();
    }

    $stmt->close();
    $conn->close();
}
