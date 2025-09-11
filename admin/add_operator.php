<?php
// add_operator.php (Frontend Only)
global $_conn_db;
include_once('function/_db.php');
session_security_check();
check_permission('can_manage_operators');

$operator_to_edit = null;

// --- PRE-FILL FORM FOR EDITING ---
if (isset($_GET['action']) && $_GET['action'] == 'edit') {
    $operator_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($operator_id) {
        try {
            $stmt = $_conn_db->prepare("SELECT * FROM operators WHERE operator_id = ?");
            $stmt->execute([$operator_id]);
            $operator_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { /* Handle error gracefully */ }
    }
}

// --- DATA FETCHING for the list ---
try {
    $stmt = $_conn_db->prepare("SELECT * FROM operators ORDER BY operator_id DESC");
    $stmt->execute();
    $operators = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $operators = []; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "head.php"; ?>
    <title>Manage Drivers & Conductors</title>
    <style>
        .card { border-radius: 0.5rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 1.5rem; transition: all 0.5s ease-in-out; }
        .card-header { background-color: #fff; font-weight: 600; padding: 0.75rem 1.25rem; border-bottom: 1px solid #e9ecef; border-top: 3px solid #0d6efd; }
        .card.highlight-edit { border-color: #0d6efd; box-shadow: 0 0 15px rgba(13, 110, 253, 0.4); }
        .search-wrapper { position: relative; max-width: 250px; }
        .search-wrapper .form-control { padding-left: 2.5rem; }
        .search-wrapper .fa-search { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #6c757d; }
        .operators-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem; }
        .operator-card { background-color: #fff; border: 1px solid #dee2e6; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); display: flex; flex-direction: column; transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .operator-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        .operator-card-header { padding: 1rem 1.25rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #dee2e6; }
        .operator-card-header .driver-name { font-size: 1.1rem; font-weight: 600; color: #0d6efd; }
        .operator-card-body { padding: 1.25rem; flex-grow: 1; }
        .operator-card .info-item { display: flex; align-items: flex-start; margin-bottom: 0.75rem; font-size: 0.9rem; color: #495057; }
        .operator-card .info-item i { color: #6c757d; width: 20px; margin-right: 12px; text-align: center; margin-top: 2px; }
        .operator-card-footer { padding: 0.75rem 1.25rem; background-color: #f8f9fa; border-top: 1px solid #dee2e6; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px; display: flex; justify-content: flex-end; gap: 0.5rem; }
        #no-results-message { grid-column: 1 / -1; text-align: center; padding: 2rem; }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include_once('sidebar.php'); ?>
    <div class="main-content">
        <?php include_once('header.php'); ?>
        <div class="container-fluid">
            <h2 class="my-4">Manage Drivers & Conductors</h2>
            <div class="row">
                <div class="col-lg-4">
                    <div class="card <?php if ($operator_to_edit) echo 'highlight-edit'; ?>" id="operator-form-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><?php echo $operator_to_edit ? 'Edit Details' : 'Add New Record'; ?></span>
                            <?php if ($operator_to_edit): ?><a href="add_operator.php" class="btn btn-sm btn-outline-secondary">Cancel Edit</a><?php endif; ?>
                        </div>
                        <div class="card-body">
                            <form class="data-form" action="function/backend/operator_actions.php" method="POST">
                                <input type="hidden" name="action" value="save_operator">
                                <input type="hidden" name="action_type" value="<?php echo $operator_to_edit ? 'update' : 'add'; ?>">
                                <input type="hidden" name="operator_id" value="<?php echo $operator_to_edit['operator_id'] ?? ''; ?>">
                                
                                <!-- --- THIS FORM IS NOW COMPLETE AND CORRECT --- -->
                                  <div class="row g-1   ">
                                <div class="mb-3 col-6 ">
                                    <label class="form-label">Driver Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="operator_name" value="<?php echo htmlspecialchars($operator_to_edit['operator_name'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-3 col-6 ">
                                    <label class="form-label">Conductor Name</label>
                                    <input type="text" class="form-control" name="contact_person" value="<?php echo htmlspecialchars($operator_to_edit['contact_person'] ?? ''); ?>">
                                </div></div>
                                <div class="row g-1">
                                    <div class="mb-3 col-md-6 col-6 ">
                                        <label class="form-label">Conductor Mobile</label>
                                        <input type="tel" class="form-control" name="contact_phone" value="<?php echo htmlspecialchars($operator_to_edit['contact_phone'] ?? ''); ?>">
                                    </div>
                                    <div class="mb-3 col-md-6 col-6 ">
                                        <label class="form-label">Conductor Email</label>
                                        <input type="email" class="form-control" name="contact_email" value="<?php echo htmlspecialchars($operator_to_edit['contact_email'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Address</label>
                                    <textarea class="form-control" name="address" rows="2"><?php echo htmlspecialchars($operator_to_edit['address'] ?? ''); ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="Active" <?php echo (isset($operator_to_edit) && $operator_to_edit['status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                                        <option value="Inactive" <?php echo (isset($operator_to_edit) && $operator_to_edit['status'] == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 submit-btn"><?php echo $operator_to_edit ? 'Update Details' : 'Save Details'; ?></button>
                                
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>Existing Records</span>
                            <div class="search-wrapper"><i class="fas fa-search"></i><input type="text" id="operator-search-input" class="form-control form-control-sm" placeholder="Search..."></div>
                        </div>
                        <div class="card-body">
                            <div class="operators-grid" id="operators-grid-container">
                                <?php if (empty($operators)): ?>
                                    <div id="no-results-message">No records found.</div>
                                <?php else: foreach ($operators as $operator): ?>
                                    <div class="operator-card" id="operator-card-<?php echo $operator['operator_id']; ?>" data-search-text="<?php echo strtolower(htmlspecialchars(implode(' ', $operator))); ?>">
                                        <div class="operator-card-header">
                                            <span class="driver-name"><i class="fas fa-user-shield me-2"></i><?php echo htmlspecialchars($operator['operator_name']); ?></span>
                                            <span class="badge <?php echo $operator['status'] == 'Active' ? 'bg-success' : 'bg-secondary'; ?>"><?php echo $operator['status']; ?></span>
                                        </div>
                                        <div class="operator-card-body">
                                            <div class="info-item"><i class="fas fa-user-friends fa-fw"></i><span>Conductor: <strong><?php echo htmlspecialchars($operator['contact_person'] ?: 'N/A'); ?></strong></span></div>
                                            <div class="info-item"><i class="fas fa-phone fa-fw"></i><span><?php echo htmlspecialchars($operator['contact_phone'] ?: 'N/A'); ?></span></div>
                                            <div class="info-item"><i class="fas fa-envelope fa-fw"></i><span><?php echo htmlspecialchars($operator['contact_email'] ?: 'N/A'); ?></span></div>
                                            <div class="info-item"><i class="fas fa-map-marker-alt fa-fw"></i><span><?php echo htmlspecialchars($operator['address'] ?: 'N/A'); ?></span></div>
                                        </div>
                                        <div class="operator-card-footer">
                                            <a href="add_operator.php?action=edit&id=<?php echo $operator['operator_id']; ?>" class="btn btn-sm btn-outline-info" title="Edit"><i class="fas fa-edit"></i> Edit</a>
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-operator-btn" data-id="<?php echo $operator['operator_id']; ?>" title="Delete"><i class="fas fa-trash"></i> Delete</button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <div id="no-results-message" style="display: none;">No records match your search.</div>
                                <?php endif; ?>
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
    // --- AJAX FORM SUBMISSION ---
    
    // --- AJAX DELETE FUNCTIONALITY ---
    $('.delete-operator-btn').on('click', function() {
        const operatorId = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?', text: "This record will be permanently deleted!", icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: "function/backend/operator_actions.php",
                    data: { action: 'delete_operator', operator_id: operatorId },
                    dataType: "json",
                    success: function(data) {
                        $.notify({ title: data.notif_title, message: data.notif_desc }, { type: data.notif_type });
                        if (data.res === 'true') {
                            $('#operator-card-' + operatorId).fadeOut(500, function() { $(this).remove(); });
                        }
                    },
                    error: function() { $.notify({ title: 'Error', message: 'Could not connect to the server.' }, { type: 'danger' }); }
                });
            }
        });
    });

    // --- Live Search ---
    $('#operator-search-input').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        let visibleCount = 0;
        $('.operator-card').each(function() {
            const card = $(this);
            const cardText = card.data('search-text');
            if (cardText.includes(searchTerm)) {
                card.show(); visibleCount++;
            } else { card.hide(); }
        });
        $('#no-results-message').toggle(visibleCount === 0 && $('.operator-card').length > 0);
    });

    // --- Scroll and Highlight on Edit ---
    <?php if ($operator_to_edit): ?>
        document.getElementById('operator-form-card').scrollIntoView({ behavior: 'smooth', block: 'center' });
    <?php endif; ?>
});
</script>
</body>
</html>