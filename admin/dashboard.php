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
        // ===============================================
        // DATA FETCHING FOR MAIN ADMIN
        // ===============================================
        $kpi['active_buses'] = $_conn_db->query("SELECT COUNT(*) FROM buses WHERE status = 'Active'")->fetchColumn();
        $kpi['active_routes'] = $_conn_db->query("SELECT COUNT(*) FROM routes WHERE status = 'Active'")->fetchColumn();
        $kpi['total_users'] = $_conn_db->query("SELECT COUNT(*) FROM users WHERE status = 1")->fetchColumn();
        $kpi['pending_cancellations'] = $_conn_db->query("SELECT COUNT(*) FROM cancellations WHERE status = 'PENDING'")->fetchColumn();
        
        $stmt_today = $_conn_db->prepare("SELECT COUNT(booking_id), COALESCE(SUM(total_fare), 0) FROM bookings WHERE DATE(created_at) = ? AND booking_status = 'CONFIRMED'");
        $stmt_today->execute([$today_date]);
        [$kpi['today_bookings'], $kpi['today_revenue']] = $stmt_today->fetch(PDO::FETCH_NUM);
        $kpi['today_avg_booking_value'] = ($kpi['today_bookings'] > 0) ? $kpi['today_revenue'] / $kpi['today_bookings'] : 0;
        
        $stmt_yesterday = $_conn_db->prepare("SELECT COUNT(booking_id), COALESCE(SUM(total_fare), 0) FROM bookings WHERE DATE(created_at) = ? AND booking_status = 'CONFIRMED'");
        $stmt_yesterday->execute([$yesterday_date]);
        [$kpi['yesterday_bookings'], $kpi['yesterday_revenue']] = $stmt_yesterday->fetch(PDO::FETCH_NUM);
        
        $stmt_passengers = $_conn_db->prepare("SELECT COUNT(p.passenger_id) FROM passengers p JOIN bookings b ON p.booking_id = b.booking_id WHERE DATE(b.created_at) = ? AND b.booking_status = 'CONFIRMED'");
        $stmt_passengers->execute([$today_date]);
        $kpi['today_passengers'] = $stmt_passengers->fetchColumn();
        
        $stmt_monthly = $_conn_db->prepare("SELECT COALESCE(SUM(total_fare), 0) FROM bookings WHERE DATE_FORMAT(created_at, '%Y-%m') = ? AND booking_status = 'CONFIRMED'");
        $stmt_monthly->execute([$current_month]);
        $kpi['monthly_revenue'] = $stmt_monthly->fetchColumn();

        $stmt_occupancy = $_conn_db->prepare("SELECT (SELECT COUNT(p.passenger_id) FROM passengers p JOIN bookings b ON p.booking_id = b.booking_id WHERE b.travel_date = :today_date1 AND b.booking_status = 'CONFIRMED') as total_booked, (SELECT COUNT(s.seat_id) FROM seats s JOIN routes r ON s.bus_id = r.bus_id JOIN route_schedules rs ON r.route_id = rs.route_id WHERE rs.operating_day = DAYNAME(:today_date2) AND s.is_bookable = 1) as total_available_seats");
        $stmt_occupancy->execute([':today_date1' => $today_date, ':today_date2' => $today_date]);
        $occupancy_data = $stmt_occupancy->fetch(PDO::FETCH_ASSOC);
        $kpi['today_occupancy_rate'] = ($occupancy_data['total_available_seats'] > 0) ? ($occupancy_data['total_booked'] / $occupancy_data['total_available_seats']) * 100 : 0;
        
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

        $stmt_sales_breakdown = $_conn_db->prepare("SELECT COALESCE(SUM(CASE WHEN t.transaction_id IS NOT NULL THEN b.total_fare ELSE 0 END), 0) as online_sales, COALESCE(SUM(CASE WHEN t.transaction_id IS NULL THEN b.total_fare ELSE 0 END), 0) as cash_sales FROM bookings b LEFT JOIN transactions t ON b.booking_id = t.booking_id WHERE DATE(b.created_at) = ? AND b.booking_status = 'CONFIRMED'");
        $stmt_sales_breakdown->execute([$today_date]);
        $charts['sales_breakdown'] = $stmt_sales_breakdown->fetch(PDO::FETCH_ASSOC);

        $feeds['live_bookings'] = $_conn_db->query("SELECT b.booking_id, b.ticket_no, r.route_name, COALESCE(a.name, u.username, 'Online') as booker, b.created_at FROM bookings b JOIN routes r ON b.route_id = r.route_id LEFT JOIN admin a ON b.booked_by_employee_id = a.id LEFT JOIN users u ON b.user_id = u.id ORDER BY b.created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
        $feeds['top_routes'] = $_conn_db->query("SELECT r.route_id, r.route_name, COUNT(b.booking_id) as booking_count, SUM(b.total_fare) as total_revenue FROM bookings b JOIN routes r ON b.route_id = r.route_id WHERE b.booking_status = 'CONFIRMED' GROUP BY r.route_id ORDER BY total_revenue DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
        $feeds['top_employees'] = $_conn_db->query("SELECT a.id, a.name, SUM(b.total_fare) as total_sales, COUNT(b.booking_id) as booking_count FROM bookings b JOIN admin a ON b.booked_by_employee_id = a.id WHERE b.booking_status = 'CONFIRMED' GROUP BY a.id ORDER BY total_sales DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

    } else {
        // ===============================================
        // DATA FETCHING FOR SUB-ADMIN
        // ===============================================
        $kpi['my_assigned_routes'] = count($allowed_route_ids_for_employee);
        
        $stmt_today = $_conn_db->prepare("SELECT COUNT(booking_id), COALESCE(SUM(total_fare), 0) FROM bookings WHERE DATE(created_at) = ? AND booking_status = 'CONFIRMED' AND booked_by_employee_id = ?");
        $stmt_today->execute([$today_date, $employee_id]);
        [$kpi['my_today_bookings'], $kpi['my_today_revenue']] = $stmt_today->fetch(PDO::FETCH_NUM);
        
        $stmt_yesterday = $_conn_db->prepare("SELECT COALESCE(SUM(total_fare), 0) FROM bookings WHERE DATE(created_at) = ? AND booking_status = 'CONFIRMED' AND booked_by_employee_id = ?");
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
            $stmt_my_top_routes = $_conn_db->prepare("SELECT r.route_name, r.route_id, COUNT(b.booking_id) as booking_count FROM bookings b JOIN routes r ON b.route_id = r.route_id WHERE b.booking_status = 'CONFIRMED' AND r.route_id IN ($placeholders) GROUP BY r.route_id ORDER BY booking_count DESC LIMIT 5");
            $stmt_my_top_routes->execute($allowed_route_ids_for_employee);
            $feeds['my_top_routes'] = $stmt_my_top_routes->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $feeds['my_top_routes'] = [];
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
        body { background-color: #f8f9fa; font-family: 'Inter', sans-serif; }
        .dashboard-header { margin-bottom: 2rem; }
        .dashboard-title { font-weight: 700; font-size: 1.8rem; }
        .stat-card {
            background-color: #fff; border-radius: 12px; padding: 1.25rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05); transition: all 0.2s ease;
            text-decoration: none; display: flex; flex-direction: column; height: 100%;
        }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.08); }
        .stat-card .title-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem; }
        .stat-card .title { font-weight: 600; color: #6c757d; font-size: 0.9rem; }
        .stat-card .icon { font-size: 1.5rem; opacity: 0.3; }
        .stat-card .value { font-size: 2rem; font-weight: 700; color: #212529; line-height: 1.2; }
        .stat-card .comparison { font-size: 0.8rem; font-weight: 500; margin-top: auto; padding-top: 0.5rem;}
        .text-success { color: #198754 !important; }
        .text-danger { color: #dc3545 !important; }
        
        .chart-card, .data-list-card {
            background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); height: 100%;
        }
        .chart-card .card-header, .data-list-card .card-header {
            background: transparent; border-bottom: 1px solid #e9ecef; font-weight: 600;padding: 0 8px;
        }
        .chart-container { padding: 1.5rem; }
        .data-list-item {
            display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem;
            border-bottom: 1px solid #f0f0f0; text-decoration: none; color: #343a40;
        }
        .data-list-item:last-child { border-bottom: none; }
        .data-list-item:hover { background-color: #f8f9fa; }
        .item-info .title { font-weight: 600; }
        .item-info .subtitle { font-size: 0.85em; color: #6c757d; }
        
        /* --- RESPONSIVE STYLES --- */
        @media (max-width: 991.98px) {
            .dashboard-title { font-size: 1.6rem; }
            .stat-card .value { font-size: 1.75rem; }
            .chart-card, .data-list-card { margin-bottom: 1.5rem; }
        }

        @media (max-width: 767.98px) {
            .stat-card { padding: 1rem; }
            .stat-card .value { font-size: 1.5rem; }
            .stat-card .icon { font-size: 1.2rem; }
            .stat-card .comparison { font-size: 0.75rem; }
            .chart-container { padding: 1rem; }
        }

        @media (max-width: 575.98px) {
            .dashboard-header { text-align: center; }
            .col-6 { flex: 0 0 50%; max-width: 50%; } /* Ensure 2 cards per row on mobile */
            .data-list-item { padding: 0.75rem 1rem; }
            .item-info .title { font-size: 0.9rem; }
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
                <!-- ============================ MAIN ADMIN VIEW ============================ -->
                <div class="row g-4">
                    <div class="col-xl-3 col-md-6"><a href="employee_bookings.php?date_from=<?php echo $today_date; ?>&date_to=<?php echo $today_date; ?>" class="stat-card">
                        <div class="title-row"><span class="title">Today's Revenue</span><i class="fas fa-rupee-sign icon text-success"></i></div>
                        <div class="value">₹<?php echo number_format($kpi['today_revenue']); ?></div>
                        <div class="comparison text-<?php $rev_diff = $kpi['today_revenue'] - $kpi['yesterday_revenue']; echo ($rev_diff >= 0) ? 'success' : 'danger'; ?>">
                            <i class="fas fa-arrow-<?php echo ($rev_diff >= 0) ? 'up' : 'down'; ?>"></i>
                            <?php $rev_perc = $kpi['yesterday_revenue'] > 0 ? ($rev_diff / $kpi['yesterday_revenue']) * 100 : 100; echo number_format(abs($rev_perc), 1); ?>% vs Yesterday
                        </div>
                    </a></div>
                    <div class="col-xl-3 col-md-6"><a href="employee_bookings.php?date_from=<?php echo $today_date; ?>&date_to=<?php echo $today_date; ?>" class="stat-card">
                        <div class="title-row"><span class="title">Bookings Today</span><i class="fas fa-ticket-alt icon text-info"></i></div>
                        <div class="value"><?php echo (int)$kpi['today_bookings']; ?></div>
                        <div class="comparison text-<?php $book_diff = $kpi['today_bookings'] - $kpi['yesterday_bookings']; echo ($book_diff >= 0) ? 'success' : 'danger'; ?>">
                             <i class="fas fa-arrow-<?php echo ($book_diff >= 0) ? 'up' : 'down'; ?>"></i>
                            <?php $book_perc = $kpi['yesterday_bookings'] > 0 ? ($book_diff / $kpi['yesterday_bookings']) * 100 : 100; echo number_format(abs($book_perc), 1); ?>% vs Yesterday
                        </div>
                    </a></div>
                    <div class="col-xl-3 col-md-6"><a href="employee_bookings.php?date_from=<?php echo $today_date; ?>&date_to=<?php echo $today_date; ?>" class="stat-card">
                        <div class="title-row"><span class="title">Avg. Booking Value</span><i class="fas fa-balance-scale icon text-primary"></i></div>
                        <div class="value">₹<?php echo number_format($kpi['today_avg_booking_value'], 2); ?></div>
                        <div class="comparison text-muted">For today's bookings</div>
                    </a></div>
                     <div class="col-xl-3 col-md-6"><a href="todays_departures" class="stat-card">
                        <div class="title-row"><span class="title">Today's Bus Occupancy</span><i class="fas fa-chair icon text-warning"></i></div>
                        <div class="value"><?php echo number_format($kpi['today_occupancy_rate'], 1); ?>%</div>
                        <div class="progress" style="height: 5px;"><div class="progress-bar bg-warning" style="width: <?php echo $kpi['today_occupancy_rate']; ?>%"></div></div>
                    </a></div>
                </div>
                <div class="row g-4 mt-4">
                    <div class="col-lg-7">
                        <div class="chart-card"><div class="card-header">Revenue Last 7 Days</div><div class="chart-container"><canvas id="weeklyRevenueChart"></canvas></div></div>
                    </div>
                    <div class="col-lg-5">
                        <div class="chart-card"><div class="card-header">Today's Sales Breakdown</div><div class="chart-container"><canvas id="salesBreakdownChart"></canvas></div></div>
                    </div>
                </div>
                <div class="row g-4 mt-4">
                    <div class="col-lg-4"><div class="data-list-card"><div class="card-header">Live Bookings</div><?php foreach($feeds['live_bookings'] as $b) { echo "<a href='booking_details.php?booking_id={$b['booking_id']}' class='data-list-item'><div class='item-info'><div class='title'>".htmlspecialchars($b['route_name'])."</div><div class='subtitle'>#".htmlspecialchars($b['ticket_no'])." by ".htmlspecialchars($b['booker'])."</div></div><small class='text-muted'>".date('h:i A', strtotime($b['created_at']))."</small></a>"; } if(empty($feeds['live_bookings'])) echo "<div class='p-4 text-muted text-center'>No recent bookings.</div>"; ?></div></div>
                    <div class="col-lg-4"><div class="data-list-card"><div class="card-header">Busiest Routes (By Revenue)</div><?php foreach($feeds['top_routes'] as $r) { echo "<a href='view_bookings.php?route_id={$r['route_id']}' class='data-list-item'><div class='item-info'><div class='title'>".htmlspecialchars($r['route_name'])."</div><div class='subtitle'>{$r['booking_count']} bookings</div></div><span class='badge bg-success rounded-pill'>₹".number_format($r['total_revenue'])."</span></a>"; } if(empty($feeds['top_routes'])) echo "<div class='p-4 text-muted text-center'>No data.</div>"; ?></div></div>
                    <div class="col-lg-4"><div class="data-list-card"><div class="card-header">Top Employees</div><?php foreach($feeds['top_employees'] as $e) { echo "<a href='employee_bookings.php?employee_id={$e['id']}' class='data-list-item'><div class='item-info'><div class='title'>".htmlspecialchars($e['name'])."</div><div class='subtitle'>{$e['booking_count']} bookings</div></div><span class='badge bg-success rounded-pill'>₹".number_format($e['total_sales'])."</span></a>"; } if(empty($feeds['top_employees'])) echo "<div class='p-4 text-muted text-center'>No sales data.</div>"; ?></div></div>
                </div>

                <?php else: ?>
                <!-- ============================ SUB-ADMIN VIEW ============================ -->
                <div class="row g-4">
                    <div class="col-lg-3 col-md-6"><a href="my_collections.php" class="stat-card">
                        <div class="title-row"><span class="title">My Sales (Today)</span><i class="fas fa-rupee-sign icon text-success"></i></div>
                        <div class="value">₹<?php echo number_format($kpi['my_today_revenue']); ?></div>
                        <div class="comparison text-<?php $rev_diff = $kpi['my_today_revenue'] - $kpi['my_yesterday_revenue']; echo ($rev_diff >= 0) ? 'success' : 'danger'; ?>"><?php echo ($rev_diff >= 0 ? '+' : '') . '₹' . number_format(abs($rev_diff)); ?> vs Yesterday</div>
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
                            <div class="card-header">My Recent Bookings</div>
                            <?php if(empty($feeds['my_live_bookings'])): ?>
                                <div class="p-4 text-center text-muted">You have no recent bookings.</div>
                            <?php else: foreach ($feeds['my_live_bookings'] as $b): ?>
                            <a href="ticket_view.php?booking_id=<?php echo $b['booking_id']; ?>" class="data-list-item">
                                <div class="item-info"><div class="title"><?php echo htmlspecialchars($b['route_name']); ?></div><div class="subtitle">#<?php echo htmlspecialchars($b['ticket_no']); ?></div></div>
                                <small class="text-muted"><?php echo date('h:i A', strtotime($b['created_at'])); ?></small>
                            </a>
                            <?php endforeach; endif; ?>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="data-list-card">
                            <div class="card-header">My Busiest Routes</div>
                            <?php if(empty($feeds['my_top_routes'])): ?>
                                <div class="p-4 text-center text-muted">No booking data on your assigned routes.</div>
                            <?php else: foreach ($feeds['my_top_routes'] as $r): ?>
                            <a href="view_bookings.php?route_id=<?php echo $r['route_id']; ?>" class="data-list-item">
                                <div class="item-info"><div class="title"><?php echo htmlspecialchars($r['route_name']); ?></div></div>
                                <span class="badge bg-primary rounded-pill"><?php echo $r['booking_count']; ?> Bookings</span>
                            </a>
                            <?php endforeach; endif; ?>
                        </div>
                    </div>
                </div>

                <?php endif; ?>
            </div>
        </div>
    </div>
    <br>
    <?php include_once('foot.php'); ?>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($is_main_admin && !empty($charts)): ?>
        // Weekly Revenue Chart
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
                        borderWidth: 2, borderRadius: 6,
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, grid: { color: '#e9ecef' } }, x: { grid: { display: false } } }, plugins: { legend: { display: false } } }
            });
        }
        // Sales Breakdown Chart
        const salesBreakdownCtx = document.getElementById('salesBreakdownChart')?.getContext('2d');
        if (salesBreakdownCtx) {
            new Chart(salesBreakdownCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Online Sales', 'Cash Sales'],
                    datasets: [{
                        data: [<?php echo $charts['sales_breakdown']['online_sales']; ?>, <?php echo $charts['sales_breakdown']['cash_sales']; ?>],
                        backgroundColor: ['#1dd1a1', '#feca57'],
                        borderColor: '#ffffff', borderWidth: 4, hoverOffset: 8
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 15, padding: 20 } } } }
            });
        }
        <?php endif; ?>
    });
    </script>
</body>
</html>