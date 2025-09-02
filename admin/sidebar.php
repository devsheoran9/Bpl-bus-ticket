<?php
// --- Variable Definition: Ideally, these lines should be at the top of your main page file (e.g., admin.php) ---
// --- that includes this sidebar.php file. ---

$php_self = $_SERVER['PHP_SELF'];

// 1. Get the last part (filename.php)
//    e.g., dashboard.php
$filename = basename($php_self);

// 2. Remove the file extension
//    e.g., dashboard
$current_page = pathinfo($filename, PATHINFO_FILENAME);

// --- Define active status for individual links and dropdown parents ---

// For direct links:
$dashboard_active = ($current_page == 'dashboard');
$category_active = ($current_page == 'categories');
$rates_active = ($current_page == 'view_rates');
$contact_submissions_active = ($current_page == 'view_contacts');
$quotes_active = ($current_page == 'view_quotes');
$website_details_active = ($current_page == 'website_details');
$reviews_active = ($current_page == 'view_reviews');
$change_password_active = ($current_page == 'change_password');
$change_profile = ($current_page == 'change_profile');
$logout_active = ($current_page == 'logout'); // Typically logout isn't marked active, but keeping for consistency.


// For collapsed menu parents:
$is_blog_active = in_array($current_page, ['insert_blog', 'view_blogs']);
$is_shipment_active = in_array($current_page, ['generate_shipment', 'view_shipment']);
$is_gallery_active = in_array($current_page, ['video_gallery', 'manage_images']);


// Note: $is_transaction_active was declared global but never defined or used. Removed for clarity.
// $is_quotes_active and $is_contact_active were used as globals, but more precise
// variables ($quotes_active, $contact_submissions_active) are now defined for individual links.
// If you intended $is_quotes_active or $is_contact_active to light up a *section* header
// for a parent item (which they aren't, as these are individual links), you would use them for that.
// For the current structure, using the specific link variables is more appropriate.


