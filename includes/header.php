<?php
include "admin/function/_db.php";
require_once 'auth_check.php';

$current_page = basename($_SERVER['PHP_SELF']);
$abc = user_login($type = 'header');


$all_settings = [];

try {
    // Prepare and execute the query to fetch all settings.
    // PDO::FETCH_KEY_PAIR is highly efficient for key-value tables.
    // It creates an associative array like ['setting_key' => 'setting_value', ...].
    $settings_query = $_conn_db->query("SELECT setting_key, setting_value FROM settings");
    $all_settings = $settings_query->fetchAll(PDO::FETCH_KEY_PAIR);

} catch (PDOException $e) {
    // If the database query fails, log the error and continue with empty settings.
    // This prevents the entire site from crashing if the settings table has an issue.
    error_log("Failed to fetch company settings: " . $e->getMessage());
    // On failure, all settings variables below will default to an empty string.
}


// --- Assign each setting to its own variable for easy access ---
// The null coalescing operator (?? '') provides a default empty string if a setting is not found.
// htmlspecialchars() is used as a security measure to prevent XSS attacks if you echo these variables directly into HTML.

// Basic Information
$company_name = htmlspecialchars($all_settings['company_name'] ?? '');
$company_address = htmlspecialchars($all_settings['company_address'] ?? '');

// Contact Details - Mobile
$mobile_primary = htmlspecialchars($all_settings['mobile_primary'] ?? '');
$mobile_optional_1 = htmlspecialchars($all_settings['mobile_optional_1'] ?? '');
$mobile_optional_2 = htmlspecialchars($all_settings['mobile_optional_2'] ?? '');

// Contact Details - WhatsApp
$whatsapp_primary = htmlspecialchars($all_settings['whatsapp_primary'] ?? '');
$whatsapp_optional_1 = htmlspecialchars($all_settings['whatsapp_optional_1'] ?? '');

// Contact Details - Email
$email_primary = htmlspecialchars($all_settings['email_primary'] ?? '');
$email_optional_1 = htmlspecialchars($all_settings['email_optional_1'] ?? '');
$email_optional_2 = htmlspecialchars($all_settings['email_optional_2'] ?? '');

// Social Media Links
$facebook_url = htmlspecialchars($all_settings['facebook_url'] ?? '');
$instagram_url = htmlspecialchars($all_settings['instagram_url'] ?? '');
$twitter_url = htmlspecialchars($all_settings['twitter_url'] ?? '');
$youtube_url = htmlspecialchars($all_settings['youtube_url'] ?? '');

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bpl Bus Ticket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <!-- Link to your new custom CSS file -->
    <link rel="stylesheet" href="css/custom.css?v=<?php echo time(); ?>">
</head>

<body>
    <!-- === MODIFIED NAVBAR STRUCTURE FOR RESPONSIVE DESIGN === -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index">
                <i class="bi bi-bus-front"></i> BPL Tickets
            </a>
            <ul class="navbar-nav">
    <li class="nav-item">
        <?php 
        // Check if the user is logged in by looking for the 'user_data' session variable.
        // You can change 'user_data' to whatever you use to store login info, e.g., 'user_id'.
        if (isset($_SESSION['user_id'])): 
        ?>
            <!-- If LOGGED IN, show this link -->
            <a class="nav-link" href="#" data-bs-toggle="offcanvas" data-bs-target="#accountSidebar" aria-controls="accountSidebar">
                <i class="bi bi-person-circle"></i> My Profile
            </a>
        <?php else: ?>
            <!-- If NOT LOGGED IN, show this link -->
            <a class="nav-link" href="#" data-bs-toggle="offcanvas" data-bs-target="#accountSidebar" aria-controls="accountSidebar">
                <i class="bi bi-person-circle"></i> Account
            </a>
        <?php endif; ?>
    </li>
