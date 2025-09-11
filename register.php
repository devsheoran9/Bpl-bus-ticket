<?php include 'includes/header.php'; ?>

<div class="container my-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card">
                <div class="card-body p-4">
                    <h3 class="card-title text-center mb-4">Create Account</h3>
                    <form action="register_process.php" method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="mobile_no" class="form-label">Mobile Number</label>
                            <input type="tel" class="form-control" id="mobile_no" name="mobile_no" required pattern="[0-9]{10}" title="Please enter a valid 10-digit mobile number.">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email address (Optional)</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Register</button>
                        <p class="text-center mt-3">
                            Already have an account? <a href="login.php">Log In</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>