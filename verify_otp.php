<?php
include 'includes/header.php';
if (!isset($_SESSION['otp_email'])) {
    header("Location: forgot_password.php");
    exit();
}
?>

<div class="container my-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card">
                <div class="card-body p-4">
                    <h3 class="card-title text-center mb-2">Verify OTP</h3>
                    <p class="text-muted text-center mb-4">Enter the 6-digit code sent to your email.</p>

                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-info"><?php echo $_SESSION['message'];
                                                        unset($_SESSION['message']); ?></div>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger"><?php echo $_SESSION['error_message'];
                                                        unset($_SESSION['error_message']); ?></div>
                    <?php endif; ?>

                    <!-- View OTP for testing (now using PDO). REMOVE this block in production! -->
                    <?php
                    $temp_email = $_SESSION['otp_email'];
                    // db_connect.php is included in header.php
                    $otp_stmt = $_conn_db->prepare("SELECT otp FROM users WHERE email = :email");
                    $otp_stmt->execute([':email' => $temp_email]);
                    $otp_result = $otp_stmt->fetch();
                    if ($otp_result && $otp_result['otp']) {
                        echo '<div class="alert alert-warning">TESTING: Your OTP is <strong>' . $otp_result['otp'] . '</strong></div>';
                    }
                    ?>

                    <form action="reset_password.php" method="POST">
                        <div class="mb-3">
                            <label for="otp" class="form-label">OTP Code</label>
                            <input type="text" class="form-control" id="otp" name="otp" required maxlength="6" pattern="\d{6}">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Verify & Proceed</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>