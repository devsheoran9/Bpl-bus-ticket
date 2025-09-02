<?php
// session_start(); // Assuming session_start() is already in _db.php or an included file
include_once('function/_db.php'); // Include database connection and helper functions
check_user_login(); // Ensure user is logged in

global $_conn_db; // Access the global PDO connection

$user_id = $_SESSION['user']['id']; // Get the logged-in user's ID

$message = '';
$msg_type = '';

// --- State 1: Password Verification (Gatekeeper) ---
// This section checks if the user has already verified their password for this session
if (!isset($_SESSION['profile_access_verified']) || !$_SESSION['profile_access_verified']) {
    // If not verified, handle the password verification form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_password'])) {
        $entered_password = $_POST['current_password'];

        try {
            // Fetch user's actual password hash from the database using their ID
            $stmt = $_conn_db->prepare("SELECT password FROM users WHERE id = :id");
            $stmt->execute([':id' => $user_id]);
            $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user_data && password_verify($entered_password, $user_data['password'])) {
                $_SESSION['profile_access_verified'] = true; // Mark as verified for this session
                // Redirect to the same page to display the update form
                header("Location: change_profile.php");
                exit();
            } else {
                $message = 'Invalid password. Please try again.';
                $msg_type = 'danger';
            }
        } catch (PDOException $e) {
            error_log("Password verification error: " . $e->getMessage());
            $message = 'An error occurred during verification. Please try again.';
            $msg_type = 'danger';
        }
    }

    // If not verified, or verification failed, display the password verification form
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <?php include_once('head.php'); ?>
        <title>Verify Access - Update Profile</title>
        <style>
            body { font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f4f6f9; color: #343a40; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
            .verification-card { background-color: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 6px 20px rgba(0,0,0,0.1); width: 100%; max-width: 400px; text-align: center; }
            .verification-card h2 { color: #007bff; margin-bottom: 25px; font-weight: 600; }
            .verification-card .form-group { margin-bottom: 20px; text-align: left; }
            .verification-card label { display: block; margin-bottom: 8px; font-weight: 500; color: #495057; }
            .verification-card input[type="password"] { width: 100%; padding: 12px 15px; border: 1px solid #ced4da; border-radius: 8px; box-sizing: border-box; font-size: 1rem; }
            .verification-card input[type="password"]:focus { border-color: #007bff; box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25); outline: none; }
            .verification-card .btn-primary { background-color: #007bff; border-color: #007bff; color: white; padding: 12px 25px; border-radius: 8px; cursor: pointer; font-size: 1rem; width: 100%; transition: all 0.3s ease; }
            .verification-card .btn-primary:hover { background-color: #0056b3; border-color: #004d9c; transform: translateY(-1px); box-shadow: 0 4px 10px rgba(0,123,255,0.2); }
            .alert { padding: 15px; margin-bottom: 20px; border-radius: 8px; font-weight: 500; text-align: left; }
            .alert-danger { color: #842029; background-color: #f8d7da; border-color: #f5c2c7; }
        </style>
    </head>
    <body>
        <div class="verification-card">
            <h2>Verify Your Identity</h2>
            <?php
            if ($message) {
                echo '<div class="alert alert-' . $msg_type . '">' . $message . '</div>';
            }
            ?>
            <p>Please enter your current password to access profile settings.</p>
            <form action="change_profile.php" method="POST">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required autofocus>
                </div>
                <button type="submit" name="verify_password" class="btn-primary">Verify</button>
            </form>
        </div>
        <?php include_once('foot.php'); ?>
    </body>
    </html>
    <?php
    exit(); // Stop execution here if not verified
}

// --- State 2: Profile Update Form (After successful verification) ---

// Fetch current user data from the database
$user = [];
try {
    $stmt = $_conn_db->prepare("SELECT name, mobile, email FROM users WHERE id = :id");
    $stmt->execute([':id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        // User not found, which should ideally not happen after check_user_login()
        $_SESSION['message'] = 'User data not found.';
        $_SESSION['msg_type'] = 'danger';
        header("Location: dashboard.php"); // Redirect to a safe page
        exit();
    }
} catch (PDOException $e) {
    error_log("Error fetching user data for profile: " . $e->getMessage());
    $_SESSION['message'] = 'Error loading profile data.';
    $_SESSION['msg_type'] = 'danger';
    header("Location: dashboard.php");
    exit();
}


// Handle profile update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $current_password_for_update = $_POST['current_password_for_update'];
    $new_name = trim($_POST['name']);
    $new_mobile = trim($_POST['mobile']);
    $new_email = trim($_POST['email']);
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];

    // Input Validation
    if (empty($new_name) || empty($new_mobile) || empty($new_email) || empty($current_password_for_update)) {
        $message = 'All fields (Name, Mobile, Email, Current Password) are required.';
        $msg_type = 'danger';
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email format.';
        $msg_type = 'danger';
    } elseif (!preg_match('/^[0-9]{10}$/', $new_mobile)) { // Simple 10-digit mobile validation
        $message = 'Mobile number must be 10 digits.';
        $msg_type = 'danger';
    } else {
        // First, verify the current password one more time before applying changes
        try {
            $stmt = $_conn_db->prepare("SELECT password FROM users WHERE id = :id");
            $stmt->execute([':id' => $user_id]);
            $db_user_data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$db_user_data || !password_verify($current_password_for_update, $db_user_data['password'])) {
                $message = 'Current password is incorrect. No changes were saved.';
                $msg_type = 'danger';
            } else {
                // Current password verified, proceed with updates
                $update_fields = [];
                $update_params = [':id' => $user_id];

                // Update name, mobile, email
                $update_fields[] = 'name = :name';
                $update_params[':name'] = $new_name;
                $update_fields[] = 'mobile = :mobile';
                $update_params[':mobile'] = $new_mobile;
                $update_fields[] = 'email = :email';
                $update_params[':email'] = $new_email;

                // Handle new password if provided
                if (!empty($new_password)) {
                    if ($new_password !== $confirm_new_password) {
                        $message = 'New password and confirm new password do not match.';
                        $msg_type = 'danger';
                    } elseif (strlen($new_password) < 6) { // Example password strength
                        $message = 'New password must be at least 6 characters long.';
                        $msg_type = 'danger';
                    } else {
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $update_fields[] = 'password = :password';
                        // password_hash internally manages salt, so password_salt column might not be explicitly needed for modern hashes
                        // If your system uses a separate salt column with a custom hashing method, you'd add:
                        // $update_fields[] = 'password_salt = :password_salt';
                        // $update_params[':password_salt'] = $new_salt; // Assuming you generate a new salt
                        $update_params[':password'] = $hashed_password;
                    }
                }

                if ($message === '') { // Only proceed if no password mismatch/strength errors
                    if (!empty($update_fields)) {
                        $update_query = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE id = :id";
                        $stmt = $_conn_db->prepare($update_query);

                        if ($stmt->execute($update_params)) {
                            // Update session data immediately after successful DB update
                            $_SESSION['user']['name'] = $new_name;
                            $_SESSION['user']['mobile'] = $new_mobile;
                            $_SESSION['user']['email'] = $new_email;

                            $message = 'Profile updated successfully!';
                            $msg_type = 'success';
                            // Refresh user data from DB to ensure form fields are up-to-date
                            $user['name'] = $new_name;
                            $user['mobile'] = $new_mobile;
                            $user['email'] = $new_email;
                        } else {
                            $message = 'Failed to update profile. Please try again.';
                            $msg_type = 'danger';
                        }
                    } else {
                        $message = 'No changes detected to update.';
                        $msg_type = 'info';
                    }
                }
            }
        } catch (PDOException $e) {
            error_log("Profile update error: " . $e->getMessage());
            $message = 'An error occurred during the update. Please try again.';
            $msg_type = 'danger';
        }
    }

    // Store message in session for redirection (if any) or display on the current page
    $_SESSION['message'] = $message;
    $_SESSION['msg_type'] = $msg_type;
    // Redirect to self to prevent form resubmission on refresh
    header("Location: change_profile.php");
    exit();
}

// Retrieve any session message after a redirect
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $msg_type = $_SESSION['msg_type'];
    unset($_SESSION['message']);
    unset($_SESSION['msg_type']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once('head.php'); ?>
    <title>Update Profile</title>    
    <style>
        /* General Body and Container Styling */
        body {
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f4f6f9; /* Lighter background for the entire page */
            color: #343a40; /* Darker text for better readability */
        }

        .container-fluid {
            padding: 20px 25px; /* Adjust padding for inner content */
        }

        /* Header/Title Section */
        .page-header {
            background-color: #fff;
            padding: 20px 25px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
        }

        .page-header h2 {
            color: #007bff; /* Primary blue for the main heading */
            font-weight: 600;
            margin: 0;
            flex-grow: 1;
        }

        @media (max-width: 768px) {
            .page-header h2 {
                margin-bottom: 15px;
                width: 100%;
            }
        }

        /* Alert Styling */
        .alert {
            padding: 15px 20px;
            margin-bottom: 25px;
            border-radius: 8px;
            font-weight: 500;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .alert-success {
            color: #0f5132;
            background-color: #d1e7dd;
            border-color: #badbcc;
        }

        .alert-danger {
            color: #842029;
            background-color: #f8d7da;
            border-color: #f5c2c7;
        }
        .alert-info {
            color: #055160;
            background-color: #cff4fc;
            border-color: #b6effb;
        }

        /* Card Styling for the form */
        .card {
            border: none;
            box-shadow: 0 4px 18px rgba(0,0,0,0.1);
            border-radius: 12px;
            background-color: #fff;
            padding: 25px; /* Inner padding for the card */
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #495057;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ced4da;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 1rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            outline: none;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            width: auto;
            min-width: 120px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 5px rgba(0,123,255,0.2);
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004d9c;
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(0,123,255,0.3);
        }
        .btn-primary i {
            margin-left: 8px;
            transition: transform 0.3s ease;
        }
        .btn-primary:hover i {
            transform: translateX(3px);
        }

        /* Media Queries for Responsiveness */
        @media (max-width: 768px) {
            .container-fluid {
                padding: 15px;
            }
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                padding: 15px;
            }
            .page-header h2 {
                font-size: 1.5rem;
                margin-bottom: 15px;
            }
            .card {
                padding: 15px;
            }
            .form-control {
                padding: 10px 12px;
                font-size: 0.95rem;
            }
            .btn-primary {
                width: 100%;
                padding: 12px;
                font-size: 1.1rem;
            }
        }

        @media (max-width: 480px) {
            .container-fluid {
                padding: 10px;
            }
            .page-header h2 {
                font-size: 1.3rem;
            }
            .card {
                padding: 10px;
            }
        }
    </style>
</head>
<body>

<div id="wrapper">
    <?php include_once('sidebar.php'); ?>
    <div class="main-content">
        <?php include_once('header.php'); ?>
        <div class="container-fluid">
            <div class="page-header">
                <h2>Update Profile</h2>
            </div>

            <?php
            if ($message) {
                echo '<div class="alert alert-' . $msg_type . '">' . $message . '</div>';
            }
            ?>

            <div class="card">
                <form action="change_profile.php" method="POST">
                    <div class="row">

                   
                    <div class="form-group col-md-6">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="mobile">Mobile Number</label>
                        <input type="tel" id="mobile" name="mobile" class="form-control" value="<?php echo htmlspecialchars($user['mobile']); ?>" required pattern="[0-9]{10}" title="Mobile number must be 10 digits">
                    </div>
                    <div class="form-group col-md-12">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
<div class="col-md-12">
                    <hr style="margin: 30px 0; border-top: 1px solid #e0e0e0;">

                    <p style="font-weight: 500; color: #495057;">Change Password (Leave blank if you don't want to change)</p></div>
                    <div class="form-group col-md-6">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Enter new password (min 6 characters)">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="confirm_new_password">Confirm New Password</label>
                        <input type="password" id="confirm_new_password" name="confirm_new_password" class="form-control" placeholder="Confirm new password">
                    </div>
<div class="col-md-12">
                    <hr style="margin: 30px 0; border-top: 1px solid #e0e0e0;">
                    </div>
                    <p style="font-weight: 500; color: #dc3545;">*Current password is required to save any changes.</p>
                    <div class="form-group col-md-12">
                        <label for="current_password_for_update">Current Password <span style="color: #dc3545;">*</span></label>
                        <input type="password" id="current_password_for_update" name="current_password_for_update" class="form-control" required placeholder="Enter your current password">
                    </div>
<div class="col-md-12">
                    <button type="submit" name="update_profile" class="btn-primary">
                        Update Profile <i class="fas fa-arrow-right"></i>
                    </button>
                    </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include_once('foot.php'); ?>
</body>
</html>
<?php pdo_close_conn($_conn_db); ?>