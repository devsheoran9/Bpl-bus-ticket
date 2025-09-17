<?php
// profile.php
include_once('function/_db.php');
session_security_check();

// Fetch the current user's data from the session to pre-fill the form
$current_name = $_SESSION['user']['name'] ?? '';
$current_mobile = $_SESSION['user']['mobile'] ?? '';
$current_email = $_SESSION['user']['email'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "head.php"; ?>
    <title>My Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    :root {
        --primary: #3B82F6;
        --primary-dark: #2563EB;
        --primary-light: #DBEAFE;
        --secondary: #6366F1;
        --success: #10B981;
        --warning: #F59E0B;
        --danger: #EF4444;
        --light-gray: #F8FAFC;
        --border-color: #E2E8F0;
        --text-dark: #1E293B;
        --text-light: #64748B;
        --text-muted: #94A3B8;
        --white: #FFFFFF;
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        --radius-sm: 0.375rem;
        --radius-md: 0.5rem;
        --radius-lg: 0.75rem;
        --radius-xl: 1rem;
    }

    * {
        box-sizing: border-box;
    }

  

    /* Animated Background */
    .background-animation {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
        z-index: -1;
        pointer-events: none;
    }

    .floating-shape {
        position: absolute;
        background: rgb(140 184 255 / 10%);
        border-radius: 50%;
        animation: float 15s infinite linear;
    }

    .floating-shape:nth-child(1) {
        width: 80px;
        height: 80px;
        left: 10%;
        animation-delay: 0s;
    }

    .floating-shape:nth-child(2) {
        width: 120px;
        height: 120px;
        left: 20%;
        animation-delay: 2s;
    }

    .floating-shape:nth-child(3) {
        width: 60px;
        height: 60px;
        left: 70%;
        animation-delay: 4s;
    }

    .floating-shape:nth-child(4) {
        width: 100px;
        height: 100px;
        left: 80%;
        animation-delay: 6s;
    }

    @keyframes float {
        0% {
            transform: translateY(100vh) rotate(0deg);
            opacity: 0;
        }
        10% {
            opacity: 1;
        }
        90% {
            opacity: 1;
        }
        100% {
            transform: translateY(-100px) rotate(360deg);
            opacity: 0;
        }
    }

   

    .profile-container {
        width: 100%;
        max-width: 600px;
        margin: 0 auto;
    }

    .profile-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-xl);
        overflow: hidden;
        position: relative;
        transition: all 0.3s ease;
    }

    .profile-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }

    .profile-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary), var(--secondary));
    }

    .card-header-custom {
        text-align: center;
        padding: 3rem 2rem 2rem;
        position: relative;
        background: transparent;
        border: none;
    }

    .icon-container {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        border-radius: 50%;
        margin-bottom: 1.5rem;
        box-shadow: var(--shadow-lg);
        transition: all 0.3s ease;
    }

    .profile-card:hover .icon-container {
        transform: scale(1.1) rotate(5deg);
    }

    .icon-container i {
        font-size: 2rem;
        color: var(--white);
    }

    .card-header-custom h3 { 
        margin: 0;
        font-size: 1.875rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 0.5rem;
    }

    .card-header-custom p {
        color: var(--text-light);
        margin: 0;
        font-size: 1rem;
    }

    .card-body {
        padding: 0 2rem 3rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
        position: relative;
    }

    .form-label {
        display: block;
        font-weight: 600;
        font-size: 0.875rem;
        color: var(--text-dark);
        margin-bottom: 0.5rem;
        transition: color 0.2s ease;
    }

    .input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .input-icon {
        position: absolute;
        left: 1rem;
        color: var(--text-muted);
        font-size: 1.125rem;
        z-index: 2;
        transition: color 0.2s ease;
    }

    .form-control {
        width: 100%;
        padding: 1rem 1rem 1rem 3rem;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-lg);
        font-size: 1rem;
        background: var(--white);
        color: var(--text-dark);
        transition: all 0.2s ease;
        outline: none;
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .form-control:focus + .input-icon {
        color: var(--primary);
    }

    .input-group {
        position: relative;
        display: flex;
        align-items: center;
    }

    .input-group .form-control {
        padding-right: 3.5rem;
        padding-left: 3rem;
    }

    .toggle-password {
        position: absolute;
        right: 1rem;
        background: none;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        padding: 0.5rem;
        border-radius: var(--radius-sm);
        transition: all 0.2s ease;
        z-index: 3;
    }

    .toggle-password:hover {
        color: var(--primary);
        background: var(--primary-light);
    }

    .security-alert {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(251, 191, 36, 0.1));
        border: 1px solid rgba(245, 158, 11, 0.3);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        margin: 2rem 0;
        position: relative;
        overflow: hidden;
    }

    .security-alert::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: var(--warning);
    }

    .alert-heading {
        display: flex;
        align-items: center;
        font-size: 1rem;
        font-weight: 600;
        color: var(--warning);
        margin-bottom: 0.5rem;
    }

    .alert-heading i {
        margin-right: 0.5rem;
        font-size: 1.125rem;
    }

    .security-alert p {
        color: var(--text-dark);
        margin: 0;
        font-size: 0.875rem;
        line-height: 1.5;
    }

    .btn-primary {
        width: 100%;
        padding: 1rem 2rem;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        border: none;
        border-radius: var(--radius-lg);
        color: var(--white);
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
        margin-top: 1rem;
    }

    .btn-primary::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s ease;
    }

    .btn-primary:hover::before {
        left: 100%;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    .btn-primary:active {
        transform: translateY(0);
    }

    .btn-primary:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    .btn-primary:disabled:hover {
        transform: none;
        box-shadow: none;
    }

    .spinner {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        border-top-color: var(--white);
        animation: spin 1s ease-in-out infinite;
        margin-right: 0.5rem;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    /* Error Styles */
    .parsley-errors-list {
        list-style: none;
        padding: 0;
        margin: 0.5rem 0 0;
        font-size: 0.875rem;
        color: var(--danger);
    }

    .parsley-errors-list li {
        display: flex;
        align-items: center;
        margin-bottom: 0.25rem;
    }

    .parsley-errors-list li::before {
        content: '⚠';
        margin-right: 0.5rem;
        font-size: 0.75rem;
    }

    .form-control.parsley-error {
        border-color: var(--danger);
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }

    /* Profile Info Cards */
    .info-card {
        background: rgba(255, 255, 255, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: var(--radius-lg);
        padding: 1rem;
        margin-bottom: 1rem;
        transition: all 0.2s ease;
    }

    .info-card:hover {
        background: rgba(255, 255, 255, 0.7);
        transform: translateY(-2px);
    }

    .info-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.25rem;
    }

    .info-value {
        font-size: 1rem;
        font-weight: 500;
        color: var(--text-dark);
    }

    /* Responsive Design */
    @media (max-width: 640px) {
        

        .card-header-custom {
            padding: 2rem 1.5rem 1.5rem;
        }

        .card-body {
            padding: 0 1.5rem 2rem;
        }

        .card-header-custom h3 {
            font-size: 1.5rem;
        }

        .icon-container {
            width: 60px;
            height: 60px;
        }

        .icon-container i {
            font-size: 1.5rem;
        }

        .form-control {
            padding: 0.875rem 0.875rem 0.875rem 2.5rem;
        }

        .input-group .form-control {
            padding-right: 3rem;
        }

        .input-icon {
            left: 0.75rem;
            font-size: 1rem;
        }

        .toggle-password {
            right: 0.75rem;
        }
    }

    /* Success Animation */
    @keyframes successPulse {
        0% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
        100% {
            transform: scale(1);
        }
    }

    .success-animation {
        animation: successPulse 0.6s ease-in-out;
    }

    /* Focus improvements */
    .form-control:focus + .input-icon {
        color: var(--primary);
    }

    .form-group:focus-within .form-label {
        color: var(--primary);
    }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="background-animation">
        <div class="floating-shape"></div>
        <div class="floating-shape"></div>
        <div class="floating-shape"></div>
        <div class="floating-shape"></div>
    </div>

    <div id="wrapper">
        <?php include_once('sidebar.php'); ?>
        <div class="main-content">
        <?php include_once('header.php'); ?>
        <div class="container-fluid">
         
            <div class="profile-container mt-4">
                <div class="profile-card">
                    <div class="card-header-custom">
                        <div class="icon-container">
                            <i class="fas fa-user-edit"></i>
                        </div>
                        <h3>My Profile</h3>
                        <p>Update your personal information</p>
                    </div>
                    <div class="card-body">
                        <form id="profileForm" data-parsley-validate>
                            <div class="form-group">
                                <label for="name" class="form-label">Full Name</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-user input-icon"></i>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($current_name); ?>" placeholder="Enter your full name" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="mobile" class="form-label">Mobile Number</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-phone input-icon"></i>
                                    <input type="tel" class="form-control" id="mobile" name="mobile" value="<?php echo htmlspecialchars($current_mobile); ?>" placeholder="Enter your mobile number" required data-parsley-type="digits" data-parsley-length="[10, 10]">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="email" class="form-label">Email Address</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-envelope input-icon"></i>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($current_email); ?>" placeholder="Enter your email address" required>
                                </div>
                            </div>

                            <div class="security-alert">
                                <h6 class="alert-heading">
                                    <i class="fas fa-shield-alt"></i>
                                    Security Confirmation Required
                                </h6>
                                <p>For your security, please enter your current password to save changes to your profile.</p>
                            </div>

                            <div class="form-group">
                                <label for="password_confirm" class="form-label">Current Password</label>
                                <div class="input-group">
                                    <i class="fas fa-lock input-icon"></i>
                                    <input type="password" class="form-control" id="password_confirm" name="password_confirm" placeholder="Enter your current password" required>
                                    <button type="button" class="toggle-password">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" id="submit-btn" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>

    <?php include_once('foot.php'); ?>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/parsleyjs@2.9.2/dist/parsley.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
    $(document).ready(function() {
        // Initialize Parsley
        $('#profileForm').parsley();

        // Password visibility toggle
        $('.toggle-password').on('click', function() {
            const passwordField = $(this).siblings('input');
            const icon = $(this).find('i');
            const isPassword = passwordField.attr('type') === 'password';
            
            passwordField.attr('type', isPassword ? 'text' : 'password');
            icon.toggleClass('fa-eye fa-eye-slash');
        });

        // Form submission with AJAX and SweetAlert2
        $('#profileForm').on('submit', function(e) {
            e.preventDefault();
            
            if (!$(this).parsley().isValid()) {
                return;
            }

            const form = $(this);
            const submitBtn = $('#submit-btn');
            const originalText = submitBtn.html();
            
            // Show loading state
            submitBtn.prop('disabled', true).html('<span class="spinner"></span>Saving Changes...');
            
            $.ajax({
                url: 'function/backend/profile_actions.php',
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        // Add success animation
                        $('.profile-card').addClass('success-animation');
                        
                        Swal.fire({
                            title: 'Success!',
                            text: response.message,
                            icon: 'success',
                            timer: 3000,
                            showConfirmButton: false,
                            customClass: {
                                popup: 'animated fadeInDown'
                            }
                        });
                        
                        // Update the header with the new name if it exists
                        if (response.new_name) {
                            $('.dropdown-toggle').html(`<i class="fas fa-user me-2"></i> ${response.new_name}`);
                        }
                        
                        // Clear the password field after successful update
                        $('#password_confirm').val('');
                        
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: response.message,
                            icon: 'error',
                            confirmButtonColor: '#EF4444'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    Swal.fire({
                        title: 'Server Error!',
                        text: 'A server error occurred. Please try again later.',
                        icon: 'error',
                        confirmButtonColor: '#EF4444'
                    });
                },
                complete: function() {
                    // Restore button state
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });

        // Add smooth focus animations
        $('.form-control').on('focus', function() {
            $(this).closest('.form-group').find('.form-label').css('color', 'var(--primary)');
        }).on('blur', function() {
            $(this).closest('.form-group').find('.form-label').css('color', 'var(--text-dark)');
        });

        // Input validation feedback
        $('.form-control').on('input', function() {
            const field = $(this);
            if (field.hasClass('parsley-error')) {
                field.parsley().validate();
            }
        });
    });
    </script>
</body>
</html>