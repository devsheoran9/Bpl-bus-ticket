<?php
session_start();
require 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mobile_no = $_POST['mobile_no'];
    $password = $_POST['password'];

    // Maghanap ng user ayon sa mobile number
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE mobile_no = ?");
    $stmt->bind_param("s", $mobile_no);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $username, $hashed_password);
        $stmt->fetch();

        // I-verify ang password
        if (password_verify($password, $hashed_password)) {
            // Tama ang password, simulan ang session
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            
            // Mag-redirect sa home page
            header("Location: index.php");
            exit();
        } else {
            // Maling password
            echo "Invalid mobile number or password.";
            header("refresh:2;url=login.php");
            exit();
        }
    } else {
        // Hindi nahanap ang user
        echo "Invalid mobile number or password.";
        header("refresh:2;url=login.php");
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>