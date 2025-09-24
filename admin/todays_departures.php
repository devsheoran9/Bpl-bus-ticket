<?php
// dashboard.php - "Today's Departures" Mission Control
global $_conn_db;
include_once('function/_db.php');
session_security_check();
check_permission('main_admin');
// --- 1. GET DATE FILTER AND CALCULATE DAY ---
$filter_date = $_GET['date'] ?? date('Y-m-d');
$day_of_week = date('D', strtotime($filter_date));

try {
    // --- 2. FETCH ALL DEPARTURES FOR THE SELECTED DATE ---
    $departures_stmt = $_conn_db->prepare("
        SELECT 
            r.route_id,
            r.route_name,
            r.starting_point,
            r.ending_point,
            rsch.departure_time,
            b.bus_name,
            b.registration_number
        FROM route_schedules rsch
        JOIN routes r ON rsch.route_id = r.route_id
        JOIN buses b ON r.bus_id = b.bus_id
        WHERE rsch.operating_day = ? AND r.status = 'Active'
        ORDER BY rsch.departure_time ASC
    ");
    $departures_stmt->execute([$day_of_week]);
    $departures = $departures_stmt->fetchAll(PDO::FETCH_ASSOC);

    // --- 3. FOR EACH DEPARTURE, FETCH STAFF AND SALES STATS ---
    foreach ($departures as &$departure) { // Use reference '&' to modify the array
        
        // A. Fetch Assigned Staff
        $staff_stmt = $_conn_db->prepare("
            SELECT s.name, rsa.role 
            FROM route_staff_assignments rsa
            JOIN staff s ON rsa.staff_id = s.staff_id
            WHERE rsa.route_id = ?
            ORDER BY FIELD(rsa.role, 'Driver', 'Co-Driver', 'Conductor', 'Co-Conductor', 'Helper')
        ");
        $staff_stmt->execute([$departure['route_id']]);
        $departure['staff'] = $staff_stmt->fetchAll(PDO::FETCH_ASSOC);

        // B. Fetch Sales and Occupancy Stats
        $stats_stmt = $_conn_db->prepare("
            SELECT
                COUNT(p.passenger_id) AS booked_seats,
                COALESCE(SUM(p.fare), 0) AS total_revenue,
                COALESCE(SUM(CASE WHEN t.transaction_id IS NULL THEN p.fare ELSE 0 END), 0) AS cash_sales,
                COALESCE(SUM(CASE WHEN t.transaction_id IS NOT NULL THEN p.fare ELSE 0 END), 0) AS online_sales,
                (SELECT COUNT(*) FROM seats s WHERE s.bus_id = bk.bus_id AND s.is_bookable = 1) as total_seats
            FROM bookings bk
            JOIN passengers p ON bk.booking_id = p.booking_id
            LEFT JOIN transactions t ON bk.booking_id = t.booking_id
            WHERE bk.route_id = ? AND bk.travel_date = ? AND p.passenger_status = 'CONFIRMED'
            GROUP BY bk.bus_id
        ");
        $stats_stmt->execute([$departure['route_id'], $filter_date]);
        $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

        $departure['stats'] = $stats ?: [
            'booked_seats' => 0, 'total_revenue' => 0, 'cash_sales' => 0,
            'online_sales' => 0, 'total_seats' => 0
        ];
    }

} catch (PDOException $e) {
    die("Dashboard Error: Could not fetch data. " . $e->getMessage());
}
$name = $_SESSION['user']['name'] ?? 'Guest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once('head.php');?>
    <title>Dashboard - Today's Departures</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* ========== CSS VARIABLES & RESET ========== */
        :root {
            --primary: #667eea;
            --primary-dark: #5a67d8;
            --secondary: #48bb78;
            --secondary-dark: #38a169;
            --accent: #ed8936;
            --danger: #f56565;
            --warning: #ecc94b;
            --info: #4299e1;
            
            --text-primary: #1a202c;
            --text-secondary: #4a5568;
            --text-muted: #718096;
            --text-light: #a0aec0;
            
            --bg-primary: #ffffff;
            --bg-secondary: #f7fafc;
            --bg-tertiary: #edf2f7;
            --bg-dark: #2d3748;
            
            --border-light: #e2e8f0;
            --border-medium: #cbd5e0;
            
            --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --shadow-colored: 0 10px 40px -15px rgba(102, 126, 234, 0.4);
            
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
            --radius-2xl: 1.5rem;
            
            --transition-fast: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-base: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-slow: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

      
        body {
            background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-tertiary) 100%);
            min-height: 100vh;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            color: var(--text-primary);
            line-height: 1.6;
        }

      
        .dashboard-header {
            margin-bottom: 2rem;
            animation: slideDown 0.5s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dashboard-title {
            font-size: clamp(1.75rem, 4vw, 2.5rem);
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
            letter-spacing: -0.02em;
        }

        .dashboard-subtitle {
            color: var(--text-secondary);
            font-size: 1.1rem;
            font-weight: 400;
        }

        /* ========== FILTER BAR ========== */
        .filter-bar {
            background: var(--bg-primary);
            padding: 1.5rem;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-md);
            margin-bottom: 2rem;
            border: 1px solid var(--border-light);
            backdrop-filter: blur(10px);
            animation: fadeInUp 0.6s ease-out 0.1s both;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: center;
        }

        .filter-label {
            font-weight: 600;
            color: var(--text-secondary);
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .date-input {
            padding: 0.75rem 1rem;
            border: 2px solid var(--border-light);
            border-radius: var(--radius-md);
            font-size: 1rem;
            transition: var(--transition-fast);
            background: var(--bg-primary);
            color: var(--text-primary);
            font-weight: 500;
            min-width: 200px;
        }

        .date-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--radius-md);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition-fast);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            box-shadow: var(--shadow-md);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-secondary {
            background: var(--bg-tertiary);
            color: var(--text-secondary);
            border: 2px solid var(--border-light);
        }

        .btn-secondary:hover {
            background: var(--bg-secondary);
            border-color: var(--border-medium);
        }

        /* ========== STATS SUMMARY (Optional Enhancement) ========== */
        .stats-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
            animation: fadeInUp 0.7s ease-out 0.2s both;
        }

        .summary-card {
            background: var(--bg-primary);
            padding: 1.25rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            border-left: 4px solid;
            transition: var(--transition-fast);
        }

        .summary-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .summary-card.primary { border-left-color: var(--primary); }
        .summary-card.success { border-left-color: var(--secondary); }
        .summary-card.warning { border-left-color: var(--warning); }
        .summary-card.info { border-left-color: var(--info); }

        /* ========== DEPARTURES GRID ========== */
        .departures-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 1.5rem;
            animation: fadeInUp 0.8s ease-out 0.3s both;
        }

        /* ========== DEPARTURE CARD ========== */
        .departure-card {
            background: var(--bg-primary);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-md);
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            transition: var(--transition-base);
            position: relative;
            overflow: hidden;
            border: 1px solid var(--border-light);
            animation: cardEntrance 0.5s ease-out both;
        }

        @keyframes cardEntrance {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .departure-card:nth-child(1) { animation-delay: 0.1s; }
        .departure-card:nth-child(2) { animation-delay: 0.2s; }
        .departure-card:nth-child(3) { animation-delay: 0.3s; }
        .departure-card:nth-child(4) { animation-delay: 0.4s; }
        .departure-card:nth-child(5) { animation-delay: 0.5s; }
        .departure-card:nth-child(6) { animation-delay: 0.6s; }

        .departure-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: var(--shadow-xl);
            border-color: var(--primary);
        }

        .departure-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 50%, var(--accent) 100%);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease-out;
        }

        .departure-card:hover::before {
            transform: scaleX(1);
        }

        /* Card Status Indicator */
        .card-status {
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--secondary);
            box-shadow: 0 0 0 3px rgba(72, 187, 120, 0.2);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(72, 187, 120, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(72, 187, 120, 0); }
            100% { box-shadow: 0 0 0 0 rgba(72, 187, 120, 0); }
        }

        /* Card Main Info */
        .card-main-info {
            padding: 1.75rem;
            border-bottom: 1px solid var(--border-light);
            position: relative;
        }

        .departure-time-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .departure-time {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.02em;
        }

        .route-badge {
            background: var(--bg-tertiary);
            padding: 0.25rem 0.75rem;
            border-radius: var(--radius-2xl);
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .route-name {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .route-name i {
            color: var(--primary);
            font-size: 1rem;
        }

        .route-details {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .bus-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .bus-info i {
            color: var(--accent);
        }

        /* Stats Grid */
        .card-stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            padding: 1.5rem;
            background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-primary) 100%);
        }

        .stat-item {
            text-align: center;
            padding: 0.75rem;
            border-radius: var(--radius-md);
            background: var(--bg-primary);
            border: 1px solid var(--border-light);
            transition: var(--transition-fast);
        }

        .stat-item:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }

        .stat-icon {
            width: 32px;
            height: 32px;
            margin: 0 auto 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-md);
            font-size: 1rem;
        }

        .stat-icon.bookings {
            background: rgba(102, 126, 234, 0.1);
            color: var(--primary);
        }

        .stat-icon.revenue {
            background: rgba(72, 187, 120, 0.1);
            color: var(--secondary);
        }

        .stat-icon.cash {
            background: rgba(237, 137, 54, 0.1);
            color: var(--accent);
        }

        .stat-icon.online {
            background: rgba(66, 153, 225, 0.1);
            color: var(--info);
        }

        .stat-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--text-primary);
            line-height: 1;
        }

        .stat-value.success {
            color: var(--secondary);
        }

        /* Occupancy Section */
        .occupancy-section {
            padding: 1.5rem;
            background: var(--bg-primary);
            border-top: 1px solid var(--border-light);
        }

        .occupancy-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .occupancy-title {
            font-weight: 600;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .occupancy-percentage {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--primary);
        }

        .progress-bar-container {
            background: var(--bg-tertiary);
            border-radius: var(--radius-2xl);
            height: 12px;
            overflow: hidden;
            position: relative;
        }

        .progress-bar-fill {
            height: 100%;
            border-radius: var(--radius-2xl);
            background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
            transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .progress-bar-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
            background: linear-gradient(
                90deg,
                transparent,
                rgba(255, 255, 255, 0.3),
                transparent
            );
            transform: translateX(-100%);
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            100% {
                transform: translateX(100%);
            }
        }

        .occupancy-details {
            display: flex;
            justify-content: space-between;
            margin-top: 0.5rem;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        /* Staff List */
        .card-staff-list {
            padding: 1.5rem;
            background: linear-gradient(135deg, var(--bg-tertiary) 0%, var(--bg-secondary) 100%);
            border-top: 1px solid var(--border-light);
            font-size: 0.9rem;
        }

        .staff-title {
            font-weight: 700;
            color: var(--text-secondary);
            margin-bottom: 0.75rem;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }

        .staff-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .staff-member {
            background: var(--bg-primary);
            padding: 0.5rem 0.75rem;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border: 1px solid var(--border-light);
            transition: var(--transition-fast);
        }

        .staff-member:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
            border-color: var(--primary);
        }

        .staff-role {
            font-weight: 600;
            color: var(--primary);
            font-size: 0.75rem;
        }

        .staff-name {
            color: var(--text-secondary);
            font-weight: 500;
        }

        .no-staff {
            color: var(--text-muted);
            font-style: italic;
        }

        /* Empty State */
        .empty-state {
            background: var(--bg-primary);
            border-radius: var(--radius-xl);
            padding: 4rem 2rem;
            text-align: center;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-light);
        }

        .empty-state-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: var(--bg-tertiary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: var(--text-muted);
        }

        .empty-state-title {
            font-size: 1.5rem;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }

        .empty-state-text {
            color: var(--text-muted);
        }

        /* ========== MOBILE RESPONSIVENESS ========== */
        @media (max-width: 768px) {
            .container-fluid {
                padding: 1rem;
            }

            .dashboard-title {
                font-size: 1.75rem;
            }

            .dashboard-subtitle {
                font-size: 0.95rem;
            }

            .filter-bar {
                padding: 1rem;
            }

            .filter-form {
                flex-direction: column;
                align-items: stretch;
            }

            .date-input {
                width: 100%;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .departures-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .departure-card {
                margin: 0;
            }

            .departure-time {
                font-size: 1.5rem;
            }

            .route-name {
                font-size: 1.1rem;
            }

            .card-stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.5rem;
                padding: 1rem;
            }

            .stat-item {
                padding: 0.5rem;
            }

            .stat-value {
                font-size: 1.25rem;
            }

            .staff-grid {
                flex-direction: column;
            }

            .staff-member {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .dashboard-title {
                font-size: 1.5rem;
            }

            .departure-time-wrapper {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .route-details {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .card-stats-grid {
                grid-template-columns: 2, 1fr;
            }
        }

        /* ========== LOADING ANIMATION ========== */
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid var(--border-light);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* ========== ACCESSIBILITY ========== */
        .departure-card:focus {
            outline: 3px solid var(--primary);
            outline-offset: 2px;
        }

        .btn:focus,
        .date-input:focus {
            outline: 3px solid var(--primary);
            outline-offset: 2px;
        }

        /* ========== PRINT STYLES ========== */
        @media print {
            body {
                background: white;
            }

            .filter-bar,
            .sidebar,
            .header {
                display: none;
            }

            .departure-card {
                break-inside: avoid;
                box-shadow: none;
                border: 1px solid #000;
            }
        }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include_once('sidebar.php');?>
    <div class="main-content">
        <?php include_once('header.php');?>
        <div class="container-fluid">
            <!-- Dashboard Header -->
            <div class="dashboard-header">
                <h1 class="dashboard-title">
                    <i class="fas fa-rocket"></i> Today's Departures
                </h1>
                <p class="dashboard-subtitle">Live operational overview for all scheduled routes</p>
            </div>
            
            <!-- Filter Bar -->
            <div class="filter-bar">
                <form method="GET" class="filter-form">
                    <label for="date-filter" class="filter-label">
                        <i class="fas fa-calendar"></i> Select Date
                    </label>
                    <input 
                        type="date" 
                        class="date-input" 
                        name="date" 
                        id="date-filter" 
                        value="<?php echo htmlspecialchars($filter_date); ?>"
                    >
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> View Departures
                    </button>
                    <a href="todays_departures" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Today
                    </a>
                </form>
            </div>
            
            <!-- Optional: Summary Stats -->
            <?php if (!empty($departures)): 
                $total_bookings = array_sum(array_column(array_column($departures, 'stats'), 'booked_seats'));
                $total_revenue = array_sum(array_column(array_column($departures, 'stats'), 'total_revenue'));
                $total_departures = count($departures);
            ?>
            <div class="stats-summary">
                <div class="summary-card primary">
                    <div class="stat-label">Total Departures</div>
                    <div class="stat-value"><?php echo $total_departures; ?></div>
                </div>
                <div class="summary-card success">
                    <div class="stat-label">Total Bookings</div>
                    <div class="stat-value"><?php echo $total_bookings; ?></div>
                </div>
                <div class="summary-card warning">
                    <div class="stat-label">Total Revenue</div>
                    <div class="stat-value">₹<?php echo number_format($total_revenue); ?></div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Departures Grid -->
            <div class="departures-grid">
                <?php if (empty($departures)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-calendar-times"></i>
                        </div>
                        <h4 class="empty-state-title">No Departures Scheduled</h4>
                        <p class="empty-state-text">
                            No departures found for <?php echo date('F d, Y', strtotime($filter_date)); ?>
                        </p>
                    </div>
                <?php else: ?>
                    <?php foreach($departures as $dep): 
                        $stats = $dep['stats'];
                        $occupancy_percent = ($stats['total_seats'] > 0) ? round(($stats['booked_seats'] / $stats['total_seats']) * 100) : 0;
                        
                        // Determine occupancy color
                        $occupancy_color = 'var(--secondary)';
                        if ($occupancy_percent < 50) {
                            $occupancy_color = 'var(--warning)';
                        } elseif ($occupancy_percent < 30) {
                            $occupancy_color = 'var(--danger)';
                        }
                    ?>
                    <a href="view_bookings.php?route_id=<?php echo $dep['route_id']; ?>&date=<?php echo $filter_date; ?>" class="departure-card">
                        <!-- Status Indicator -->
                        <div class="card-status"></div>
                        
                        <!-- Main Info Section -->
                        <div class="card-main-info">
                            <div class="departure-time-wrapper">
                                <div class="departure-time">
                                    <i class="fas fa-clock"></i>
                                    <?php echo date('h:i A', strtotime($dep['departure_time'])); ?>
                                </div>
                                <span class="route-badge">Route #<?php echo $dep['route_id']; ?></span>
                            </div>
                            
                            <div class="route-name">
                                <i class="fas fa-route"></i>
                                <?php echo htmlspecialchars($dep['route_name']); ?>
                            </div>
                            
                            <div class="route-details">
                                <div class="bus-info">
                                    <i class="fas fa-bus"></i>
                                    <span><?php echo htmlspecialchars($dep['bus_name']); ?></span>
                                </div>
                                <div class="bus-info">
                                    <i class="fas fa-id-card"></i>
                                    <span><?php echo htmlspecialchars($dep['registration_number']); ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Stats Grid -->
                        <div class="card-stats-grid">
                            <div class="stat-item">
                                <div class="stat-icon bookings">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="stat-label">Bookings</div>
                                <div class="stat-value"><?php echo $stats['booked_seats']; ?>/<?php echo $stats['total_seats']; ?></div>
                            </div>
                            
                            <div class="stat-item">
                                <div class="stat-icon revenue">
                                    <i class="fas fa-rupee-sign"></i>
                                </div>
                                <div class="stat-label">Total Revenue</div>
                                <div class="stat-value success">₹<?php echo number_format($stats['total_revenue']); ?></div>
                            </div>
                            
                            <div class="stat-item">
                                <div class="stat-icon cash">
                                    <i class="fas fa-money-bill"></i>
                                </div>
                                <div class="stat-label">Cash Sales</div>
                                <div class="stat-value">₹<?php echo number_format($stats['cash_sales']); ?></div>
                            </div>
                            
                            <div class="stat-item">
                                <div class="stat-icon online">
                                    <i class="fas fa-credit-card"></i>
                                </div>
                                <div class="stat-label">Online Sales</div>
                                <div class="stat-value">₹<?php echo number_format($stats['online_sales']); ?></div>
                            </div>
                        </div>

                        <!-- Occupancy Section -->
                        <div class="occupancy-section">
                            <div class="occupancy-header">
                                <span class="occupancy-title">
                                    <i class="fas fa-chart-pie"></i> Seat Occupancy
                                </span>
                                <span class="occupancy-percentage" style="color: <?php echo $occupancy_color; ?>">
                                    <?php echo $occupancy_percent; ?>%
                                </span>
                            </div>
                            <div class="progress-bar-container">
                                <div class="progress-bar-fill" style="width: <?php echo $occupancy_percent; ?>%; background: linear-gradient(90deg, <?php echo $occupancy_color; ?> 0%, <?php echo $occupancy_color; ?> 100%);"></div>
                            </div>
                            <div class="occupancy-details">
                                <span><?php echo $stats['booked_seats']; ?> Booked</span>
                                <span><?php echo $stats['total_seats'] - $stats['booked_seats']; ?> Available</span>
                            </div>
                        </div>
                        
                        <!-- Staff List -->
                        <div class="card-staff-list">
                            <div class="staff-title">
                                <i class="fas fa-user-tie"></i> Assigned Staff
                            </div>
                            <?php if (empty($dep['staff'])): ?>
                                <p class="no-staff">No staff assigned yet</p>
                            <?php else: ?>
                                <div class="staff-grid">
                                    <?php 
                                    $staff_by_role = [];
                                    foreach($dep['staff'] as $staff_member) {
                                        $staff_by_role[$staff_member['role']][] = $staff_member['name'];
                                    }
                                    
                                    foreach($staff_by_role as $role => $names): 
                                        $role_icon = 'fa-user';
                                        if (stripos($role, 'driver') !== false) $role_icon = 'fa-id-badge';
                                        elseif (stripos($role, 'conductor') !== false) $role_icon = 'fa-ticket-alt';
                                        elseif (stripos($role, 'helper') !== false) $role_icon = 'fa-hands-helping';
                                    ?>
                                        <?php foreach($names as $name): ?>
                                        <div class="staff-member">
                                            <i class="fas <?php echo $role_icon; ?>"></i>
                                            <span class="staff-role"><?php echo $role; ?>:</span>
                                            <span class="staff-name"><?php echo htmlspecialchars($name); ?></span>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include_once('foot.php');?>

<!-- Optional: Add smooth scroll and other interactions -->
<script>
    // Animate progress bars on page load
    document.addEventListener('DOMContentLoaded', function() {
        const progressBars = document.querySelectorAll('.progress-bar-fill');
        progressBars.forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0';
            setTimeout(() => {
                bar.style.width = width;
            }, 100);
        });
        
        // Add hover effect to cards
        const cards = document.querySelectorAll('.departure-card');
        cards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
        });
        
        // Auto-refresh every 60 seconds
       
    });
    
    // Add keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'r' && e.ctrlKey) {
            e.preventDefault();
            location.reload();
        }
    });
</script>

</body>
</html>