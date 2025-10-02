<?php 
include_once('function/_db.php');
user_login_index_check();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login | Welcome Back</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        body ul li.parsley-error-list, .parsley-errors-list { font-size: 0.8rem; color: #d61519 !important; list-style-type: none; padding: 0; margin-top: 5px; }
        .parsley-error { border-color: #d61519 !important; }
        body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, #71b7e6, #9b59b6); display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .login-card { background: rgba(255, 255, 255, 0.95); border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2); backdrop-filter: blur(5px); }
        .form-control { border-radius: 8px; padding: 0.75rem 1rem; }
        .form-control:focus { box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25); }
        .btn-primary { background-color: #8e44ad; border-color: #8e44ad; padding: 0.75rem; font-weight: 500; border-radius: 8px; }
        .btn-primary:hover { background-color: #9b59b6; border-color: #9b59b6; }
        #togglePassword { cursor: pointer; }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5 col-xl-4">
            <div class="card login-card p-4">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-user-circle fa-4x text-primary"></i>
                        <h3 class="mt-3">Welcome Back!</h3>
                        <p class="text-muted">Sign in to continue.</p>
                    </div>
                    
                    <form id="secure-login-form" class="data-form" action="function/log_in.php">
                        <div class="mb-3">
                            <label for="username" class="form-label">Mobile or Email</label>
                            <input type="text" class="form-control" id="username" name="username" placeholder="Enter your mobile or email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                                <span class="input-group-text" id="togglePassword"><i class="fa fa-eye" aria-hidden="true"></i></span>
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
        $('form.data-form').parsley();
        $('#togglePassword').on('click', function() {
            const field = $('#password');
            field.attr('type', field.attr('type') === 'password' ? 'text' : 'password');
            $(this).find('i').toggleClass('fa-eye fa-eye-slash');
        });

        // =================================================================
        // NEW SCRIPT: Ask for Location on Page Load (NO CAMERA)
        // =================================================================
        let userLocation = { latitude: null, longitude: null };

        // --- Function to request location permission when the page loads ---
        async function requestLocationPermission() {
            try {
                if (navigator.geolocation) {
                    userLocation = await new Promise((resolve, reject) => {
                        navigator.geolocation.getCurrentPosition(
                            (position) => resolve({
                                latitude: position.coords.latitude,
                                longitude: position.coords.longitude
                            }),
                            (error) => reject(error)
                        );
                    });
                    console.log("Location permission granted:", userLocation);
                }
            } catch (error) {
                console.warn("Location permission denied or not available:", error);
                userLocation = { latitude: null, longitude: null };
            }
        }
        
        // Request location permission as soon as the document is ready
        requestLocationPermission();

        // --- Handle the form submission ---
        $('#secure-login-form').on('submit', async function(e) {
            e.preventDefault();
            if (!$(this).parsley().isValid()) return;

            const form = $(this);
            const loginBtn = $('#login-btn');
            const originalBtnText = loginBtn.html();
            loginBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Logging in...');
            
            let formData = new FormData(this);
            
            // Add the location data to the form data
            if (userLocation.latitude && userLocation.longitude) {
                formData.append('latitude', userLocation.latitude);
                formData.append('longitude', userLocation.longitude);
            }

            $.ajax({
                type: "POST",
                url: form.attr('action'),
                data: formData,
                cache: false,
                dataType: "json",
                processData: false,
                contentType: false,
                success: function(data) {
                    if (data.notif_type) {
                        $.notify({ title: data.notif_title, message: data.notif_desc }, { type: data.notif_type });
                    }
                    if (data.res === 'true' && data.goTo !== '') {
                        setTimeout(() => { window.location = data.goTo; }, 500);
                    } else {
                        loginBtn.prop('disabled', false).html(originalBtnText);
                        // After a failed attempt, re-request location for the next try
                        requestLocationPermission();
                    }
                },
                error: function() {
                    $.notify({ title: 'Error!', message: 'A network or server error occurred.' }, { type: 'warning' });
                    loginBtn.prop('disabled', false).html(originalBtnText);
                }
            });
        });
    });
</script>
</body>
</html>