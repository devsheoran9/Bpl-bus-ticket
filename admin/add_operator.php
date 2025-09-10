<?php
// add_operator.php (with Custom Responsive Table/Card View)
global $_conn_db;
include_once('function/_db.php');
session_security_check();
check_permission('can_manage_operators'); 
$operator_to_edit = null;

// A helper function for setting notifications to avoid repetition
function set_notification($type, $title, $desc) {
    $_SESSION['notif_type'] = $type;
    $_SESSION['notif_title'] = $title;
    $_SESSION['notif_desc'] = $desc;
}

// --- ACTION HANDLER (DELETE & EDIT-FORM-POPULATE) ---
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $operator_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($action == 'edit' && $operator_id) {
        try {
            $stmt = $_conn_db->prepare("SELECT * FROM operators WHERE operator_id = ?");
            $stmt->execute([$operator_id]);
            $operator_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$operator_to_edit) {
                set_notification('error', 'Not Found', 'The operator you are trying to edit does not exist.');
                header("Location: add_operator.php");
                exit();
            }
        } catch (PDOException $e) {
            set_notification('error', 'Database Error', 'Could not fetch operator details. Please try again.');
            // For debugging: error_log($e->getMessage());
        }
    }

    if ($action == 'delete' && $operator_id) {
        try {
            // Check if the operator is assigned to any buses
            $check_stmt = $_conn_db->prepare("SELECT COUNT(*) FROM buses WHERE operator_id = ?");
            $check_stmt->execute([$operator_id]);
            if ($check_stmt->fetchColumn() > 0) {
                 set_notification('error', 'Deletion Failed', 'Cannot delete operator because they are assigned to one or more buses.');
            } else {
                $delete_stmt = $_conn_db->prepare("DELETE FROM operators WHERE operator_id = ?");
                if ($delete_stmt->execute([$operator_id])) {
                    set_notification('success', 'Success', 'Operator deleted successfully.');
                } else {
                    set_notification('error', 'Deletion Failed', 'Could not delete the operator.');
                }
            }
        } catch (PDOException $e) {
            set_notification('error', 'Database Error', 'An error occurred during deletion. The operator might be in use.');
            // For debugging: error_log($e->getMessage());
        }
        header("Location: add_operator.php");
        exit();
    }
}

// --- FORM SUBMISSION LOGIC (ADD & UPDATE) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $operator_name = trim($_POST['operator_name']);
    $contact_person = trim($_POST['contact_person']);
    $contact_email = trim($_POST['contact_email']);
    $contact_phone = trim($_POST['contact_phone']);
    $address = trim($_POST['address']);
    $status = $_POST['status'];
    $action_type = $_POST['action_type'];
    $operator_id = filter_input(INPUT_POST, 'operator_id', FILTER_VALIDATE_INT);

    if (empty($operator_name)) {
        set_notification('error', 'Validation Error', 'Operator Name is a required field.');
    } else {
        try {
            if ($action_type == 'update' && $operator_id) {
                // UPDATE existing operator
                $sql = "UPDATE operators SET operator_name = ?, contact_person = ?, contact_email = ?, contact_phone = ?, address = ?, status = ? WHERE operator_id = ?";
                $stmt = $_conn_db->prepare($sql);
                if ($stmt->execute([$operator_name, $contact_person, $contact_email, $contact_phone, $address, $status, $operator_id])) {
                    set_notification('success', 'Success', 'Operator updated successfully.');
                } else {
                    set_notification('error', 'Update Failed', 'Could not update operator details.');
                }
            } else {
                // ADD new operator
                $sql = "INSERT INTO operators (operator_name, contact_person, contact_email, contact_phone, address, status) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $_conn_db->prepare($sql);
                if ($stmt->execute([$operator_name, $contact_person, $contact_email, $contact_phone, $address, $status])) {
                    set_notification('success', 'Success', 'New operator added successfully.');
                } else {
                    set_notification('error', 'Save Failed', 'Could not add the new operator.');
                }
            }
        } catch (PDOException $e) {
            set_notification('error', 'Database Error', 'An operation failed. Check if the data is unique and valid.');
            // For debugging: error_log($e->getMessage());
        }
    }
    header("Location: add_operator.php");
    exit();
}

