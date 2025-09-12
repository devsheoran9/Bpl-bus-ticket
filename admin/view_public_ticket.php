<?php
// view_public_ticket.php (FIXED AND SECURE VERSION)

// Step 1: Include the database connection file first.
include_once('function/_db.php');

// Use a secure and modern way to validate the token.
// A token should only contain hexadecimal characters (a-f, 0-9).
$token = $_GET['token'] ?? ''; // Get the token from the URL

if (!preg_match('/^[a-f0-9]{32}$/', $token)) {
    // If the token is not a 32-character hexadecimal string, it's invalid.
    die("Access token format is incorrect.");
}

try {
    // Find the booking_id associated with the provided token
    $stmt = $_conn_db->prepare("SELECT booking_id FROM ticket_access_tokens WHERE token = ?");
    $stmt->execute([$token]);
    $booking_id = $stmt->fetchColumn();

    if ($booking_id) {
        // Token is valid. Instead of redirecting, we will now INCLUDE the PDF generator.
        // The $booking_id and $_conn_db variables will be automatically available to the included file.
        // The user's URL will remain ...view_public_ticket.php?token=... which is secure.
        
        include 'generate_ticket1.php'; // The beautiful TCPDF version
        
        exit(); // Stop the script here after the PDF has been generated and sent.

    } else {
        // If the token is not found in the database
        die("This ticket link is invalid or has expired.");
    }
} catch (PDOException $e) {
    // In a production environment, you should log this error
    
    // --- FIX: Corrected the string concatenation ---
    error_log("Public Ticket View Error: " . $e->getMessage());
    
    die("A database error occurred. Please try again later.");
}
?>