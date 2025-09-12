<?php

require "./admin/function/_db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_identifier = $_POST['login_identifier'];
    $password = $_POST['password'];

    // FIX 1: Use two DIFFERENT named placeholders in the query
    $stmt = $_conn_db->prepare("SELECT id, username, email,  password, status FROM users WHERE mobile_no = :mobile OR email = :email");

    // FIX 2: Provide a value for BOTH placeholders in the execute() array
    $stmt->execute([
        ':mobile' => $login_identifier,
        ':email'  => $login_identifier
    ]);

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch();

        if (password_verify($password, $user['password'])) {
            if ($user['status'] != 1) {
                $_SESSION['error_message'] = "Your account is not active. Please contact support.";
                header("Location: login.php");
                exit();
            }

            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['mobile_no'] = $user['mobile_no'];

            $token = bin2hex(random_bytes(32));
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $_SESSION['login_token'] = $token;

            $token_stmt = $_conn_db->prepare("INSERT INTO users_login_token (user_id, token, ip_address, status) VALUES (:user_id, :token, :ip, 1)");
            $token_stmt->execute([
                ':user_id' => $user['id'],
                ':token' => $token,
                ':ip' => $ip_address
            ]);

            header("Location: index");
            exit();
        } else {
            $_SESSION['error_message'] = "Invalid credentials. Please try again.";
            header("Location: login");
            exit();
        }
    } else {
        $_SESSION['error_message'] = "Invalid credentials. Please try again.";
        header("Location: login");
        exit();
    }
}
