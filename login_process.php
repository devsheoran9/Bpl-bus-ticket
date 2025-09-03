<?php
session_start();
require 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mobile_no = $_POST['mobile_no'];
    $password = $_POST['password'];
    $remember_me = isset($_POST['remember_me']);

    // Find user by mobile number
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE mobile_no = ?");
    $stmt->bind_param("s", $mobile_no);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $username, $hashed_password);
        $stmt->fetch();

        // Verify password
        if (password_verify($password, $hashed_password)) {
            // Correct password, start session
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;

            if ($remember_me) {
                // Generate and store login token
                $token = bin2hex(random_bytes(32)); // Generate a random token
                $hashed_token = password_hash($token, PASSWORD_DEFAULT);
                $ip_address = $_SERVER['REMOTE_ADDR'];

                // Insert token into the database
                $token_stmt = $conn->prepare("INSERT INTO user_login_token (user_id, token, ip_address) VALUES (?, ?, ?)");
                $token_stmt->bind_param("iss", $id, $hashed_token, $ip_address);
                $token_stmt->execute();
                $token_stmt->close();

                // Set cookies for "Remember Me"
                // The cookie stores the user ID and the unhashed token
                // The token in the cookie is compared against the hashed one in the DB
                setcookie("user_id", $id, time() + (86400 * 30), "/"); // 30 days
                setcookie("token", $token, time() + (86400 * 30), "/"); // 30 days
            }

            // Redirect to home page
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
