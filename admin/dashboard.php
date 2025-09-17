<?php
// dashboard.php - Main Business Overview (FIXED)
global $_conn_db;
include_once('function/_db.php');
session_security_check();

try {
    $today_date = date('Y-m-d');
    $name = $_SESSION['user']['name'] ?? 'Guest';

    // --- 1. KPI STATS ---
    $total_buses = $_conn_db->query("SELECT COUNT(*) FROM buses")->fetchColumn();
    $active_routes = $_conn_db->query("SELECT COUNT(*) FROM routes WHERE status = 'Active'")->fetchColumn();

    $today_revenue_stmt = $_conn_db->prepare("SELECT COALESCE(SUM(total_fare), 0) FROM bookings WHERE DATE(created_at) = ? AND booking_status = 'CONFIRMED'");
    $today_revenue_stmt->execute([$today_date]);
    $today_revenue = $today_revenue_stmt->fetchColumn();

    $pending_cancellations = $_conn_db->query("SELECT COUNT(*) FROM cancellations WHERE status = 'PENDING'")->fetchColumn();

    // --- 2. LIVE DATA FEEDS ---
    // A. Live Bookings Feed (recent 5 bookings) - This query needs 'created_at'
    $live_bookings_stmt = $_conn_db->query("
        SELECT b.booking_id, b.ticket_no, r.route_name, COALESCE(a.name, u.username, 'Online User') as booker_name, b.created_at
        FROM bookings b
        JOIN routes r ON b.route_id = r.route_id
        LEFT JOIN admin a ON b.booked_by_employee_id = a.id
        LEFT JOIN users u ON b.user_id = u.id
        ORDER BY b.created_at DESC
        LIMIT 5
    ");
    $live_bookings = $live_bookings_stmt->fetchAll(PDO::FETCH_ASSOC);

    // B. Routes with most bookings overall (top 5) - This query does NOT need 'created_at'
    $top_routes_stmt = $_conn_db->query("
        SELECT r.route_id, r.route_name, COUNT(b.booking_id) as booking_count
        FROM bookings b
        JOIN routes r ON b.route_id = r.route_id
        WHERE b.booking_status = 'CONFIRMED'
        GROUP BY r.route_id, r.route_name
        ORDER BY booking_count DESC
        LIMIT 5
    ");
    $top_routes = $top_routes_stmt->fetchAll(PDO::FETCH_ASSOC);

    // C. Top performing employees (by sales amount) overall - This query does NOT need 'created_at'
    $top_employees_stmt = $_conn_db->query("
        SELECT a.id, a.name, SUM(b.total_fare) as total_sales
        FROM bookings b
        JOIN admin a ON b.booked_by_employee_id = a.id
        WHERE b.booking_status = 'CONFIRMED'
        GROUP BY a.id, a.name
        ORDER BY total_sales DESC
        LIMIT 5
    ");
    $top_employees = $top_employees_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Dashboard Error: Could not fetch data. " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once('head.php'); ?>
    <title>Dashboard - Business Overview</title>
    <style>
        :root {
            --primary: #4a69bd;
            --success: #1dd1a1;
            --danger: #ff6b6b;
            --warning: #feca57;
            --text-dark: #2f3542;
            --text-light: #57606f;
            --bg-light: #f1f2f6;
            --border-color: #dfe4ea;
            --card-shadow: 0 8px 30px rgba(0, 0, 0, 0.06);
        }

        body {
            background-color: var(--bg-light);
        }

        .stat-card {
            background-color: #fff;
            border-radius: 12px;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-decoration: none;
            display: block;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.08);
        }

        .stat-card .title {
            font-weight: 600;
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .stat-card .value {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--text-dark);
        }

        .stat-card .icon {
            position: absolute;
            top: 50%;
            right: 20px;
            font-size: 3.5rem;
            opacity: 0.1;
            transform: translateY(-50%) rotate(-10deg);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            width: 5px;
            border-top-left-radius: 12px;
            border-bottom-left-radius: 12px;
        }

        .stat-card.primary::before {
            background-color: var(--primary);
        }

        .stat-card.success::before {
            background-color: var(--success);
        }

        .stat-card.danger::before {
            background-color: var(--danger);
        }

        .stat-card.warning::before {
            background-color: var(--warning);
        }

        .data-list-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            height: 100%;
        }

        .data-list-card .card-header {
            background: transparent;
            border-bottom: 1px solid var(--border-color);
            font-weight: 600;
            font-size: 1.1rem;
            padding: 1.25rem;
        }

        .data-list-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border-color);
            text-decoration: none;
            color: var(--text-dark);
            transition: background-color 0.2s ease;
        }

        .data-list-item:last-child {
            border-bottom: none;
        }

        .data-list-item:hover {
            background-color: #f8f9fa;
        }

        .item-info .title {
            font-weight: 600;
        }

        .item-info .subtitle {
            font-size: 0.85em;
            color: var(--text-light);
        }

        .item-metric .badge {
            font-size: 0.9em;
            padding: 0.4em 0.7em;
        }
    </style>
