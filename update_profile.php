<?php  
require "./admin/function/_db.php";
if (!isset($_SESSION['user_id']) || $_SERVER["REQUEST_METHOD"] != "POST") {
    header("location: login");
    exit;
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['update_details'])) {
    $username = trim($_POST['username']);
    $mobile_no = trim($_POST['mobile_no']);
    $email = trim($_POST['email']);

    if (empty($username) || empty($mobile_no) || empty($email)) {
        $_SESSION['error_message'] = "All profile fields are required.";
        header("Location: profile");
        exit();
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = "Invalid email format.";
        header("Location: profile");
        exit();
    }
    if (!preg_match('/^[0-9]{10}$/', $mobile_no)) {
        $_SESSION['error_message'] = "Invalid mobile number format. Please enter 10 digits.";
        header("Location: profile");
        exit();
    }

    try {
        $check_stmt = $pdo->prepare("SELECT id, email, mobile_no FROM users WHERE (email = ? OR mobile_no = ?) AND id != ?");
        $check_stmt->execute([$email, $mobile_no, $user_id]);
        $existing_user = $check_stmt->fetch();

        if ($existing_user) {
            if ($existing_user['email'] === $email) {
                $_SESSION['error_message'] = "This email address is already registered to another account. Please use a different one.";
            } elseif ($existing_user['mobile_no'] === $mobile_no) {
                $_SESSION['error_message'] = "This mobile number is already registered to another account. Please use a different one.";
            }
            header("Location: profile"); 
            exit();
        }
        $sql = "UPDATE users SET username = ?, mobile_no = ?, email = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);

        if ($stmt->execute([$username, $mobile_no, $email, $user_id])) {
            $_SESSION['success_message'] = "Profile details updated successfully!";
            $_SESSION['username'] = $username;
        } else {
            $_SESSION['error_message'] = "Failed to update profile. Please try again.";
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "A database error occurred. Please try again later.";
        error_log("Profile Update Error: " . $e->getMessage());
    }
    header("Location: profile");
    exit();
}


// --- HANDLE PASSWORD UPDATE ---
if (isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['error_message'] = "Please fill in all password fields.";
    } elseif ($new_password !== $confirm_password) {
        $_SESSION['error_message'] = "New password and confirm password do not match.";
    } elseif (strlen($new_password) < 6) {
        $_SESSION['error_message'] = "New password must be at least 6 characters long.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            $hashed_password = $user ? $user['password'] : null;
            if ($hashed_password && password_verify($current_password, $hashed_password)) {
                $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update_stmt->execute([$new_password_hash, $user_id]);

                $_SESSION['success_message'] = "Password changed successfully!";
            } else {
                $_SESSION['error_message'] = "Incorrect current password.";
            }
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "A database error occurred while updating the password.";
            error_log("Password Update Error: " . $e->getMessage());
        }
    }
    header("Location: profile");
    exit();
}

header("Location: profile");
exit();
