<?php

// dashboard.php - Advanced Business & Personal Overview (FIXED & RESPONSIVE)
global $_conn_db;
include_once('function/_db.php');
session_security_check();

// --- PERMISSION & USER IDENTIFICATION ---
$is_main_admin = user_has_permission('main_admin');
$employee_id = $_SESSION['user']['id'];
$name = htmlspecialchars($_SESSION['user']['name'] ?? 'Guest');
$allowed_route_ids_for_employee = get_assigned_route_ids_for_employee($employee_id);

$kpi = [];
$charts = [];
$feeds = [];

try {
    $today_date = date('Y-m-d');
    $yesterday_date = date('Y-m-d', strtotime('-1 day'));
    $current_month = date('Y-m');

    if ($is_main_admin) {
        $kpi['active_buses'] = $_conn_db->query("SELECT COUNT(*) FROM buses WHERE status = 'Active'")->fetchColumn();
        $kpi['active_routes'] = $_conn_db->query("SELECT COUNT(*) FROM routes WHERE status = 'Active'")->fetchColumn();
        $kpi['total_users'] = $_conn_db->query("SELECT COUNT(*) FROM users WHERE status = 1")->fetchColumn();
        $kpi['pending_cancellations'] = $_conn_db->query("SELECT COUNT(*) FROM cancellations WHERE status = 'PENDING'")->fetchColumn();

        // Daily metrics use 'travel_date' to reflect operational data (trips happening today).
        $stmt_today = $_conn_db->prepare("SELECT COUNT(booking_id), COALESCE(SUM(total_fare), 0) FROM bookings WHERE travel_date = ? AND booking_status = 'CONFIRMED'");
        $stmt_today->execute([$today_date]);
        [$kpi['today_bookings'], $kpi['today_revenue']] = $stmt_today->fetch(PDO::FETCH_NUM);
        $kpi['today_avg_booking_value'] = ($kpi['today_bookings'] > 0) ? $kpi['today_revenue'] / $kpi['today_bookings'] : 0;

        $stmt_yesterday = $_conn_db->prepare("SELECT COUNT(booking_id), COALESCE(SUM(total_fare), 0) FROM bookings WHERE travel_date = ? AND booking_status = 'CONFIRMED'");
        $stmt_yesterday->execute([$yesterday_date]);
        [$kpi['yesterday_bookings'], $kpi['yesterday_revenue']] = $stmt_yesterday->fetch(PDO::FETCH_NUM);

        $stmt_passengers = $_conn_db->prepare("SELECT COUNT(p.passenger_id) FROM passengers p JOIN bookings b ON p.booking_id = b.booking_id WHERE b.travel_date = ? AND b.booking_status = 'CONFIRMED'");
        $stmt_passengers->execute([$today_date]);
        $kpi['today_passengers'] = $stmt_passengers->fetchColumn();

        $stmt_monthly = $_conn_db->prepare("SELECT COALESCE(SUM(total_fare), 0) FROM bookings WHERE DATE_FORMAT(created_at, '%Y-%m') = ? AND booking_status = 'CONFIRMED'");
        $stmt_monthly->execute([$current_month]);
        $kpi['monthly_revenue'] = $stmt_monthly->fetchColumn();

        $thirty_days_ago = date('Y-m-d', strtotime('-30 days'));
        
        // 1. Get Route-wise revenue for the last 30 days
        $stmt_route_performance = $_conn_db->prepare(
            "SELECT r.route_name, SUM(b.total_fare) as monthly_revenue
             FROM bookings b
             JOIN routes r ON b.route_id = r.route_id
             WHERE b.created_at >= ? AND b.booking_status = 'CONFIRMED'
             GROUP BY r.route_id, r.route_name
             ORDER BY monthly_revenue DESC
             LIMIT 10" // Get top 10 performing routes
        );
        $stmt_route_performance->execute([$thirty_days_ago]);
        $route_performance_data = $stmt_route_performance->fetchAll(PDO::FETCH_ASSOC);
        
        // Prepare data for the new chart
        $charts['route_performance'] = ['labels' => [], 'data' => []];
        foreach ($route_performance_data as $route_data) {
            $charts['route_performance']['labels'][] = $route_data['route_name'];
            $charts['route_performance']['data'][] = (float)$route_data['monthly_revenue'];
        }

        // 2. Get overall KPIs for the last 30 days
        $stmt_30_day_kpi = $_conn_db->prepare(
            "SELECT 
                COALESCE(SUM(total_fare), 0) as total_revenue_30d,
                COUNT(booking_id) as total_bookings_30d,
                COUNT(DISTINCT user_id) as unique_customers_30d
             FROM bookings 
             WHERE created_at >= ? AND booking_status = 'CONFIRMED'"
        );
        $stmt_30_day_kpi->execute([$thirty_days_ago]);
        $kpi['30_day_stats'] = $stmt_30_day_kpi->fetch(PDO::FETCH_ASSOC);

        // Corrected Bus Occupancy to EXCLUDE Chartered Buses
        $today_day_short = date('D');
        $stmt_occupancy = $_conn_db->prepare(
            "SELECT
                (SELECT COUNT(p.passenger_id)
                 FROM passengers p
                 JOIN bookings b ON p.booking_id = b.booking_id
                 WHERE b.travel_date = :today_date_1 AND b.booking_status = 'CONFIRMED') AS total_booked,
                (SELECT SUM(s_count.total_seats) FROM (
                    SELECT COUNT(s.seat_id) as total_seats
                    FROM route_schedules rs
                    JOIN routes r ON rs.route_id = r.route_id
                    JOIN buses b ON r.bus_id = b.bus_id
                    JOIN seats s ON b.bus_id = s.bus_id
                    WHERE 
                        rs.operating_day = :today_day
                        AND r.status = 'Active' 
                        AND s.is_bookable = 1
                        AND r.route_id NOT IN (SELECT route_id FROM charter_bookings WHERE travel_date = :today_date_2)
                    GROUP BY b.bus_id
                ) AS s_count) AS total_available_seats"
        );
        $stmt_occupancy->execute([':today_date_1' => $today_date, ':today_day' => $today_day_short, ':today_date_2' => $today_date]);
        $occupancy_data = $stmt_occupancy->fetch(PDO::FETCH_ASSOC);
        if (!$occupancy_data || is_null($occupancy_data['total_available_seats'])) {
            $occupancy_data = ['total_booked' => 0, 'total_available_seats' => 0];
        }
        $kpi['today_occupancy_rate'] = ($occupancy_data['total_available_seats'] > 0) ? ($occupancy_data['total_booked'] / $occupancy_data['total_available_seats']) * 100 : 0;

        // Weekly revenue chart
        $charts['weekly_revenue'] = ['labels' => [], 'data' => []];
        $seven_days_ago = date('Y-m-d', strtotime('-6 days'));
        $stmt_weekly = $_conn_db->prepare("SELECT DATE(created_at) as booking_date, SUM(total_fare) as daily_revenue FROM bookings WHERE created_at >= ? AND booking_status = 'CONFIRMED' GROUP BY DATE(created_at) ORDER BY booking_date");
        $stmt_weekly->execute([$seven_days_ago]);
        $revenue_by_date = $stmt_weekly->fetchAll(PDO::FETCH_KEY_PAIR);
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $charts['weekly_revenue']['labels'][] = date('D, j M', strtotime($date));
            $charts['weekly_revenue']['data'][] = (float)($revenue_by_date[$date] ?? 0);
        }

        // Sales breakdown for today
        $stmt_sales_breakdown = $_conn_db->prepare("SELECT COALESCE(SUM(CASE WHEN t.transaction_id IS NOT NULL THEN b.total_fare ELSE 0 END), 0) as online_sales, COALESCE(SUM(CASE WHEN t.transaction_id IS NULL THEN b.total_fare ELSE 0 END), 0) as cash_sales FROM bookings b LEFT JOIN transactions t ON b.booking_id = t.booking_id WHERE b.travel_date = ? AND b.booking_status = 'CONFIRMED'");
        $stmt_sales_breakdown->execute([$today_date]);
        $charts['sales_breakdown'] = $stmt_sales_breakdown->fetch(PDO::FETCH_ASSOC);

        // --- MODIFIED QUERIES FOR FEEDS ---
        $feeds['live_bookings'] = $_conn_db->query("SELECT b.booking_id, b.ticket_no, r.route_name, COALESCE(a.name, u.username, 'Online') as booker, b.created_at FROM bookings b JOIN routes r ON b.route_id = r.route_id LEFT JOIN admin a ON b.booked_by_employee_id = a.id LEFT JOIN users u ON b.user_id = u.id ORDER BY b.created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
        
        // MODIFICATION 1: Busiest routes based on last 30 days revenue
        $stmt_top_routes = $_conn_db->prepare(
            "SELECT r.route_id, r.route_name, COUNT(b.booking_id) as booking_count, SUM(b.total_fare) as total_revenue 
             FROM bookings b JOIN routes r ON b.route_id = r.route_id 
             WHERE b.booking_status = 'CONFIRMED' AND b.created_at >= ? 
             GROUP BY r.route_id, r.route_name 
             ORDER BY total_revenue DESC 
             LIMIT 5"
        );
        $stmt_top_routes->execute([$thirty_days_ago]);
        $feeds['top_routes'] = $stmt_top_routes->fetchAll(PDO::FETCH_ASSOC);

        // MODIFICATION 2: Top employees based on last 7 days sales
        $seven_days_ago_for_employees = date('Y-m-d', strtotime('-7 days'));
        $stmt_top_employees = $_conn_db->prepare(
            "SELECT a.id, a.name, SUM(b.total_fare) as total_sales, COUNT(b.booking_id) as booking_count 
             FROM bookings b JOIN admin a ON b.booked_by_employee_id = a.id 
             WHERE b.booking_status = 'CONFIRMED' AND b.created_at >= ? 
             GROUP BY a.id, a.name 
             ORDER BY total_sales DESC 
             LIMIT 5"
        );
        $stmt_top_employees->execute([$seven_days_ago_for_employees]);
        $feeds['top_employees'] = $stmt_top_employees->fetchAll(PDO::FETCH_ASSOC);
        // --- END OF MODIFIED QUERIES ---

    } else {
        // Sub-Admin (Employee) View Logic - No changes needed here
        $kpi['my_assigned_routes'] = count($allowed_route_ids_for_employee);

        $stmt_today = $_conn_db->prepare("SELECT COUNT(booking_id), COALESCE(SUM(total_fare), 0) FROM bookings WHERE travel_date = ? AND booking_status = 'CONFIRMED' AND booked_by_employee_id = ?");
        $stmt_today->execute([$today_date, $employee_id]);
        [$kpi['my_today_bookings'], $kpi['my_today_revenue']] = $stmt_today->fetch(PDO::FETCH_NUM);

        $stmt_yesterday = $_conn_db->prepare("SELECT COALESCE(SUM(total_fare), 0) FROM bookings WHERE travel_date = ? AND booking_status = 'CONFIRMED' AND booked_by_employee_id = ?");
        $stmt_yesterday->execute([$yesterday_date, $employee_id]);
        $kpi['my_yesterday_revenue'] = $stmt_yesterday->fetchColumn();

        $stmt_due = $_conn_db->prepare("SELECT COALESCE(SUM(total_fare), 0) FROM bookings WHERE booked_by_employee_id = ? AND booking_status = 'CONFIRMED' AND payment_status = 'PAID' AND booking_id NOT IN (SELECT booking_id FROM cash_collections_log)");
        $stmt_due->execute([$employee_id]);
        $kpi['my_cash_due'] = $stmt_due->fetchColumn();

        $stmt_my_live_bookings = $_conn_db->prepare("SELECT b.booking_id, b.ticket_no, r.route_name, b.created_at FROM bookings b JOIN routes r ON b.route_id = r.route_id WHERE b.booked_by_employee_id = ? ORDER BY b.created_at DESC LIMIT 5");
        $stmt_my_live_bookings->execute([$employee_id]);
        $feeds['my_live_bookings'] = $stmt_my_live_bookings->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($allowed_route_ids_for_employee)) {
            $placeholders = implode(',', array_fill(0, count($allowed_route_ids_for_employee), '?'));
            $stmt_my_top_routes = $_conn_db->prepare("SELECT r.route_name, r.route_id, COUNT(b.booking_id) as booking_count FROM bookings b JOIN routes r ON b.route_id = r.route_id WHERE b.booking_status = 'CONFIRMED' AND r.route_id IN ($placeholders) GROUP BY r.route_id, r.route_name ORDER BY booking_count DESC LIMIT 5");
            $stmt_my_top_routes->execute($allowed_route_ids_for_employee);
            $feeds['my_top_routes'] = $stmt_my_top_routes->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $feeds['my_top_routes'] = [];
        }
    }

    // Live Route Tracking Logic - No changes needed here
    $feeds['live_routes'] = [];
    $current_time_obj = new DateTime();
    $current_day_short = date('D');

    $stmt_live_check = $_conn_db->prepare(
        "SELECT 
            r.route_id, r.route_name, b.bus_name, rs.departure_time,
            COALESCE(MAX(rst.duration_from_start_minutes), 90) as duration_to_last_stop
        FROM routes r
        JOIN route_schedules rs ON r.route_id = rs.route_id
        JOIN buses b ON r.bus_id = b.bus_id
        LEFT JOIN route_stops rst ON r.route_id = rst.route_id
        WHERE rs.operating_day = ? AND r.status = 'Active'
        GROUP BY r.route_id, r.route_name, b.bus_name, rs.departure_time
        ORDER BY rs.departure_time ASC"
    );
    $stmt_live_check->execute([$current_day_short]);
    $potential_routes = $stmt_live_check->fetchAll(PDO::FETCH_ASSOC);

    foreach ($potential_routes as $route) {
        $departure_time_str = date('Y-m-d') . ' ' . $route['departure_time'];
        $departure_time = new DateTime($departure_time_str);
        $duration_to_last_stop = (int) $route['duration_to_last_stop'];
        $final_leg_buffer_minutes = 30;
        $total_estimated_duration = $duration_to_last_stop + $final_leg_buffer_minutes;
        $final_arrival_time = (clone $departure_time)->modify("+$total_estimated_duration minutes");

        if ($current_time_obj >= $departure_time && $current_time_obj <= $final_arrival_time) {
            $feeds['live_routes'][] = [
                'route_id' => $route['route_id'],
                'route_name' => $route['route_name'],
                'bus_name' => $route['bus_name'],
                'departure_time' => $departure_time->format('h:i A')
            ];
        }
    }
} catch (PDOException $e) {
    die("Dashboard Error: Could not fetch data. " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once('head.php'); ?>
    <title>Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Inter', sans-serif;
        }

        .dashboard-header {
            margin-bottom: 2rem;
        }

        .dashboard-title {
            font-weight: 700;
            font-size: 1.8rem;
        }

        .stat-card {
            background-color: #fff;
            border-radius: 12px;
            padding: 1.25rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }

        .stat-card .title-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .stat-card .title {
            font-weight: 600;
            color: #6c757d;
            font-size: 0.9rem;
        }

        .stat-card .icon {
            font-size: 1.5rem;
            opacity: 0.3;
        }

        .stat-card .value {
            font-size: 2rem;
            font-weight: 700;
            color: #212529;
            line-height: 1.2;
        }

        .stat-card .comparison {
            font-size: 0.8rem;
            font-weight: 500;
            margin-top: auto;
            padding-top: 0.5rem;
        }

        .text-success {
            color: #198754 !important;
        }

        .text-danger {
            color: #dc3545 !important;
        }

        .chart-card,
        .data-list-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            height: 100%;
        }

        .chart-card .card-header,
        .data-list-card .card-header {
            background: transparent;
            border-bottom: 1px solid #e9ecef;
            font-weight: 600;
            padding: 1rem 1.25rem;
        }

        .chart-container {
            padding: 1.5rem;
        }

        .data-list-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #f0f0f0;
            text-decoration: none;
            color: #343a40;
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
            color: #6c757d;
        }

        @media (max-width: 991.98px) {
            .dashboard-title {
                font-size: 1.6rem;
            }

            .stat-card .value {
                font-size: 1.75rem;
            }

            .chart-card,
            .data-list-card {
                margin-bottom: 1.5rem;
            }
        }

        @media (max-width: 767.98px) {
            .stat-card {
                padding: 1rem;
            }

            .stat-card .value {
                font-size: 1.5rem;
            }

            .stat-card .icon {
                font-size: 1.2rem;
            }

            .stat-card .comparison {
                font-size: 0.75rem;
            }

            .chart-container {
                padding: 1rem;
            }
        }

        @media (max-width: 575.98px) {
            .dashboard-header {
                text-align: center;
            }

            .col-6 {
                flex: 0 0 50%;
                max-width: 50%;
            }

            .data-list-item {
                padding: 0.75rem 1rem;
            }

            .item-info .title {
                font-size: 0.9rem;
            }
        }

        /* --- Live Tracking Timeline Styles --- */
        .live-timeline {
            list-style: none;
            padding-left: 1rem;
        }

        .live-timeline-item {
            position: relative;
            padding-bottom: 1.5rem;
            padding-left: 2rem;
        }

        .live-timeline-item::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 12px;
            bottom: 0;
            width: 2px;
            background-color: #e9ecef;
        }

        .live-timeline-item:last-child::before {
            display: none;
        }

        .live-timeline-icon {
            position: absolute;
            left: 0;
            top: 0;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            background-color: #adb5bd;
            border: 2px solid #fff;
            box-shadow: 0 0 0 2px #adb5bd;
        }

        .live-timeline-item.status-completed .live-timeline-icon {
            background-color: #198754;
            box-shadow: 0 0 0 2px #198754;
        }

        .live-timeline-item.status-current_segment .live-timeline-icon {
            background-color: #0d6efd;
            box-shadow: 0 0 0 2px #0d6efd;
            animation: pulse 1.5s infinite;
        }

        .live-timeline-item.status-completed::before {
            background-color: #198754;
        }

        .live-timeline-item.status-current_segment::before {
            background: linear-gradient(#198754, #e9ecef);
        }

        .live-timeline-item .time {
            font-size: 0.8rem;
            color: #6c757d;
        }

        .live-timeline-item .title {
            font-weight: 600;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.7);
            }

            70% {
                box-shadow: 0 0 0 8px rgba(13, 110, 253, 0);
            }

            100% {
                box-shadow: 0 0 0 2px rgba(13, 110, 253, 0);
            }
        }
          /* NEW STYLES for the analytics section */
          .analytics-section {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            margin-top: 2rem;
        }
        .analytics-kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            padding: 1.5rem;
        }
        .kpi-item {
            text-align: center;
            padding: 1rem;
            background-color: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #0d6efd;
        }
        .kpi-item .label {
            font-size: 0.9rem;
            font-weight: 600;
            color: #6c757d;
            margin-bottom: 0.5rem;
        }
        .kpi-item .value {
            font-size: 1.75rem;
            font-weight: 700;
            color: #212529;
        }
        
    </style>
