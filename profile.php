<?php
// Your header file correctly includes the database connection and session start.
include 'includes/header.php';

// This function call is kept as is.
echo user_login('page');

// Ensure user is logged in before proceeding.
if (!isset($_SESSION['user_id'])) {
    header("Location: login");
    exit();
}

$user_id = $_SESSION['user_id'];
$user = null; // Initialize user variable

try {
    // --- DATABASE LOGIC CONVERTED FROM MYSQLi TO PDO ---

    // 1. Prepare the SQL statement using the PDO object ($pdo) from your header.
    $stmt = $pdo->prepare("SELECT username, mobile_no, email FROM users WHERE id = ?");

    // 2. Execute the statement with parameters passed as an array.
    $stmt->execute([$user_id]);

    // 3. Fetch the user data directly.
    $user = $stmt->fetch();

    // --- END OF CONVERTED BLOCK ---

} catch (PDOException $e) {
    // In case of a database error, you can handle it gracefully.
    // For now, we will just ensure the user is logged out safely.
    // You could also set an error message to display.
    $user = null;
}


// If no user was found (or a DB error occurred), log out safely.
if (!$user) {
    header("location: logout");
    exit;
}
?>

<main class="container my-5 pt-5">
    <div class="row">
        <div class="col-md-10 col-lg-8 mx-auto">
            <h1 class="text-center mb-4">My Account Profile</h1>

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
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">Profile Details</h5>
                        </div>
                        <div class="card-body">
                            <form action="update_profile" method="post">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Full Name</label>
                                    <input type="text" name="username" id="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="mobile_no" class="form-label">Mobile Number</label>
                                    <input type="text" name="mobile_no" id="mobile_no" class="form-control" value="<?php echo htmlspecialchars($user['mobile_no']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                                <button type="submit" name="update_details" class="btn btn-primary w-100">Save Changes</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">Change Password</h5>
                        </div>
                        <div class="card-body">
                            <form action="update_profile" method="post">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" name="current_password" id="current_password" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" name="new_password" id="new_password" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                                </div>
                                <button type="submit" name="update_password" class="btn btn-primary w-100">Update Password</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>