<?php
// Include the database connection file which starts the session and creates the $pdo object.
require "./admin/function/_db.php";

// Security check: Ensure user is logged in and the request is a POST.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SERVER["REQUEST_METHOD"] != "POST") {
    header("location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// --- HANDLE PROFILE DETAILS UPDATE ---
if (isset($_POST['update_details'])) {
    $username = trim($_POST['username']);
    $mobile_no = trim($_POST['mobile_no']);
    $email = trim($_POST['email']);

    // Validation remains the same.
    if (empty($username) || empty($mobile_no) || empty($email)) {
        $_SESSION['error_message'] = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = "Invalid email format.";
    } else {
        // --- DATABASE LOGIC CONVERTED TO PDO ---
        try {
            $sql = "UPDATE users SET username = ?, mobile_no = ?, email = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);

            // Execute the statement. It returns true on success or throws a PDOException on failure.
            $stmt->execute([$username, $mobile_no, $email, $user_id]);

            $_SESSION['success_message'] = "Profile details updated successfully!";
            // Also update the username in the session for immediate display in the header.
            $_SESSION['username'] = $username;
        } catch (PDOException $e) {
            // Catch database errors.
            $_SESSION['error_message'] = "Error updating record: " . $e->getMessage();
        }
    }
    // Redirect back to the profile page to show messages.
    header("Location: profile.php");
    exit();
}

// --- HANDLE PASSWORD UPDATE ---
if (isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation remains the same.
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['error_message'] = "Please fill in all password fields.";
    } elseif ($new_password !== $confirm_password) {
        $_SESSION['error_message'] = "New password and confirm password do not match.";
    } else {
        // --- DATABASE LOGIC CONVERTED TO PDO ---
        try {
            // First, fetch the current hashed password from the database.
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            $hashed_password = $user ? $user['password'] : null;

            // Verify the current password.
            if ($hashed_password && password_verify($current_password, $hashed_password)) {

                // Hash the new password.
                $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

                // Prepare and execute the update statement.
                $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update_stmt->execute([$new_password_hash, $user_id]);

                $_SESSION['success_message'] = "Password changed successfully!";
            } else {
                $_SESSION['error_message'] = "Incorrect current password.";
            }
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Error updating password: " . $e->getMessage();
        }
    }
    // Redirect back to the profile page.
    header("Location: profile.php");
    exit();
}

// If the script is accessed without a valid form submission, just redirect.
header("Location: profile.php");
exit();