// --- Global Declarations (read this carefully): ---
// If this file (`sidebar.php`) is an INCLUDE file in a larger application,
// these variables (`$current_page`, `$dashboard_active`, etc.) should be defined
// IN THE MAIN PHP FILE that includes this sidebar.
// In that scenario, these `global` declarations BELOW would be necessary here
// to bring those variables into the scope of this `sidebar.php` file.
//
// However, since the variables are currently DEFINED *within this file* (above),
// declaring them `global` *here* is redundant for their use *within this file*.
// I am commenting them out for now to reflect cleaner local scope usage.
/*
global $current_page;
global $dashboard_active;
global $category_active;
global $rates_active;
global $contact_submissions_active;
global $quotes_active;
global $website_details_active;
global $reviews_active;
global $change_password_active;
global $logout_active;

global $is_blog_active;
global $is_shipment_active;
global $is_gallery_active;
// global $is_transaction_active; // This variable was declared but not used/defined.
*/
?>
<nav class="sidebar">
    <div class="sidebar-header d-flex text-center" style="justify-item:center;justify-content:center;"><img src="../assets/logo/chhavi_logo.png" style="width:110px;" alt=""></div>
   

    <div class="sidebar-scroll">
        <ul class="nav flex-column">
            <li class="nav-item pt-4">
                <!-- Apply 'active' class if $dashboard_active is true -->
                <a class="nav-link <?php echo $dashboard_active ? 'active' : ''; ?>" href="dashboard">
                    <i class="fas fa-tachometer-alt nav-icon me-2"></i>Dashboard
                </a>
            </li>
            
              
            
            <li class="nav-item">
                <!-- Apply 'active' class to parent link if $is_blog_active is true.
                     Also set 'collapsed' if not active for Bootstrap dropdown. -->
                <a class="nav-link d-flex justify-content-between align-items-center <?php echo $is_blog_active ? 'active' : 'collapsed'; ?>"
                   data-bs-toggle="collapse" href="#blogMenu" role="button"
                   aria-expanded="<?php echo $is_blog_active ? 'true' : 'false'; ?>"
                   aria-controls="blogMenu">
                    <span><i class="fas fa-blog nav-icon me-2"></i>Manage Blogs</span>
                    <i class="fas fa-chevron-down small"></i>
                </a>
                <!-- Show the collapse menu if $is_blog_active is true -->
                <div class="collapse <?php echo $is_blog_active ? 'show' : ''; ?>" id="blogMenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <!-- Apply 'active' class if $current_page is 'insert_blog' -->
                            <a class="nav-link <?php echo $current_page == 'insert_blog' ? 'active' : ''; ?>" href="insert_blog">
                                <i class="fas fa-plus-circle me-2"></i>Add Blog
                            </a>
                        </li>
                        <li class="nav-item">
                            <!-- Apply 'active' class if $current_page is 'view_blogs' -->
                            <a class="nav-link <?php echo $current_page == 'view_blogs' ? 'active' : ''; ?>" href="view_blogs">
                                <i class="fas fa-list-alt me-2"></i>View Blogs
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            
            <li class="nav-item">
                <!-- Apply 'active' class to parent link if $is_shipment_active is true.
                     Also set 'collapsed' if not active for Bootstrap dropdown. -->
                <a class="nav-link d-flex justify-content-between align-items-center <?php echo $is_shipment_active ? 'active' : 'collapsed'; ?>"
                   data-bs-toggle="collapse" href="#shipmentMenu" role="button"
                   aria-expanded="<?php echo $is_shipment_active ? 'true' : 'false'; ?>"
                   aria-controls="shipmentMenu">
                    <span><i class="fas fa-truck-loading nav-icon me-2"></i>Manage Shipment</span>
                    <i class="fas fa-chevron-down small"></i>
                </a>
                <!-- Show the collapse menu if $is_shipment_active is true -->
                <div class="collapse <?php echo $is_shipment_active ? 'show' : ''; ?>" id="shipmentMenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <!-- Apply 'active' class if $current_page is 'generate_shipment' -->
                            <a class="nav-link <?php echo $current_page == 'generate_shipment' ? 'active' : ''; ?>" href="generate_shipment">
                                <i class="fas fa-plus-circle me-2"></i>Add Shipment
                            </a>
                        </li>
                        <li class="nav-item">
                            <!-- Apply 'active' class if $current_page is 'view_shipment' -->
                            <a class="nav-link <?php echo $current_page == 'view_shipment' ? 'active' : ''; ?>" href="view_shipment">
                                <i class="fas fa-list-alt me-2"></i>View Shipments
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            
            <li class="nav-item">
                <!-- Apply 'active' class to parent link if $is_gallery_active is true.
                     Also set 'collapsed' if not active for Bootstrap dropdown. -->
                <a class="nav-link d-flex justify-content-between align-items-center <?php echo $is_gallery_active ? 'active' : 'collapsed'; ?>"
                   data-bs-toggle="collapse" href="#galleryMenu" role="button"
                   aria-expanded="<?php echo $is_gallery_active ? 'true' : 'false'; ?>"
                   aria-controls="galleryMenu">
                    <span><i class="fas fa-photo-video nav-icon me-2"></i>Gallery</span>
                    <i class="fas fa-chevron-down small"></i>
                </a>
                <!-- Show the collapse menu if $is_gallery_active is true -->
                <div class="collapse <?php echo $is_gallery_active ? 'show' : ''; ?>" id="galleryMenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <!-- Apply 'active' class if $current_page is 'video_gallery' -->
                            <a class="nav-link <?php echo $current_page == 'video_gallery' ? 'active' : ''; ?>" href="video_gallery">
                                <i class="fas fa-video me-2"></i>Video Gallery
                            </a>
                        </li>
                        <li class="nav-item">
                            <!-- Apply 'active' class if $current_page is 'manage_images' -->
                            <a class="nav-link <?php echo $current_page == 'manage_images' ? 'active' : ''; ?>" href="manage_images">
                                <i class="fas fa-images me-2"></i>Image Gallery
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <!-- Apply 'active' class if $rates_active is true -->
                <a class="nav-link <?php echo $rates_active ? 'active' : ''; ?>" href="view_rates">
                    <i class="fas fa-envelope-open-text me-2"></i>Manage Rates
                </a>
            </li>
            <li class="nav-item">
                <!-- Apply 'active' class if $contact_submissions_active is true -->
                <a class="nav-link <?php echo $contact_submissions_active ? 'active' : ''; ?>" href="view_contacts">
                    <i class="fas fa-envelope-open-text me-2"></i>Contact Submissions
                </a>
            </li>
            
            <li class="nav-item">
                <!-- Apply 'active' class if $quotes_active is true -->
                <a class="nav-link <?php echo $quotes_active ? 'active' : ''; ?>" href="view_quotes">
                    <i class="fas fa-file-invoice-dollar me-2"></i>Quote Requests
                </a>
            </li>
            
            <li class="nav-item">
                <!-- Apply 'active' class if $website_details_active is true -->
                <a class="nav-link <?php echo $website_details_active ? 'active' : ''; ?>" href="website_details">
                    <i class="fas fa-info-circle me-2"></i>Website Details
                </a>
            </li>
            
            <li class="nav-item">
                <!-- Apply 'active' class if $reviews_active is true -->
                <a class="nav-link <?php echo $reviews_active ? 'active' : ''; ?>" href="view_reviews">
                    <i class="fas fa-star me-2"></i>Reviews
                </a>
            </li>
            
            <li class="nav-item">
                <!-- Apply 'active' class if $change_password_active is true -->
                <a class="nav-link <?php echo $change_password_active ? 'active' : ''; ?>" href="change_password">
                    <i class="fas fa-key me-2"></i>Change Password
                </a>
            </li>
            <li class="nav-item">
                <!-- Apply 'active' class if $change_password_active is true -->
                <a class="nav-link <?php echo $change_profile ? 'active' : ''; ?>" href="change_profile">
                    <i class="fas fa-user me-2"></i>Account Details
                </a>
            </li>
            
            <li class="nav-item">
                <!-- Apply 'active' class if $logout_active is true -->
                <a class="nav-link <?php echo $logout_active ? 'active' : ''; ?>" href="logout" id="logout-btn">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </li>
            
        </ul>
    </div>
</nav>