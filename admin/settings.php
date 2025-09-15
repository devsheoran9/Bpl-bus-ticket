<?php
// settings.php
include_once('function/_db.php');
session_security_check();
check_permission('can_manage_settings'); // Protect the page

// Fetch all existing settings from the database
$settings_query = $_conn_db->query("SELECT setting_key, setting_value FROM settings");
$settings = $settings_query->fetchAll(PDO::FETCH_KEY_PAIR);
 
function get_setting($key, $default = '') {
    global $settings;
    return isset($settings[$key]) ? htmlspecialchars($settings[$key]) : $default;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "head.php"; ?>
    <title>Company Settings</title>
    <style>
        .card-header i { color: #0d6efd; }
        .input-group-text { width: 40px; justify-content: center; }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include_once('sidebar.php'); ?>
    <div class="main-content">
        <?php include_once('header.php'); ?>
        <div class="container-fluid">
            <h2 class="my-4">Company Settings</h2>

            <form id="settings-form">
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header"><i class="fas fa-info-circle me-2"></i>Basic Information</div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="company_name" class="form-label">Company Name</label>
                                    <input type="text" class="form-control" id="company_name" name="settings[company_name]" value="<?php echo get_setting('company_name'); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="company_address" class="form-label">Company Address</label>
                                    <input class="form-control" id="company_address" name="settings[company_address]"  value="<?php echo get_setting('company_address'); ?>"> 
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header"><i class="fas fa-phone-alt me-2"></i>Contact Details</div>
                            <div class="card-body">
                               
                                <label class="form-label">Mobile Numbers</label> <div class="row">
                                <div class="input-group mb-2 col-md-12">
                                    <span class="input-group-text"><i class="fas fa-mobile-alt"></i></span>
                                    <input type="text" class="form-control" name="settings[mobile_primary]" value="<?php echo get_setting('mobile_primary'); ?>" placeholder="Primary Mobile * (Required)">
                                </div>
                                <div class="input-group mb-2 col-md-6">
                                    <span class="input-group-text"><i class="fas fa-mobile-alt"></i></span>
                                    <input type="text" class="form-control" name="settings[mobile_optional_1]" value="<?php echo get_setting('mobile_optional_1'); ?>" placeholder="Optional Mobile 1">
                                </div>
                                <div class="input-group mb-3 col-md-6">
                                    <span class="input-group-text"><i class="fas fa-mobile-alt"></i></span>
                                    <input type="text" class="form-control" name="settings[mobile_optional_2]" value="<?php echo get_setting('mobile_optional_2'); ?>" placeholder="Optional Mobile 2">
                                </div>
                                </div>
                                <label class="form-label">WhatsApp Numbers</label>
                                <div class="input-group mb-2">
                                    <span class="input-group-text"><i class="fab fa-whatsapp"></i></span>
                                    <input type="text" class="form-control" name="settings[whatsapp_primary]" value="<?php echo get_setting('whatsapp_primary'); ?>" placeholder="Primary WhatsApp">
                                </div>
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="fab fa-whatsapp"></i></span>
                                    <input type="text" class="form-control" name="settings[whatsapp_optional_1]" value="<?php echo get_setting('whatsapp_optional_1'); ?>" placeholder="Optional WhatsApp">
                                </div>

                                <label class="form-label">Email Addresses</label>
                                <div class="input-group mb-2">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" name="settings[email_primary]" value="<?php echo get_setting('email_primary'); ?>" placeholder="Primary Email * (Required)">
                                </div>
                                <div class="input-group mb-2">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" name="settings[email_optional_1]" value="<?php echo get_setting('email_optional_1'); ?>" placeholder="Optional Email 1">
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" name="settings[email_optional_2]" value="<?php echo get_setting('email_optional_2'); ?>" placeholder="Optional Email 2">
                                </div>
                               
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header"><i class="fas fa-share-alt me-2"></i>Social Media Links</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6"><div class="input-group mb-3"><span class="input-group-text"><i class="fab fa-facebook-f"></i></span><input type="url" class="form-control" name="settings[facebook_url]" value="<?php echo get_setting('facebook_url'); ?>" placeholder="https://facebook.com/yourpage"></div></div>
                            <div class="col-md-6"><div class="input-group mb-3"><span class="input-group-text"><i class="fab fa-instagram"></i></span><input type="url" class="form-control" name="settings[instagram_url]" value="<?php echo get_setting('instagram_url'); ?>" placeholder="https://instagram.com/yourprofile"></div></div>
                            <div class="col-md-6"><div class="input-group mb-3"><span class="input-group-text"><i class="fab fa-twitter"></i></span><input type="url" class="form-control" name="settings[twitter_url]" value="<?php echo get_setting('twitter_url'); ?>" placeholder="https://twitter.com/yourhandle"></div></div>
                            <div class="col-md-6"><div class="input-group mb-3"><span class="input-group-text"><i class="fab fa-youtube"></i></span><input type="url" class="form-control" name="settings[youtube_url]" value="<?php echo get_setting('youtube_url'); ?>" placeholder="https://youtube.com/yourchannel"></div></div>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" id="save-settings-btn" class="btn btn-primary btn-lg">Save Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include "foot.php"; ?>
<script>
$(document).ready(function() {
    $('#settings-form').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize(); // Get all form data

        Swal.fire({
            title: 'Confirm Changes',
            text: 'Please enter your password to save the settings.',
            input: 'password',
            inputAttributes: {
                autocapitalize: 'off'
            },
            showCancelButton: true,
            confirmButtonText: 'Confirm & Save',
            showLoaderOnConfirm: true,
            preConfirm: (password) => {
                // This function will be called when the user clicks "Confirm"
                // It returns a Promise, allowing Swal to show a loading spinner
                return $.ajax({
                    url: 'function/backend/settings_actions.php',
                    type: 'POST',
                    // Send both the form data AND the password
                    data: formData + '&password=' + encodeURIComponent(password) + '&action=save_settings',
                    dataType: 'json'
                }).catch(error => {
                    Swal.showValidationMessage(`Request failed: ${error.statusText}`);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                // The 'result.value' contains the JSON response from our AJAX call
                if (result.value.status === 'success') {
                    Swal.fire({
                        title: 'Success!',
                        text: result.value.message,
                        icon: 'success'
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: result.value.message,
                        icon: 'error'
                    });
                }
            }
        });
    });
});
</script>
</body>
</html>