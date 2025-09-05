<?php
// add_employee.php

include_once('function/_db.php');
// Secure this page: only users who can manage employees should see it.
check_permission('can_manage_employees');

// Fetch existing employees to display in the list
try {
    $stmt = $_conn_db->prepare("SELECT id, name, mobile, email, status FROM admin WHERE type = 'employee' ORDER BY id DESC");
    $stmt->execute();
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // On error, create an empty array to avoid frontend errors
    $employees = [];
    // Log the error for debugging
    error_log("Failed to fetch employees: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "head.php"; // Includes meta tags, CSS, etc. ?>
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
        .employee-actions { display: flex; align-items: center; gap: 0.75rem; }
        .form-check-switch { font-size: 1.25rem; }
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
                                <!-- This hidden input tells the backend what to do -->
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

                                <!-- PERMISSIONS SECTION -->
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
                                    <!-- Add more checkboxes for other permissions as your system grows -->
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
                            <div class="alert alert-info">No employee accounts found. Use the form to add one.</div>
                        <?php else: ?>
                            <?php foreach ($employees as $emp): ?>
                                <div class="employee-card" id="employee-<?php echo $emp['id']; ?>">
                                    <div class="employee-info">
                                        <h5><?php echo htmlspecialchars($emp['name']); ?></h5>
                                        <p>
                                            <i class="fas fa-mobile-alt me-1"></i> <?php echo htmlspecialchars($emp['mobile']); ?> |
                                            <i class="fas fa-envelope me-1 ms-2"></i> <?php echo htmlspecialchars($emp['email']); ?>
                                        </p>
                                    </div>
                                    <div class="employee-actions">
                                        <div class="form-check form-switch form-check-switch" title="Toggle Active/Inactive Status">
                                            <input class="form-check-input status-toggle" type="checkbox" role="switch" 
                                                   data-employee-id="<?php echo $emp['id']; ?>" 
                                                   <?php echo $emp['status'] == '1' ? 'checked' : ''; ?>>
                                        </div>
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

<?php include "foot.php"; // Includes JS libraries, etc. ?>
<script>
$(document).ready(function() {
    // Initialize Parsley validation on the form
    $('form.data-form').parsley();

    const backendUrl = 'function/backend/employee_actions.php';

    // Handle Add Employee Form Submission
    $('#add-employee-form').on('submit', function(e) {
        e.preventDefault();
        
        // Stop if form is not valid
        if (!$(this).parsley().isValid()) {
            return;
        }

        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');

        $.ajax({
            url: backendUrl,
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $.notify({ message: response.message }, { type: 'success' });
                    // Reload the page to show the new employee in the list
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    $.notify({ message: response.message }, { type: 'danger' });
                }
            },
            error: function() {
                $.notify({ message: 'A server error occurred. Please try again.' }, { type: 'warning' });
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('Create Employee Account');
            }
        });
    });

    // Handle Status Toggle Switch
    $('.status-toggle').on('change', function() {
        const checkbox = $(this);
        const employeeId = checkbox.data('employee-id');
        const newStatus = checkbox.is(':checked') ? 1 : 2; // 1 for Active, 2 for Deactivated

        $.ajax({
            url: backendUrl,
            type: 'POST',
            data: {
                action: 'toggle_status',
                employee_id: employeeId,
                new_status: newStatus
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $.notify({ message: response.message }, { type: 'success' });
                } else {
                    $.notify({ message: response.message }, { type: 'danger' });
                    // Revert the switch on failure
                    checkbox.prop('checked', !checkbox.prop('checked'));
                }
            },
            error: function() {
                $.notify({ message: 'A server error occurred.' }, { type: 'warning' });
                checkbox.prop('checked', !checkbox.prop('checked'));
            }
        });
    });

    // Handle Delete Employee Button
    $('.delete-employee-btn').on('click', function() {
        const employeeId = $(this).data('employee-id');
        const card = $('#employee-' + employeeId);

        Swal.fire({
            title: 'Are you sure?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: backendUrl,
                    type: 'POST',
                    data: {
                        action: 'delete_employee',
                        employee_id: employeeId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            card.fadeOut(500, function() { $(this).remove(); });
                            Swal.fire('Deleted!', response.message, 'success');
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error!', 'A server error occurred.', 'error');
                    }
                });
            }
        });
    });
});
</script>
</body>
</html>