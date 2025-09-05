<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'db_connect.php';
 
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['login_token'])) {
    header("Location: login.php");
    exit();
}
 
$user_id = $_SESSION['user_id'];
$session_token = $_SESSION['login_token']; 
$stmt = $conn->prepare("SELECT status FROM users_login_token WHERE user_id = ? AND token = ?");
$stmt->bind_param("is", $user_id, $session_token);
$stmt->execute();
$stmt->store_result();
 
if ($stmt->num_rows > 0) { 
    $stmt->bind_result($status);
    $stmt->fetch();
 
    if ($status != 1) { 
        header("Location: logout.php");  
        exit();
    } 

} else {  
    header("Location: logout.php");
    exit();
}

$stmt->close();
?>