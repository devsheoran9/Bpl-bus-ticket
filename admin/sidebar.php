<?php
// sidebar.php (Fully Permission-Aware)

// Ensure the permission function is available.
// It's good practice to include the core function file here.
include_once('function/_db.php'); 

// Get the name of the current page file (e.g., 'dashboard', 'add_bus')
$current_page = basename($_SERVER['PHP_SELF'], ".php");

// --- Define which pages belong to which menu for active highlighting ---

$bus_pages = ['add_bus', 'view_all_buses', 'edit_bus', 'manage_seats'];
$is_bus_active = in_array($current_page, $bus_pages);

$route_pages = ['add_route', 'view_routes', 'edit_route'];
$is_route_active = in_array($current_page, $route_pages);

// NEW: Define booking pages
$booking_pages = ['book_ticket', 'view_bookings', 'ticket_view'];
$is_booking_active = in_array($current_page, $booking_pages);
?>
<nav class="sidebar">
    <div class="sidebar-header d-flex text-center" style="justify-content:center;">
        <h2>BPL Tickets</h2>
    </div>
   
    <div class="sidebar-scroll">
        <ul class="nav flex-column">
            <li class="nav-item pt-4">
                <a class="nav-link <?php echo $current_page == 'dashboard' ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="fas fa-tachometer-alt nav-icon me-2"></i>Dashboard
                </a>
            </li>

            <!-- =============================================== -->
            <!--          TICKET BOOKING MENU START              -->
            <!-- =============================================== -->
            <?php // Show this entire menu only if the user has AT LEAST ONE booking-related permission
            if (user_has_permission('can_book_tickets') || user_has_permission('can_view_bookings')): ?>
            <li class="nav-item">
                <a class="nav-link d-flex justify-content-between align-items-center <?php echo $is_booking_active ? 'active' : 'collapsed'; ?>"
                   data-bs-toggle="collapse" href="#bookingMenu" role="button" aria-expanded="<?php echo $is_booking_active ? 'true' : 'false'; ?>">
                    <span><i class="fas fa-ticket-alt nav-icon me-2"></i>Ticket Booking</span>
                    <i class="fas fa-chevron-down small"></i>
                </a>
                <div class="collapse <?php echo $is_booking_active ? 'show' : ''; ?>" id="bookingMenu">
                    <ul class="nav flex-column ms-3">
                        
                        <!-- Show "New Booking" only if user has 'can_book_tickets' permission -->
                        <?php if (user_has_permission('can_book_tickets')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'book_ticket' ? 'active' : ''; ?>" href="book_ticket.php">
                                <i class="fas fa-plus-circle me-2"></i>New Booking
                            </a>
                        </li>
                        <?php endif; ?>

                        <!-- Show "View Bookings" only if user has 'can_view_bookings' permission -->
                        <?php if (user_has_permission('can_view_bookings')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'view_bookings' ? 'active' : ''; ?>" href="view_bookings.php">
                                <i class="fas fa-list-alt me-2"></i>View Bookings
                            </a>
                        </li>
                        <?php endif; ?>
                        
                    </ul>
                </div>
            </li>
            <?php endif; ?>
            <!-- =============================================== -->
            <!--          TICKET BOOKING MENU END                -->
            <!-- =============================================== -->

            <!-- Link visible only if user has 'can_manage_operators' permission -->
            <?php if (user_has_permission('')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'add_staff' ? 'active' : ''; ?>" href="add_staff">
                    <i class="fas fa-user-tie nav-icon me-2"></i>Manage Staff
                </a>
            </li>
            <?php endif; ?>
            

            <?php if (user_has_permission('main_admin')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'employee_bookings' ? 'active' : ''; ?>" href="employee_bookings">
                    <i class="fas fa-user-tie nav-icon me-2"></i>Bookings Report
                </a>
            </li>
            <?php endif; ?>
            
            <!-- Menu visible only if user has 'can_manage_buses' permission -->
            <?php if (user_has_permission('can_manage_buses')): ?>
            <li class="nav-item">
                <a class="nav-link d-flex justify-content-between align-items-center <?php echo $is_bus_active ? 'active' : 'collapsed'; ?>"
                   data-bs-toggle="collapse" href="#busMenu" role="button" aria-expanded="<?php echo $is_bus_active ? 'true' : 'false'; ?>">
                    <span><i class="fas fa-bus nav-icon me-2"></i>Manage Buses</span>
                    <i class="fas fa-chevron-down small"></i>
                </a>
                <div class="collapse <?php echo $is_bus_active ? 'show' : ''; ?>" id="busMenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'add_bus' ? 'active' : ''; ?>" href="add_bus.php">
                                <i class="fas fa-plus-circle me-2"></i>Add New Bus
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'view_all_buses' ? 'active' : ''; ?>" href="view_all_buses.php">
                                <i class="fas fa-list-alt me-2"></i>View All Buses
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <?php endif; ?>
            
            <!-- Menu visible only if user has route-related permissions -->
            <?php if (user_has_permission('can_manage_routes') || user_has_permission('can_edit_routes') || user_has_permission('can_delete_routes')): ?>
            <li class="nav-item">
                <a class="nav-link d-flex justify-content-between align-items-center <?php echo $is_route_active ? 'active' : 'collapsed'; ?>"
                   data-bs-toggle="collapse" href="#routeMenu" role="button" aria-expanded="<?php echo $is_route_active ? 'true' : 'false'; ?>">
                    <span><i class="fas fa-route nav-icon me-2"></i>Manage Routes</span>
                    <i class="fas fa-chevron-down small"></i>
                </a>
                <div class="collapse <?php echo $is_route_active ? 'show' : ''; ?>" id="routeMenu">
                    <ul class="nav flex-column ms-3">
                        <?php if (user_has_permission('can_manage_routes')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'add_route' ? 'active' : ''; ?>" href="add_route.php">
                                <i class="fas fa-plus-circle me-2"></i>Add Routes
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'view_routes' ? 'active' : ''; ?>" href="view_routes.php">
                                <i class="fas fa-list-alt me-2"></i>View All Routes
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <?php endif; ?>
           
            <!-- Link visible only if user has 'can_manage_employees' permission -->
            <?php if (user_has_permission('can_manage_employees')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'add_employee' ? 'active' : ''; ?>" href="add_employee.php">
                    <i class="fas fa-user-plus me-2"></i>Manage Employees
                </a>
            </li>
            <?php endif; ?>
            
            <!-- General links visible to everyone logged in -->
            <!-- Show "Change Password" only if user has 'can_change_password' permission -->
            <?php if (user_has_permission('can_change_password')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'change_password' ? 'active' : ''; ?>" href="change_password.php">
                    <i class="fas fa-key me-2"></i>Change Password
                </a>
            </li>
            <?php endif; ?>
            
            <li class="nav-item">
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </li>
        </ul>
    </div>
</nav>