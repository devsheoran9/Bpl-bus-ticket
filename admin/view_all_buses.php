<?php
// view_buses.php (Redesigned with More Details in Custom Table)
global $_conn_db;
include_once('function/_db.php');
session_security_check(); 
$can_edit = user_has_permission('can_edit_buses');
$can_delete = user_has_permission('can_delete_buses');
$can_manage_seats = user_has_permission('can_manage_seats');
check_permission('can_view_buses');
// A single, powerful query to get all bus details at once
try {
    $sql = "
        SELECT 
            b.*,
            (SELECT COUNT(*) FROM seats s WHERE s.bus_id = b.bus_id AND s.seat_type = 'SEATER' AND s.is_bookable = 1) as seater_count,
            (SELECT COUNT(*) FROM seats s WHERE s.bus_id = b.bus_id AND s.seat_type = 'SLEEPER' AND s.is_bookable = 1) as sleeper_count,
            (SELECT GROUP_CONCAT(r.route_name SEPARATOR '</li><li>') FROM routes r WHERE r.bus_id = b.bus_id) as assigned_routes,
            (SELECT GROUP_CONCAT(c.category_name SEPARATOR ',') 
             FROM bus_category_map map
             JOIN bus_categories c ON map.category_id = c.category_id
             WHERE map.bus_id = b.bus_id) as categories
        FROM buses b
        ORDER BY b.bus_id DESC
    ";
    $stmt = $_conn_db->query($sql);
    $buses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // For debugging: error_log($e->getMessage());
    $buses = []; // Gracefully handle DB error
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once('head.php');?>
    <title>Manage Buses</title>
    
    <style>
        /* Base styles */
        body { background-color: #f8f9fa; color: #343a40; }
        #wrapper { display: flex; }
        
        .container-fluid { padding: 8px; } /* Remove container padding as main-content already has */

        /* Page Header and Search */
        .page-header {
            display: flex; flex-wrap: wrap; justify-content: space-between;
            align-items: center; gap: 1rem; margin-bottom: 1.5rem;
        }
        .page-header h2 { font-weight: 700; color: #212529; }
        .search-wrapper { position: relative; width: 100%; max-width: 350px; }
        .search-wrapper .form-control { padding-left: 2.5rem; border-radius: 0.5rem; border: 1px solid #ced4da; }
        .search-wrapper .form-control:focus { border-color: #86b7fe; box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25); }
        .search-wrapper .fa-search { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #6c757d; }
        .page-header .btn-primary { border-radius: 0.5rem; font-weight: 600; }

        /* Card container for table/cards */
        .card { box-shadow: 0 8px 30px rgba(0,0,0,0.07); border: none; border-radius: 1rem; background-color: #ffffff; }
        .card-header {
            background-color: #ffffff;
            font-weight: 600;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #dee2e6;
            font-size: 1.1rem;
            display: flex; /* For alignment of title and badge */
            justify-content: space-between;
            align-items: center;
        }
        .card-header span:first-child { line-height: 1.2; }
        .card-header a { font-size: 0.85rem; color: #0d6efd; text-decoration: none; font-weight: 500;}
        .card-header a:hover { text-decoration: underline; }

        /* --- TABLE STYLING (for larger screens) --- */
        .table thead th {
            background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; color: #495057;
            font-weight: 600; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px;
            padding: 1rem 1.5rem;
        }
        .table tbody tr { transition: background-color 0.2s ease-in-out; }
        .table td { border-top: 1px solid #dee2e6; vertical-align: middle; padding: 0.75rem 1.5rem; }
        
        /* Custom Cell Styling for Table */
        .bus-info-cell { display: flex; align-items: center; gap: 1rem; }
        .bus-icon-circle {
            width: 50px; height: 50px; border-radius: 50%; background-color: #e9ecef; color: #0d6efd;
            display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;
            border: 1px solid #dee2e6;
        }
        .bus-name-table { font-weight: 600; color: #212529; display: block; }
        .bus-reg-table { font-size: 0.85rem; color: #6c757d; display: block; }
        .action-buttons { display: flex; gap: 0.5rem; justify-content: flex-end; }
        .action-buttons .btn { padding: 0.3rem 0.7rem; border-radius: 0.4rem; font-size: 0.85rem; }

        /* Details in Table Cells */
        .category-badge {
            font-size: 0.75rem; font-weight: 500;
            padding: 0.3em 0.6em; margin: 0 2px 2px 0; display: inline-block;
            border-radius: 12px; background-color: #e0f2fe; color: #0d6efd; border: 1px solid #b6d4fe;
        }
        .seat-layout-info span { font-size: 0.9em; font-weight: 500; color: #495057; }
        .seat-layout-info .fas { margin-right: 0.3em; }
        .route-list { list-style: none; padding: 0; margin: 0; font-size: 0.85em; max-height: 80px; overflow-y: auto; }
        .route-list li { padding-bottom: 2px; color: #6c757d; }
        .route-list li:last-child { padding-bottom: 0; }
        .status-badge { font-size: 0.8em; padding: 0.4em 0.7em; border-radius: 15px; }
        .status-badge.bg-success { background-color: #d1e7dd !important; color: #0f5132 !important; border: 1px solid #badbcc; }
        .status-badge.bg-warning { background-color: #fff3cd !important; color: #664d03 !important; border: 1px solid #ffecb5; }
        .status-badge.bg-secondary { background-color: #e2e3e5 !important; color: #495057 !important; border: 1px solid #d3d6db; }

        /* --- CUSTOM COMPACT CARD STYLING (for smaller screens) --- */
        .bus-card-sm {
            background-color: white;
            border: 1px solid #e0e0e0;
            border-radius: 0.75rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            margin-bottom: 1rem; /* Space between cards */
        }
        .bus-card-sm:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        }
        .bus-card-sm .card-body {
            padding: 0.75rem 1rem; /* Reduced padding */
        }

        .bus-card-sm .bus-header-compact {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px dashed #dee2e6;
            margin-bottom: 0.75rem;
        }
        .bus-icon-circle-compact {
            width: 45px; height: 45px;
            min-width: 45px;
            border-radius: 50%;
            background-color: #e0f2fe; /* Light primary color */
            color: #0d6efd;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
            border: 1px solid #b6d4fe;
        }
        .bus-name-reg-wrapper { flex-grow: 1; }
        .bus-name-card-compact {
            font-size: 1rem; font-weight: 600; margin-bottom: 0.1rem; color: #212529;
            line-height: 1.2;
        }
        .bus-reg-card-compact {
            font-size: 0.8rem; color: #6c757d;
            line-height: 1.2;
        }
        .status-badge-card {
            font-size: 0.75rem; padding: 0.3em 0.6em; border-radius: 15px;
            margin-left: auto; /* Push to right */
            line-height: 1;
        }
        .status-badge-card.bg-success { background-color: #d1e7dd !important; color: #0f5132 !important; border: 1px solid #badbcc; }
        .status-badge-card.bg-warning { background-color: #fff3cd !important; color: #664d03 !important; border: 1px solid #ffecb5; }
        .status-badge-card.bg-secondary { background-color: #e2e3e5 !important; color: #495057 !important; border: 1px solid #d3d6db; }


        .bus-details-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr); /* Two columns by default on mobile */
            gap: 0.75rem; /* Space between details */
            margin-bottom: 0.75rem;
        }
        .detail-item-compact {
            display: flex; flex-direction: column;
        }
        .detail-label-compact {
            font-size: 0.65rem; color: #6c757d; margin-bottom: 0.1rem; line-height: 1;
        }
        .detail-value-compact {
            font-size: 0.85rem; font-weight: 500; color: #212529;
            display: flex; align-items: center; gap: 0.3rem; line-height: 1.2;
        }
        .detail-value-compact .fas { font-size: 0.7rem; }

        .categories-compact, .routes-compact { margin-top: 0.75rem; }
        .categories-compact .detail-label-compact, .routes-compact .detail-label-compact { margin-bottom: 0.3rem; }
        .category-badge-compact {
            font-size: 0.65rem; padding: 0.25em 0.5em; margin: 0 2px 2px 0; border-radius: 10px;
            background-color: #e0f2fe; color: #0d6efd; border: 1px solid #b6d4fe; line-height: 1;
        }
        .routes-list-compact {
            list-style: none; padding: 0; margin: 0; font-size: 0.75rem; color: #495057;
            max-height: 60px; overflow-y: auto; /* Scroll for many routes */
        }
        .routes-list-compact li { padding-bottom: 2px; line-height: 1.2; }

        .bus-card-sm .card-footer {
            background-color: #f8f9fa; /* Light background for footer */
            border-top: 1px solid #dee2e6;
            padding: 0.75rem 1rem;
            border-bottom-left-radius: 0.75rem;
            border-bottom-right-radius: 0.75rem;
            display: flex; justify-content: flex-end; gap: 0.5rem;
            flex-wrap: wrap; /* Allow buttons to wrap */
        }
        .bus-card-sm .card-footer .btn {
            font-size: 0.75rem; padding: 0.3em 0.6em; border-radius: 0.4rem;
            white-space: nowrap; /* Prevent button text from wrapping */
        }
        /* No results for cards */
        .bus-list-cards .alert {
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border-radius: 0.75rem;
        }

        /* Responsive adjustments for filter/add bus section */
        @media (max-width: 767.98px) {
            .page-header { flex-direction: column; align-items: flex-start; margin-bottom: 1rem; gap: 0.5rem; }
            .page-header h2 { font-size: 1.5rem; margin-bottom: 0.5rem; }
            .page-header .search-wrapper { max-width: none; width: 100%; order: 1; margin-bottom: 0.5rem; }
            .page-header .d-block { width: 100% !important; order: 2; }
            .page-header .btn-primary { width: 100%; }

            .card-header { padding: 1rem; }
            .card-header span:first-child { font-size: 0.95rem; }
            .card-header span:first-child a { font-size: 0.75rem; }
            .card-header .badge { font-size: 0.8rem; }
        }
    </style>
</head>
<body>

<div id="wrapper">
    <?php include_once('sidebar.php');?>
    <div class="main-content">
        <?php include_once('header.php');?>
        <div class="container-fluid">
            
            <div class="page-header mt-4">
                <h2 class="mb-0">Manage Buses</h2>
                <div class="d-flex align-items-center gap-1 flex-wrap w-100 justify-content-end">
                    <div class="search-wrapper flex-grow-1">
                        <i class="fas fa-search"></i>
                        <input type="text" id="bus-search-input" class="form-control" placeholder="Search buses by name, reg. no, route...">
                    </div>
                    <div class="d-block flex-shrink-0"> <!-- Removed fixed width -->
                    <?php if (user_has_permission('can_add_buses')): ?>
                        <a href="add_bus.php" class=" button-13 bg-warning"><i class="fas fa-plus me-1"></i> Add Bus</a>
                    <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Bus List Table (Visible on medium screens and up) -->
            <div class="card d-none d-md-block">
                <div class="card-header">
                    <span>All Buses <br> <a href="deleted_buses_report">View Deleted Buses</a></span>
                    <span class="badge bg-primary rounded-pill"><?php echo count($buses); ?> Total</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="padding-left: 1.5rem;">Bus Details</th>
                                    <th>Categories</th>
                                    <th>Seat Layout</th>
                                    <th>Assigned Routes</th>
                                    <th>Status</th>
                                    <th class="text-end" style="padding-right: 1.5rem;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="buses-table-body">
                                <?php if (empty($buses)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-5">
                                            <i class="fas fa-bus fa-2x mb-2"></i>
                                            <p class="mb-0">No buses found. Please add a new bus.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($buses as $bus):
                                        $status_badge = 'bg-secondary';
                                        if ($bus['status'] == 'Active') $status_badge = 'bg-success';
                                        elseif ($bus['status'] == 'Inactive') $status_badge = 'bg-warning text-dark';
                                    ?>
                                        <tr id="bus-row-<?php echo $bus['bus_id']; ?>">
                                            <td>
                                                <div class="bus-info-cell">
                                                    <div class="bus-icon-circle"><i class="fas fa-bus-alt"></i></div>
                                                    <div>
                                                        <span class="bus-name-table"><?php echo htmlspecialchars($bus['bus_name']); ?></span>
                                                        <span class="bus-reg-table"><?php echo htmlspecialchars($bus['registration_number']); ?></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if (!empty($bus['categories'])): 
                                                    $categories = explode(',', $bus['categories']);
                                                    foreach($categories as $category): ?>
                                                        <span class="badge category-badge"><?php echo htmlspecialchars(trim($category)); ?></span>
                                                    <?php endforeach; 
                                                else: ?>
                                                    <span class="text-muted small">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="seat-layout-info">
                                                <span class="me-2"><i class="fas fa-chair text-success"></i> <?php echo $bus['seater_count']; ?></span>
                                                <span><i class="fas fa-bed text-info"></i> <?php echo $bus['sleeper_count']; ?></span>
                                            </td>
                                            <td>
                                                <?php if (!empty($bus['assigned_routes'])): ?>
                                                    <ul class="route-list">
                                                        <?php echo $bus['assigned_routes']; // Already formatted with <li> ?>
                                                    </ul>
                                                <?php else: ?>
                                                    <span class="text-muted small">No routes assigned</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="badge status-badge <?php echo $status_badge; ?>"><?php echo htmlspecialchars($bus['status']); ?></span></td>
                                            <td>
                                                <div class="action-buttons">
                                                <?php if ($can_edit): ?>
                                                    <a href="edit_bus.php?bus_id=<?php echo $bus['bus_id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-pencil-alt"></i></a>
                                                    <?php endif; ?>
                                                    <?php if ($can_manage_seats): ?>
                                                    <a href="manage_seats.php?bus_id=<?php echo $bus['bus_id']; ?>" class="btn btn-sm btn-outline-info" title="Manage Seats"><i class="fas fa-chair"></i></a>
                                                    <?php endif; ?>
                                                    <?php if ($can_delete): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-danger delete-bus-btn" 
                                                            data-bus-id="<?php echo $bus['bus_id']; ?>" 
                                                            data-bus-name="<?php echo htmlspecialchars($bus['bus_name']); ?>"
                                                            title="Delete">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <tr id="no-results-row-table" style="display: none;">
                                    <td colspan="6" class="text-center text-muted py-4">No buses match your search.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Bus List Cards (Visible on small screens only) -->
            <div class="d-md-none mt-4 bus-list-cards">
                <div class="card-header">
                    <span>All Buses <br> <a href="deleted_buses_report">View Deleted Buses</a></span>
                    <span class="badge bg-primary rounded-pill"><?php echo count($buses); ?> Total</span>
                </div>
                <div class="card-body p-0 pt-3"> <!-- Added top padding to card body for first card -->
                    <?php if (empty($buses)): ?>
                        <div class="alert alert-info text-center py-4 mb-0" role="alert">
                            <i class="fas fa-bus fa-2x mb-2"></i>
                            <p class="mb-0">No buses found. Please add a new bus.</p>
                        </div>
                    <?php else: ?>
                        <div class="row row-cols-1 g-3" id="buses-card-body">
                            <?php foreach ($buses as $bus):
                                $status_badge = 'bg-secondary';
                                if ($bus['status'] == 'Active') $status_badge = 'bg-success';
                                elseif ($bus['status'] == 'Inactive') $status_badge = 'bg-warning text-dark';
                            ?>
                                <div class="col" id="bus-card-<?php echo $bus['bus_id']; ?>">
                                    <div class="card bus-card-sm">
                                        <div class="card-body">
                                            <div class="bus-header-compact">
                                                <div class="bus-icon-circle-compact"><i class="fas fa-bus-alt"></i></div>
                                                <div class="bus-name-reg-wrapper">
                                                    <h6 class="bus-name-card-compact"><?php echo htmlspecialchars($bus['bus_name']); ?></h6>
                                                    <span class="bus-reg-card-compact"><?php echo htmlspecialchars($bus['registration_number']); ?></span>
                                                </div>
                                                <span class="badge status-badge-card <?php echo $status_badge; ?>"><?php echo htmlspecialchars($bus['status']); ?></span>
                                            </div>

                                            <div class="bus-details-grid">
                                                <div class="detail-item-compact">
                                                    <small class="detail-label-compact">Seater</small>
                                                    <span class="detail-value-compact"><i class="fas fa-chair text-success"></i><?php echo $bus['seater_count']; ?></span>
                                                </div>
                                                <div class="detail-item-compact">
                                                    <small class="detail-label-compact">Sleeper</small>
                                                    <span class="detail-value-compact"><i class="fas fa-bed text-info"></i><?php echo $bus['sleeper_count']; ?></span>
                                                </div>
                                            </div>
                                            
                                            <?php if (!empty($bus['categories'])): ?>
                                            <div class="categories-compact">
                                                <small class="detail-label-compact">Categories</small>
                                                <div class="d-flex flex-wrap mt-1">
                                                <?php $categories = explode(',', $bus['categories']);
                                                    foreach($categories as $category): ?>
                                                        <span class="badge category-badge-compact"><?php echo htmlspecialchars(trim($category)); ?></span>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                            <?php endif; ?>

                                            <?php if (!empty($bus['assigned_routes'])): ?>
                                            <div class="routes-compact mt-2">
                                                <small class="detail-label-compact">Assigned Routes</small>
                                                <ul class="routes-list-compact mt-1">
                                                    <?php echo $bus['assigned_routes']; // Already formatted with <li> ?>
                                                </ul>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-footer">
                                            <?php if ($can_edit): ?>
                                                <a href="edit_bus.php?bus_id=<?php echo $bus['bus_id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-pencil-alt me-1"></i> Edit</a>
                                            <?php endif; ?>
                                            <?php if ($can_manage_seats): ?>
                                                <a href="manage_seats.php?bus_id=<?php echo $bus['bus_id']; ?>" class="btn btn-sm btn-outline-info" title="Manage Seats"><i class="fas fa-chair me-1"></i> Seats</a>
                                            <?php endif; ?>
                                            <?php if ($can_delete): ?>
                                                <button type="button" class="btn btn-sm btn-outline-danger delete-bus-btn-card" 
                                                        data-bus-id="<?php echo $bus['bus_id']; ?>" 
                                                        data-bus-name="<?php echo htmlspecialchars($bus['bus_name']); ?>"
                                                        title="Delete">
                                                    <i class="fas fa-trash-alt me-1"></i> Delete
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="alert alert-info text-center py-4 mb-0" role="alert" id="no-results-row-cards" style="display: none;">
                            <i class="fas fa-bus fa-2x mb-2"></i>
                            <p class="mb-0">No buses match your search.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include_once('foot.php');?>

<script>
$(document).ready(function() {
    // --- Live Search Functionality (Unified for Table and Cards) ---
    $('#bus-search-input').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        
        // For Table (if visible)
        const tableBody = $('#buses-table-body');
        let visibleCountTable = 0;
        if (tableBody.is(':visible')) {
            tableBody.find('tr:not(#no-results-row-table)').each(function() {
                const row = $(this);
                const rowText = row.text().toLowerCase();
                if (rowText.includes(searchTerm)) {
                    row.show();
                    visibleCountTable++;
                } else {
                    row.hide();
                }
            });
            if (visibleCountTable === 0) {
                tableBody.find('#no-results-row-table').show();
            } else {
                tableBody.find('#no-results-row-table').hide();
            }
        }

        // For Cards (if visible)
        const cardsContainer = $('#buses-card-body');
        const noResultsCards = $('#no-results-row-cards');
        let visibleCountCards = 0;
        if (cardsContainer.is(':visible')) {
            cardsContainer.find('.col').each(function() {
                const cardCol = $(this);
                const cardText = cardCol.text().toLowerCase();
                if (cardText.includes(searchTerm)) {
                    cardCol.show();
                    visibleCountCards++;
                } else {
                    cardCol.hide();
                }
            });
            if (visibleCountCards === 0) {
                noResultsCards.show();
            } else {
                noResultsCards.hide();
            }
        }
    });

    // --- Delete Functionality (Unified for both Table and Cards) ---
    $(document).on('click', '.delete-bus-btn, .delete-bus-btn-card', function() {
        const busId = $(this).data('bus-id');
        const busName = $(this).data('bus-name');
        
        // Determine the element to target for fading out
        const targetElementTable = $('#bus-row-' + busId);
        const targetElementCard = $('#bus-card-' + busId);

        Swal.fire({
            title: `Delete "${busName}"?`,
            text: "All related data (seats, routes, etc.) will be affected. This cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'function/backend/bus_actions.php',
                    type: 'POST',
                    dataType: 'json',
                    data: { action: 'delete_bus', bus_id: busId },
                    success: function(response) {
                        if (response.res === 'true') {
                            $.notify({ title: response.notif_title, message: response.notif_desc }, { type: 'success' });
                            
                            // Fade out and remove both if they exist
                            targetElementTable.css('background-color', '#ffdddd').fadeOut(600, function() {
                                $(this).remove();
                                // Recalculate and update total count for table if visible
                                if($('#buses-table-body').is(':visible')) {
                                    const currentTableRows = $('#buses-table-body tr:visible:not(#no-results-row-table)').length;
                                    $('.card-header .badge').text(currentTableRows + ' Total');
                                    if(currentTableRows === 0) {
                                        $('#buses-table-body').html('<tr><td colspan="6" class="text-center text-muted py-5"><i class="fas fa-bus fa-2x mb-2"></i><p class="mb-0">No buses found. Please add a new bus.</p></td></tr>');
                                    }
                                }
                            });
                            targetElementCard.fadeOut(600, function() {
                                $(this).remove();
                                // Recalculate and update total count for cards if visible
                                if($('#buses-card-body').is(':visible')) {
                                    const currentCardRows = $('#buses-card-body .col:visible').length;
                                    $('.card-header .badge').text(currentCardRows + ' Total');
                                    if(currentCardRows === 0) {
                                        $('#buses-card-body').html('<div class="alert alert-info text-center py-4 mb-0" role="alert"><i class="fas fa-bus fa-2x mb-2"></i><p class="mb-0">No buses found. Please add a new bus.</p></div>');
                                    }
                                }
                            });

                            // Update total count badge
                            let currentTotal = parseInt($('.card-header .badge').text().split(' ')[0]);
                            if (!isNaN(currentTotal) && currentTotal > 0) {
                                $('.card-header .badge').text((currentTotal - 1) + ' Total');
                            }
                        } else {
                            Swal.fire('Error!', response.notif_desc, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error!', 'Could not connect to the server.', 'error');
                    }
                });
            }
        });
    });
});
</script>

</body>
</html>
<?php pdo_close_conn($_conn_db); ?>