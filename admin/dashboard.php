<?php
// PHP Configuration & Database Connection
// Ensure _db.php handles session_start() if it's not done elsewhere.
// It also should define $_conn_db and check_user_login().
global $_conn_db;
include_once('function/_db.php'); // Your DB connection and helper functions
check_user_login(); // This function should also handle redirect if not logged in.

// Function to sanitize output (essential for preventing XSS)
if (!function_exists('show_rhyno_data')) {
    function show_rhyno_data($data) {
        return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// User Session Data
$name = $_SESSION['user']['name'] ?? 'Guest';
$email = $_SESSION['user']['email'] ?? 'N/A';
$mobile = $_SESSION['user']['mobile'] ?? 'N/A';
$user_id = $_SESSION['user']['id'] ?? null;

 


$dashboard_error = null;  

 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once('head.php');?>
    <!-- ** NEW & IMPROVED STYLES ** -->
    <style>
        :root {
            --primary-color: #0d6efd;
            --primary-light: #e7f0ff;
            --success-color: #198754;
            --success-light: #e8f3ee;
            --warning-color: #ffc107;
            --warning-light: #fff8e7;
            --danger-color: #dc3545;
            --danger-light: #fceeee;
            --info-color: #0dcaf0;
            --info-light: #e7fafe;
            --secondary-color: #6c757d;
            --light-gray: #f8f9fa;
            --border-color: #e9ecef;
            --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            --card-hover-shadow: 0 6px 24px rgba(0, 0, 0, 0.08);
        }
        

        /* General Layout */
         
        .dashboard-heading { color: #212529; font-weight: 700; }
        .dashboard-subheading { color: var(--secondary-color); margin-bottom: 2rem; }

        /* KPI Stat Cards */
        .stat-card {
            background-color: #fff;
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--card-shadow);
        }
        .stat-card .icon-container {
            font-size: 1.75rem;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            margin-right: 1.25rem;
        }
        .stat-card .stat-details .stat-number {
            font-size: 2.25rem;
            font-weight: 700;
            line-height: 1;
        }
        .stat-card .stat-details .stat-title {
            font-size: 1rem;
            color: var(--secondary-color);
        }
        /* KPI Colors */
        .stat-card.primary .icon-container { background-color: var(--primary-light); color: var(--primary-color); }
        .stat-card.success .icon-container { background-color: var(--success-light); color: var(--success-color); }
        .stat-card.warning .icon-container { background-color: var(--warning-light); color: var(--warning-color); }
        .stat-card.danger .icon-container { background-color: var(--danger-light); color: var(--danger-color); }

        /* Cards */
        .card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: var(--card-shadow);
            transition: box-shadow 0.2s ease;
            height: 100%;
        }
        .card:hover { box-shadow: var(--card-hover-shadow); }
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid var(--border-color);
            font-weight: 600;
            font-size: 1.1rem;
            color: #343a40;
            padding: 1rem 1.5rem;
        }
        
        /* Section Headings */
        .section-title {
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: #495057;
        }

        /* Quick Action Buttons */
        .quick-action-btn {
            background-color: #fff;
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            padding: 1.5rem 1rem;
            text-align: center;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }
        .quick-action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
            border-color: var(--primary-color);
        }
        .quick-action-btn i {
            font-size: 2rem;
            margin-bottom: 0.75rem;
            transition: color 0.2s ease;
        }
        .quick-action-btn span {
            font-weight: 600;
            font-size: 0.95rem;
            color: #333;
        }

        /* NEW: Div-based Data List */
        .data-list { list-style: none; padding: 0; margin: 0; }
        .data-list li {
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            border-bottom: 1px solid var(--border-color);
            transition: background-color 0.2s;
            position: relative;
            padding-left: 2rem;
        }
        .data-list li:last-child { border-bottom: none; }
        .data-list li:hover { background-color: var(--light-gray); }
        .data-list li::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 5px;
            height: 100%;
            background-color: var(--border-color); /* Default line color */
        }
        /* Status Colors for List Items */
        .data-list li[data-status='Pending']::before, .data-list li[data-status='pending']::before { background-color: var(--secondary-color); }
        .data-list li[data-status='In Transit']::before { background-color: var(--info-color); }
        .data-list li[data-status='Delivered']::before, .data-list li[data-status='responded']::before, .data-list li[data-status='published']::before { background-color: var(--success-color); }
        .data-list li[data-status='Cancelled']::before, .data-list li[data-status='archived']::before { background-color: var(--danger-color); }
        .data-list li[data-status='draft']::before { background-color: var(--warning-color); }

        .data-list .item-icon { color: var(--secondary-color); font-size: 1.25rem; width: 25px; text-align: center;}
        .data-list .item-content { flex-grow: 1; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 0.5rem; }
        .data-list .item-primary { font-weight: 600; color: #212529; }
        .data-list .item-secondary { font-size: 0.9rem; color: var(--secondary-color); }
        .data-list .item-status { margin-left: auto; }
        
        /* Badge Styling */
        .badge { padding: 0.7em 0.8em; font-size: 0.75rem; font-weight: 600; border-radius: 5px; }
    </style>
</head>
<body>

<div id="wrapper">
    <!-- Sidebar -->
    <?php include_once('sidebar.php');?>
    <!-- End Sidebar -->

    <!-- Page Content Wrapper -->
    <div class="main-content">
        <!-- Header / Navbar Top -->
        <?php include_once('header.php');?>
        <!-- End Header -->

        <!-- Main Dashboard Content -->
        <div class="container-fluid ">
            <?php if ($dashboard_error): ?>
                <?php echo $dashboard_error; ?>
            <?php endif; ?>

            <h1 class="dashboard-heading">Dashboard</h1>
            <p class="dashboard-subheading">Welcome back, <?php echo show_rhyno_data($name); ?>! Here's your business snapshot.</p>
            
            
            <!-- KPI Statistics Section -->
            <!-- <div class="row g-4 mb-5">
                <div class="col-xl-3  col-md-6">
                    <div class="stat-card primary">
                        <div class="icon-container"><i class="fas fa-truck-loading"></i></div>
                        <div class="stat-details">
                            <div class="stat-number"><?php echo show_rhyno_data($stats_in_transit); ?></div>
                            <div class="stat-title">Shipments In Transit</div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card success">
                        <div class="icon-container"><i class="fas fa-file-invoice-dollar"></i></div>
                        <div class="stat-details">
                            <div class="stat-number"><?php echo show_rhyno_data($stats_pending_quotes); ?></div>
                            <div class="stat-title">Pending Quotes</div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card warning">
                        <div class="icon-container"><i class="fas fa-pen-nib"></i></div>
                        <div class="stat-details">
                            <div class="stat-number"><?php echo show_rhyno_data($stats_published_blogs); ?></div>
                            <div class="stat-title">Published Blogs</div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card danger">
                        <div class="icon-container"><i class="fas fa-envelope-open-text"></i></div>
                        <div class="stat-details">
                            <div class="stat-number"><?php echo show_rhyno_data($stats_new_contacts); ?></div>
                            <div class="stat-title">New Contact Messages</div>
                        </div>
                    </div>
                </div>
            </div> -->

            <!-- Quick Actions -->
            <h3 class="section-title">Quick Actions</h3>
            <div class="row g-3 mb-5">
                <div class="col-6 col-sm-4 col-md-3 col-lg-2"><a href="generate_shipment.php" class="quick-action-btn"><i class="fas fa-box-open text-primary"></i><span>Add Shipment</span></a></div>
                <div class="col-6 col-sm-4 col-md-3 col-lg-2"><a href="view_shipment.php" class="quick-action-btn"><i class="fas fa-truck text-info"></i><span>View Shipments</span></a></div>
                <div class="col-6 col-sm-4 col-md-3 col-lg-2"><a href="view_quotes.php" class="quick-action-btn"><i class="fas fa-file-invoice text-success"></i><span>Quote Requests</span></a></div>
                <div class="col-6 col-sm-4 col-md-3 col-lg-2"><a href="insert_blog.php" class="quick-action-btn"><i class="fas fa-plus-circle text-warning"></i><span>Add Blog</span></a></div>
                <div class="col-6 col-sm-4 col-md-3 col-lg-2"><a href="manage_images.php" class="quick-action-btn"><i class="fas fa-images text-danger"></i><span>Image Gallery</span></a></div>
                <div class="col-6 col-sm-4 col-md-3 col-lg-2"><a href="view_reviews.php" class="quick-action-btn"><i class="fas fa-star" style="color: #fd7e14;"></i><span>Manage Reviews</span></a></div>
            </div>

            <!-- Recent Data Sections -->
            <div class="row g-4">
                <!-- Latest Shipments -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>Latest Shipments</span>
                            <a href="view_shipment.php" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body p-0">
                            <?php if (!empty($recent_shipments)): ?>
                                <ul class="data-list">
                                    <?php foreach ($recent_shipments as $shipment): ?>
                                    <li data-status="<?php echo show_rhyno_data($shipment['status']); ?>">
                                        <div class="item-icon"><i class="fas fa-dolly-flatbed"></i></div>
                                        <div class="item-content">
                                            <div>
                                                <div class="item-primary"><?php echo show_rhyno_data($shipment['shipment_id']); ?></div>
                                                <div class="item-secondary"><?php echo show_rhyno_data($shipment['sender_name']); ?> to <?php echo show_rhyno_data($shipment['receiver_name']); ?></div>
                                            </div>
                                            <div class="item-status">
                                                <?php $s_class = ['Pending' => 'bg-secondary', 'In Transit' => 'bg-info text-dark', 'Delivered' => 'bg-success', 'Cancelled' => 'bg-danger'][$shipment['status']] ?? 'bg-light text-dark'; ?>
                                                <span class="badge <?php echo $s_class; ?>"><?php echo show_rhyno_data($shipment['status']); ?></span>
                                                <a href="view_shipment.php?search_shipment_id=<?php echo show_rhyno_data($shipment['shipment_id']); ?>&filter_status=&filter_created_from=&filter_created_to=" class="btn btn-outline-secondary btn-sm"><i class='bx bx-show'></i></a>
                                            </div>
                                        </div>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="text-center text-muted p-5 m-0">No recent shipments found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Latest Quote Requests -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>Latest Quote Requests</span>
                            <a href="view_quotes.php" class="btn btn-sm btn-outline-success">View All</a>
                        </div>
                        <div class="card-body p-0">
                             <?php if (!empty($recent_quote_requests)): ?>
                                <ul class="data-list">
                                    <?php foreach ($recent_quote_requests as $quote): ?>
                                    <li data-status="<?php echo show_rhyno_data($quote['status']); ?>">
                                        <div class="item-icon"><i class="fas fa-receipt"></i></div>
                                        <div class="item-content">
                                            <div>
                                                <div class="item-primary"><?php echo show_rhyno_data($quote['full_name']); ?></div>
                                                <div class="item-secondary"><?php echo show_rhyno_data($quote['from_location']); ?> to <?php echo show_rhyno_data($quote['to_location']); ?></div>
                                            </div>
                                            <div class="item-status">
                                                <span class=" badge <?php echo ($quote['status'] == 'pending') ? 'bg-warning text-dark' : 'bg-success'; ?>">
                                                    <?php echo show_rhyno_data(ucfirst($quote['status'])); ?>
                                                </span>
                                            </div>
                                            <a href="view_quotes?id=<?php echo show_rhyno_data($quote['id']); ?>" class="btn btn-outline-secondary btn-sm"><i class='bx bx-show'></i></a>
                                        </div>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="text-center text-muted p-5 m-0">No recent quote requests found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Latest Blog Posts -->
                <div class="col-lg-12 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>Latest Blog Posts</span>
                            <a href="view_blogs.php" class="btn btn-sm btn-outline-warning text-dark">View All</a>
                        </div>
                        <div class="card-body p-0">
                            <?php if (!empty($recent_blogs)): ?>
                                <ul class="data-list">
                                    <?php foreach ($recent_blogs as $blog): ?>
                                    <li data-status="<?php echo show_rhyno_data($blog['status']); ?>">
                                        <div class="item-icon"><i class="fas fa-newspaper"></i></div>
                                        <div class="item-content">
                                             <div>
                                                <div class="item-primary"><?php echo show_rhyno_data(mb_strimwidth($blog['title'], 0, 60, "...")); ?></div>
                                                <div class="item-secondary">by <?php echo show_rhyno_data($blog['author_name']); ?> on <?php echo show_rhyno_data(date('M d, Y', strtotime($blog['created_at']))); ?></div>
                                            </div>
                                            <div class="item-status">
                                                <?php $b_class = ['draft' => 'bg-secondary', 'published' => 'bg-success', 'archived' => 'bg-dark'][$blog['status']] ?? 'bg-light text-dark'; ?>
                                                <span class="badge <?php echo $b_class; ?>"><?php echo show_rhyno_data(ucfirst($blog['status'])); ?></span>
                                                <a href="view_blogs.php?search_query=<?php echo show_rhyno_data($blog['title']); ?>" class="btn  btn-outline-warning btn-sm"><i class='bx bx-show'></i></a>
                                                <a href="edit_blog.php?id=<?php echo show_rhyno_data($blog['id']); ?>" class="btn  btn-outline-primary btn-sm"><i class='bx bx-edit'></i></a>
                                                <form action="function/delete/delete_blog.php" method="POST"
                                                class="d-inline delete-form">
                                                <input type="hidden" name="blog_id"
                                                    value="<?php echo show_rhyno_data($blog['id']); ?>">
                                                <button type="button" class="btn btn-sm  btn-outline-danger delete-blog-btn"
                                                    data-blog-title="<?php echo show_rhyno_data($blog['title']); ?>"
                                                    data-blog-id="<?php echo show_rhyno_data(data_rhyno: $blog['id']); ?>" title="Delete">
                                                    <i class='bx bx-trash'></i>
                                                </button>
                                                </form>
                                            </div>
                                        </div>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="text-center text-muted p-5 m-0">No recent blog posts found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div> <!-- End of row -->
        </div>
        <!-- End Main Dashboard Content -->
    </div>
    <!-- End Page Content Wrapper -->
</div>
<!-- Overlay for mobile sidebar (outside wrapper for positioning) -->
<div class="overlay" id="sidebar-overlay"></div>

<?php include_once('foot.php');?>

</body>
</html>
<?php pdo_close_conn($_conn_db); ?>