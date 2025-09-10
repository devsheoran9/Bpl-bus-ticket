<?php
// add_employee.php (Full Add & Edit Functionality)
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
                // Decode their current permissions to check the boxes
                $employee_permissions = json_decode($employee_to_edit['permissions'], true) ?: [];
            }
        } catch (PDOException $e) {
            $_SESSION['notif_type'] = 'error'; $_SESSION['notif_title'] = 'Error';
            $_SESSION['notif_desc'] = 'Could not fetch employee details for editing.';
        }
    }
}

 
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
    <title><?php echo $edit_mode ? 'Edit' : 'Add'; ?> Employee</title>
    <style>
        body { background-color: #f8f9fa; }
        .form-card { border-top: 4px solid <?php echo $edit_mode ? '#ffc107' : '#0d6efd'; ?>; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .search-wrapper { position: relative; }
        .search-wrapper .form-control { padding-left: 2.5rem; }
        .search-wrapper .fa-search { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #6c757d; }
        .employees-grid { display: grid; grid-template-columns: 1fr; gap: 1rem; }
        .employee-card {
            background-color: #fff; border: 1px solid #e9ecef; border-radius: 8px;
            display: flex; flex-wrap: wrap; align-items: center; gap: 1rem;
            padding: 1rem; transition: box-shadow 0.2s ease;
        }
        .employee-card:hover { box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        .employee-info { flex-grow: 1; min-width: 200px; }
        .employee-info h5 { margin-bottom: 0.25rem; font-size: 1.1rem; }
        .employee-info p { margin-bottom: 0; color: #6c757d; font-size: 0.9em; }
        .employee-actions { display: flex; align-items: center; gap: 0.5rem; }
        .form-check-switch { font-size: 1.25rem; }
        .online-indicator { color: #28a745; font-size: 0.8em; font-weight: bold; }
        .last-login-info { font-size: 0.8em; color: #6c757d; }
        #no-results-message { display: none; }
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
                <!-- Add/Edit Employee Form Column -->
                <div class="col-lg-4 mb-4">
                    <div class="card form-card">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5><?php echo $edit_mode ? 'Edit Employee Details' : 'Add New Employee'; ?></h5>
                            <?php if ($edit_mode): ?>
                                <a href="add_employee.php" class="btn btn-sm btn-outline-secondary">Cancel Edit</a>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <form id="employee-form"   data-parsley-validate>
                                <input type="hidden" name="action" value="<?php echo $edit_mode ? 'update_employee' : 'add_employee'; ?>">
                                <?php if ($edit_mode): ?>
                                    <input type="hidden" name="employee_id" value="<?php echo $employee_to_edit['id']; ?>">
                                <?php endif; ?>
                                
                                <div class="mb-3"><label for="name" class="form-label">Full Name</label><input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($employee_to_edit['name'] ?? ''); ?>" required></div>
                                <div class="mb-3"><label for="mobile" class="form-label">Mobile Number</label><input type="tel" class="form-control" id="mobile" name="mobile" value="<?php echo htmlspecialchars($employee_to_edit['mobile'] ?? ''); ?>" required data-parsley-type="digits" data-parsley-length="[10, 10]"></div>
                                <div class="mb-3"><label for="email" class="form-label">Email Address</label><input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($employee_to_edit['email'] ?? ''); ?>" required></div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" <?php echo !$edit_mode ? 'required' : ''; ?> data-parsley-minlength="6" placeholder="<?php echo $edit_mode ? 'Leave blank to keep unchanged' : ''; ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Assign Permissions</label>
                                    <div class="row">
                                        <?php 
                                        $permissions_list = [
                                            'Tickets' => ['can_book_tickets' => 'Can Book', 'can_view_bookings' => 'Can View', 'can_delete_bookings' => 'Can Delete'],
                                            'Routes' => ['can_manage_routes' => 'Can Add', 'can_edit_routes' => 'Can Edit', 'can_delete_routes' => 'Can Delete'],
                                            'Other' => ['can_manage_operators' => 'Operators', 'can_manage_buses' => 'Buses', 'can_change_password' => 'Change Password', 'can_manage_employees' => 'Manage Employees']
                                        ];
                                        foreach ($permissions_list as $group => $perms):
                                        ?>
                                        <div class="col-md-6 mb-2">
                                            <h6><i class="fas fa-<?php echo strtolower($group) == 'tickets' ? 'ticket-alt' : (strtolower($group) == 'routes' ? 'route' : 'cogs'); ?> me-2"></i><?php echo $group; ?></h6>
                                            <?php foreach ($perms as $key => $label): ?>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="<?php echo $key; ?>" id="perm_<?php echo $key; ?>" <?php echo isset($employee_permissions[$key]) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="perm_<?php echo $key; ?>"><?php echo $label; ?></label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <button type="submit" class="btn <?php echo $edit_mode ? 'btn-warning' : 'btn-primary'; ?> w-100 mt-2"><?php echo $edit_mode ? 'Update Employee' : 'Create Employee'; ?></button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Existing Employees List Column -->
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
                                    <div id="no-results-message" class="alert alert-warning w-100">No employees match your search.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="historyModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Login History</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body" id="historyModalBody"></div></div></div></div>

<?php include "foot.php"; ?>
<script>
$(document).ready(function() {
    $('form.data-form').parsley();
    const backendUrl = 'function/backend/employee_actions.php';

    // Live Search Functionality
    const searchInput = document.getElementById('employee-search-input');
    const employeeCards = document.querySelectorAll('.employee-card');
    const noResultsMessage = document.getElementById('no-results-message');
    searchInput.addEventListener('keyup', function(event) {
        const searchTerm = event.target.value.toLowerCase();
        let visibleCount = 0;
        employeeCards.forEach(card => {
            const cardText = card.textContent.toLowerCase();
            if (cardText.includes(searchTerm)) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });
        noResultsMessage.style.display = (visibleCount === 0 && employeeCards.length > 0) ? 'block' : 'none';
    });

    // --- FORM SUBMISSION (Handles both ADD and UPDATE) ---
    $('#employee-form').on('submit', function(e) {
        e.preventDefault();
        if (!$(this).parsley().isValid()) return;
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalBtnText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');
        
        $.ajax({
            url: backendUrl, type: 'POST', data: form.serialize(), dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $.notify({ message: response.message }, { type: 'success' });
                    // Redirect to clear the form and show the updated list
                    setTimeout(() => window.location.href = 'add_employee.php', 1500);
                } else {
                    $.notify({ message: response.message }, { type: 'danger' });
                }
            },
            error: () => $.notify({ message: 'A server error occurred.' }, { type: 'warning' }),
            complete: () => submitBtn.prop('disabled', false).html(originalBtnText)
        });
    });
    
    // --- Toggle Status ---
    $(document).on('change', '.status-toggle', function() {
        const checkbox = $(this);
        $.ajax({
            url: backendUrl, type: 'POST',
            data: { action: 'toggle_status', employee_id: checkbox.data('employee-id'), new_status: checkbox.is(':checked') ? 1 : 2 },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') { $.notify({ message: response.message }, { type: 'success' }); } 
                else { $.notify({ message: response.message }, { type: 'danger' }); checkbox.prop('checked', !checkbox.prop('checked')); }
            },
            error: () => { $.notify({ message: 'A server error occurred.' }, { type: 'warning' }); checkbox.prop('checked', !checkbox.prop('checked')); }
        });
    });

    // --- Delete Employee ---
    $(document).on('click', '.delete-employee-btn', function() {
        const employeeId = $(this).data('employee-id');
        Swal.fire({
            title: 'Are you sure?', text: "This action cannot be undone!", icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: backendUrl, type: 'POST', data: { action: 'delete_employee', employee_id: employeeId }, dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#employee-' + employeeId).fadeOut(500, function() { $(this).remove(); });
                            Swal.fire('Deleted!', response.message, 'success');
                        } else { Swal.fire('Error!', response.message, 'error'); }
                    },
                    error: () => Swal.fire('Error!', 'Could not connect to server.', 'error')
                });
            }
        });
    });

    // --- Force Logout ---
    $(document).on('click', '.force-logout-btn', function() {
        const employeeId = $(this).data('employee-id');
        Swal.fire({
            title: 'Force Logout?', text: 'This will immediately terminate the user\'s session.', icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#ffc107', confirmButtonText: 'Yes, terminate session!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: backendUrl, type: 'POST', data: { action: 'force_logout', employee_id: employeeId }, dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire('Success!', response.message, 'success').then(() => window.location.reload());
                        } else { Swal.fire('Error!', response.message, 'error'); }
                    },
                    error: () => Swal.fire('Error!', 'Could not connect to server.', 'error')
                });
            }
        });
    });
     
    const historyModal = new bootstrap.Modal(document.getElementById('historyModal'));
    $(document).on('click', '.history-btn', function() {
        const employeeId = $(this).data('employee-id');
        const modalBody = $('#historyModalBody');
        modalBody.html('<div class="text-center p-4"><div class="spinner-border"></div></div>');
        historyModal.show();
        
        $.ajax({
            url: backendUrl,
            type: 'GET',
            data: { action: 'get_login_history', employee_id: employeeId },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    let content = '<ul class="list-group list-group-flush">';
                    if (response.data.history && response.data.history.length > 0) {
                        response.data.history.forEach(log => {
                            let activityClass = log.activity_type === 'login' ? 'text-success' : 'text-danger';
                            // Format the date nicely
                            let formattedDate = new Date(log.log_time).toLocaleString('en-GB', { 
                                day: '2-digit', month: 'short', year: 'numeric', 
                                hour: '2-digit', minute: '2-digit', second: '2-digit' 
                            });
                            content += `
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong class="${activityClass}">${log.activity_type.toUpperCase()}</strong>
                                        <br>
                                        <small class="text-muted">IP: ${log.ip_address}</small>
                                    </div>
                                    <small>${formattedDate}</small>
                                </li>`;
                        });
                    } else {
                        content += '<li class="list-group-item">No login history found for this employee.</li>';
                    }
                    modalBody.html(content + '</ul>');
                } else {
                    modalBody.html(`<div class="alert alert-danger">${response.message}</div>`);
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