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

  
?>
<nav class="sidebar">
    <div class="sidebar-header d-flex text-center" style="justify-item:center;justify-content:center;"><h2>BPL Tickets</h2></div>
   

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
                    <span><i class="fas fa-blog nav-icon me-2"></i>Manage Buses</span>
                    <i class="fas fa-chevron-down small"></i>
                </a>
                <!-- Show the collapse menu if $is_blog_active is true -->
                <div class="collapse <?php echo $is_blog_active ? 'show' : ''; ?>" id="blogMenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <!-- Apply 'active' class if $current_page is 'insert_blog' -->
                            <a class="nav-link <?php echo $current_page == 'add_bus' ? 'active' : ''; ?>" href="add_bus">
                                <i class="fas fa-plus-circle me-2"></i>Add New Bus
                            </a>
                        </li>
                        <li class="nav-item">
                            <!-- Apply 'active' class if $current_page is 'view_blogs' -->
                            <a class="nav-link <?php echo $current_page == 'view_all_buses' ? 'active' : ''; ?>" href="view_all_buses">
                                <i class="fas fa-list-alt me-2"></i>View All Buses
                            </a>
                        </li>
                        <!-- Add this line inside your sidebar's navigation list (<ul>) -->
<li class="nav-item">
    <a class="nav-link <?php if ($current_page == 'add_operator.php') echo 'active'; ?>" href="add_operator.php">
        <i class="fas fa-user-tie"></i> <!-- Example icon -->
        <span>Operators</span>
    </a>
</li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
    <a class="nav-link <?php if ($current_page == 'add_route.php') echo 'active'; ?>" href="add_route.php">
        <i class="fas fa-route"></i> <!-- Example icon -->
        <span>Routes</span>
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