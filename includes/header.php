<?php
// It's best practice to connect to the DB first and start the session.
include_once __DIR__ . "/../admin/function/_db.php";
// The auth_check.php file will now have access to the DB connection.
require_once 'auth_check.php';

// --- NEW: Get the current page filename to use for the active sidebar link ---
$current_page = basename($_SERVER['PHP_SELF']);
$abc = user_login($type = 'header');
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Link to your new custom CSS file -->
    <link rel="stylesheet" href="css/custom.css?v=<?php echo time(); ?>">
</head>

<body>

    <!-- === MODIFIED NAVBAR STRUCTURE FOR RESPONSIVE DESIGN === -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <!-- You can place your logo image here -->
                <i class="bi bi-bus-front"></i> BPL Tickets
            </a>

            <!-- The main menu items now sit between the logo and the account button -->
           
            <!-- The account link is now separate and will always be on the far right -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="#" data-bs-toggle="offcanvas" data-bs-target="#accountSidebar" aria-controls="accountSidebar">
                        <i class="bi bi-person-circle"></i> Account
                    </a>
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
                <h2 class="sidebar-login-prompt">Log in to manage your bookings</h2>
                <div class="d-grid gap-2 my-4">
                    <a href="login.php" class="btn btn-primary btn-lg">Log in</a>
                </div>
                <p class="text-center">Don't have an account? <a href="register.php">Sign up</a></p>
            <?php endif; ?>

            <h4 class="sidebar-heading">My details</h4>
            <ul class="sidebar-menu">
                <!-- === MODIFIED SIDEBAR LINKS WITH ACTIVE CLASS LOGIC === -->
                <li>
                    <a href="bookings.php" class="sidebar-menu-item <?php if ($current_page == 'bookings.php') echo 'active'; ?>">
                        <div class="icon-text-group"><i class="bi bi-list-ul"></i><span>Bookings</span></div><i class="bi bi-chevron-right"></i>
                    </a>
                </li>
                <?php if ($abc !== 'logout_user') { ?>
                    <li>
                        <a href="profile.php" class="sidebar-menu-item <?php if ($current_page == 'profile.php') echo 'active'; ?>">
                            <div class="icon-text-group"><i class="bi bi-person"></i><span>Personal information</span></div><i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                    <li>
                        <a href="add_review.php" class="sidebar-menu-item <?php if ($current_page == 'add_review.php') echo 'active'; ?>">
                            <div class="icon-text-group"><i class="bi bi-pencil-square"></i><span>Add your review</span></div><i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                <?php } ?>
                <li>
                    <a href="reviews.php" class="sidebar-menu-item <?php if ($current_page == 'reviews.php') echo 'active'; ?>">
                        <div class="icon-text-group"><i class="bi bi-chat-left-text"></i><span>View Reviews</span></div><i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>

            <h3 class="sidebar-heading">FAQS</h3>
            <ul class="sidebar-menu">
                <li>
                    <a href="faq.php" class="sidebar-menu-item <?php if ($current_page == 'faq.php') echo 'active'; ?>">
                        <div class="icon-text-group"><i class="bi bi-question-circle"></i><span>Any doubt</span></div><i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>

            <h3 class="sidebar-heading">More</h3>
            <ul class="sidebar-menu">
                <li>
                    <a href="#" class="sidebar-menu-item">
                        <div class="icon-text-group"><i class="bi bi-tag"></i><span>Offers</span></div><i class="bi bi-chevron-right"></i>
                    </a>
                </li>
                <li>
                    <a href="about_us.php" class="sidebar-menu-item <?php if ($current_page == 'about_us.php') echo 'active'; ?>">
                        <div class="icon-text-group"><i class="bi bi-info-circle"></i><span>Know about us</span></div><i class="bi bi-chevron-right"></i>
                    </a>
                </li>
                <li>
                    <a href="help.php" class="sidebar-menu-item <?php if ($current_page == 'help.php') echo 'active'; ?>">
                        <div class="icon-text-group"><i class="bi bi-question-circle"></i><span>Help</span></div><i class="bi bi-chevron-right"></i>
                    </a>
                </li>
                <li>
                    <a href="cancel_ticket" class="sidebar-menu-item">
                        <div class="icon-text-group"><i class="bi bi-scissors"></i><span>Cancel Ticket</span></div><i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>

            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                <div class="d-grid gap-2 mt-4">
                    <a href="logout.php" class="btn btn-outline-secondary" onclick="return confirm('Are you sure you want to logout?');">Logout</a>
                </div>
            <?php endif; ?>
        </div>
    </div>