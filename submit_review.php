<?php
// submit_review.php

// 1. Start the session to access session variables
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Include your database connection file
include 'db_connect.php';

// 3. SECURITY: Check if the user is actually logged in.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php?error=You must be logged in to post a review.");
    exit();
}

// 4. SECURITY: Check if the request method is POST.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: add_review.php");
    exit();
}

// 5. VALIDATION: Check if essential form fields are empty.
if (empty($_POST['rating']) || empty($_POST['review_text'])) {
    header("Location: add_review.php?error=Please provide a rating and a review message.");
    exit();
}

// 6. DATA PROCESSING: Get secure user data from the SESSION.
// This uses the trusted data from the session, not the editable form fields.
$userId = $_SESSION['user_id'];
$userName = $_SESSION['username'];
// --- ADDED ---: Get email and mobile from the session
$userEmail = $_SESSION['email'];
$userMobile = $_SESSION['mobile_no'];

// 7. DATA PROCESSING: Get review data from the form.
$rating = (int)$_POST['rating']; // Cast to integer for safety
$reviewText = $conn->real_escape_string($_POST['review_text']); // Sanitize for SQL

// 8. VALIDATION: Ensure the rating is within the correct range (1-5).
if ($rating < 1 || $rating > 5) {
    header("Location: add_review.php?error=Invalid rating value.");
    exit();
}

// 9. DATABASE ACTION: Prepare the SQL INSERT statement.
// --- UPDATED ---: The SQL query now includes the new email and mobile columns.
$stmt = $conn->prepare("INSERT INTO reviews (user_id, user_name, email, mobile, rating, review_text) VALUES (?, ?, ?, ?, ?, ?)");

// Check if the statement preparation failed
if ($stmt === false) {
    error_log("Database prepare failed: " . $conn->error);
    header("Location: add_review.php?error=An unexpected server error occurred.");
    exit();
}

// --- UPDATED ---: Bind the new email and mobile variables to the statement.
// The types string is now "isssis" for (i)nt, (s)tring, (s)tring, (s)tring, (i)nt, (s)tring
$stmt->bind_param("isssis", $userId, $userName, $userEmail, $userMobile, $rating, $reviewText);

// 10. FINAL FEEDBACK: Execute the statement and redirect based on the result.
if ($stmt->execute()) {
    // Success! Redirect back to the form with a success message.
    header("Location: add_review.php?review_success=1");
    exit();
} else {
    // Failure! Log the error and redirect back with a generic error message.
    error_log("Review submission execute failed: " . $stmt->error);
    header("Location: add_review.php?error=Your review could not be saved at this time.");
    exit();
}

// 11. Clean up
$stmt->close();
$conn->close();
