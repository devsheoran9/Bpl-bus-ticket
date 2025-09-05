<?php
// sidebar.php

// Determine active page for highlighting the link
$current_page = basename($_SERVER['PHP_SELF'], ".php");

$is_bus_active = in_array($current_page, ['add_bus', 'view_all_buses', 'edit_bus', 'bus_seat_layout']);
$is_route_active = in_array($current_page, ['add_route', 'view_routes', 'edit_route']);
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

            <!-- Link visible only if user has 'can_manage_operators' permission -->
            <?php if (user_has_permission('can_manage_operators')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'add_operator' ? 'active' : ''; ?>" href="add_operator.php">
                    <i class="fas fa-user-tie nav-icon me-2"></i>Operators
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
            
            <!-- Menu visible only if user has 'can_manage_routes' permission -->
            <?php if (user_has_permission('can_manage_routes')): ?>
            <li class="nav-item">
                <a class="nav-link d-flex justify-content-between align-items-center <?php echo $is_route_active ? 'active' : 'collapsed'; ?>"
                   data-bs-toggle="collapse" href="#routeMenu" role="button" aria-expanded="<?php echo $is_route_active ? 'true' : 'false'; ?>">
                    <span><i class="fas fa-route nav-icon me-2"></i>Manage Routes</span>
                    <i class="fas fa-chevron-down small"></i>
                </a>
                <div class="collapse <?php echo $is_route_active ? 'show' : ''; ?>" id="routeMenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'add_route' ? 'active' : ''; ?>" href="add_route.php">
                                <i class="fas fa-plus-circle me-2"></i>Add Routes
                            </a>
                        </li>
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
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'change_password' ? 'active' : ''; ?>" href="change_password.php">
                    <i class="fas fa-key me-2"></i>Change Password
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </li>
        </ul>
    </div>
</nav>