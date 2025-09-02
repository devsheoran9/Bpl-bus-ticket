<?php
// आपकी PHP फाइलें वैसे ही रहेंगी
global $token;
include_once('function/_db.php');
check_user_login();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once('head.php'); ?>
    <title>Admin Panel - Change Password</title>
    
    <!-- Bootstrap 5 CSS -->
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"> -->
    
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

      
    </style>
</head>
<body>
<div id="wrapper">
    <?php include_once('sidebar.php'); ?>

    <div class="main-content">
        <?php include_once('header.php'); ?>

        <div class="container-fluid pt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card change-password-card p-4">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-lock fa-4x text-primary mb-3"></i>
                        <h3>Change Your Password</h3>
                        <p class="text-muted">Ensure your account is secure.</p>
                    </div>
                    
                    <form id="changePasswordForm" class="data-form" method="POST" action="function/change_password.php" data-parsley-validate>
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="current_password" name="current_password" required placeholder="Enter your current password">
                                <span class="input-group-text toggle-password"><i class="fa fa-eye"></i></span>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="new_password" name="new_password" minlength="6" required placeholder="Minimum 6 characters">
                                <span class="input-group-text toggle-password"><i class="fa fa-eye"></i></span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" data-parsley-equalto="#new_password" required placeholder="Re-type new password">
                                <span class="input-group-text toggle-password"><i class="fa fa-eye"></i></span>
                            </div>
                        </div>

                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token);?>">
                        
                        <div class="d-grid gap-2">
                           <button type="submit" id="submit-btn" class="btn btn-primary">Change Password</button>
                           <a href="dashboard" class="btn btn-outline-secondary">Back To Dashboard</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</div> 
</div> 
<?php include_once('foot.php'); ?>
<script src="assets/js/jquery-3.6.0.min.js"></script>
<script src="assets/js/notify.js?2"></script>
<script src="assets/js/parsley.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    // Parsley validation को शुरू करें
    $('#changePasswordForm').parsley();

    // पासवर्ड दिखाएँ/छिपाएँ के लिए टॉगल
    $('.toggle-password').on('click', function() {
        const passwordField = $(this).prev('input[type="password"], input[type="text"]');
        const icon = $(this).find('i');
        
        if (passwordField.attr('type') === 'password') {
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
            const submitBtn = $('#submit-btn');
            const originalBtnHtml = submitBtn.html();

            // बटन को डिसेबल करें और स्पिनर दिखाएँ
            submitBtn.prop('disabled', true);
            submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Changing...');

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
                    const notify_type = data.notif_type;
                    const notify_title = data.notif_title;
                    const notify_desc = data.notif_desc;

                    if (notify_type) {
                        $.notify({ title: notify_title, message: notify_desc }, { type: notify_type });
                    }

                    if (data.res === 'true' && data.goTo) {
                        // सफल होने पर कुछ सेकंड बाद रीडायरेक्ट करें ताकि उपयोगकर्ता संदेश पढ़ सके
                        setTimeout(function() {
                            window.location = data.goTo;
                        }, 1500);
                    } else {
                        // अगर विफल होता है तो बटन को वापस ओरिजिनल स्टेट में लाएँ
                        submitBtn.prop('disabled', false);
                        submitBtn.html(originalBtnHtml);
                    }
                },
                error: function(jqXHR, exception) {
                    var msg = '';
                    if (jqXHR.status === 0) { msg = "Network Problem. Please check your connection."; } 
                    else if (jqXHR.status === 404) { msg = "Error 404: Requested page not found."; } 
                    else if (jqXHR.status === 500) { msg = "Error 500: Internal Server Error."; } 
                    else if (exception === 'parsererror') { msg = "Data parsing error. Please contact support."; } 
                    else if (exception === 'timeout') { msg = "Network timeout. Please try again."; } 
                    else { msg = "An unexpected error occurred."; }
                    
                    $.notify({ title: 'Request Failed!', message: msg }, { type: 'danger' });

                    // एरर होने पर भी बटन को रीसेट करें
                    submitBtn.prop('disabled', false);
                    submitBtn.html(originalBtnHtml);
                }
            });
        }
    });
});
</script>
</body>
</html>