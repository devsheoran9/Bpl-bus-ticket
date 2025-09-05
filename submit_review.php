<?php
// submit_review.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db_connect.php'; 

// --- ROBUST CHECKS START HERE ---

// 1. Check if the user is logged in at all.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php?error=You must be logged in to post a review.");
    exit();
}

// 2. NEW: Check if the required session variables actually exist.
// This is the direct fix for your "Undefined array key" warnings.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username']) || !isset($_SESSION['email']) || !isset($_SESSION['mobile_no'])) {
    // If any are missing, the session is incomplete. Redirect with an error.
    header("Location: add_review.php?error=Your session is incomplete. Please log out and log back in.");
    exit();
}

// 3. Check if the request method is POST.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: add_review.php");
    exit();
}

// 4. Validate that form fields are not empty.
if (empty($_POST['rating']) || empty($_POST['review_text'])) {
    header("Location: add_review.php?error=Please provide a rating and a review message.");
    exit();
}

// --- DATA PROCESSING (NOW SAFE) ---

// Get secure user data from the SESSION. It's now safe to access these.
$userId = $_SESSION['user_id'];
$userName = $_SESSION['username']; 
$userEmail = $_SESSION['email'];
$userMobile = $_SESSION['mobile_no'];

// Get review data from the form.
$rating = (int)$_POST['rating'];
$reviewText = $conn->real_escape_string($_POST['review_text']);
$status = 1; // Default status is Active/Approved

// Validate rating range
if ($rating < 1 || $rating > 5) {
    header("Location: add_review.php?error=Invalid rating value.");
    exit();
}

// --- DATABASE ACTION (UNCHANGED, BUT NOW RECEIVES CORRECT DATA) ---

$stmt = $conn->prepare("INSERT INTO reviews (user_id, user_name, email, mobile, rating, review_text, status) VALUES (?, ?, ?, ?, ?, ?, ?)");

if ($stmt === false) {
    error_log("Database prepare failed: " . $conn->error);
    header("Location: add_review.php?error=An unexpected server error occurred.");
    exit();
}

$stmt->bind_param("isssisi", $userId, $userName, $userEmail, $userMobile, $rating, $reviewText, $status);

if ($stmt->execute()) {
    header("Location: add_review.php?review_success=1");
    exit();
} else {
    error_log("Review submission execute failed: " . $stmt->error);
    header("Location: add_review.php?error=Your review could not be saved at this time.");
    exit();
}

$stmt->close();
$conn->close();
?>