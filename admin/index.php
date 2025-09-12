<?php 
// आपकी PHP फाइलें वैसे ही रहेंगी
include_once('function/_db.php');
user_login_index_check();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login | Welcome Back</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    
    <!-- Google Fonts for better typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        /* आपकी Parsley CSS स्टाइलिंग वैसी ही रहेगी */
        body ul li.parsley-type,
        body ul li.parsley-required,
        body ul li.parsley-maxlength,
        body ul li.parsley-length,
        body ul li.parsley-min,
        body ul li.parsley-range,
        body ul li.parsley-equalto,
        body ul li.parsley-minlength,
        body ul li.parsley-max {
            font-size: 0.8rem;
            color: #d61519 !important;
            list-style-type: none;
            padding: 0;
            margin-top: 5px;
        }
        .parsley-error {
            border-color: #d61519 !important;
        }
        .parsley-errors-list {
            padding: 0;
            margin: 0;
        }

        /* नई स्टाइलिंग */
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #71b7e6, #9b59b6);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
        }
        .form-control {
            border-radius: 8px;
            padding: 0.75rem 1rem;
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        .btn-primary {
            background-color: #8e44ad;
            border-color: #8e44ad;
            padding: 0.75rem;
            font-weight: 500;
            border-radius: 8px;
        }
        .btn-primary:hover {
            background-color: #9b59b6;
            border-color: #9b59b6;
        }
        .btn-warning {
             background-color: #f39c12;
             border-color: #f39c12;
        }
        #togglePassword {
            cursor: pointer;
        }
        .form-check-label {
            font-size: 0.9rem;
        }
        .forgot-password a {
            font-size: 0.9rem;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5  col-xl-4">
            <div class="card login-card p-4">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-user-circle fa-4x text-primary"></i>
                        <h3 class="mt-3">Welcome Back!</h3>
                        <p class="text-muted">Sign in to continue.</p>
                    </div>
                    
                    <form class="data-form" action="function/log_in">
                        <div class="mb-3">
                            <label for="username" class="form-label">Mobile or Email</label>
                            <input type="text" class="form-control" id="username" name="username" placeholder="Enter your mobile or email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                                <span class="input-group-text" id="togglePassword">
                                    <i class="fa fa-eye" aria-hidden="true"></i>
                                </span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="" id="rememberMe">
                                <label class="form-check-label" for="rememberMe">
                                    Remember me
                                </label>
                            </div>
                            <div class="forgot-password">
                                <a href="#">Forgot password?</a>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" id="login-btn" class="btn btn-primary">Login</button>
                        </div>
                    </form>

                    
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/jquery-3.6.0.min.js"></script>
<script src="assets/js/notify.js"></script>
<script src="assets/js/parsley.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        // Parsley validation को शुरू करें
        $('form.data-form').parsley();

        // पासवर्ड दिखाएँ/छिपाएँ के लिए टॉगल
        $('#togglePassword').on('click', function() {
            const passwordField = $('#password');
            const passwordFieldType = passwordField.attr('type');
            const icon = $(this).find('i');

            if (passwordFieldType === 'password') {
                passwordField.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                passwordField.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

        // आपका मौजूदा फॉर्म सबमिशन कोड
        $(document).on('submit', 'form.data-form', function(e) {
            e.preventDefault();

            if ($(this).parsley().isValid()) {
                const action = $(this).attr('action');
                const m_form_data = new FormData(this);
                const loginBtn = $('#login-btn');
                const originalBtnText = loginBtn.html();

                // बटन को डिसेबल करें और स्पिनर दिखाएँ
                loginBtn.prop('disabled', true);
                loginBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Logging in...');
                
                $.ajax({
                    type: "POST",
                    url: action,
                    data: m_form_data,
                    cache: false,
                    dataType: "json",
                    enctype: 'multipart/form-data',
                    processData: false,
                    contentType: false,
                    success: function(data) {
                        let goTo = '';
                        const notify_type = data.notif_type;
                        const notify_title = data.notif_title;
                        const notify_desc = data.notif_desc;

                        if (notify_type) {
                            $.notify({ title: notify_title, message: notify_desc }, { type: notify_type });
                        }

                        goTo = data.goTo;
                        if (data.res === 'true' && goTo !== '') {
                            window.location = goTo;
                        } else {
                            // अगर लॉगिन विफल होता है तो बटन को वापस ओरिजिनल स्टेट में लाएँ
                            loginBtn.prop('disabled', false);
                            loginBtn.html(originalBtnText);
                        }
                    },
                    error: function(jqXHR, exception) {
                        var msg = '';
                        if (jqXHR.status === 0) { msg = "Network Problem"; } 
                        else if (jqXHR.status === 404) { msg = "404 Error: Page not found."; } 
                        else if (jqXHR.status === 500) { msg = "500 Error: Internal Server Error."; } 
                        else if (exception === 'parsererror') { msg = "Data parsing error."; } 
                        else if (exception === 'timeout') { msg = "Network timeout."; } 
                        else if (exception === 'abort') { msg = "Request aborted."; } 
                        else { msg = "Oops! Something went wrong."; }
                        
                        $.notify({ title: 'Error!', message: msg }, { type: 'warning' });

                        // एरर होने पर भी बटन को रीसेट करें
                        loginBtn.prop('disabled', false);
                        loginBtn.html(originalBtnText);
                    }
                });
            }
        });
    });
</script>
</body>
</html>