<nav class="sidebar">
    <div class="sidebar-header d-flex text-center" style="justify-content:center;">
        <h2>BPL Tickets</h2>
    </div>
   
    <div class="sidebar-scroll">
        <ul class="nav flex-column">
            <li class="nav-item pt-4">
                <a class="nav-link <?php echo $dashboard_active ? 'active' : ''; ?>" href="dashboard">
                    <i class="fas fa-tachometer-alt nav-icon me-2"></i>Dashboard
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo $add_operator_active ? 'active' : ''; ?>" href="add_operator.php">
                    <i class="fas fa-user-tie nav-icon me-2"></i>Operators
                </a>
            </li>
            
            <li class="nav-item">
                <!-- Dropdown for Buses -->
                <a class="nav-link d-flex justify-content-between align-items-center <?php echo $is_bus_active ? 'active' : 'collapsed'; ?>"
                   data-bs-toggle="collapse" href="#busMenu" role="button"
                   aria-expanded="<?php echo $is_bus_active ? 'true' : 'false'; ?>"
                   aria-controls="busMenu">
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
            
            <li class="nav-item">
                <!-- Dropdown for Routes -->
                <a class="nav-link d-flex justify-content-between align-items-center <?php echo $is_route_active ? 'active' : 'collapsed'; ?>"
                   data-bs-toggle="collapse" href="#routeMenu" role="button"
                   aria-expanded="<?php echo $is_route_active ? 'true' : 'false'; ?>"
                   aria-controls="routeMenu">
                    <span><i class="fas fa-route nav-icon me-2"></i>Manage Routes</span>
                    <i class="fas fa-chevron-down small"></i>
                </a>
                <div class="collapse <?php echo $is_route_active ? 'show' : ''; ?>" id="routeMenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'add_route' ? 'active' : ''; ?>" href="add_route.php">
                                <i class="fas fa-plus-circle me-2"></i>Add / Edit Routes
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
           
            <li class="nav-item">
                <a class="nav-link <?php echo $change_password_active ? 'active' : ''; ?>" href="change_password.php">
                    <i class="fas fa-key me-2"></i>Change Password
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $change_profile_active ? 'active' : ''; ?>" href="change_profile.php">
                    <i class="fas fa-user me-2"></i>Account Details
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