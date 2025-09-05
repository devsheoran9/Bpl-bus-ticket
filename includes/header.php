<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
} 

require 'auth_check.php';
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
    <link rel="stylesheet" href="css/custom.css?<?php echo time(); ?>">
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index">
                <!-- <img src="https://s3.rdbuz.com/web/images/website/rb_logo.png" alt="Logo" style="height: 35px;"> -->
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="index"><i class="bi bi-bus-front-fill"></i> Bus Tickets</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <a class="nav-link" href="#" data-bs-toggle="offcanvas" data-bs-target="#accountSidebar" aria-controls="accountSidebar">
                        <i class="bi bi-person-circle"></i> Account
                    </a>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Right Sidebar -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="accountSidebar" aria-labelledby="accountSidebarLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="accountSidebarLabel">Account</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">

            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                <!-- Logged In View -->
                <div class="mb-4">
                    <h5>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h5>
                </div>
            <?php else: ?>
                <!-- Logged Out View -->
                <h2 class="sidebar-login-prompt">Log in to manage your bookings</h2>
                <div class="d-grid gap-2 my-4">
                    <a href="login.php" class="btn btn-primary btn-lg">Log in</a>
                </div>
                <p class="text-center">Don't have an account? <a href="register.php">Sign up</a></p>
            <?php endif; ?>

            <?php if ($abc !== 'logout_user'){ ?>
            <h3 class="sidebar-heading">My details</h3>
            <ul class="sidebar-menu">
                <li>
                    <a href="#" class="sidebar-menu-item">
                        <div class="icon-text-group">
                            <i class="bi bi-list-ul"></i>
                            <span>Bookings</span>
                        </div>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
                <li>
                    <a href="profile" class="sidebar-menu-item">
                        <div class="icon-text-group">
                            <i class="bi bi-person"></i>
                            <span>Personal information</span>
                        </div>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>
            <?php } ?>

            <h3 class="sidebar-heading">FAQS</h3>
            <ul class="sidebar-menu">
                <li>
                    <a href="faq" class="sidebar-menu-item">
                        <div class="icon-text-group">
                            <i class="bi bi-question-circle"></i>
                            <span>Any doubt</span>
                        </div>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>


            <h3 class="sidebar-heading">More</h3>
            <ul class="sidebar-menu">
                <li>
                    <a href="#" class="sidebar-menu-item">
                        <div class="icon-text-group">
                            <i class="bi bi-tag"></i>
                            <span>Offers</span>
                        </div>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
                <li>
                    <a href="about_us" class="sidebar-menu-item">
                        <div class="icon-text-group">
                            <i class="bi bi-info-circle"></i>
                            <span>Know about us</span>
                        </div>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
                <li>
                    <a href="help" class="sidebar-menu-item">
                        <div class="icon-text-group">
                            <i class="bi bi-question-circle"></i>
                            <span>Help</span>
                        </div>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
                <li>
                    <a href="#" class="sidebar-menu-item">
                        <div class="icon-text-group">
                            <i class="bi bi-scissors"></i>
                            <span>Cancel Ticket</span>
                        </div>
                        <i class="bi bi-chevron-right"></i>
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