<?php
include 'includes/header.php';
if (!isset($_SESSION['otp_email'])) {
    header("Location: forgot_password.php");
    exit();
}

// --- NEW: Fetch the OTP expiry time to pass to JavaScript ---
$otp_expiry_js = 0;
$otp_email = $_SESSION['otp_email'];
try {
    // db_connect.php is included in header.php
    $stmt = $_conn_db->prepare("SELECT otp_expires_at FROM users WHERE email = :email");
    $stmt->execute([':email' => $otp_email]);
    $result = $stmt->fetch();
    if ($result && $result['otp_expires_at']) {
        // Convert the expiry time to a JavaScript-friendly timestamp (milliseconds)
        $otp_expiry_js = strtotime($result['otp_expires_at']) * 1000;
    }
} catch (Exception $e) {
    // Handle error if needed, but the page can still load
    error_log("Could not fetch OTP expiry: " . $e->getMessage());
}
?>

<div class="container my-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card">
                <div class="card-body p-4">
                    <h3 class="card-title text-center mb-2">Verify OTP</h3>
                    <p class="text-muted text-center mb-4">Enter the 6-digit code sent to your email.</p>

                    <div id="message-container">
                        <?php if (isset($_SESSION['message'])): ?>
                            <div class="alert alert-info"><?php echo $_SESSION['message'];
                                                            unset($_SESSION['message']); ?></div>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['error_message'])): ?>
                            <div class="alert alert-danger"><?php echo $_SESSION['error_message'];
                                                            unset($_SESSION['error_message']); ?></div>
                        <?php endif; ?>
                    </div>

                    <form action="reset_password.php" method="POST" id="otp-form">
                        <div class="mb-3">
                            <label for="otp" class="form-label">OTP Code</label>
                            <input type="text" class="form-control form-control-lg text-center" id="otp" name="otp" required maxlength="6" pattern="\d{6}" style="letter-spacing: 0.5em;">
                        </div>

                        <!-- NEW: Timer and Resend Button container -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span id="timer" class="text-muted"></span>
                            <button type="button" id="resend-btn" class="btn btn-link" style="display: none;">Resend OTP</button>
                        </div>

                        <button type="submit" id="submit-btn" class="btn btn-primary w-100">Verify & Proceed</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<br><br><br><br><br>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const otpExpiryTimestamp = <?php echo $otp_expiry_js; ?>;
        const timerEl = document.getElementById('timer');
        const submitBtn = document.getElementById('submit-btn');
        const resendBtn = document.getElementById('resend-btn');
        const otpInput = document.getElementById('otp');
        const messageContainer = document.getElementById('message-container');

        let timerInterval;

        function startTimer(expiryTime) {
            // Clear any existing timer
            clearInterval(timerInterval);

            // Show the timer, hide the resend button
            timerEl.style.display = 'inline';
            resendBtn.style.display = 'none';
            submitBtn.disabled = false;
            otpInput.disabled = false;

            timerInterval = setInterval(() => {
                const now = new Date().getTime();
                const distance = expiryTime - now;

                if (distance < 0) {
                    clearInterval(timerInterval);
                    timerEl.innerHTML = "<span class='text-danger'>OTP Expired</span>";
                    submitBtn.disabled = true; // Disable verification
                    resendBtn.style.display = 'inline'; // Show resend button
                    return;
                }

                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                // Add leading zero if seconds is less than 10
                const displaySeconds = seconds < 10 ? '0' + seconds : seconds;
                timerEl.textContent = `Expires in: ${minutes}:${displaySeconds}`;
            }, 1000);
        }

        resendBtn.addEventListener('click', function() {
            this.disabled = true;
            this.textContent = 'Sending...';
            messageContainer.innerHTML = ''; // Clear old messages

            fetch('resend_otp.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        messageContainer.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                        // Restart the timer with the new expiry time from the server
                        startTimer(data.otp_expires_at);
                    } else {
                        messageContainer.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    messageContainer.innerHTML = '<div class="alert alert-danger">An unexpected error occurred. Please try again.</div>';
                })
                .finally(() => {
                    this.disabled = false;
                    this.textContent = 'Resend OTP';
                });
        });

        // Initial call to start the timer when the page loads
        if (otpExpiryTimestamp > 0) {
            startTimer(otpExpiryTimestamp);
        } else {
            timerEl.innerHTML = "<span class='text-danger'>OTP Expired</span>";
            submitBtn.disabled = true;
            resendBtn.style.display = 'inline';
        }
    });
</script>

    