</head>

<body>
    <div id="wrapper">
        <?php include_once('sidebar.php'); ?>
        <div class="main-content">
            <?php include_once('header.php'); ?>
            <div class="container-fluid">
                <div class="dashboard-header">
                    <h1 class="dashboard-title">Welcome back, <?php echo $name; ?>!</h1>
                    <p class="text-muted"><?php echo $is_main_admin ? "Here's the network-wide business overview." : "Here's your personal performance summary."; ?></p>
                </div>

                <?php if ($is_main_admin): ?>
                    <!-- MAIN ADMIN VIEW -->
                    <div class="row g-4">
                        <div class="col-xl-3 col-md-6"><a href="employee_bookings.php?date_from=<?php echo $today_date; ?>&date_to=<?php echo $today_date; ?>" class="stat-card">
                                <div class="title-row"><span class="title">Today's Revenue</span><i class="fas fa-rupee-sign icon text-success"></i></div>
                                <div class="value">₹<?php echo number_format($kpi['today_revenue']); ?></div>
                                <div class="comparison text-<?php $rev_diff = $kpi['today_revenue'] - $kpi['yesterday_revenue'];
                                                            echo ($rev_diff >= 0) ? 'success' : 'danger'; ?>">
                                    <i class="fas fa-arrow-<?php echo ($rev_diff >= 0) ? 'up' : 'down'; ?>"></i>
                                    <?php $rev_perc = $kpi['yesterday_revenue'] > 0 ? ($rev_diff / $kpi['yesterday_revenue']) * 100 : 100;
                                    echo number_format(abs($rev_perc), 1); ?>% vs Yesterday
                                </div>
                            </a></div>
                        <div class="col-xl-3 col-md-6"><a href="employee_bookings.php?date_from=<?php echo $today_date; ?>&date_to=<?php echo $today_date; ?>" class="stat-card">
                                <div class="title-row"><span class="title">Bookings Today</span><i class="fas fa-ticket-alt icon text-info"></i></div>
                                <div class="value"><?php echo (int)$kpi['today_bookings']; ?></div>
                                <div class="comparison text-<?php $book_diff = $kpi['today_bookings'] - $kpi['yesterday_bookings'];
                                                            echo ($book_diff >= 0) ? 'success' : 'danger'; ?>">
                                    <i class="fas fa-arrow-<?php echo ($book_diff >= 0) ? 'up' : 'down'; ?>"></i>
                                    <?php $book_perc = $kpi['yesterday_bookings'] > 0 ? ($book_diff / $kpi['yesterday_bookings']) * 100 : 100;
                                    echo number_format(abs($book_perc), 1); ?>% vs Yesterday
                                </div>
                            </a></div>
                        <div class="col-xl-3 col-md-6"><a href="employee_bookings.php?date_from=<?php echo $today_date; ?>&date_to=<?php echo $today_date; ?>" class="stat-card">
                                <div class="title-row"><span class="title">Avg. Booking Value</span><i class="fas fa-balance-scale icon text-primary"></i></div>
                                <div class="value">₹<?php echo number_format($kpi['today_avg_booking_value'], 2); ?></div>
                                <div class="comparison text-muted">For today's bookings</div>
                            </a></div>
                        <div class="col-xl-3 col-md-6"><a href="todays_departures.php" class="stat-card">
                                <div class="title-row"><span class="title">Today's Bus Occupancy</span><i class="fas fa-chair icon text-warning"></i></div>
                                <div class="value"><?php echo number_format($kpi['today_occupancy_rate'], 1); ?>%</div>
                                <div class="progress" style="height: 5px;">
                                    <div class="progress-bar bg-warning" style="width: <?php echo $kpi['today_occupancy_rate']; ?>%"></div>
                                </div>
                            </a></div>
                    </div>
                    <div class="row g-4 mt-4">
                        <div class="col-lg-7">
                            <div class="chart-card">
                                <div class="card-header">Revenue Last 7 Days</div>
                                <div class="chart-container"><canvas id="weeklyRevenueChart"></canvas></div>
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="chart-card">
                                <div class="card-header">Today's Sales Breakdown</div>
                                <div class="chart-container"><canvas id="salesBreakdownChart"></canvas></div>
                            </div>
                        </div>
                    </div>
                    <div class="analytics-section p-1">
                        <div class="card-header">
                            <h4><i class="fas fa-chart-line me-2"></i>30-Day Performance Analytics</h4>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="height: 400px;">
                                <canvas id="routePerformanceChart"></canvas>
                            </div>
                            <hr>
                            <div class="analytics-kpi-grid">
                                <div class="kpi-item">
                                    <div class="label">Total Revenue (30 Days)</div>
                                    <div class="value">₹<?php echo number_format($kpi['30_day_stats']['total_revenue_30d']); ?></div>
                                </div>
                                <div class="kpi-item" style="border-left-color: #198754;">
                                    <div class="label">Total Bookings (30 Days)</div>
                                    <div class="value"><?php echo number_format($kpi['30_day_stats']['total_bookings_30d']); ?></div>
                                </div>
                                <div class="kpi-item" style="border-left-color: #ffc107;">
                                    <div class="label">Unique Customers (30 Days)</div>
                                    <div class="value"><?php echo number_format($kpi['30_day_stats']['unique_customers_30d']); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mt-4">
                        <div class="col-12">
                            <div class="data-list-card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-bus-alt text-primary me-2"></i>Live Bus Tracking</span>
                                    <span class="badge bg-success rounded-pill"><?php echo count($feeds['live_routes']); ?> On Route</span>
                                </div>
                                <?php if (empty($feeds['live_routes'])): ?>
                                    <div class="p-4 text-center text-muted">No buses are currently on their routes.</div>
                                <?php else: ?>
                                    <?php foreach (array_slice($feeds['live_routes'], 0, 5) as $route): ?>
                                        <a href="#" class="data-list-item live-route-details-trigger" data-route-id="<?php echo $route['route_id']; ?>">
                                            <div class="item-info">
                                                <div class="title"><?php echo htmlspecialchars($route['route_name']); ?></div>
                                                <div class="subtitle"><?php echo htmlspecialchars($route['bus_name']); ?> - Departs at <?php echo $route['departure_time']; ?></div>
                                            </div>
                                            <span class="text-primary"><i class="fas fa-map-marked-alt me-2"></i>View Progress</span>
                                        </a>
                                    <?php endforeach; ?>
                                    <?php if (count($feeds['live_routes']) > 5): ?>
                                        <div class="card-footer text-center bg-light">
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#liveRoutesModal">View All <?php echo count($feeds['live_routes']); ?> Live Buses</button>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="row g-4 mt-4">
                        <div class="col-lg-4">
                            <div class="data-list-card">
                                <div class="card-header">Live Bookings</div>
                                <?php if (empty($feeds['live_bookings'])): ?>
                                    <div class='p-4 text-muted text-center'>No recent bookings.</div>
                                <?php else: ?>
                                    <?php foreach ($feeds['live_bookings'] as $b): ?>
                                        <a href='booking_details.php?booking_id=<?php echo $b['booking_id']; ?>' class='data-list-item'>
                                            <div class='item-info'>
                                                <div class='title'><?php echo htmlspecialchars($b['route_name']); ?></div>
                                                <div class='subtitle'>#<?php echo htmlspecialchars($b['ticket_no']); ?> by <?php echo htmlspecialchars($b['booker']); ?></div>
                                            </div>
                                            <!-- MODIFICATION 3: Changed Date Format Here -->
                                            <small class='text-muted'><?php echo date('d M, h:i A', strtotime($b['created_at'])); ?></small>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="data-list-card">
                                <div class="card-header">Busiest Routes (Last 30 Days)</div>
                                <?php if (empty($feeds['top_routes'])): ?>
                                    <div class='p-4 text-muted text-center'>No data.</div>
                                <?php else: ?>
                                    <?php foreach ($feeds['top_routes'] as $r): ?>
                                        <a href='view_bookings.php?route_id=<?php echo $r['route_id']; ?>' class='data-list-item'>
                                            <div class='item-info'>
                                                <div class='title'><?php echo htmlspecialchars($r['route_name']); ?></div>
                                                <div class='subtitle'><?php echo $r['booking_count']; ?> bookings</div>
                                            </div>
                                            <span class='badge bg-success rounded-pill'>₹<?php echo number_format($r['total_revenue']); ?></span>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="data-list-card">
                                <div class="card-header">Top Employees (Last 7 Days)</div>
                                <?php if (empty($feeds['top_employees'])): ?>
                                    <div class='p-4 text-muted text-center'>No sales data.</div>
                                <?php else: ?>
                                    <?php foreach ($feeds['top_employees'] as $e): ?>
                                        <a href='employee_bookings.php?employee_id=<?php echo $e['id']; ?>' class='data-list-item'>
                                            <div class='item-info'>
                                                <div class='title'><?php echo htmlspecialchars($e['name']); ?></div>
                                                <div class='subtitle'><?php echo $e['booking_count']; ?> bookings</div>
                                            </div>
                                            <span class='badge bg-success rounded-pill'>₹<?php echo number_format($e['total_sales']); ?></span>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- SUB-ADMIN VIEW (Employee) - No changes here -->
                    <div class="row g-4">
                        <div class="col-lg-3 col-md-6"><a href="my_collections.php" class="stat-card">
                                <div class="title-row"><span class="title">My Sales (Today)</span><i class="fas fa-rupee-sign icon text-success"></i></div>
                                <div class="value">₹<?php echo number_format($kpi['my_today_revenue']); ?></div>
                                <div class="comparison text-<?php $rev_diff = $kpi['my_today_revenue'] - $kpi['my_yesterday_revenue'];
                                                            echo ($rev_diff >= 0) ? 'success' : 'danger'; ?>"><?php echo ($rev_diff >= 0 ? '+' : '') . '₹' . number_format(abs($rev_diff)); ?> vs Yesterday</div>
                            </a></div>
                        <div class="col-lg-3 col-md-6"><a href="my_collections.php" class="stat-card">
                                <div class="title-row"><span class="title">My Cash Due</span><i class="fas fa-wallet icon text-danger"></i></div>
                                <div class="value">₹<?php echo number_format($kpi['my_cash_due']); ?></div>
                                <div class="comparison text-muted">To be submitted to Admin</div>
                            </a></div>
                        <div class="col-lg-3 col-md-6"><a href="my_collections.php" class="stat-card">
                                <div class="title-row"><span class="title">My Bookings (Today)</span><i class="fas fa-ticket-alt icon text-info"></i></div>
                                <div class="value"><?php echo (int)$kpi['my_today_bookings']; ?></div>
                                <div class="comparison text-muted">&nbsp;</div>
                            </a></div>
                        <div class="col-lg-3 col-md-6"><a href="view_routes.php" class="stat-card">
                                <div class="title-row"><span class="title">My Assigned Routes</span><i class="fas fa-route icon text-primary"></i></div>
                                <div class="value"><?php echo (int)$kpi['my_assigned_routes']; ?></div>
                                <div class="comparison text-muted">&nbsp;</div>
                            </a></div>
                    </div>
                    <div class="row g-4 mt-4">
                        <div class="col-lg-6">
                            <div class="data-list-card">
                                <div class="card-header">My Recent Bookings</div><?php if (empty($feeds['my_live_bookings'])): ?><div class="p-4 text-center text-muted">You have no recent bookings.</div><?php else: foreach ($feeds['my_live_bookings'] as $b): ?><a href="ticket_view.php?booking_id=<?php echo $b['booking_id']; ?>" class="data-list-item">
                                            <div class="item-info">
                                                <div class="title"><?php echo htmlspecialchars($b['route_name']); ?></div>
                                                <div class="subtitle">#<?php echo htmlspecialchars($b['ticket_no']); ?></div>
                                            </div><small class="text-muted"><?php echo date('d M, h:i A', strtotime($b['created_at'])); ?></small>
                                        </a><?php endforeach; endif; ?>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="data-list-card">
                                <div class="card-header">My Busiest Routes</div><?php if (empty($feeds['my_top_routes'])): ?><div class="p-4 text-center text-muted">No booking data on your assigned routes.</div><?php else: foreach ($feeds['my_top_routes'] as $r): ?><a href="view_bookings.php?route_id=<?php echo $r['route_id']; ?>" class="data-list-item">
                                            <div class="item-info">
                                                <div class="title"><?php echo htmlspecialchars($r['route_name']); ?></div>
                                            </div><span class="badge bg-primary rounded-pill"><?php echo $r['booking_count']; ?> Bookings</span>
                                        </a><?php endforeach; endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <br>

    <!-- Modals for Live Tracking -->
    <div class="modal fade" id="liveRoutesModal" tabindex="-1" aria-labelledby="liveRoutesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="liveRoutesModalLabel">All Live Buses (<?php echo count($feeds['live_routes']); ?>)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="list-group list-group-flush">
                        <?php foreach ($feeds['live_routes'] as $route): ?>
                            <a href="#" class="list-group-item list-group-item-action live-route-details-trigger" data-route-id="<?php echo $route['route_id']; ?>" data-bs-dismiss="modal">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($route['route_name']); ?></h6>
                                    <small class="text-muted">Departs <?php echo $route['departure_time']; ?></small>
                                </div>
                                <p class="mb-1 small text-muted"><?php echo htmlspecialchars($route['bus_name']); ?></p>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="liveRouteDetailModal" tabindex="-1" aria-labelledby="liveRouteDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="liveRouteDetailModalLabel">Loading...</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center p-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Fetching live progress...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include_once('foot.php'); ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            <?php if ($is_main_admin && !empty($charts)): ?>
                // Chart.js code remains exactly the same
                const weeklyRevenueCtx = document.getElementById('weeklyRevenueChart')?.getContext('2d');
                if (weeklyRevenueCtx) {
                    new Chart(weeklyRevenueCtx, {
                        type: 'bar',
                        data: {
                            labels: <?php echo json_encode($charts['weekly_revenue']['labels']); ?>,
                            datasets: [{
                                label: 'Daily Revenue',
                                data: <?php echo json_encode($charts['weekly_revenue']['data']); ?>,
                                backgroundColor: 'rgba(74, 105, 189, 0.7)',
                                borderColor: 'rgba(74, 105, 189, 1)',
                                borderWidth: 2,
                                borderRadius: 6
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: '#e9ecef'
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                }
                            }
                        }
                    });
                }
                const salesBreakdownCtx = document.getElementById('salesBreakdownChart')?.getContext('2d');
                if (salesBreakdownCtx) {
                    new Chart(salesBreakdownCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Online Sales', 'Cash Sales'],
                            datasets: [{
                                data: [<?php echo $charts['sales_breakdown']['online_sales']; ?>, <?php echo $charts['sales_breakdown']['cash_sales']; ?>],
                                backgroundColor: ['#1dd1a1', '#feca57'],
                                borderColor: '#ffffff',
                                borderWidth: 4,
                                hoverOffset: 8
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '70%',
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        boxWidth: 15,
                                        padding: 20
                                    }
                                }
                            }
                        }
                    });
                }
                const routePerformanceCtx = document.getElementById('routePerformanceChart')?.getContext('2d');
                setTimeout(() => { if (routePerformanceCtx) {
                    new Chart(routePerformanceCtx, {
                        type: 'bar',
                        data: {
                            labels: <?php echo json_encode($charts['route_performance']['labels']); ?>,
                            datasets: [{
                                label: 'Revenue in Last 30 Days',
                                data: <?php echo json_encode($charts['route_performance']['data']); ?>,
                                backgroundColor: 'rgba(74, 144, 226, 0.8)',
                                borderColor: 'rgba(74, 144, 226, 1)',
                                borderWidth: 1,
                                borderRadius: 5
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            indexAxis: 'y', // This makes the bar chart horizontal
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value, index, values) {
                                            return '₹' + value.toLocaleString('en-IN');
                                        }
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return ' Revenue: ₹' + context.parsed.x.toLocaleString('en-IN');
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            }, 500); 
            <?php endif; ?>
 
            $(document).on('click', '.live-route-details-trigger', function(e) {
                e.preventDefault();
                const routeId = $(this).data('route-id');
                const detailModal = new bootstrap.Modal(document.getElementById('liveRouteDetailModal'));
                const modalBody = $('#liveRouteDetailModal .modal-body');
                const modalTitle = $('#liveRouteDetailModal .modal-title');

                modalTitle.text('Loading...');
                modalBody.html('<div class="text-center p-4"><div class="spinner-border text-primary"></div><p class="mt-2">Fetching live progress...</p></div>');
                detailModal.show();

                $.ajax({
                    url: 'ajax_get_live_route_details.php',
                    type: 'POST',
                    data: {
                        route_id: routeId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            modalTitle.text(response.routeName);

                            let modalContentHtml = '';

                            // --- NEW: Display Charter Badge or Live Stats ---
                            if (response.isChartered) {
                                modalContentHtml += `
                                    <div class="alert alert-info text-center fw-bold fs-5">
                                        <i class="fas fa-lock me-2"></i> This bus is on a Private Charter.
                                    </div>
                                 `;
                            } else if (response.stats) {
                                modalContentHtml += `
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-4"><div class="p-3 bg-success-light rounded-3 text-center"><div class="small text-uppercase text-success fw-bold">Total Income</div><div class="fs-4 fw-bolder text-success">₹${parseFloat(response.stats.total_income).toLocaleString('en-IN')}</div></div></div>
                                        <div class="col-md-4"><div class="p-3 bg-primary-light rounded-3 text-center"><div class="small text-uppercase text-primary fw-bold">Seats Booked</div><div class="fs-4 fw-bolder text-primary">${response.stats.booked_seats} / ${response.stats.total_seats}</div></div></div>
                                        <div class="col-md-4"><div class="p-3 bg-warning-light rounded-3 text-center"><div class="small text-uppercase text-warning fw-bold">Seats Left</div><div class="fs-4 fw-bolder text-warning">${response.stats.seats_left}</div></div></div>
                                    </div>
                                    <hr/>`;
                            }

                            // --- Build Bus & Staff Details HTML (unchanged) ---
                            modalContentHtml += '<div class="p-3 bg-light rounded-2 mb-3">';
                            if (response.busDetails) {
                                modalContentHtml += `<div class="mb-3"><h6 class="small text-uppercase text-muted fw-bold"><i class="fas fa-bus me-2"></i>Bus Details</h6><p class="mb-0 ms-1"><strong>Name:</strong> ${response.busDetails.name}</p><p class="mb-0 ms-1"><strong>Reg. No:</strong> ${response.busDetails.reg_no}</p></div>`;
                            }
                            if (response.staff && response.staff.length > 0) {
                                modalContentHtml += `<div><h6 class="small text-uppercase text-muted fw-bold"><i class="fas fa-users me-2"></i>Assigned Crew</h6><ul class="list-unstyled mb-0 ms-1">`;
                                response.staff.forEach(member => {
                                    modalContentHtml += `<li><strong>${member.role}:</strong> ${member.name}</li>`;
                                });
                                modalContentHtml += `</ul></div>`;
                            }
                            modalContentHtml += '</div>';

                            // --- Build Timeline HTML (unchanged) ---
                            modalContentHtml += '<h6 class="small text-uppercase text-muted fw-bold mt-4"><i class="fas fa-route me-2"></i>Live Progress</h6>';
                            modalContentHtml += '<ul class="live-timeline">';
                            response.timeline.forEach(item => {
                                let icon = 'fa-map-marker-alt';
                                if (item.type === 'start') icon = 'fa-play';
                                if (item.type === 'end') icon = 'fa-flag-checkered';
                                modalContentHtml += `<li class="live-timeline-item status-${item.status}"><div class="live-timeline-icon"><i class="fas ${icon}"></i></div><div class="fw-bold">${item.name}</div><div class="small text-muted">${item.time}</div></li>`;
                            });
                            modalContentHtml += '</ul>';

                            modalBody.html(modalContentHtml);

                        } else {
                            modalBody.html(`<div class="alert alert-danger">${response.message}</div>`);
                        }
                    },
                    error: function() {
                        modalBody.html('<div class="alert alert-danger">Could not connect to the server.</div>');
                    }
                });
            });
        });
      

    </script>
    

    <style>
        .bg-success-light {
            background-color: rgba(25, 135, 84, 0.1);
        }

        .text-success {
            color: #0f5132 !important;
        }

        .bg-primary-light {
            background-color: rgba(13, 110, 253, 0.1);
        }

        .text-primary {
            color: #0a58ca !important;
        }

        .bg-warning-light {
            background-color: rgba(255, 193, 7, 0.1);
        }

        .text-warning {
            color: #997404 !important;
        }
    </style>
</body>

</html>