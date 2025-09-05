<?php
// submit_review.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db_connect.php';

// 1. Check if the user is logged in and their user_id is set in the session.
// This is the only session data we need.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['user_id'])) {
    header("Location: login.php?error=You must be logged in to post a review.");
    exit();
}

// 2. Check if the request method is POST.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: add_review.php");
    exit();
}

// 3. Validate that essential form fields are not empty.
if (empty($_POST['rating']) || empty($_POST['review_text'])) {
    header("Location: add_review.php?error=Please provide a rating and a review message.");
    exit();
}

// --- NEW LOGIC: FETCH USER DATA DIRECTLY FROM DATABASE ---

// 4. Get the logged-in user's ID from the session.
$userId = $_SESSION['user_id'];

// 5. Prepare a query to fetch the user's details from the 'users' table.
$stmt_user = $conn->prepare("SELECT username, email, mobile_no FROM users WHERE id = ?");
if ($stmt_user === false) {
    error_log("User data prepare failed: " . $conn->error);
    header("Location: add_review.php?error=Server error: Could not fetch user data.");
    exit();
}

$stmt_user->bind_param("i", $userId);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

// 6. Check if a user with that ID was actually found.
if ($result_user->num_rows !== 1) {
    header("Location: add_review.php?error=Could not find your user account details.");
    exit();
}

// 7. Fetch the data and store it in variables.
$user_data = $result_user->fetch_assoc();
$userName = $user_data['username'];
$userEmail = $user_data['email'];
$userMobile = $user_data['mobile_no'];
$stmt_user->close(); // Clean up the user statement.

// --- END OF NEW LOGIC ---


// 8. Get review data from the form.
$rating = (int)$_POST['rating'];
$reviewText = $conn->real_escape_string($_POST['review_text']);
$status = 1; // Default status is Active/Approved

// 9. Validate rating range.
if ($rating < 1 || $rating > 5) {
    header("Location: add_review.php?error=Invalid rating value.");
    exit();
}

// 10. Prepare and execute the INSERT statement for the 'reviews' table.
$stmt_review = $conn->prepare("INSERT INTO reviews (user_id, user_name, email, mobile, rating, review_text, status) VALUES (?, ?, ?, ?, ?, ?, ?)");

if ($stmt_review === false) {
    error_log("Review insert prepare failed: " . $conn->error);
    header("Location: add_review.php?error=An unexpected server error occurred.");
    exit();
}

$stmt_review->bind_param("isssisi", $userId, $userName, $userEmail, $userMobile, $rating, $reviewText, $status);

// 11. Redirect based on success or failure.
if ($stmt_review->execute()) {
    header("Location: add_review.php?review_success=1");
    exit();
} else {
    error_log("Review submission execute failed: " . $stmt_review->error);
    header("Location: add_review.php?error=Your review could not be saved at this time.");
    exit();
}

$stmt_review->close();
$conn->close();
