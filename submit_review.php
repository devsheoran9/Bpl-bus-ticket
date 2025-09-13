<?php  
require 'admin/function/_db.php'; 

// 1. Check if the user is logged in.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['user_id'])) {
    header("Location: login?error=You must be logged in to post a review.");
    exit();
}

// 2. Check if the request method is POST.
if ($_SERVER["REQUEST_METHOD"] !== "POST") { 
    header("Location: index"); 
    exit();
}

// 3. Validate that essential form fields are not empty.
if (empty($_POST['rating']) || empty(trim($_POST['review_text']))) {
    header("Location: add_review?error=Please provide a rating and a review message.");
    exit();
}

// 4. Get review data from the form.
$userId = $_SESSION['user_id'];
$rating = (int)$_POST['rating']; 
$reviewText = $_POST['review_text']; // No need for real_escape_string with PDO prepared statements
$status = 1; // Default status is Active/Approved

// 5. Validate rating range.
if ($rating < 1 || $rating > 5) {
    header("Location: add_review?error=Invalid rating value.");
    exit();
}

// Use a try-catch block for PDO database operations to handle errors gracefully.
try {
    // --- FETCH USER DATA DIRECTLY FROM DATABASE USING PDO ---
    
    // 6. Prepare a query to fetch the user's details.
    $stmt_user = $pdo->prepare("SELECT username, email, mobile_no FROM users WHERE id = ?");
    
    // 7. Execute the query, passing the user ID.
    $stmt_user->execute([$userId]);
    
    // 8. Fetch the user data. fetch() returns false if no user is found.
    $user_data = $stmt_user->fetch(PDO::FETCH_ASSOC);

    if (!$user_data) {
        header("Location: add_review.php?error=Could not find your user account details.");
        exit();
    }

    // 9. Store fetched data in variables.
    $userName = $user_data['username'];
    $userEmail = $user_data['email'];
    $userMobile = $user_data['mobile_no'];

    // --- INSERT THE REVIEW INTO THE DATABASE USING PDO ---

    // 10. Prepare the INSERT statement for the 'reviews' table.
    $stmt_review = $pdo->prepare(
        "INSERT INTO reviews (user_id, user_name, email, mobile, rating, review_text, status) 
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );

    // 11. Execute the statement with all the required data in an array.
    // The order of elements in the array must match the order of the placeholders (?).
    $success = $stmt_review->execute([
        $userId, 
        $userName, 
        $userEmail, 
        $userMobile, 
        $rating, 
        $reviewText, 
        $status
    ]);
    
    // 12. Redirect based on success or failure.
    if ($success) {
        header("Location: add_review?review_success=1");
        exit();
    } else {
        // This 'else' might not be reached if exceptions are on, but it's good for defense.
        header("Location: add_review?error=Your review could not be saved at this time.");
        exit();
    }

} catch (PDOException $e) {
    // If any database error occurs, log it and show a generic error to the user.
    error_log("Review Submission PDO Error: " . $e->getMessage());
    header("Location: add_review?error=An unexpected server error occurred.");
    exit();
}