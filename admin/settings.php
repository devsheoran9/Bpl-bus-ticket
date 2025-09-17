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
        /* General White Theme Styling */
        body { background-color: #f8f9fa; color: #343a40; }
        .container-fluid { padding-top: 2rem; padding-bottom: 2rem; }
        h2.my-4 { font-weight: 700; color: #212529; }

        /* Card Styling */
        .card {
            background-color: #ffffff;
            border-radius: 1rem; /* Softer rounded corners */
            box-shadow: 0 8px 30px rgba(0,0,0,0.08); /* More subtle and modern shadow */
            border: 1px solid #e0e0e0; /* Light border */
        }
        .card-header {
            background-color: #f8fafd; /* Light blueish-grey for header */
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e9ecef;
            font-size: 1.15rem; /* Slightly larger font */
            font-weight: 600;
            color: #212529;
            border-top-left-radius: 1rem; /* Match card radius */
            border-top-right-radius: 1rem;
            display: flex;
            align-items: center;
        }
        .card-header i { color: #0d6efd; margin-right: 0.75rem; font-size: 1.2rem; } /* Primary color for icon */
        .card-body { padding: 1.5rem; } /* Consistent padding */

        /* Form Elements Styling */
        .form-label {
            font-weight: 600; /* Bolder labels */
            color: #495057;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }
        .form-control, .form-select {
            border-radius: 0.5rem;
            border: 1px solid #ced4da;
            padding: 0.65rem 1rem; /* Consistent padding */
            transition: all 0.2s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25); /* Primary focus shadow */
        }
        .input-group-text {
            background-color: #e9ecef; /* Light grey background for icon */
            border: 1px solid #ced4da;
            border-right: none;
            border-radius: 0.5rem 0 0 0.5rem; /* Match input border radius */
            padding: 0.65rem 0.75rem;
            color: #6c757d;
            width: 45px; /* Fixed width for better alignment */
            justify-content: center;
        }
        .input-group .form-control { border-left: none; border-radius: 0 0.5rem 0.5rem 0; }
        .input-group { margin-bottom: 1rem; } /* Consistent spacing for input groups */
        .input-group:last-child { margin-bottom: 0 !important; } /* No margin for last child in a group */

        /* Save Button Styling */
        #save-settings-btn {
            padding: 0.85rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 0.75rem; /* More rounded */
            background-color: #0d6efd;
            border-color: #0d6efd;
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.25); /* Button shadow */
            transition: all 0.2s ease;
        }
        #save-settings-btn:hover {
            background-color: #0a58ca;
            border-color: #0a58ca;
            box-shadow: 0 6px 20px rgba(13, 110, 253, 0.35);
        }

        /* Responsive Adjustments */
        @media (max-width: 991.98px) { /* For small to medium screens (tablet portraits) */
            .col-lg-6 { margin-bottom: 1.5rem !important; } /* Space between stacked cards */
        }
        @media (max-width: 575.98px) { /* For extra small screens (mobile phones) */
            .container-fluid { padding-top: 1rem; padding-bottom: 1rem; }
            h2.my-4 { font-size: 1.5rem; margin-top: 1rem; margin-bottom: 1rem; }
            .card { border-radius: 0.75rem; }
            .card-header { padding: 1rem; font-size: 1rem; border-top-left-radius: 0.75rem; border-top-right-radius: 0.75rem; }
            .card-header i { font-size: 1rem; margin-right: 0.5rem; }
            .card-body { padding: 1rem; }
            .form-label { font-size: 0.9rem; margin-bottom: 0.3rem; }
            .form-control, .form-select { padding: 0.5rem 0.75rem; font-size: 0.9rem; }
            .input-group-text { width: 40px; padding: 0.5rem 0.6rem; font-size: 0.9rem; }
            .input-group { margin-bottom: 0.75rem; }
            #save-settings-btn { padding: 0.75rem 1.5rem; font-size: 1rem; border-radius: 0.5rem; }
        }
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
                                    <textarea class="form-control" id="company_address" name="settings[company_address]" rows="3"><?php echo get_setting('company_address'); ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header"><i class="fas fa-phone-alt me-2"></i>Contact Details</div>
                            <div class="card-body">
                                <label class="form-label">Mobile Numbers</label>
                                <div class="row g-2 mb-3"> <!-- Using row g-2 for compact spacing -->
                                    <div class="col-12">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-mobile-alt"></i></span>
                                            <input type="text" class="form-control" name="settings[mobile_primary]" value="<?php echo get_setting('mobile_primary'); ?>" placeholder="Primary Mobile * (Required)">
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-12"> <!-- Stack on xs, 2-col on sm+ -->
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-mobile-alt"></i></span>
                                            <input type="text" class="form-control" name="settings[mobile_optional_1]" value="<?php echo get_setting('mobile_optional_1'); ?>" placeholder="Optional Mobile 1">
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-12"> <!-- Stack on xs, 2-col on sm+ -->
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-mobile-alt"></i></span>
                                            <input type="text" class="form-control" name="settings[mobile_optional_2]" value="<?php echo get_setting('mobile_optional_2'); ?>" placeholder="Optional Mobile 2">
                                        </div>
                                    </div>
                                </div>

                                <label class="form-label">WhatsApp Numbers</label>
                                <div class="row g-2 mb-3">
                                    <div class="col-12">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fab fa-whatsapp"></i></span>
                                            <input type="text" class="form-control" name="settings[whatsapp_primary]" value="<?php echo get_setting('whatsapp_primary'); ?>" placeholder="Primary WhatsApp">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fab fa-whatsapp"></i></span>
                                            <input type="text" class="form-control" name="settings[whatsapp_optional_1]" value="<?php echo get_setting('whatsapp_optional_1'); ?>" placeholder="Optional WhatsApp">
                                        </div>
                                    </div>
                                </div>

                                <label class="form-label">Email Addresses</label>
                                <div class="row g-2">
                                    <div class="col-12">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                            <input type="email" class="form-control" name="settings[email_primary]" value="<?php echo get_setting('email_primary'); ?>" placeholder="Primary Email * (Required)">
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-12"> <!-- Stack on xs, 2-col on sm+ -->
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                            <input type="email" class="form-control" name="settings[email_optional_1]" value="<?php echo get_setting('email_optional_1'); ?>" placeholder="Optional Email 1">
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-12"> <!-- Stack on xs, 2-col on sm+ -->
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                            <input type="email" class="form-control" name="settings[email_optional_2]" value="<?php echo get_setting('email_optional_2'); ?>" placeholder="Optional Email 2">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header"><i class="fas fa-share-alt me-2"></i>Social Media Links</div>
                    <div class="card-body">
                        <div class="row g-3"> <!-- Consistent row gap -->
                            <div class="col-md-6 col-12"><div class="input-group"><span class="input-group-text"><i class="fab fa-facebook-f"></i></span><input type="url" class="form-control" name="settings[facebook_url]" value="<?php echo get_setting('facebook_url'); ?>" placeholder="https://facebook.com/yourpage"></div></div>
                            <div class="col-md-6 col-12"><div class="input-group"><span class="input-group-text"><i class="fab fa-instagram"></i></span><input type="url" class="form-control" name="settings[instagram_url]" value="<?php echo get_setting('instagram_url'); ?>" placeholder="https://instagram.com/yourprofile"></div></div>
                            <div class="col-md-6 col-12"><div class="input-group"><span class="input-group-text"><i class="fab fa-twitter"></i></span><input type="url" class="form-control" name="settings[twitter_url]" value="<?php echo get_setting('twitter_url'); ?>" placeholder="https://twitter.com/yourhandle"></div></div>
                            <div class="col-md-6 col-12"><div class="input-group"><span class="input-group-text"><i class="fab fa-youtube"></i></span><input type="url" class="form-control" name="settings[youtube_url]" value="<?php echo get_setting('youtube_url'); ?>" placeholder="https://youtube.com/yourchannel"></div></div>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-4"> <!-- Added margin-top -->
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