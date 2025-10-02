<?php
// add_employee.php (Full Add & Edit Functionality with Staff Linking & Detailed History)
include_once('function/_db.php');
session_security_check();
check_permission('can_manage_employees'); // Page-specific permission

$employee_to_edit = null;
$edit_mode = false;
$employee_permissions = [];

// --- ACTION HANDLER (to populate the form for editing) ---
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $edit_employee_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($edit_employee_id) {
        try {
            $stmt = $_conn_db->prepare("SELECT * FROM admin WHERE id = ? AND type = 'employee'");
            $stmt->execute([$edit_employee_id]);
            $employee_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($employee_to_edit) {
                $edit_mode = true;
                $employee_permissions = json_decode($employee_to_edit['permissions'], true) ?: [];
            }
        } catch (PDOException $e) {
            $_SESSION['notif_type'] = 'error';
            $_SESSION['notif_title'] = 'Error';
            $_SESSION['notif_desc'] = 'Could not fetch employee details for editing.';
        }
    }
}

// --- DATA FETCHING for form and list ---
$all_staff = [];
try {
    $staff_stmt = $_conn_db->query("SELECT staff_id, name, mobile FROM staff WHERE status = 'Active' ORDER BY name ASC");
    $all_staff = $staff_stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $_conn_db->prepare("SELECT id, name, mobile, email, status, last_login_time, last_login_ip, session_token, linked_staff_id FROM admin WHERE type = 'employee' ORDER BY id DESC");
    $stmt->execute();
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $employees = [];
    $all_staff = [];
    error_log("Failed to fetch data for add_employee page: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include "head.php"; ?>
    <title><?php echo $edit_mode ? 'Edit' : 'Add'; ?> Employee</title>
    <!-- Fancybox CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css" />
    <style>
        body {
            background-color: #f8f9fa;
        }

        .form-card {
            border-top: 4px solid <?php echo $edit_mode ? '#ffc107' : '#0d6efd'; ?>;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .search-wrapper {
            position: relative;
        }

        .search-wrapper .form-control {
            padding-left: 2.5rem;
        }

        .search-wrapper .fa-search {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }

        .employees-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .employee-card {
            background-color: #fff;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            transition: box-shadow 0.2s ease;
        }

        .employee-card:hover {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .employee-info {
            flex-grow: 1;
            min-width: 200px;
        }

        .employee-info h5 {
            margin-bottom: 0.25rem;
            font-size: 1.1rem;
        }

        .employee-info p {
            margin-bottom: 0;
            color: #6c757d;
            font-size: 0.9em;
        }

        .employee-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-check-switch {
            font-size: 1.25rem;
        }

        .online-indicator {
            color: #28a745;
            font-size: 0.8em;
            font-weight: bold;
        }

        .last-login-info {
            font-size: 0.8em;
            color: #6c757d;
        }

        #no-results-message {
            display: none;
        }

        .permissions-section h6 {
            font-size: 1rem;
            color: #0d6efd;
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 0.5rem;
            margin-top: 1rem;
            margin-bottom: 0.75rem;
        }

        .permissions-section .form-check {
            margin-bottom: 0.25rem;
        }

        /* Styles for the history modal */
        .history-item {
            border-bottom: 1px solid #f0f0f0;
            padding: 1rem 0;
        }

        .history-item:last-child {
            border-bottom: none;
        }

        .history-details {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem 1.5rem;
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }

        .status-login {
            color: #198754;
            font-weight: bold;
        }

        .status-failed_attempt {
            color: #dc3545;
            font-weight: bold;
        }

        .status-deactivated_attempt {
            color: #664d03;
            font-weight: bold;
            background-color: #fff3cd;
            padding: 0.1rem 0.4rem;
            border-radius: 4px;
        }

        .status-logout {
            color: #6c757d;
        }

        .fancybox__container {
            z-index: 1060;
            /* Bootstrap Modals are usually at z-index 1050-1055 */
        }
    </style>
</head>

<body>
    <div id="wrapper">
        <?php include_once('sidebar.php'); ?>
        <div class="main-content">
            <?php include_once('header.php'); ?>
            <div class="container-fluid">
                <h2 class="my-4"><?php echo $edit_mode ? 'Edit Employee' : 'Add & Manage Employees'; ?></h2>
                <div class="row">
                    <div class="col-lg-4 mb-4">
                        <div class="card form-card">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5><?php echo $edit_mode ? 'Edit Employee Details' : 'Add New Employee'; ?></h5>
                                <?php if ($edit_mode): ?>
                                    <a href="add_employee.php" class="btn btn-sm btn-outline-secondary">Cancel Edit</a>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <form id="employee-form" action="function/backend/employee_actions.php" method="POST" data-parsley-validate>
                                    <input type="hidden" name="action" value="<?php echo $edit_mode ? 'update_employee' : 'add_employee'; ?>">
                                    <?php if ($edit_mode): ?>
                                        <input type="hidden" name="employee_id" value="<?php echo $employee_to_edit['id']; ?>">
                                    <?php endif; ?>
                                    <div class="row g-3">
                                        <div class="mb-3 col-12">
                                            <label for="linked_staff_id" class="form-label">Link to Staff Member <small>(Optional)</small></label>
                                            <select class="form-select" id="linked_staff_id" name="linked_staff_id">
                                                <option value="">-- No Link / Create New --</option>
                                                <?php foreach ($all_staff as $staff): ?>
                                                    <option value="<?php echo $staff['staff_id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($staff['name']); ?>"
                                                        data-mobile="<?php echo htmlspecialchars($staff['mobile']); ?>"
                                                        <?php if ($edit_mode && isset($employee_to_edit['linked_staff_id']) && $employee_to_edit['linked_staff_id'] == $staff['staff_id']) echo 'selected'; ?>>
                                                        <?php echo htmlspecialchars($staff['name']); ?> (ID: <?php echo $staff['staff_id']; ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="form-text">Select a staff member to auto-fill their details below.</div>
                                        </div>
                                        <div class="mb-3 col-12 col-md-6"><label for="name" class="form-label">Full Name</label><input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($employee_to_edit['name'] ?? ''); ?>" required></div>
                                        <div class="mb-3 col-12 col-md-6"><label for="mobile" class="form-label">Mobile Number</label><input type="tel" class="form-control" id="mobile" name="mobile" value="<?php echo htmlspecialchars($employee_to_edit['mobile'] ?? ''); ?>" required data-parsley-type="digits" data-parsley-length="[10, 10]"></div>
                                        <div class="mb-3 col-12"><label for="email" class="form-label">Email Address</label><input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($employee_to_edit['email'] ?? ''); ?>" required></div>
                                        <div class="mb-3 col-12">
                                            <label for="password" class="form-label">Password</label>
                                            <input type="password" class="form-control" id="password" name="password" <?php echo !$edit_mode ? 'required' : ''; ?> data-parsley-minlength="6" placeholder="<?php echo $edit_mode ? 'Leave blank to keep unchanged' : ''; ?>">
                                        </div>
                                        <div class="mb-3 col-12 permissions-section">
                                            <label class="form-label fw-bold">Assign Permissions</label>
                                            <div class="row g-2">
                                                <?php
                                                $permissions_list = [
                                                    'Booking & Cancellations' => ['can_book_tickets' => 'Can Book Tickets', 'can_view_bookings' => 'View Daily Bookings', 'can_delete_bookings' => 'Can Delete Bookings', 'can_manage_cancellations' => 'Process Cancellations'],

                                                    'Operations Management' => ['can_manage_routes' => 'Manage Routes (Add/Edit)', 'can_delete_routes' => 'Delete Routes', 'can_charter_bus' => 'Book Full Bus Charter', 'can_toggle_popular_route' => 'Toggle Popular Route Status', 'can_manage_buses' => 'Manage Buses (Add/View)', 'can_edit_buses' => 'Edit Buses', 'can_delete_buses' => 'Delete Buses', 'can_manage_seats' => 'Manage Bus Seats', 'can_manage_staff' => 'Manage Staff (Add/Edit/Delete)', 'can_manage_reviews' => 'Manage Reviews'],

                                                    'Reports' => ['can_view_own_collections' => 'View Own Cash Report', 'can_view_reports' => 'View Full Bookings Report', 'can_manage_enqury' => 'Inquries For Bus Charter'],

                                                    'System Admin' => ['can_manage_employees' => 'Manage Other Employees', 'can_manage_settings' => 'Manage Company Settings', 'main_admin' => 'Is Main Administrator (Full Access)']
                                                ];
                                                foreach ($permissions_list as $group => $perms): ?>
                                                    <div class="col-12 col-sm-6">
                                                        <h6><?php echo $group; ?></h6>
                                                        <?php foreach ($perms as $key => $label): ?>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="permissions[]" value="<?php echo $key; ?>" id="perm_<?php echo $key; ?>" <?php echo isset($employee_permissions[$key]) && $employee_permissions[$key] === true ? 'checked' : ''; ?>>
                                                                <label class="form-check-label" for="perm_<?php echo $key; ?>"><?php echo $label; ?></label>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn <?php echo $edit_mode ? 'btn-warning' : 'btn-primary'; ?> w-100 mt-3"><?php echo $edit_mode ? 'Update Employee' : 'Create Employee'; ?></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5>Existing Employees</h5>
                                <div class="search-wrapper"><i class="fas fa-search"></i><input type="text" id="employee-search-input" class="form-control form-control-sm" placeholder="Search employees..."></div>
                            </div>
                            <div class="card-body">
                                <div class="employees-grid">
                                    <?php if (empty($employees)): ?>
                                        <div class="alert alert-info w-100">No employee accounts found.</div>
                                    <?php else: ?>
                                        <?php foreach ($employees as $emp): ?>
                                            <div class="employee-card" id="employee-<?php echo $emp['id']; ?>">
                                                <div class="employee-info">
                                                    <h5><?php echo htmlspecialchars($emp['name']); ?><?php if (!empty($emp['session_token'])): ?><span class="online-indicator ms-2">(<i class="fas fa-circle"></i> Online)</span><?php endif; ?></h5>
                                                    <p><i class="fas fa-mobile-alt me-1"></i> <?php echo htmlspecialchars($emp['mobile']); ?> | <i class="fas fa-envelope me-1 ms-2"></i> <?php echo htmlspecialchars($emp['email']); ?></p>
                                                    <p class="last-login-info mt-1">Last Login: <?php echo $emp['last_login_time'] ? date('M j, Y g:i A', strtotime($emp['last_login_time'])) . ' from ' . htmlspecialchars($emp['last_login_ip']) : 'Never'; ?></p>
                                                </div>
                                                <div class="employee-actions">
                                                    <a href="?action=edit&id=<?php echo $emp['id']; ?>" class="btn btn-sm btn-outline-info" title="Edit Employee"><i class="fas fa-edit"></i></a>
                                                    <button class="btn btn-sm btn-outline-secondary history-btn" title="Login History" data-employee-id="<?php echo $emp['id']; ?>"><i class="fas fa-history"></i></button>
                                                    <div class="form-check form-switch form-check-switch" title="Toggle Active/Inactive"><input class="form-check-input status-toggle" type="checkbox" role="switch" data-employee-id="<?php echo $emp['id']; ?>" <?php echo $emp['status'] == '1' ? 'checked' : ''; ?>></div>
                                                    <?php if (!empty($emp['session_token'])): ?>
                                                        <button class="btn btn-sm btn-outline-warning force-logout-btn" title="Force Logout" data-employee-id="<?php echo $emp['id']; ?>"><i class="fas fa-power-off"></i></button>
                                                    <?php endif; ?>
                                                    <button class="btn btn-sm btn-outline-danger delete-employee-btn" title="Delete Employee" data-employee-id="<?php echo $emp['id']; ?>"><i class="fas fa-trash"></i></button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                        <div id="no-results-message" class="alert alert-warning w-100" style="display:none;">No employees match your search.</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Detailed Login History -->
    <div class="modal fade" id="historyModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detailed Login History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="historyModalBody"></div>
            </div>
        </div>
    </div>

    <?php include "foot.php"; ?>
    <!-- Fancybox JS -->
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
    <script>
        $(document).ready(function() {
            const backendUrl = 'function/backend/employee_actions.php';

            $('#employee-form').on('submit', function(e) {
                e.preventDefault();
                if (typeof $(this).parsley === 'function' && !$(this).parsley().isValid()) return;

                const form = $(this);
                const submitBtn = form.find('button[type="submit"]');
                const originalBtnText = submitBtn.html();
                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                title: 'Success!',
                                text: response.message,
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = 'add_employee.php';
                            });
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: () => Swal.fire('Error!', 'A server error occurred. Please try again.', 'error'),
                    complete: () => submitBtn.prop('disabled', false).html(originalBtnText)
                });
            });

            $('#linked_staff_id').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                if ($(this).val()) {
                    $('#name').val(selectedOption.data('name'));
                    $('#mobile').val(selectedOption.data('mobile'));
                } else {
                    $('#name').val('');
                    $('#mobile').val('');
                }
                if (typeof $('#employee-form').parsley === 'function') {
                    $('#employee-form').parsley().validate();
                }
            });

            $('#employee-search-input').on('keyup', function() {
                const searchTerm = $(this).val().toLowerCase();
                let visibleCount = 0;
                $('.employee-card').each(function() {
                    const cardText = $(this).text().toLowerCase();
                    if (cardText.includes(searchTerm)) {
                        $(this).show();
                        visibleCount++;
                    } else {
                        $(this).hide();
                    }
                });
                $('#no-results-message').toggle(visibleCount === 0 && $('.employee-card').length > 0);
            });

            $(document).on('change', '.status-toggle', function() {
                const checkbox = $(this);
                $.post(backendUrl, {
                        action: 'toggle_status',
                        employee_id: checkbox.data('employee-id'),
                        new_status: checkbox.is(':checked') ? 1 : 2
                    }, 'json')
                    .done(response => {
                        if (response.status === 'success') {
                            Swal.fire({
                                title: 'Success',
                                text: response.message,
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                            checkbox.prop('checked', !checkbox.prop('checked'));
                        }
                    }).fail(() => {
                        Swal.fire('Error', 'A server error occurred.', 'error');
                        checkbox.prop('checked', !checkbox.prop('checked'));
                    });
            });

            $(document).on('click', '.delete-employee-btn', function() {
                const employeeId = $(this).data('employee-id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This action cannot be undone!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post(backendUrl, {
                                action: 'delete_employee',
                                employee_id: employeeId
                            }, 'json')
                            .done(response => {
                                if (response.status === 'success') {
                                    $('#employee-' + employeeId).fadeOut(500, () => $(this).remove());
                                    Swal.fire('Deleted!', response.message, 'success');
                                } else {
                                    Swal.fire('Error!', response.message, 'error');
                                }
                            }).fail(() => Swal.fire('Error!', 'Could not connect to server.', 'error'));
                    }
                });
            });

            $(document).on('click', '.force-logout-btn', function() {
                const employeeId = $(this).data('employee-id');
                Swal.fire({
                    title: 'Force Logout?',
                    text: 'This will immediately terminate the user\'s session.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ffc107',
                    confirmButtonText: 'Yes, terminate session!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post(backendUrl, {
                                action: 'force_logout',
                                employee_id: employeeId
                            }, 'json')
                            .done(response => {
                                if (response.status === 'success') {
                                    Swal.fire('Success!', response.message, 'success').then(() => window.location.reload());
                                } else {
                                    Swal.fire('Error!', response.message, 'error');
                                }
                            }).fail(() => Swal.fire('Error!', 'Could not connect to server.', 'error'));
                    }
                });
            });

            // --- NEW, ENHANCED HISTORY MODAL LOGIC ---
            const historyModal = new bootstrap.Modal(document.getElementById('historyModal'));
            $(document).on('click', '.history-btn', function() {
                const employeeId = $(this).data('employee-id');
                const modalBody = $('#historyModalBody');
                modalBody.html('<div class="text-center p-4"><div class="spinner-border"></div><p class="mt-2">Loading History...</p></div>');
                historyModal.show();

                $.ajax({
                    url: backendUrl, // The backendUrl variable is already defined
                    type: 'GET',
                    data: {
                        action: 'get_login_history',
                        employee_id: employeeId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success' && response.data.history && response.data.history.length > 0) {
                            let content = '';
                            response.data.history.forEach(log => {
                                let activityClass = log.activity_type.includes('fail') ? 'text-danger' : (log.activity_type === 'login' ? 'text-success' : 'text-warning');
                                let formattedDate = new Date(log.log_time).toLocaleString('en-GB');

                                let locationHtml = `<span class="text-muted">N/A</span>`;
                                if (log.geo_lat && log.geo_long) {
                                    locationHtml = `<a href="https://www.google.com/maps?q=${log.geo_lat},${log.geo_long}" target="_blank" class="text-decoration-none">
                                                    <i class="fas fa-map-marker-alt text-danger me-1"></i> View on Map
                                                </a>`;
                                }

                                let imageHtml = `<span class="text-muted">No Image</span>`;
                                if (log.captured_image) {
                                    imageHtml = `<a data-fancybox data-src="uploads/login_captures/${log.captured_image}" class="text-decoration-none fw-bold">
                                                <i class="fas fa-camera me-1"></i> View Capture
                                             </a>`;
                                }

                                content += `
                                <div class="history-item">
                                    <div class="d-flex justify-content-between align-items-start flex-wrap">
                                        <div>
                                            <h6 class="mb-1">
                                                <strong class="status-${log.activity_type}">${log.activity_type.replace(/_/g, ' ').toUpperCase()}</strong> 
                                                <span class="text-muted fw-normal">from ${log.ip_address}</span>
                                            </h6>
                                            <div class="history-details">
                                                <span><i class="fas fa-map-pin me-1 opacity-75"></i> ${locationHtml}</span>
                                                
                                                <span><i class="fas fa-desktop me-1 opacity-75"></i> ${log.device_type} / ${log.os}</span>
                                                <span><i class="fab fa-chrome me-1 opacity-75"></i> ${log.browser}</span>
                                            </div>
                                        </div>
                                        <small class="text-muted text-nowrap mt-2 mt-sm-0">${formattedDate}</small>
                                    </div>
                                </div>
                            `;
                            });
                            modalBody.html(content);
                            Fancybox.bind("[data-fancybox]", {}); // Re-initialize Fancybox for new content

                        } else {
                            modalBody.html('<div class="alert alert-info">No detailed login history found for this employee.</div>');
                        }
                    },
                    error: () => {
                        modalBody.html('<div class="alert alert-danger">Failed to load history. Please try again.</div>');
                    }
                });
            });
        });
    </script>
</body>

</html>