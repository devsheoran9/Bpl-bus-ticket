<?php include 'includes/header.php'; ?>
<?php 
require 'db_connect.php';
 
// 2. Fetch the current user's data from the database
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, mobile_no, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $mobile_no, $email);
$stmt->fetch();
$stmt->close();

// If for some reason user data is not found, log them out
if (empty($username)) {
    header("location: logout.php");
    exit;
}
?>



<main class="container my-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <h1 class="mb-4 text-center text-primary">My Account Profile</h1>

            <!-- Display Success/Error Messages from the update process -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success_message']; ?></div>
                <?php unset($_SESSION['success_message']); // Clear the message after displaying ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error_message']; ?></div>
                <?php unset($_SESSION['error_message']); // Clear the message after displaying ?>
            <?php endif; ?>

            <div class="container">
                <div class="row">
                    <div class="col-lg-6">  <!-- Profile Details Update Form -->
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Profile Details</h5></div>
                <div class="card-body">
                    <form action="update_profile.php" method="post">
                        <div class="mb-3">
                            <label for="username" class="form-label">Full Name</label>
                            <input type="text" name="username" id="username" class="form-control" value="<?php echo htmlspecialchars($username); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="mobile_no" class="form-label">Mobile Number</label>
                            <input type="text" name="mobile_no" id="mobile_no" class="form-control" value="<?php echo htmlspecialchars($mobile_no); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>
                        <button type="submit" name="update_details" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
</div>
                    <div class="col-lg-6"> <!-- Change Password Form -->
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Change Password</h5></div>
                <div class="card-body">
                    <form action="update_profile.php" method="post">
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
                        <button type="submit" name="update_password" class="btn btn-primary">Update Password</button>
                    </form>
                </div>
            </div></div>
                </div>
            </div>

          
           
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>