</head>

<body>
    <div id="wrapper">
        <?php include_once('sidebar.php'); ?>
        <div class="main-content">
            <?php include_once('header.php'); ?>
            <div class="container-fluid">
                <h1 class="mt-4">Business Overview</h1>
                <p class="text-muted mb-4">A high-level summary of your network's performance.</p>

                <div class="row g-2">
                    <div class="col-xl-3 col-md-6 col-6">
                        <a href="view_all_buses.php" class="stat-card primary">
                            <div class="title">Total Buses</div>
                            <div class="value"><?php echo (int)$total_buses; ?></div>
                            <i class="fas fa-bus icon"></i>
                        </a>
                    </div>
                    <div class="col-xl-3 col-md-6 col-6">
                        <a href="employee_bookings.php?date_from=<?php echo $today_date; ?>&date_to=<?php echo $today_date; ?>" class="stat-card success">
                            <div class="title">Today's Revenue</div>
                            <div class="value">₹<?php echo number_format($today_revenue); ?></div>
                            <i class="fas fa-rupee-sign icon"></i>
                        </a>
                    </div>
                    <div class="col-xl-3 col-md-6 col-6">
                        <a href="view_routes.php" class="stat-card info">
                            <div class="title">Active Routes</div>
                            <div class="value"><?php echo (int)$active_routes; ?></div>
                            <i class="fas fa-route icon"></i>
                        </a>
                    </div>
                    <div class="col-xl-3 col-md-6 col-6">
                        <a href="cancellations.php" class="stat-card warning">
                            <div class="title">Cancellations req.</div>
                            <div class="value"><?php echo (int)$pending_cancellations; ?></div>
                            <i class="fas fa-undo-alt icon"></i>
                        </a>
                    </div>
                </div>

                <div class="row g-4 mt-4">
                    <div class="col-lg-4">
                        <div class="data-list-card">
                            <div class="card-header">Live Bookings Feed</div>
                            <div class="list-group list-group-flush">
                                <?php if (empty($live_bookings)): ?>
                                    <div class="p-4 text-center text-muted">No recent bookings.</div>
                                    <?php else: foreach ($live_bookings as $booking): ?>
                                        <a href="booking_details.php?booking_id=<?php echo $booking['booking_id']; ?>" class="data-list-item">
                                            <div class="item-info">
                                                <div class="title text-truncate"><?php echo htmlspecialchars($booking['route_name']); ?></div>
                                                <div class="subtitle">#<?php echo htmlspecialchars($booking['ticket_no']); ?> by <?php echo htmlspecialchars($booking['booker_name']); ?></div>
                                            </div>
                                            <div class="item-metric">
                                                <!-- This line is now safe because the query guarantees 'created_at' exists -->
                                                <small class="text-muted"><?php echo date('h:i A', strtotime($booking['created_at'])); ?></small>
                                            </div>
                                        </a>
                                <?php endforeach;
                                endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="data-list-card">
                            <div class="card-header">Busiest Routes (All-Time)</div>
                            <div class="list-group list-group-flush">
                                <?php if (empty($top_routes)): ?>
                                    <div class="p-4 text-center text-muted">No booking data available.</div>
                                    <?php else: foreach ($top_routes as $route): ?>
                                        <a href="view_bookings.php?route_id=<?php echo $route['route_id']; ?>" class="data-list-item">
                                            <div class="item-info">
                                                <div class="title text-truncate"><?php echo htmlspecialchars($route['route_name']); ?></div>
                                            </div>
                                            <div class="item-metric">
                                                <span class="badge bg-primary rounded-pill"><?php echo $route['booking_count']; ?> Bookings</span>
                                            </div>
                                        </a>
                                <?php endforeach;
                                endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="data-list-card">
                            <div class="card-header">Top Employees (All-Time Sales)</div>
                            <div class="list-group list-group-flush">
                                <?php if (empty($top_employees)): ?>
                                    <div class="p-4 text-center text-muted">No employee sales recorded.</div>
                                    <?php else: foreach ($top_employees as $employee): ?>
                                        <a href="employee_bookings.php?employee_id=<?php echo $employee['id']; ?>" class="data-list-item">
                                            <div class="item-info">
                                                <div class="title text-truncate"><?php echo htmlspecialchars($employee['name']); ?></div>
                                            </div>
                                            <div class="item-metric">
                                                <span class="badge bg-success rounded-pill">₹<?php echo number_format($employee['total_sales']); ?></span>
                                            </div>
                                        </a>
                                <?php endforeach;
                                endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <br>
    <?php include_once('foot.php'); ?>
</body>

</html>