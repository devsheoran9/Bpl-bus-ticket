<?php
// Your header file correctly includes the database connection and session start.
include 'includes/header.php';

// This function call is kept as is.
echo user_login('page');

// Ensure user is logged in before proceeding.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user = null; // Initialize user variable

try {
    // Fetch the user data using PDO
    $stmt = $pdo->prepare("SELECT username, mobile_no, email FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    // In case of a database error, log it and handle gracefully.
    error_log("Profile Page DB Error: " . $e->getMessage());
    $user = null;
}

// If no user was found (or a DB error occurred), log out safely.
if (!$user) {
    header("location: logout.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <!-- Your existing CSS and Bootstrap from header.php -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>

<body style="background-color: #f8f9fa;">

    <main class="container my-5 pt-5">
        <div class="row">
            <div class="col-md-10 col-lg-8 mx-auto">
                <h1 class="text-center mb-4" style="color: #333; font-weight: 700;">My Account Profile</h1>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['success_message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['error_message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>

                <div class="row">
                    <!-- Profile Details Card -->
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <div class="card h-100" style="border: none; border-radius: 15px; box-shadow: 0 8px 25px rgba(0,0,0,0.07); overflow: hidden; position: relative;">
                            <div style="position: absolute; right: -30px; top: -20px; font-size: 150px; color: #e9ecef; opacity: 0.5; z-index: 1; transform: rotate(-15deg);"><i class="bi bi-person-circle"></i></div>
                            <div class="card-body p-4 p-lg-5" style="position: relative; z-index: 2;">
                                <h5 class="mb-4" style="font-weight: 600; color: #343a40;">Profile Details</h5>
                                <form action="update_profile.php" method="post">
                                    <div style="margin-bottom: 1.5rem; position: relative;">
                                        <label for="username" class="form-label" style="font-weight: 500; color: #6c757d;">Full Name</label>
                                        <i class="bi bi-person" style="position: absolute; left: 15px; top: 43px; color: #adb5bd;"></i>
                                        <input type="text" name="username" id="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required style="padding-left: 40px; border-radius: 8px;">
                                    </div>
                                    <div style="margin-bottom: 1.5rem; position: relative;">
                                        <label for="mobile_no" class="form-label" style="font-weight: 500; color: #6c757d;">Mobile Number</label>
                                        <i class="bi bi-phone" style="position: absolute; left: 15px; top: 43px; color: #adb5bd;"></i>
                                        <input type="text" name="mobile_no" id="mobile_no" class="form-control" value="<?php echo htmlspecialchars($user['mobile_no']); ?>" required style="padding-left: 40px; border-radius: 8px;">
                                    </div>
                                    <div style="margin-bottom: 1.5rem; position: relative;">
                                        <label for="email" class="form-label" style="font-weight: 500; color: #6c757d;">Email Address</label>
                                        <i class="bi bi-envelope" style="position: absolute; left: 15px; top: 43px; color: #adb5bd;"></i>
                                        <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required style="padding-left: 40px; border-radius: 8px;">
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" name="update_details" class="btn btn-primary" style="padding: 10px; border-radius: 8px; font-weight: 600;">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Change Password Card -->
                    <div class="col-lg-6">
                        <div class="card h-100" style="border: none; border-radius: 15px; box-shadow: 0 8px 25px rgba(0,0,0,0.07); overflow: hidden; position: relative;">
                            <div style="position: absolute; right: -30px; top: -20px; font-size: 150px; color: #e9ecef; opacity: 0.5; z-index: 1; transform: rotate(-15deg);"><i class="bi bi-shield-lock"></i></div>
                            <div class="card-body p-4 p-lg-5" style="position: relative; z-index: 2;">
                                <h5 class="mb-4" style="font-weight: 600; color: #343a40;">Change Password</h5>
                                <form action="update_profile.php" method="post">
                                    <div style="margin-bottom: 1.5rem; position: relative;">
                                        <label for="current_password" class="form-label" style="font-weight: 500; color: #6c757d;">Current Password</label>
                                        <i class="bi bi-key" style="position: absolute; left: 15px; top: 43px; color: #adb5bd;"></i>
                                        <input type="password" name="current_password" id="current_password" class="form-control" required style="padding-left: 40px; border-radius: 8px;">
                                    </div>
                                    <div style="margin-bottom: 1.5rem; position: relative;">
                                        <label for="new_password" class="form-label" style="font-weight: 500; color: #6c757d;">New Password</label>
                                        <i class="bi bi-lock" style="position: absolute; left: 15px; top: 43px; color: #adb5bd;"></i>
                                        <input type="password" name="new_password" id="new_password" class="form-control" required style="padding-left: 40px; border-radius: 8px;">
                                    </div>
                                    <div style="margin-bottom: 1.5rem; position: relative;">
                                        <label for="confirm_password" class="form-label" style="font-weight: 500; color: #6c757d;">Confirm New Password</label>
                                        <i class="bi bi-check2-circle" style="position: absolute; left: 15px; top: 43px; color: #adb5bd;"></i>
                                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required style="padding-left: 40px; border-radius: 8px;">
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" name="update_password" class="btn btn-primary" style="padding: 10px; border-radius: 8px; font-weight: 600;">Update Password</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>

</html>