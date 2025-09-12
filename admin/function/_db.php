<?php
session_start();
global $_conn_db;
$db_s_host = '127.0.0.1';
$db_s_user = 'root';
$db_s_pass = '';
$db_s_name = 'bpl-bus-ticket';
$charset   = 'utf8mb4';

$dsn = "mysql:host=$db_s_host;dbname=$db_s_name;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // exceptions for errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // fetch associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // use real prepared statements
];

try {
    $_conn_db = new PDO($dsn, $db_s_user, $db_s_pass, $options);
    $pdo = $_conn_db;
    // Proceed as connected
} catch (PDOException $e) {
    error_log('PDO Connection failed: ' . $e->getMessage());
    exit('Database connection failed.');
}

// IP address utilities
$ip = $_SERVER['REMOTE_ADDR'];
$localIP = gethostbyaddr($ip);

$token = $_SESSION['user']['token'] ?? '';
include_once('common_function.php');

$rozerapi = 'rzp_test_xISbqnYlqqrWvs';
$rozersecretapi = 'RxquG8pfP9f5inluawqEAw92';
?>