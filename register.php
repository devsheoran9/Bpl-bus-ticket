<?php include 'includes/header.php'; ?>

<body>
    <div class="container my-5 pt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card">
                    <div class="card-body p-4">
                        <h3 class="card-title text-center mb-4">Create Account</h3>
                        <form id="register-form">
                            <div class="mb-3">
                                <label for="username" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="mobile_no" class="form-label">Mobile Number</label>
                                <input type="tel" class="form-control" id="mobile_no" name="mobile_no" required pattern="[0-9]{10}" title="Please enter a valid 10-digit mobile number.">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email address</label>
                                <input type="email" class="form-control" id="email" required name="email">
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" id="register-btn" class="btn btn-primary w-100">
                                <span class="btn-text">Register</span>
                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            </button>
                            <p class="text-center mt-3">
                                Already have an account? <a href="login">Log In</a>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- OTP Verification Modal -->
    <div class="modal fade" id="otpModal" tabindex="-1" aria-labelledby="otpModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="otpModalLabel">Verify Your Email</h5>
                </div>
                <div class="modal-body">
                    <p class="text-muted">An OTP has been sent to your email address. Please enter it below to complete your registration.</p>
                    <div class="form-group">
                        <label for="otpInput">Enter OTP</label>
                        <input type="text" class="form-control" id="otpInput" placeholder="6-Digit Code" maxlength="6">
                        <div id="otp-error" class="text-danger small mt-2"></div>
                    </div>
                    <div class="text-center mt-3">
                        <span id="otp-timer"></span>
                        <a href="#" id="resend-otp-link" class="d-none">Resend OTP</a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="verify-otp-btn" class="btn btn-primary">
                        <span class="btn-text">Verify & Register</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <!-- Add jQuery if it's not already in your footer -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const registerForm = document.getElementById('register-form');
            const registerBtn = document.getElementById('register-btn');
            const otpModal = new bootstrap.Modal(document.getElementById('otpModal'));
            const verifyOtpBtn = document.getElementById('verify-otp-btn');
            const otpInput = document.getElementById('otpInput');
            const otpError = document.getElementById('otp-error');
            let timerInterval;

            const startTimer = () => {
                let timeLeft = 120; // 2 minutes
                const timerElement = document.getElementById('otp-timer');
                const resendLink = document.getElementById('resend-otp-link');

                timerElement.classList.remove('d-none');
                resendLink.classList.add('d-none');
                clearInterval(timerInterval); // Clear any existing timer

                timerInterval = setInterval(() => {
                    if (timeLeft <= 0) {
                        clearInterval(timerInterval);
                        timerElement.classList.add('d-none');
                        resendLink.classList.remove('d-none');
                    } else {
                        const minutes = Math.floor(timeLeft / 60);
                        let seconds = timeLeft % 60;
                        seconds = seconds < 10 ? '0' + seconds : seconds;
                        timerElement.textContent = `Resend OTP in ${minutes}:${seconds}`;
                        timeLeft--;
                    }
                }, 1000);
            };

            const toggleButtonLoading = (btn, isLoading) => {
                const text = btn.querySelector('.btn-text');
                const spinner = btn.querySelector('.spinner-border');
                if (isLoading) {
                    text.classList.add('d-none');
                    spinner.classList.remove('d-none');
                    btn.disabled = true;
                } else {
                    text.classList.remove('d-none');
                    spinner.classList.add('d-none');
                    btn.disabled = false;
                }
            };

            registerForm.addEventListener('submit', function(e) {
                e.preventDefault();
                toggleButtonLoading(registerBtn, true);

                $.ajax({
                    url: 'register_process',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            otpModal.show();
                            startTimer();
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function() {
                        alert('An error occurred. Please try again.');
                    },
                    complete: function() {
                        toggleButtonLoading(registerBtn, false);
                    }
                });
            });

            verifyOtpBtn.addEventListener('click', function() {
                const otp = otpInput.value;
                // Get the email from the form to send for verification
                const email = document.getElementById('email').value;

                if (otp.length !== 6 || !/^\d+$/.test(otp)) {
                    otpError.textContent = 'Please enter a valid 6-digit OTP.';
                    return;
                }
                otpError.textContent = '';
                toggleButtonLoading(verifyOtpBtn, true);

                $.ajax({
                    url: 'verify_otp_register',
                    type: 'POST',
                    data: {
                        otp: otp,
                        email: email // Send the email along with the OTP
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert('Registration successful! You can now log in.');
                            window.location.href = response.redirectUrl;
                        } else {
                            otpError.textContent = response.message;
                        }
                    },
                    error: function() {
                        otpError.textContent = 'An error occurred during verification.';
                    },
                    complete: function() {
                        toggleButtonLoading(verifyOtpBtn, false);
                    }
                });
            });

            document.getElementById('resend-otp-link').addEventListener('click', function(e) {
                e.preventDefault();
                registerForm.dispatchEvent(new Event('submit'));
            });
        });
    </script>
</body>

</html>