<?php
// dashboard.php - ULTIMATE "Mission Control"
global $_conn_db;
include_once('function/_db.php');
session_security_check();

try {
    // --- 1. KPI STATS ---
    $total_buses_active = $_conn_db->query("SELECT COUNT(*) FROM buses WHERE status = 'Active'")->fetchColumn();
    $total_routes_active = $_conn_db->query("SELECT COUNT(*) FROM routes WHERE status = 'Active'")->fetchColumn();
    $today_date = date('Y-m-d');
    $stmt_today = $_conn_db->prepare("SELECT COUNT(booking_id) as count, COALESCE(SUM(total_fare), 0) as sum FROM bookings WHERE DATE(created_at) = ? AND booking_status = 'CONFIRMED'");
    $stmt_today->execute([$today_date]);
    $today_stats = $stmt_today->fetch(PDO::FETCH_ASSOC);
    $total_pending_cash = $_conn_db->query("SELECT COALESCE(SUM(b.total_fare), 0) FROM bookings b LEFT JOIN transactions t ON b.booking_id = t.booking_id LEFT JOIN cash_collections_log ccl ON b.booking_id = ccl.booking_id WHERE b.booked_by_employee_id IS NOT NULL AND t.transaction_id IS NULL AND ccl.collection_id IS NULL")->fetchColumn();

    // --- 2. CHART DATA ---
    // A. Monthly Revenue Chart (Last 30 Days)
    $chart_labels_monthly = [];
    $chart_data_monthly = [];
    for ($i = 29; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $chart_labels_monthly[] = $date;
        $stmt_chart = $_conn_db->prepare("SELECT COALESCE(SUM(total_fare), 0) FROM bookings WHERE DATE(created_at) = ? AND booking_status = 'CONFIRMED'");
        $stmt_chart->execute([$date]);
        $chart_data_monthly[] = (float)$stmt_chart->fetchColumn();
    }

    // B. Bookings by Type Chart
    $stmt_booking_type = $_conn_db->query("SELECT SUM(CASE WHEN booked_by_employee_id IS NOT NULL THEN 1 ELSE 0 END) as employee, SUM(CASE WHEN user_id IS NOT NULL THEN 1 ELSE 0 END) as online FROM bookings WHERE booking_status = 'CONFIRMED'");
    $booking_type_data = $stmt_booking_type->fetch(PDO::FETCH_ASSOC);

    // --- 3. LIVE DATA FEEDS ---
    $live_bookings = $_conn_db->query("SELECT b.booking_id, b.ticket_no, b.total_fare, r.route_name, COALESCE(a.name, u.username) as booker_name, b.created_at FROM bookings b JOIN routes r ON b.route_id = r.route_id LEFT JOIN admin a ON b.booked_by_employee_id = a.id LEFT JOIN users u ON b.user_id = u.id ORDER BY b.created_at DESC LIMIT 7")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Dashboard Error: Could not fetch data. " . $e->getMessage());
}
$name = $_SESSION['user']['name'] ?? 'Guest';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once('head.php');?>
    <title>Dashboard - BPL Tickets</title>
    <!-- CSS and JS for libraries are now in head.php -->
    <link rel="stylesheet" href="assets/leaflet/dist/leaflet.css" />
    <style>
        :root {
            --primary: #5E50F9; --primary-light: #F0EEFF;
            --success: #198754; --success-light: #E8F3EE;
            --danger: #DC3545; --danger-light: #FCEEEE;
            --secondary: #6c757d; --light-gray: #f8f9fa;
            --border-color: #dee2e6; --card-shadow: 0 8px 30px rgba(0,0,0,0.06);
        }
        body { background-color: var(--light-gray); }
        .stat-card { background-color: #fff; border-radius: 1rem; padding: 1.5rem; transition: all 0.3s ease; box-shadow: var(--card-shadow); border: 1px solid var(--border-color); }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 12px 35px rgba(0,0,0,0.08); }
        .stat-card-header { display: flex; justify-content: space-between; align-items: flex-start; }
        .stat-card .icon { font-size: 1.5rem; width: 48px; height: 48px; border-radius: 12px; display: grid; place-items: center; }
        .stat-card .title { font-weight: 600; color: var(--secondary); }
        .stat-card .main-value { font-size: 2.25rem; font-weight: 700; line-height: 1.2; margin-top: 1rem; color: #161C2D; }
        .stat-card.primary .icon { background: var(--primary-light); color: var(--primary); }
        .stat-card.success .icon { background: var(--success-light); color: var(--success); }
        .stat-card.danger .icon { background: var(--danger-light); color: var(--danger); }
        .card { border: none; border-radius: 1rem; box-shadow: var(--card-shadow); height: 100%; }
        .card-header { background-color: #fff; border-bottom: 1px solid var(--border-color); font-weight: 600; padding: 1.25rem 1.5rem; font-size: 1.1rem; }
        #live-map { height: 400px; border-radius: 12px; }
        .list-group-item-action { transition: background-color 0.2s ease; }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include_once('sidebar.php');?>
    <div class="main-content">
        <?php include_once('header.php');?>
        <div class="container-fluid">
            <h1 class="mt-4">Mission Control</h1>
            <p class="text-muted mb-4">Live overview of the BPL Tickets network.</p>
            
            <div class="row g-4">
                <div class="col-xl-3 col-md-6"><div class="stat-card primary"><div class="stat-card-header"><span class="title">Active Buses</span><div class="icon"><i class="fas fa-bus"></i></div></div><div class="main-value"><?php echo (int)$total_buses_active; ?></div></div></div>
                <div class="col-xl-3 col-md-6"><div class="stat-card success"><div class="stat-card-header"><span class="title">Today's Revenue</span><div class="icon"><i class="fas fa-rupee-sign"></i></div></div><div class="main-value">₹<?php echo number_format($today_stats['sum']); ?></div></div></div>
                <div class="col-xl-3 col-md-6"><div class="stat-card warning"><div class="stat-card-header"><span class="title">Bookings Today</span><div class="icon"><i class="fas fa-ticket-alt"></i></div></div><div class="main-value"><?php echo (int)$today_stats['count']; ?></div></div></div>
                <div class="col-xl-3 col-md-6"><div class="stat-card danger"><div class="stat-card-header"><span class="title">Pending Cash</span><div class="icon"><i class="fas fa-wallet"></i></div></div><div class="main-value">₹<?php echo number_format($total_pending_cash); ?></div></div></div>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-xl-8">
                    <div class="card"><div class="card-header">Revenue Trend (Last 30 Days)</div><div class="card-body"><div id="revenueChart"></div></div></div>
                </div>
                <div class="col-xl-4">
                    <div class="card"><div class="card-header">Bookings by Type</div><div class="card-body d-flex align-items-center justify-content-center"><div id="bookingTypeChart" style="min-height: 380px;"></div></div></div>
                </div>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-lg-7">
                     <div class="card">
                        <div class="card-header">Live Bookings Feed</div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                <?php foreach ($live_bookings as $booking): ?>
                                <a href="booking_details.php?booking_id=<?php echo $booking['booking_id']; ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($booking['route_name']); ?></h6>
                                        <small class="text-muted"><?php echo date('h:i A', strtotime($booking['created_at'])); ?></small>
                                    </div>
                                    <p class="mb-1">Ticket <b>#<?php echo htmlspecialchars($booking['ticket_no']); ?></b> by <?php echo htmlspecialchars($booking['booker_name']); ?></p>
                                    <small class="text-success fw-bold">₹<?php echo number_format($booking['total_fare']); ?></small>
                                </a>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                     </div>
                </div>
                 <div class="col-lg-5">
                     <div class="card">
                        <div class="card-header">Live Bus Map (Demonstration)</div>
                        <div class="card-body">
                            <div id="live-map"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="overlay" id="sidebar-overlay"></div>
<?php include_once('foot.php');?>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="assets/leaflet/dist/leaflet.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // --- ApexCharts: Revenue Trend ---
    var revenueOptions = {
        series: [{ name: 'Revenue', data: <?php echo json_encode($chart_data_monthly); ?> }],
        chart: { type: 'area', height: 350, zoom: { enabled: false }, toolbar: { show: false } },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 3 },
        colors: ['#5E50F9'],
        xaxis: { type: 'datetime', categories: <?php echo json_encode($chart_labels_monthly); ?>, labels: { format: 'MMM dd' } },
        yaxis: { labels: { formatter: (value) => '₹' + value.toLocaleString('en-IN') } },
        tooltip: { x: { format: 'dd MMMM, yyyy' } },
        fill: {
            type: 'gradient',
            gradient: { shade: 'light', type: "vertical", shadeIntensity: 0.25, opacityFrom: 0.7, opacityTo: 0.1, stops: [0, 100] }
        }
    };
    var revenueChart = new ApexCharts(document.querySelector("#revenueChart"), revenueOptions);
    revenueChart.render();

    // --- ApexCharts: Bookings by Type ---
    var bookingTypeOptions = {
        series: <?php echo json_encode([$booking_type_data['employee'] ?? 0, $booking_type_data['online'] ?? 0]); ?>,
        chart: { type: 'donut', height: 380 },
        labels: ['Employee Sales', 'Online Sales'],
        colors: ['#5E50F9', '#198754'],
        legend: { position: 'bottom' },
        plotOptions: { pie: { donut: { labels: { show: true, total: { show: true, label: 'Total Bookings' } } } } }
    };
    var bookingTypeChart = new ApexCharts(document.querySelector("#bookingTypeChart"), bookingTypeOptions);
    bookingTypeChart.render();

    // --- Live Map Initialization (Example) ---
    const map = L.map('live-map').setView([28.6139, 77.2090], 5);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>'
    }).addTo(map);

    const busIcon = L.icon({ iconUrl: 'https://cdn-icons-png.flaticon.com/512/3097/3097151.png', iconSize: [38, 38] });
    const busLocations = [
        { name: "Bus HR-01 (Delhi-Mumbai)", lat: 28.7041, lng: 77.1025 },
        { name: "Bus RJ-02 (Jaipur-Agra)", lat: 26.9124, lng: 75.7873 },
        { name: "Bus MH-03 (Pune-Goa)", lat: 18.5204, lng: 73.8567 }
    ];
    busLocations.forEach(bus => {
        L.marker([bus.lat, bus.lng], { icon: busIcon }).addTo(map).bindPopup(`<b>${bus.name}</b>`);
    });
});
</script>
</body>
</html>