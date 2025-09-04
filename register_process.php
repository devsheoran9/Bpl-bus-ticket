<?php
session_start();
require 'db_connect.php'; // Make sure this path is correct

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $mobile_no = $_POST['mobile_no'];
    $email = $_POST['email'] ?? null; // Email is optional
    $password = $_POST['password'];

    // --- NEW: Get the user's IP address ---
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if the mobile number already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE mobile_no = ?");
    $stmt->bind_param("s", $mobile_no);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Mobile number already exists
        echo "Error: This mobile number is already registered.";
        // You can redirect back with an error message
        header("refresh:2;url=register.php");
        exit();
    }
    $stmt->close();

    // --- UPDATED: Insert the new user with their IP address ---
    // The SQL query now includes the 'ip_address' column
    $stmt = $conn->prepare("INSERT INTO users (username, mobile_no, email, password, ip_address) VALUES (?, ?, ?, ?, ?)");

    // The bind_param now includes a fifth parameter 's' for the IP address string
    $stmt->bind_param("sssss", $username, $mobile_no, $email, $hashed_password, $ip_address);

    if ($stmt->execute()) {
        // Registration successful
        header("Location: login.php");
        exit();
    } else {
        // An error occurred
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
