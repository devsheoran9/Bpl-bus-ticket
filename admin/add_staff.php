<?php
// add_staff.php (Redesigned with Custom Table & Updated Form Layout)
global $_conn_db;
include_once('function/_db.php');
session_security_check();
check_permission('can_manage_staff');

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
    // FIXED: Cannot use isset() on the result of an expression
    // First, check if $words[1] exists. If it does, then process it.
    if (isset($words[1]) && !empty($words[1])) { // Added !empty for robustness against empty second names
        $cleaned_second_word = str_replace('.', '', $words[1]);
        $initials .= strtoupper(substr($cleaned_second_word, 0, 1));
    }
    return $initials ?: '?';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "head.php"; ?>
    <title>Manage Staff</title>
    <style>
        /* --- General & Form Styling --- */
        :root {
            --primary: #5E50F9; --primary-light: #F0EEFF;
            --secondary: #6c757d; --light-gray: #f8f9fa;
            --border-color: #dee2e6; --card-shadow: 0 8px 30px rgba(0,0,0,0.06);
            --text-dark: #212529;
            --text-muted: #6c757d;
        }
        body { background-color: var(--light-gray); }
        .card { border-radius: 1rem; box-shadow: var(--card-shadow); border: 1px solid var(--border-color); }
        .card-header { background-color: #fff; font-weight: 600; padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); font-size: 1.1rem; }
        .form-label { font-weight: 500; }
        .form-control, .form-select { border-radius: 0.5rem; padding: 0.75rem 1rem; }
        .form-control:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-light); }
        .submit-btn { padding: 0.75rem; font-weight: 600; border-radius: 0.5rem; }
        #dl-number-wrapper { display: none; }
        
        /* --- CUSTOM TABLE STYLING (for larger screens) --- */
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
            border: 1px solid var(--border-color);
        }
        .staff-avatar-initials-table {
            width: 50px; height: 50px; border-radius: 50%;
            background-color: var(--primary-light); color: var(--primary);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; font-weight: 600;
            flex-shrink: 0;
            border: 1px solid var(--border-color);
        }
        .staff-name-table { font-weight: 600; color: var(--text-dark); display: block; }
        .staff-mobile-table { font-size: 0.85rem; color: var(--text-muted); display: block; }
        .designation-badge {
            font-size: 0.8rem;
            font-weight: 500;
            padding: 0.4em 0.8em;
            border-radius: 20px;
            background-color: var(--primary-light);
            color: var(--primary);
        }
        .action-buttons { display: flex; gap: 0.5rem; justify-content: flex-end; }
        .action-buttons .btn { padding: 0.3rem 0.7rem; }

        /* --- CUSTOM COMPACT CARD STYLING (for smaller screens - White Theme) --- */
        .staff-card-sm {
            background-color: white;
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); /* Subtler shadow */
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .staff-card-sm:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        }

        .staff-card-sm .card-body {
            padding: 0.75rem 1rem; /* Reduced padding */
        }
        .staff-card-sm .staff-header-compact {
            display: flex;
            align-items: center;
            gap: 0.75rem; /* Smaller gap */
            padding-bottom: 0.75rem; /* Separates header from details */
            border-bottom: 1px dashed var(--border-color); /* Dashed line for subtle separation */
            margin-bottom: 0.75rem;
        }
        .staff-avatar-card-compact {
            width: 40px; height: 40px; /* Smaller avatar */
            min-width: 40px; /* Prevent shrinking */
            border-radius: 50%;
            background-color: var(--primary-light); /* Light background for initials */
            color: var(--primary); /* Primary text color for initials */
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem; /* Smaller font size */
            font-weight: 600;
            overflow: hidden;
            border: 1px solid var(--primary-light); /* Subtle border */
        }
        .staff-avatar-card-compact img {
            width: 100%; height: 100%; object-fit: cover;
        }
        .staff-name-designation-wrapper {
            flex-grow: 1;
        }
        .staff-name-card-compact {
            font-size: 0.95rem; /* Smaller name font */
            font-weight: 600;
            margin-bottom: 0.1rem;
            color: var(--text-dark);
        }
        .designation-badge-card-compact {
            font-size: 0.7rem; /* Smaller badge font */
            font-weight: 500;
            padding: 0.2em 0.5em; /* Smaller padding */
            border-radius: 12px;
            background-color: var(--primary-light);
            color: var(--primary);
        }

        .staff-details-grid {
            display: grid;
            grid-template-columns: 1fr; /* Default to single column */
            gap: 0.5rem; /* Reduced gap between detail items */
            margin-bottom: 0.75rem;
        }
        @media (min-width: 420px) { /* Adjust breakpoint for two columns if desired */
            .staff-details-grid {
                grid-template-columns: repeat(2, 1fr); /* Two columns on slightly wider small screens */
            }
        }
        
        .detail-item-compact {
            display: flex;
            flex-direction: column; /* Stack label and value */
        }
        .detail-label-compact {
            font-size: 0.65rem; /* Very small label */
            color: var(--text-muted);
            margin-bottom: 0; /* No margin */
            line-height: 1;
        }
        .detail-value-compact {
            font-size: 0.85rem; /* Compact value font */
            font-weight: 500;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 0.3rem; /* Small gap for icon */
            line-height: 1.2;
        }
        .detail-value-compact .fas {
            font-size: 0.7rem; /* Smaller icon */
        }

        .staff-remark-compact {
            font-size: 0.8rem;
            color: var(--text-muted);
            line-height: 1.4;
            margin-top: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .staff-card-sm .card-footer {
            background-color: transparent; /* Transparent footer background */
            border-top: 1px solid var(--border-color); /* Light border */
            padding: 0.75rem 1rem; /* Consistent padding */
            border-bottom-left-radius: 0.75rem;
            border-bottom-right-radius: 0.75rem;
            text-align: right; /* Align buttons to the right */
        }
        .staff-card-sm .card-footer .btn {
            font-size: 0.8rem; /* Smaller buttons */
            padding: 0.35rem 0.7rem; /* Compact padding */
            border-radius: 0.4rem;
        }
        /* No Staff Member Message for Cards */
        .staff-list-cards .alert {
            box-shadow: var(--card-shadow);
        }
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
                                <div class="row g-1 mb-3">
                                    <div class="col-md-6 col-6">
                                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($staff_to_edit['name'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-6 col-6">
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
                                
                                <div class="row g-1 mb-3">
                                    <div class="col-md-6 col-sm-12">
                                        <label class="form-label">Aadhar Number <small>(Optional)</small></label>
                                        <input type="text" class="form-control" name="aadhar_no" value="<?php echo htmlspecialchars($staff_to_edit['aadhar_no'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6 col-sm-12">
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
                     <!-- Staff List Table (Visible on medium screens and up) -->
                     <div class="card d-none d-md-block">
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
                                                    <span class="badge designation-badge">
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

                    <!-- Staff List Cards (Visible on small screens only) -->
                    <div class="d-md-none mt-4 staff-list-cards">
                        <h5 class="mb-3">Staff List <span class="badge bg-primary rounded-pill ms-2"><?php echo count($staff_list); ?> Total</span></h5>
                        <?php if (empty($staff_list)): ?>
                            <div class="alert alert-info text-center mt-3" role="alert">
                                <i class="fas fa-users fa-2x mb-2"></i>
                                <p class="mb-0">No staff members have been added yet.</p>
                            </div>
                        <?php else: ?>
                            <div class="row row-cols-1 g-3">
                                <?php foreach ($staff_list as $staff): ?>
                                    <div class="col">
                                        <div class="card staff-card-sm" id="staff-card-<?php echo $staff['staff_id']; ?>">
                                            <div class="card-body">
                                                <div class="staff-header-compact">
                                                    <div class="staff-avatar-card-compact">
                                                        <?php if (!empty($staff['profile_image_path'])): ?>
                                                            <img src="uploads/staff_images/<?php echo htmlspecialchars($staff['profile_image_path']); ?>" alt="Profile">
                                                        <?php else: ?>
                                                            <span><?php echo get_initials($staff['name']); ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="staff-name-designation-wrapper">
                                                        <h6 class="staff-name-card-compact"><?php echo htmlspecialchars($staff['name']); ?></h6>
                                                        <span class="badge designation-badge-card-compact"><?php echo htmlspecialchars($staff['designation']); ?></span>
                                                    </div>
                                                </div>

                                                <div class="staff-details-grid">
                                                    <div class="detail-item-compact">
                                                        <small class="detail-label-compact">Mobile</small>
                                                        <span class="detail-value-compact"><i class="fas fa-phone-alt"></i><?php echo htmlspecialchars($staff['mobile']); ?></span>
                                                    </div>
                                                    <?php if ($staff['designation'] == 'Driver' && !empty($staff['driving_licence_no'])): ?>
                                                        <div class="detail-item-compact">
                                                            <small class="detail-label-compact">DL No.</small>
                                                            <span class="detail-value-compact"><i class="fas fa-id-card"></i><?php echo htmlspecialchars($staff['driving_licence_no']); ?></span>
                                                        </div>
                                                    <?php elseif (!empty($staff['aadhar_no'])): ?>
                                                        <div class="detail-item-compact">
                                                            <small class="detail-label-compact">Aadhar No.</small>
                                                            <span class="detail-value-compact"><i class="fas fa-id-card-alt"></i><?php echo htmlspecialchars($staff['aadhar_no']); ?></span>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="detail-item-compact">
                                                            <small class="detail-label-compact">ID Details</small>
                                                            <span class="detail-value-compact text-muted">N/A</span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>

                                                <?php if (!empty($staff['remark'])): ?>
                                                    <p class="staff-remark-compact mb-0">
                                                        <small class="detail-label-compact">Remark</small>
                                                        <?php echo htmlspecialchars($staff['remark']); ?>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="card-footer d-flex justify-content-end gap-2">
                                                <a href="add_staff.php?action=edit&id=<?php echo $staff['staff_id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-pencil-alt me-1"></i> Edit</a>
                                                <button type="button" class="btn btn-sm btn-outline-danger delete-staff-btn-card" data-id="<?php echo $staff['staff_id']; ?>" title="Delete"><i class="fas fa-trash-alt me-1"></i> Delete</button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
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
            dlInput.prop('required', false).val(''); // Clear value when hidden
        }
    }
    // Initial check on page load
    toggleDlField();
    // Event listener for changes
    designationSelect.on('change', toggleDlField);
 
    
    // --- AJAX DELETE FUNCTIONALITY (Unified for both Table and Cards) ---
    $(document).on('click', '.delete-staff-btn, .delete-staff-btn-card', function() {
        const staffId = $(this).data('id');
        const targetElement = $('#staff-row-' + staffId).length ? $('#staff-row-' + staffId) : $('#staff-card-' + staffId);

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
                            targetElement.css('background-color', '#ffdddd').fadeOut(600, function() {
                                $(this).remove();
                                // Refresh count badges if needed
                                let currentTotal = parseInt($('.badge.rounded-pill').text().split(' ')[0]);
                                if (!isNaN(currentTotal) && currentTotal > 0) {
                                    $('.badge.rounded-pill').text((currentTotal - 1) + ' Total');
                                }
                                // If no staff left, show the empty message for cards
                                if ($('.staff-list-cards .row.row-cols-1 .col').length === 0) {
                                    $('.staff-list-cards').html('<div class="alert alert-info text-center mt-3" role="alert"><i class="fas fa-users fa-2x mb-2"></i><p class="mb-0">No staff members have been added yet.</p></div>');
                                }
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