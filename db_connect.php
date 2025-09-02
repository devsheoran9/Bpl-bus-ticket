<?php
// Database configuration
$dbHost = 'localhost';
$dbUsername = 'root'; // Ang iyong database username
$dbPassword = ''; // Ang iyong database password
$dbName = 'bpl-bus-ticket'; // Ang pangalan ng iyong database

// Gumawa ng koneksyon sa database
$conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

// Suriin ang koneksyon
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>