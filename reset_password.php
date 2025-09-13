<?php
require "./admin/function/_db.php";

$user_can_reset = false;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['otp'])) {
    $otp = $_POST['otp'];
    $email = $_SESSION['otp_email'] ?? null;

    if ($email && $otp) {
        // FIX: PDO syntax
        $stmt = $_conn_db->prepare("SELECT id, otp, otp_expires_at FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && $user['otp'] == $otp && strtotime($user['otp_expires_at']) > time()) {
            $_SESSION['password_reset_user_id'] = $user['id'];
            $user_can_reset = true;
        } else {
            $_SESSION['error_message'] = "Invalid or expired OTP.";
            header("Location: verify_otp");
            exit();
        }
    }
} elseif (isset($_SESSION['password_reset_user_id'])) {
    $user_can_reset = true;
}

if (!$user_can_reset) {
    header("Location: login");
    exit();
}

include 'includes/header.php';
?>

<div class="container my-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card">
                <div class="card-body p-4">
                    <h3 class="card-title text-center mb-4">Reset Your Password</h3>
                    <form action="update_password" method="POST">
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>