<?php
// Database configuration
$dbHost = 'localhost';
$dbUsername = 'root';
$dbPassword = '';
$dbName = 'bpl-bus-ticket';

// Gumawa ng koneksyon sa database
$conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

// Suriin ang koneksyon
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
<?php if (session_status() === PHP_SESSION_NONE) {
    session_start();
} ?>