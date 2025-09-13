<?php include 'includes/header.php'; ?>

<body>
    <div class="container my-5 pt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card">
                    <div class="card-body p-4">
                        <h3 class="card-title text-center mb-2">Forgot Password</h3>
                        <p class="text-muted text-center mb-4">Enter your email to receive a password reset OTP.</p>

                        <?php if (isset($_SESSION['message'])): ?>
                            <div class="alert alert-info"><?php echo $_SESSION['message'];
                                                            unset($_SESSION['message']); ?></div>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['error_message'])): ?>
                            <div class="alert alert-danger"><?php echo $_SESSION['error_message'];
                                                            unset($_SESSION['error_message']); ?></div>
                        <?php endif; ?>

                        <form action="send_otp" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Send OTP</button>
                            <p class="text-center mt-3">
                                Remember your password? <a href="login">Login</a>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<br><br><br><br><br>
    <?php include 'includes/footer.php'; ?>

</body>

</html>