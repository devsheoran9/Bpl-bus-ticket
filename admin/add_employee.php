<?php
// add_employee.php

include_once('function/_db.php');
session_security_check();  
check_permission('can_manage_employees'); // पृष्ठ-विशिष्ट अनुमति

// मौजूदा कर्मचारियों को उनकी अंतिम लॉगिन जानकारी के साथ प्राप्त करें
try {
    $stmt = $_conn_db->prepare("SELECT id, name, mobile, email, status, last_login_time, last_login_ip, session_token FROM admin WHERE type = 'employee' ORDER BY id DESC");
    $stmt->execute();
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $employees = [];
    error_log("Failed to fetch employees: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "head.php"; ?>
    <title>Manage Employees</title>
    <style>
        body { background-color: #f8f9fa; }
        .form-card { border-top: 4px solid #0d6efd; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .employee-card {
            background-color: #fff; border: 1px solid #e9ecef; border-radius: 8px;
            padding: 1.25rem; margin-bottom: 1rem;
            display: flex; flex-wrap: wrap; align-items: center; gap: 1rem;
        }
        .employee-info { flex-grow: 1; }
        .employee-info h5 { margin-bottom: 0.25rem; }
        .employee-info p { margin-bottom: 0; color: #6c757d; font-size: 0.9em; }
        .employee-actions { display: flex; align-items: center; gap: 0.5rem; } /* Reduced gap */
        .form-check-switch { font-size: 1.25rem; }
        .online-indicator { color: #28a745; font-size: 0.8em; font-weight: bold; }
        .last-login-info { font-size: 0.8em; color: #6c757d; }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include_once('sidebar.php'); ?>
    <div class="main-content">
        <?php include_once('header.php'); ?>
        <div class="container-fluid">
            <h2 class="my-4">Add & Manage Employees</h2>
            <div class="row">
                <!-- Add Employee Form Column -->
                <div class="col-lg-4 mb-4">
                    <div class="card form-card">
                        <div class="card-header bg-white"><h5>Add New Employee</h5></div>
                        <div class="card-body">
                            <form id="add-employee-form" class="data-form" data-parsley-validate>
                                <input type="hidden" name="action" value="add_employee">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="mobile" class="form-label">Mobile Number</label>
                                    <input type="tel" class="form-control" id="mobile" name="mobile" required data-parsley-type="digits" data-parsley-length="[10, 10]">
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required data-parsley-minlength="6">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Assign Permissions</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="can_manage_operators" id="perm_operators">
                                        <label class="form-check-label" for="perm_operators">Manage Operators</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="can_manage_buses" id="perm_buses">
                                        <label class="form-check-label" for="perm_buses">Manage Buses</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="can_manage_routes" id="perm_routes">
                                        <label class="form-check-label" for="perm_routes">Manage Routes</label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Create Employee Account</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Existing Employees List Column -->
                <div class="col-lg-8">
                    <div class="employee-list-container">
                        <?php if (empty($employees)): ?>
                            <div class="alert alert-info">No employee accounts found.</div>
                        <?php else: ?>
                            <?php foreach ($employees as $emp): ?>
                                <div class="employee-card" id="employee-<?php echo $emp['id']; ?>">
                                    <div class="employee-info">
                                        <h5>
                                            <?php echo htmlspecialchars($emp['name']); ?>
                                            <?php if (!empty($emp['session_token'])): ?>
                                                <span class="online-indicator ms-2">(Online)</span>
                                            <?php endif; ?>
                                        </h5>
                                        <p>
                                            <i class="fas fa-mobile-alt me-1"></i> <?php echo htmlspecialchars($emp['mobile']); ?> |
                                            <i class="fas fa-envelope me-1 ms-2"></i> <?php echo htmlspecialchars($emp['email']); ?>
                                        </p>
                                        <p class="last-login-info mt-1">
                                            Last Login: 
                                            <?php echo $emp['last_login_time'] ? date('M j, Y g:i A', strtotime($emp['last_login_time'])) . ' from ' . htmlspecialchars($emp['last_login_ip']) : 'Never'; ?>
                                        </p>
                                    </div>
                                    <div class="employee-actions">
                                        <button class="btn btn-sm btn-outline-secondary history-btn" title="Login History" data-employee-id="<?php echo $emp['id']; ?>">
                                            <i class="fas fa-history"></i>
                                        </button>
                                        <div class="form-check form-switch form-check-switch" title="Toggle Active/Inactive">
                                            <input class="form-check-input status-toggle" type="checkbox" role="switch" 
                                                   data-employee-id="<?php echo $emp['id']; ?>" <?php echo $emp['status'] == '1' ? 'checked' : ''; ?>>
                                        </div>
                                        <?php if (!empty($emp['session_token'])): ?>
                                        <button class="btn btn-sm btn-outline-warning force-logout-btn" title="Force Logout" data-employee-id="<?php echo $emp['id']; ?>">
                                            <i class="fas fa-power-off"></i>
                                        </button>
                                        <?php endif; ?>
                                        <button class="btn btn-sm btn-outline-danger delete-employee-btn" title="Delete Employee" data-employee-id="<?php echo $emp['id']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Login History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Login History</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="historyModalBody"></div>
    </div>
  </div>
</div>

<?php include "foot.php"; ?>
<script>
$(document).ready(function() {
    $('form.data-form').parsley();
    const backendUrl = 'function/backend/employee_actions.php';

    // Add Employee
    $('#add-employee-form').on('submit', function(e) {
        e.preventDefault();
        if (!$(this).parsley().isValid()) return;
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');
        $.ajax({
            url: backendUrl, type: 'POST', data: form.serialize(), dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $.notify({ message: response.message }, { type: 'success' });
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    $.notify({ message: response.message }, { type: 'danger' });
                }
            },
            error: () => $.notify({ message: 'A server error occurred.' }, { type: 'warning' }),
            complete: () => submitBtn.prop('disabled', false).html('Create Employee Account')
        });
    });

    // Toggle Status
    $(document).on('change', '.status-toggle', function() {
        const checkbox = $(this);
        $.ajax({
            url: backendUrl, type: 'POST',
            data: { action: 'toggle_status', employee_id: checkbox.data('employee-id'), new_status: checkbox.is(':checked') ? 1 : 2 },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $.notify({ message: response.message }, { type: 'success' });
                } else {
                    $.notify({ message: response.message }, { type: 'danger' });
                    checkbox.prop('checked', !checkbox.prop('checked'));
                }
            },
            error: () => {
                $.notify({ message: 'A server error occurred.' }, { type: 'warning' });
                checkbox.prop('checked', !checkbox.prop('checked'));
            }
        });
    });

    // Delete Employee
    $(document).on('click', '.delete-employee-btn', function() {
        const employeeId = $(this).data('employee-id');
        Swal.fire({
            title: 'Are you sure?', text: "This action cannot be undone!", icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: backendUrl, type: 'POST',
                    data: { action: 'delete_employee', employee_id: employeeId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#employee-' + employeeId).fadeOut(500, function() { $(this).remove(); });
                            Swal.fire('Deleted!', response.message, 'success');
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    }
                });
            }
        });
    });

    // Force Logout
    $(document).on('click', '.force-logout-btn', function() {
        const employeeId = $(this).data('employee-id');
        Swal.fire({
            title: 'Force Logout?', text: 'This will immediately terminate the user\'s session.', icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#ffc107', confirmButtonText: 'Yes, terminate session!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: backendUrl, type: 'POST',
                    data: { action: 'force_logout', employee_id: employeeId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire('Success!', response.message, 'success').then(() => window.location.reload());
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    }
                });
            }
        });
    });
    
    // Login History
    const historyModal = new bootstrap.Modal(document.getElementById('historyModal'));
    $(document).on('click', '.history-btn', function() {
        const employeeId = $(this).data('employee-id');
        const modalBody = $('#historyModalBody');
        modalBody.html('<div class="text-center p-4"><div class="spinner-border"></div></div>');
        historyModal.show();
        $.ajax({
            url: backendUrl, type: 'GET',
            data: { action: 'get_login_history', employee_id: employeeId },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    let content = '<ul class="list-group list-group-flush">';
                    if(response.history.length > 0) {
                        response.history.forEach(log => {
                            let activityClass = log.activity_type === 'login' ? 'text-success' : 'text-danger';
                            let formattedDate = new Date(log.log_time).toLocaleString();
                            content += `<li class="list-group-item d-flex justify-content-between align-items-center">
                                <div><strong class="${activityClass}">${log.activity_type.toUpperCase()}</strong><br><small class="text-muted">IP: ${log.ip_address}</small></div>
                                <small>${formattedDate}</small>
                            </li>`;
                        });
                    } else {
                        content += '<li class="list-group-item">No history found.</li>';
                    }
                    modalBody.html(content + '</ul>');
                } else {
                    modalBody.html(`<div class="alert alert-danger">${response.message}</div>`);
                }
            }
        });
    });
});
</script>
</body>
</html>