</ul>
        </div>
    </nav>
    <!-- === END OF MODIFIED NAVBAR === -->

    <!-- Right Sidebar -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="accountSidebar" aria-labelledby="accountSidebarLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="accountSidebarLabel">Account</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                <div class="mb-4">
                    <h5>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h5>
                </div>
            <?php else: ?>
                <h4 class="sidebar-login-prompt">Log in to manage your bookings</h4>
                <div class="d-grid gap-2 my-4">
                    <a href="login" class="btn btn-primary btn-lg">Log in</a>
                </div>
                <p class="text-center">Don't have an account? <a href="register">Sign up</a></p>
            <?php endif; ?>
            <hr>
            <h4 class="sidebar-heading">My details</h4>
            <ul class="sidebar-menu">
                <!-- === MODIFIED SIDEBAR LINKS WITH ACTIVE CLASS LOGIC === -->
                <li>
                    <a href="bookings" class="sidebar-menu-item <?php if ($current_page == 'bookings.php') echo 'active'; ?>">
                        <div class="icon-text-group"><i class="bi bi-list-ul"></i><span>Bookings</span></div><i class="bi bi-chevron-right"></i>
                    </a>
                </li>
                <?php if ($abc !== 'logout_user') { ?>
                    <li>
                        <a href="profile" class="sidebar-menu-item <?php if ($current_page == 'profile.php') echo 'active'; ?>">
                            <div class="icon-text-group"><i class="bi bi-person"></i><span>Personal information</span></div><i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                    <li>
                        <a href="cancel_ticket" class="sidebar-menu-item">
                            <div class="icon-text-group"><i class="bi bi-scissors"></i><span>Cancel Ticket</span></div><i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                    <hr>
                    <li>
                        <a href="add_review" class="sidebar-menu-item <?php if ($current_page == 'add_review.php') echo 'active'; ?>">
                            <div class="icon-text-group"><i class="bi bi-pencil-square"></i><span>Add your review</span></div><i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                <?php } ?>
                <li>
                    <a href="reviews" class="sidebar-menu-item <?php if ($current_page == 'reviews.php') echo 'active'; ?>">
                        <div class="icon-text-group"><i class="bi bi-chat-left-text"></i><span>View Reviews</span></div><i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>
            <hr>
            <h3 class="sidebar-heading">FAQS</h3>
            <ul class="sidebar-menu">
                <li>
                    <a href="faq" class="sidebar-menu-item <?php if ($current_page == 'faq.php') echo 'active'; ?>">
                        <div class="icon-text-group"><i class="bi bi-question-circle"></i><span>Any doubt</span></div><i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>
            <hr>
            <h3 class="sidebar-heading">More</h3>
            <ul class="sidebar-menu">
                <!-- <li>
                    <a href="#" class="sidebar-menu-item">
                        <div class="icon-text-group"><i class="bi bi-tag"></i><span>Offers</span></div><i class="bi bi-chevron-right"></i>
                    </a>
                </li> -->
                <li>
                    <a href="about_us" class="sidebar-menu-item <?php if ($current_page == 'about_us') echo 'active'; ?>">
                        <div class="icon-text-group"><i class="bi bi-info-circle"></i><span>Know about us</span></div><i class="bi bi-chevron-right"></i>
                    </a>
                </li>
                <li>
                    <a href="help" class="sidebar-menu-item <?php if ($current_page == 'help.php') echo 'active'; ?>">
                        <div class="icon-text-group"><i class="bi bi-question-circle"></i><span>Help</span></div><i class="bi bi-chevron-right"></i>
                    </a>
                </li>
                <li>
                    <a href="terms_and_conditions" class="sidebar-menu-item <?php if ($current_page == 'terms_and_conditions') echo 'active'; ?>">
                        <div class="icon-text-group"><i class="bi bi-info-circle"></i><span>Terms & Conditions</span></div><i class="bi bi-chevron-right"></i>
                    </a>
                </li>
                <li>
                    <a href="cancellation_policy" class="sidebar-menu-item <?php if ($current_page == 'cancellation_policy') echo 'active'; ?>">
                        <div class="icon-text-group"><i class="bi bi-info-circle"></i><span>Cancellation & Refund Policy</span></div><i class="bi bi-chevron-right"></i>
                    </a>
                </li>
                <li>
                    <a href="contact" class="sidebar-menu-item <?php if ($current_page == 'contact') echo 'active'; ?>">
                        <div class="icon-text-group"><i class="bi bi-info-circle"></i><span>Contact us</span></div><i class="bi bi-chevron-right"></i>
                    </a>
                </li>
                

            </ul>

            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                <div class="d-grid gap-2 mt-4">
                    <a href="logout" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to logout?');">Logout</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <style>
    /* Unique styles for the charter pop-up to avoid conflicts */
    .bpl-charter-popup-v2 {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1055; /* High z-index */
        width: 100%;
        max-width: 320px; /* Reduced width for a more compact look */
        background-color: #ffffff;
        border-radius: 12px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        border: 1px solid #e9ecef;
        
        display: flex; /* Key change for side-by-side layout */
        align-items: center;
        gap: 15px; /* Space between icon and text */
        padding: 16px;
        
        /* Initial state for animation */
        transform: translateY(120%);
        opacity: 0;
        visibility: hidden;
        transition: transform 0.6s cubic-bezier(0.25, 1, 0.5, 1), opacity 0.6s ease, visibility 0.6s;
    }

    /* State when the pop-up is visible */
    .bpl-charter-popup-v2.bpl-popup-show {
        transform: translateY(0);
        opacity: 1;
        visibility: visible;
        animation: bpl-subtle-float 4s ease-in-out infinite;
    }
    
    /* The close button */
    #bpl-charter-close-btn-v2 {
        position: absolute;
        top: -8px; /* Positioned slightly outside for a modern look */
        right: -8px;
        width: 28px;
        height: 28px;
        background-color: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 50%;
        color: #868e96;
        font-size: 14px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        transition: all 0.2s ease;
    }

    #bpl-charter-close-btn-v2:hover {
        background-color: #dc3545; /* Red on hover */
        color: #ffffff;
        transform: scale(1.1) rotate(90deg);
    }
    
    /* Pop-up icon container */
    .bpl-charter-icon-v2 {
        flex-shrink: 0; /* Prevents the icon from shrinking */
        width: 50px;
        height: 50px;
        background-color: #f0e6f2; /* Lighter shade of brand color */
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .bpl-charter-icon-v2 i {
        font-size: 24px;
        color: #7b003a; /* Your brand color */
        animation: bpl-bus-shake 5s ease-in-out infinite;
    }
    
    /* Pop-up content container (text and button) */
    .bpl-charter-content-v2 {
        flex-grow: 1; /* Takes up remaining space */
    }

    /* Pop-up text content */
    .bpl-charter-content-v2 p {
        font-size: 0.95rem; /* Slightly smaller font */
        color: #343a40;
        line-height: 1.5;
        margin: 0 0 12px 0; /* Space between text and button */
        font-weight: 500;
    }
    
    /* Call-to-action button */
    #bpl-charter-cta-btn-v2 {
        display: inline-block;
        width: 100%;
        padding: 8px 15px; /* Reduced padding */
        background-image: linear-gradient(to right, #8e44ad, #7b003a); /* Gradient button */
        color: #ffffff;
        border: none;
        border-radius: 8px;
        font-size: 0.9rem; /* Smaller font */
        font-weight: 600;
        text-align: center;
        text-decoration: none;
        cursor: pointer;
        box-shadow: 0 4px 10px rgba(123, 0, 58, 0.2);
        transition: all 0.2s ease;
    }

    #bpl-charter-cta-btn-v2:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(123, 0, 58, 0.3);
    }

    /* Animation keyframes */
    @keyframes bpl-subtle-float {
        0% { transform: translateY(0); }
        50% { transform: translateY(-5px); }
        100% { transform: translateY(0); }
    }
    
    @keyframes bpl-bus-shake {
        0%, 100% { transform: translateX(0) rotate(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-1px) rotate(-2deg); }
        20%, 40%, 60%, 80% { transform: translateX(1px) rotate(2deg); }
    }
    
    /* Responsive styles for smaller screens */
    @media (max-width: 400px) {
        .bpl-charter-popup-v2 {
            bottom: 10px;
            right: 10px;
            left: 10px;
            max-width: none; /* Full width on small screens */
            padding: 12px;
            gap: 12px;
        }
        .bpl-charter-icon-v2 {
            width: 40px;
            height: 40px;
        }
        .bpl-charter-icon-v2 i {
            font-size: 20px;
        }
        .bpl-charter-content-v2 p {
            font-size: 0.85rem;
            margin-bottom: 8px;
        }
        #bpl-charter-cta-btn-v2 {
            font-size: 0.85rem;
            padding: 8px 12px;
        }
    }

    </style>