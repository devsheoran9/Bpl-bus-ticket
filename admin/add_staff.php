<?php
// add_staff.php (Redesigned with Custom Table & Updated Form Layout)
global $_conn_db;
include_once('function/_db.php');
session_security_check();
// check_permission('can_manage_staff');

$staff_to_edit = null;

if (isset($_GET['action']) && $_GET['action'] == 'edit') {
    $staff_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($staff_id) {
        $stmt = $_conn_db->prepare("SELECT * FROM staff WHERE staff_id = ?");
        $stmt->execute([$staff_id]);
        $staff_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

try {
    $staff_list = $_conn_db->query("SELECT * FROM staff ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $staff_list = []; }

// Helper function to generate avatar with initials
function get_initials($name) {
    $words = explode(" ", $name);
    $initials = "";
    if (isset($words[0])) $initials .= strtoupper(substr($words[0], 0, 1));
    if (isset($words[1])) $initials .= strtoupper(substr($words[1], 0, 1));
    return $initials ?: '?';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "head.php"; ?>
    <title>Manage Staff</title>
    <style>
        /* --- General & Form Styling (Similar to original) --- */
        :root {
            --primary: #5E50F9; --primary-light: #F0EEFF;
            --secondary: #6c757d; --light-gray: #f8f9fa;
            --border-color: #dee2e6; --card-shadow: 0 8px 30px rgba(0,0,0,0.06);
        }
        body { background-color: var(--light-gray); }
        .card { border-radius: 1rem; box-shadow: var(--card-shadow); border: 1px solid var(--border-color); }
        .card-header { background-color: #fff; font-weight: 600; padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); font-size: 1.1rem; }
        .form-label { font-weight: 500; }
        .form-control, .form-select { border-radius: 0.5rem; padding: 0.75rem 1rem; }
        .form-control:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-light); }
        .submit-btn { padding: 0.75rem; font-weight: 600; border-radius: 0.5rem; }
        #dl-number-wrapper { display: none; }
        
        /* --- CUSTOM TABLE STYLING --- */
        .table { border-collapse: separate; border-spacing: 0; }
        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid var(--border-color);
            color: #495057;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            vertical-align: middle;
        }
        .table tbody tr { transition: background-color 0.2s ease-in-out; }
        .table tbody tr:last-child td { border-bottom: 0; }
        .table td { border-top: 1px solid var(--border-color); padding: 1rem 1.5rem; }
        .staff-info-cell { display: flex; align-items: center; gap: 1rem; }
        .staff-avatar-table {
            width: 50px; height: 50px;
            border-radius: 50%; object-fit: cover;
            flex-shrink: 0;
        }
        .staff-avatar-initials-table {
            width: 50px; height: 50px; border-radius: 50%;
            background-color: var(--primary); color: white;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; font-weight: 600;
            flex-shrink: 0;
        }
        .staff-name-table { font-weight: 600; color: #212529; display: block; }
        .staff-mobile-table { font-size: 0.85rem; color: var(--secondary); display: block; }
        .designation-badge {
            font-size: 0.8rem;
            font-weight: 500;
            padding: 0.4em 0.8em;
            border-radius: 20px;
        }
        .action-buttons { display: flex; gap: 0.5rem; justify-content: flex-end; }
        .action-buttons .btn { padding: 0.3rem 0.7rem; }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include_once('sidebar.php'); ?>
    <div class="main-content">
        <?php include_once('header.php'); ?>
        <div class="container-fluid">
            <h2 class="my-4">Manage Staff Members</h2>
            <div class="row">
                <div class="col-xl-4 mb-4">
                    <div class="card">
                        <div class="card-header"><?php echo $staff_to_edit ? 'Edit Staff Details' : 'Add New Staff Member'; ?></div>
                        <div class="card-body p-4">
                            <form class="data-form" action="function/backend/staff_actions.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="save_staff">
                                <input type="hidden" name="action_type" value="<?php echo $staff_to_edit ? 'update' : 'add'; ?>">
                                <input type="hidden" name="staff_id" value="<?php echo $staff_to_edit['staff_id'] ?? ''; ?>">
                                <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($staff_to_edit['profile_image_path'] ?? ''); ?>">

                                <!-- START: MODIFIED FORM LAYOUT -->
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($staff_to_edit['name'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control" name="mobile" value="<?php echo htmlspecialchars($staff_to_edit['mobile'] ?? ''); ?>" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Designation <span class="text-danger">*</span></label>
                                    <select class="form-select" name="designation" id="designation-select" required>
                                        <option value="">-- Select Role --</option>
                                        <?php $roles = ['Driver', 'Conductor', 'Helper', 'Telecaller', 'Manager', 'Mechanic', 'Cleaner'];
                                        foreach ($roles as $role): ?>
                                            <option value="<?php echo $role; ?>" <?php echo (isset($staff_to_edit) && $staff_to_edit['designation'] == $role) ? 'selected' : ''; ?>><?php echo $role; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3" id="dl-number-wrapper">
                                    <label class="form-label">Driving Licence No. <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="driving_licence_no" name="driving_licence_no" value="<?php echo htmlspecialchars($staff_to_edit['driving_licence_no'] ?? ''); ?>">
                                </div>
                                
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Aadhar Number <small>(Optional)</small></label>
                                        <input type="text" class="form-control" name="aadhar_no" value="<?php echo htmlspecialchars($staff_to_edit['aadhar_no'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Profile Image <small>(Optional)</small></label>
                                        <input type="file" class="form-control" name="profile_image" accept="image/*">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Remark <small>(Optional)</small></label>
                                    <textarea class="form-control" name="remark" rows="2"><?php echo htmlspecialchars($staff_to_edit['remark'] ?? ''); ?></textarea>
                                </div>
                                <!-- END: MODIFIED FORM LAYOUT -->

                                <button type="submit" class="btn btn-primary w-100 submit-btn"><?php echo $staff_to_edit ? 'Update Details' : 'Save Staff Member'; ?></button>
                                <?php if ($staff_to_edit): ?>
                                    <a href="add_staff.php" class="btn btn-secondary w-100 mt-2">Cancel Edit</a>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-xl-8">
                     <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>Staff List</span>
                            <span class="badge bg-primary rounded-pill"><?php echo count($staff_list); ?> Total</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th scope="col" style="padding-left: 1.5rem;">Staff Member</th>
                                            <th scope="col">Designation</th>
                                            <th scope="col">Licence / Aadhar</th>
                                            <th scope="col" class="text-end" style="padding-right: 1.5rem;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($staff_list)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-5">
                                                    <i class="fas fa-users fa-2x mb-2"></i>
                                                    <p class="mb-0">No staff members have been added yet.</p>
                                                </td>
                                            </tr>
                                        <?php else: foreach ($staff_list as $staff): ?>
                                            <tr id="staff-row-<?php echo $staff['staff_id']; ?>">
                                                <td>
                                                    <div class="staff-info-cell">
                                                        <?php if (!empty($staff['profile_image_path'])): ?>
                                                            <img src="uploads/staff_images/<?php echo htmlspecialchars($staff['profile_image_path']); ?>" alt="Profile" class="staff-avatar-table">
                                                        <?php else: ?>
                                                            <div class="staff-avatar-initials-table"><?php echo get_initials($staff['name']); ?></div>
                                                        <?php endif; ?>
                                                        <div>
                                                            <span class="staff-name-table"><?php echo htmlspecialchars($staff['name']); ?></span>
                                                            <span class="staff-mobile-table"><i class="fas fa-phone-alt fa-xs me-1"></i><?php echo htmlspecialchars($staff['mobile']); ?></span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary-light text-primary designation-badge">
                                                        <?php echo htmlspecialchars($staff['designation']); ?>
                                                    </span>
                                                </td>
                                                <td class="text-muted small">
                                                    <?php if ($staff['designation'] == 'Driver' && !empty($staff['driving_licence_no'])): ?>
                                                        DL: <strong><?php echo htmlspecialchars($staff['driving_licence_no']); ?></strong>
                                                    <?php elseif (!empty($staff['aadhar_no'])): ?>
                                                        Aadhar: <strong><?php echo htmlspecialchars($staff['aadhar_no']); ?></strong>
                                                    <?php else: ?>
                                                        N/A
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <a href="add_staff.php?action=edit&id=<?php echo $staff['staff_id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-pencil-alt"></i></a>
                                                        <button type="button" class="btn btn-sm btn-outline-danger delete-staff-btn" data-id="<?php echo $staff['staff_id']; ?>" title="Delete"><i class="fas fa-trash-alt"></i></button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "foot.php"; ?>
<script>
$(document).ready(function() {
    const designationSelect = $('#designation-select');
    const dlWrapper = $('#dl-number-wrapper');
    const dlInput = $('#driving_licence_no');

    function toggleDlField() {
        if (designationSelect.val() === 'Driver') {
            dlWrapper.slideDown();
            dlInput.prop('required', true);
        } else {
            dlWrapper.slideUp();
            dlInput.prop('required', false).val('');
        }
    }
    toggleDlField();
    designationSelect.on('change', toggleDlField);
 
    
    
    // --- AJAX DELETE FUNCTIONALITY (UPDATED FOR TABLE) ---
    $('.delete-staff-btn').on('click', function() {
        const staffId = $(this).data('id');
        const row = $('#staff-row-' + staffId); // Target the table row

        Swal.fire({
            title: 'Are you sure?',
            text: "This staff member will be permanently deleted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'function/backend/staff_actions.php',
                    type: 'POST',
                    data: { action: 'delete_staff', staff_id: staffId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            row.css('background-color', '#ffdddd').fadeOut(600, function() { 
                                $(this).remove(); 
                            });
                            $.notify({ message: response.message }, { type: 'success' });
                        } else {
                            $.notify({ message: response.message }, { type: 'danger' });
                        }
                    },
                    error: function() {
                        $.notify({ message: 'A server error occurred.' }, { type: 'danger' });
                    }
                });
            }
        });
    });
});
</script>
</body>
</html>