// --- DATA FETCHING LOGIC (FOR THE TABLE/CARDS) ---
try {
    $stmt = $_conn_db->prepare("SELECT * FROM operators ORDER BY operator_id DESC");
    $stmt->execute();
    $operators = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $operators = []; 
    set_notification('error', 'Data Fetch Error', 'Could not load the list of operators.');
    // For debugging: error_log($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "head.php"; ?>
    <title>Manage Operators</title>
    <!-- ================== CUSTOM STYLES ================== -->
    <style>
        .card { border-radius: 0.5rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 1.5rem; transition: all 0.5s ease-in-out; }
        .card-header { background-color: #fff; font-weight: 600; padding: 0.75rem 1.25rem; border-bottom: 1px solid #e9ecef; border-top: 3px solid #0d6efd; }
        .card.highlight-edit { border-color: #0d6efd; box-shadow: 0 0 15px rgba(13, 110, 253, 0.4); }
        .search-wrapper { position: relative; }
        .search-wrapper .form-control { padding-left: 2.5rem; }
        .search-wrapper .fa-search { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #6c757d; }
        .operators-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; }
        .operator-card { background-color: #fff; border: 1px solid #dee2e6; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); display: flex; flex-direction: column; transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .operator-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        .operator-card-header { padding: 1rem 1.25rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #dee2e6; }
        .operator-card-header .operator-name { font-size: 1.1rem; font-weight: 600; color: #0d6efd; }
        .operator-card-body { padding: 1.25rem; flex-grow: 1; }
        .operator-card .info-item { display: flex; align-items: flex-start; margin-bottom: 0.85rem; font-size: 0.9rem; color: #495057; }
        .operator-card .info-item i { color: #6c757d; width: 20px; margin-right: 12px; text-align: center; margin-top: 2px; }
        .operator-card-footer { padding: 0.75rem 1.25rem; background-color: #f8f9fa; border-top: 1px solid #dee2e6; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px; display: flex; justify-content: flex-end; gap: 0.5rem; }
        #no-results-message { grid-column: 1 / -1; text-align: center; padding: 2rem; font-size: 1.2rem; color: #6c757d; }
    </style>
</head>
<body>

<div id="wrapper">
    <?php include_once('sidebar.php'); ?>
    <div class="main-content">
        <?php include_once('header.php'); ?>
        <div class="container-fluid">
            <h2 class="my-4">Add & Manage Bus Operators</h2>
            
            <div class="row">
                <!-- Add/Edit Operator Form Column -->
                <div class="col-lg-4">
                    <div class="card <?php if ($operator_to_edit) echo 'highlight-edit'; ?>" id="operator-form-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><?php echo $operator_to_edit ? 'Edit Operator' : 'Add New Operator'; ?></span>
                            <?php if ($operator_to_edit): ?>
                                <a href="add_operator.php" class="btn btn-sm btn-outline-secondary">Cancel Edit</a>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <form action="add_operator.php" method="POST">
                                <input type="hidden" name="action_type" value="<?php echo $operator_to_edit ? 'update' : 'add'; ?>">
                                <input type="hidden" name="operator_id" value="<?php echo $operator_to_edit['operator_id'] ?? ''; ?>">
                                
                                <div class="mb-3">
                                    <label for="operator_name" class="form-label">Operator Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="operator_name" name="operator_name" value="<?php echo htmlspecialchars($operator_to_edit['operator_name'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="contact_person" class="form-label">Contact Person</label>
                                    <input type="text" class="form-control" id="contact_person" name="contact_person" value="<?php echo htmlspecialchars($operator_to_edit['contact_person'] ?? ''); ?>">
                                </div>
                                <div class="row">
                                    <div class="mb-3 col-md-6">
                                        <label for="contact_phone" class="form-label">Contact Phone</label>
                                        <input type="tel" class="form-control" id="contact_phone" name="contact_phone" value="<?php echo htmlspecialchars($operator_to_edit['contact_phone'] ?? ''); ?>">
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label for="contact_email" class="form-label">Contact Email</label>
                                        <input type="email" class="form-control" id="contact_email" name="contact_email" value="<?php echo htmlspecialchars($operator_to_edit['contact_email'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="2"><?php echo htmlspecialchars($operator_to_edit['address'] ?? ''); ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select id="status" name="status" class="form-select">
                                        <option value="Active" <?php echo (isset($operator_to_edit) && $operator_to_edit['status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                                        <option value="Inactive" <?php echo (isset($operator_to_edit) && $operator_to_edit['status'] == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary w-100"><?php echo $operator_to_edit ? 'Update Operator' : 'Save Operator'; ?></button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Manage Operators Card Grid Column -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>Existing Operators (<?php echo count($operators); ?>)</span>
                            <div class="search-wrapper">
                                <i class="fas fa-search"></i>
                                <input type="text" id="operator-search-input" class="form-control form-control-sm" placeholder="Search Operators...">
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="operators-grid" id="operators-grid-container">
                                <?php if (empty($operators)): ?>
                                    <p id="no-operators-found" class="text-center w-100">No operators found. Please add a new one using the form.</p>
                                <?php else: ?>
                                    <?php foreach ($operators as $operator): ?>
                                        <div class="operator-card" data-search-text="<?php echo strtolower(htmlspecialchars(implode(' ', $operator))); ?>">
                                            <div class="operator-card-header">
                                                <span class="operator-name"><i class="fas fa-building me-2"></i><?php echo htmlspecialchars($operator['operator_name']); ?></span>
                                                <span class="badge <?php echo $operator['status'] == 'Active' ? 'bg-success' : 'bg-secondary'; ?>">
                                                    <?php echo $operator['status']; ?>
                                                </span>
                                            </div>
                                            <div class="operator-card-body">
                                                <div class="info-item">
                                                    <i class="fas fa-user-tie fa-fw"></i>
                                                    <span><strong>Contact:</strong> <?php echo htmlspecialchars($operator['contact_person'] ?: 'N/A'); ?></span>
                                                </div>
                                                <div class="info-item">
                                                    <i class="fas fa-phone fa-fw"></i>
                                                    <span><?php echo htmlspecialchars($operator['contact_phone'] ?: 'N/A'); ?></span>
                                                </div>
                                                <div class="info-item">
                                                    <i class="fas fa-envelope fa-fw"></i>
                                                    <span><?php echo htmlspecialchars($operator['contact_email'] ?: 'N/A'); ?></span>
                                                </div>
                                                <div class="info-item">
                                                    <i class="fas fa-map-marker-alt fa-fw"></i>
                                                    <span><?php echo htmlspecialchars($operator['address'] ?: 'N/A'); ?></span>
                                                </div>
                                            </div>
                                            <div class="operator-card-footer">
                                                <a href="add_operator.php?action=edit&id=<?php echo $operator['operator_id']; ?>" class="btn btn-sm btn-outline-info" title="Edit"><i class="fas fa-edit"></i> Edit</a>
                                                <a href="add_operator.php?action=delete&id=<?php echo $operator['operator_id']; ?>" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this operator? This cannot be undone.');"><i class="fas fa-trash"></i> Delete</a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    <div id="no-results-message" style="display: none;">No operators match your search.</div>
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
    // --- Live Search Functionality ---
    const searchInput = document.getElementById('operator-search-input');
    const operatorCards = document.querySelectorAll('.operator-card');
    const noResultsMessage = document.getElementById('no-results-message');
    const noOperatorsMessage = document.getElementById('no-operators-found');

    searchInput.addEventListener('keyup', function(event) {
        const searchTerm = event.target.value.toLowerCase();
        let visibleCount = 0;

        operatorCards.forEach(card => {
            // Using a data attribute for more reliable searching
            const cardText = card.dataset.searchText;
            if (cardText.includes(searchTerm)) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });
        
        // Only show no-results message if there are cards to search through
        if (operatorCards.length > 0) {
            noResultsMessage.style.display = (visibleCount === 0) ? 'block' : 'none';
        }
    });

    // --- Scroll and Highlight on Edit ---
    <?php if ($operator_to_edit): ?>
        const formCard = document.getElementById('operator-form-card');
        if (formCard) {
            // The class is now added directly in PHP for instant effect
            formCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
            document.getElementById('operator_name').focus();
            setTimeout(() => { formCard.classList.remove('highlight-edit'); }, 2500);
        }
    <?php endif; ?>
});
</script>

</body>
</html>
<?php pdo_close_conn($_conn_db); ?>