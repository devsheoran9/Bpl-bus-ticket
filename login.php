<?php include 'includes/header.php'; ?>

<body>
    <div class="container my-5 pt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card">
                    <div class="card-body p-4">
                        <h3 class="card-title text-center mb-4">Login</h3>

                        <?php if (isset($_SESSION['error_message'])): ?>
                            <div class="alert alert-danger"><?php echo $_SESSION['error_message'];
                                                            unset($_SESSION['error_message']); ?></div>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['success_message'])): ?>
                            <div class="alert alert-success"><?php echo $_SESSION['success_message'];
                                                                unset($_SESSION['success_message']); ?></div>
                        <?php endif; ?>

                        <form action="login_process" method="POST">
                            <div class="mb-3">
                                <label for="login_identifier" class="form-label">  Email / Mobile Number</label>
                                <input type="text" class="form-control" id="login_identifier" name="login_identifier" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="text-end mb-3">
                                <a href="forgot_password">Forgot Password?</a>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                            <p class="text-center mt-3">
                                Don't have an account? <a href="register">Sign Up</a>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div> 

    <br><br><br>
    <?php include "includes/footer.php" ?>
</body>

</html>