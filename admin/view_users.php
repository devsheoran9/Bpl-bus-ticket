<?php
// view_users.php
include_once('function/_db.php');
session_security_check();
check_permission('main_admin'); // Only main admin can view and manage users

$users = [];
try {
    // A comprehensive query to get all user details including their last login info
    $stmt = $_conn_db->query("
        SELECT 
            u.id, u.username, u.mobile_no, u.email, u.status, u.created_at,
            lt.last_login_time, lt.last_login_ip
        FROM users u
        LEFT JOIN (
            SELECT 
                user_id, 
                MAX(date_time) AS last_login_time,
                SUBSTRING_INDEX(GROUP_CONCAT(ip_address ORDER BY date_time DESC), ',', 1) AS last_login_ip
            FROM users_login_token
            GROUP BY user_id
        ) AS lt ON u.id = lt.user_id
        ORDER BY u.id DESC
    ");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching users: " . $e->getMessage());
    $_SESSION['notif_type'] = 'error';
    $_SESSION['notif_title'] = 'Database Error';
    $_SESSION['notif_desc'] = 'Could not fetch user data.';
    $users = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "head.php"; ?>
    <title>Manage Registered Users</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- DataTables CSS with Responsive Extension -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs5/dt-1.13.6/b-2.4.1/b-html5-2.4.1/b-print-2.4.1/r-2.5.0/datatables.min.css"/>

    <style>
        body { background-color: #f8f9fa; font-family: 'Inter', sans-serif; }
        #wrapper{
            display: block;
        }
        .panel-card {
            background: #fff; border-radius: 12px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.05);
            border: 1px solid #e9ecef; margin-bottom: 1.5rem;
            border-top: 5px solid #0d6efd;
        }
        .panel-card .card-header {
            background-color: transparent; border-bottom: 1px solid #e9ecef;
            color: #0d6efd; font-weight: 700; font-size: 1.2rem;
        }
        /* .dataTables_wrapper { padding: 1.5rem; } */
        
        /* Table Styles */
        table.dataTable { border-collapse: collapse !important; }
        .users-table-header th { font-size: 0.8rem; text-transform: uppercase; }
        .users-table-body td { font-size: 0.9rem; vertical-align: middle; }
        .user-info { display: flex; align-items: center; gap: 0.75rem; }
        .user-avatar {
            width: 40px; height: 40px; border-radius: 50%;
            background-color: #0d6efd; color: white;
            display: inline-flex; align-items: center; justify-content: center;
            font-weight: 600; font-size: 1rem; flex-shrink: 0;
        }
        .user-name { font-weight: 600; color: #212529; }
        .user-id { font-size: 0.8em; color: #6c757d; }
        .contact-info { font-size: 0.85em; color: #6c757d; }
        .status-badge { font-size: 0.75rem; font-weight: 600; padding: 0.3em 0.6em; border-radius: 12px; }
        .actions-cell { text-align: right; white-space: nowrap; }
        .actions-cell .btn { margin-left: 0.25rem; }

        /* Modal Styles */
        #userDetailsModal .modal-header { background-color: #0d6efd; color: white; }
        #userDetailsModal .modal-title { font-weight: 600; }
        #userDetailsModal .stat-card {
            background-color: #f8f9fa; border-radius: 8px; padding: 1rem;
            text-align: center; border: 1px solid #e9ecef;
        }
        #userDetailsModal .stat-label { font-size: 0.8rem; color: #6c757d; font-weight: 500; }
        #userDetailsModal .stat-value { font-size: 1.5rem; font-weight: 700; color: #212529; }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include_once('sidebar.php'); ?>
    <div class="main-content">
        <?php include_once('header.php'); ?>
        <div class="container-fluid">
            <h2 class="my-4">Manage Registered Users</h2>
            
            <div class="card panel-card">
                <div class="card-header"><i class="fas fa-users me-2"></i>All Customer Accounts</div>
                <div class="card-body">
                    <table class="table table-hover dt-responsive nowrap" id="users-table" style="width:100%">
                        <thead class="users-table-header">
                            <tr>
                                <th>User Info</th>
                                <th>Contact</th>
                                <th>Status</th>
                                <th>Registered On</th>
                                <th>Last Login</th>
                                <th class="no-export">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="users-table-body">
                            <?php foreach ($users as $user): ?>
                                <tr id="user-row-<?php echo htmlspecialchars($user['id']); ?>">
                                    <td>
                                        <div class="user-info">
                                            <div class="user-avatar"><?php echo htmlspecialchars(mb_substr($user['username'], 0, 1)); ?></div>
                                            <div>
                                                <div class="user-name"><?php echo htmlspecialchars($user['username']); ?></div>
                                                <div class="user-id">ID: <?php echo htmlspecialchars($user['id']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div><?php echo htmlspecialchars($user['email']); ?></div>
                                        <div class="contact-info"><i class="fas fa-mobile-alt fa-xs"></i> <?php echo htmlspecialchars($user['mobile_no']); ?></div>
                                    </td>
                                    <td>
                                        <span class="badge status-badge <?php echo $user['status'] == 1 ? 'bg-success-subtle text-success-emphasis' : 'bg-danger-subtle text-danger-emphasis'; ?>">
                                            <?php echo $user['status'] == 1 ? 'Active' : 'Deactivated'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d M, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <?php if ($user['last_login_time']): ?>
                                            <div><?php echo date('d M Y, h:i A', strtotime($user['last_login_time'])); ?></div>
                                            <div class="contact-info">IP: <?php echo htmlspecialchars($user['last_login_ip']); ?></div>
                                        <?php else: ?>
                                            <span class="text-muted">Never</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="actions-cell">
                                        <button class="btn btn-sm btn-outline-info view-details-btn" title="View User Details" 
                                                data-user-id="<?php echo htmlspecialchars($user['id']); ?>" 
                                                data-user-name="<?php echo htmlspecialchars($user['username']); ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <div class="form-check form-switch form-check-lg d-inline-block" title="Toggle Active/Inactive">
                                            <input class="form-check-input toggle-status-btn" type="checkbox" role="switch" 
                                                   data-user-id="<?php echo htmlspecialchars($user['id']); ?>" 
                                                   <?php echo $user['status'] == 1 ? 'checked' : ''; ?>>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- User Details Modal -->
<div class="modal fade" id="userDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userDetailsModalLabel">User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="userDetailsModalContent">
                    <div class="text-center p-5"><div class="spinner-border text-primary"></div></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "foot.php"; ?>
<!-- DataTables JS with Responsive Extension -->
<script type="text/javascript" src="https://cdn.datatables.net/v/bs5/dt-1.13.6/b-2.4.1/b-html5-2.4.1/b-print-2.4.1/r-2.5.0/datatables.min.js"></script>

<script>
$(document).ready(function() {
    let usersTable = $('#users-table').DataTable({
        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        responsive: true,
        order: [[3, 'desc']], // Default sort by "Registered On" date
        columnDefs: [
            { responsivePriority: 1, targets: 0 }, // User Info (Most Important)
            { responsivePriority: 2, targets: 5 }, // Actions (Second)
            { responsivePriority: 3, targets: 2 }, // Status (Third)
            { responsivePriority: 10001, targets: 1 }, // Contact (Hide first on small screens)
            { responsivePriority: 10002, targets: 4 }  // Last Login (Hide next)
        ]
    });

    // Event delegation for status toggle
    $('#users-table tbody').on('change', '.toggle-status-btn', function() {
        const checkbox = $(this);
        const userId = checkbox.data('user-id');
        const newStatus = checkbox.is(':checked') ? 1 : 2;
        const actionText = newStatus === 1 ? 'activate' : 'deactivate';

        Swal.fire({
            title: `Confirm ${actionText}`,
            text: `Are you sure you want to ${actionText} this user's account?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: newStatus === 1 ? '#198754' : '#dc3545',
            confirmButtonText: `Yes, ${actionText} it!`
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'function/backend/user_actions.php', type: 'POST',
                    data: { action: 'toggle_user_status', user_id: userId, new_status: newStatus },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire('Success!', response.message, 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                            checkbox.prop('checked', !checkbox.prop('checked'));
                        }
                    },
                    error: () => {
                        Swal.fire('Error!', 'Could not connect to the server.', 'error');
                        checkbox.prop('checked', !checkbox.prop('checked'));
                    }
                });
            } else {
                checkbox.prop('checked', !checkbox.prop('checked'));
            }
        });
    });

    // Event delegation for details button
    const userDetailsModal = new bootstrap.Modal(document.getElementById('userDetailsModal'));
    $('#users-table tbody').on('click', '.view-details-btn', function() {
        const userId = $(this).data('user-id');
        const userName = $(this).data('user-name');
        
        $('#userDetailsModalLabel').text(`Details for ${userName}`);
        $('#userDetailsModalContent').html('<div class="text-center p-5"><div class="spinner-border text-primary"></div></div>');
        userDetailsModal.show();

        $.ajax({
            url: 'function/backend/user_actions.php', type: 'GET',
            data: { action: 'get_user_details', user_id: userId },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success' && response.data.details) {
                    const details = response.data.details;
                    let recentBookingsHtml = '<h6 class="mt-4">Recent Bookings (Last 5)</h6>';
                    if (details.recent_bookings.length > 0) {
                        recentBookingsHtml += '<ul class="list-group list-group-flush">';
                        details.recent_bookings.forEach(booking => {
                            recentBookingsHtml += `<li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>${htmlspecialchars(booking.route_name)} <small class="text-muted d-block">#${htmlspecialchars(booking.ticket_no)} on ${new Date(booking.travel_date).toLocaleDateString()}</small></div>
                                <span class="badge bg-success rounded-pill">₹${parseFloat(booking.total_fare).toFixed(2)}</span>
                            </li>`;
                        });
                        recentBookingsHtml += '</ul>';
                    } else {
                        recentBookingsHtml += '<p class="text-muted">No recent bookings found.</p>';
                    }

                    const modalContent = `
                        <div class="row g-3 mb-3">
                            <div class="col-md-4"><div class="stat-card"><div class="stat-label">Total Bookings</div><div class="stat-value">${details.stats.total_bookings}</div></div></div>
                            <div class="col-md-4"><div class="stat-card"><div class="stat-label">Total Spent</div><div class="stat-value">₹${parseFloat(details.stats.total_spent).toLocaleString('en-IN')}</div></div></div>
                            <div class="col-md-4"><div class="stat-card"><div class="stat-label">Last Travel Date</div><div class="stat-value" style="font-size: 1.2rem;">${details.stats.last_travel_date ? new Date(details.stats.last_travel_date).toLocaleDateString() : 'N/A'}</div></div></div>
                        </div>
                        ${recentBookingsHtml}
                    `;
                    $('#userDetailsModalContent').html(modalContent);
                } else {
                    $('#userDetailsModalContent').html(`<div class="alert alert-danger">${response.message}</div>`);
                }
            },
            error: () => {
                $('#userDetailsModalContent').html('<div class="alert alert-danger">Failed to load user details.</div>');
            }
        });
    });

    function htmlspecialchars(str) {
        if (typeof str !== 'string') return '';
        var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return str.replace(/[&<>"']/g, m => map[m]);
    }
});
</script>
</body>
</html>