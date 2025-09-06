<?php
global $_conn_db;
include_once('function/_db.php');
// check_user_login();
session_security_check(); 
$operator_to_edit = null;

// --- ACTION HANDLER (DELETE & EDIT-FORM-POPULATE) ---
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $operator_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($action == 'edit' && $operator_id) {
        try {
            $stmt = $_conn_db->prepare("SELECT * FROM operators WHERE operator_id = ?");
            $stmt->execute([$operator_id]);
            $operator_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $_SESSION['notif_type'] = 'error';
            $_SESSION['notif_title'] = 'Error';
            $_SESSION['notif_desc'] = 'Could not fetch operator details.';
        }
    }

    if ($action == 'delete' && $operator_id) {
        try {
            $check_stmt = $_conn_db->prepare("SELECT COUNT(*) FROM buses WHERE operator_id = ?");
            $check_stmt->execute([$operator_id]);
            if ($check_stmt->fetchColumn() > 0) {
                 $_SESSION['notif_type'] = 'error';
                 $_SESSION['notif_title'] = 'Deletion Failed';
                 $_SESSION['notif_desc'] = 'Cannot delete this operator as they have buses assigned to them.';
            } else {
                $delete_stmt = $_conn_db->prepare("DELETE FROM operators WHERE operator_id = ?");
                if ($delete_stmt->execute([$operator_id])) {
                    $_SESSION['notif_type'] = 'success';
                    $_SESSION['notif_title'] = 'Success';
                    $_SESSION['notif_desc'] = 'Operator deleted successfully.';
                }
            }
        } catch (PDOException $e) {
            $_SESSION['notif_type'] = 'error';
            $_SESSION['notif_title'] = 'Database Error';
            $_SESSION['notif_desc'] = 'Could not delete the operator.';
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
        $_SESSION['notif_type'] = 'error';
        $_SESSION['notif_title'] = 'Validation Error';
        $_SESSION['notif_desc'] = 'Operator Name is a required field.';
    } else {
        try {
            if ($action_type == 'update' && $operator_id) {
                $sql = "UPDATE operators SET operator_name = ?, contact_person = ?, contact_email = ?, contact_phone = ?, address = ?, status = ? WHERE operator_id = ?";
                $stmt = $_conn_db->prepare($sql);
                if ($stmt->execute([$operator_name, $contact_person, $contact_email, $contact_phone, $address, $status, $operator_id])) {
                    $_SESSION['notif_type'] = 'success';
                    $_SESSION['notif_title'] = 'Success';
                    $_SESSION['notif_desc'] = 'Operator details updated successfully.';
                }
            } else {
                $sql = "INSERT INTO operators (operator_name, contact_person, contact_email, contact_phone, address, status) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $_conn_db->prepare($sql);
                if ($stmt->execute([$operator_name, $contact_person, $contact_email, $contact_phone, $address, $status])) {
                    $_SESSION['notif_type'] = 'success';
                    $_SESSION['notif_title'] = 'Success';
                    $_SESSION['notif_desc'] = 'New operator added successfully.';
                }
            }
        } catch (PDOException $e) {
            $_SESSION['notif_type'] = 'error';
            $_SESSION['notif_title'] = 'Database Operation Failed';
            $_SESSION['notif_desc'] = ($e->getCode() == '23000') ? 'An operator with this name might already exist.' : 'A database error occurred.';
        }
    }
    header("Location: add_operator.php");
    exit();
}

// --- DATA FETCHING LOGIC (FOR THE TABLE) ---
try {
    $stmt = $_conn_db->prepare("SELECT * FROM operators ORDER BY operator_id DESC");
    $stmt->execute();
    $operators = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $operators = [];
    $_SESSION['notif_type'] = 'error';
    $_SESSION['notif_title'] = 'Data Fetch Error';
    $_SESSION['notif_desc'] = 'Could not retrieve the list of operators.';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "head.php"; ?>
    <style>
        .card {
            border-radius: 0.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
            transition: all 0.5s ease-in-out; /* Added for smooth highlight transition */
        }
        .card-header {
            background-color: #fff;
            font-weight: 600;
            padding: 0.75rem 1.25rem;
            border-bottom: 1px solid #e9ecef;
            border-top: 3px solid #0d6efd;
        }
        .table-actions {
            white-space: nowrap;
            width: 1%;
        }
        /* STEP 1: ADD THIS CSS */
        .card.highlight-edit {
            border-color: #0d6efd;
            box-shadow: 0 0 15px rgba(13, 110, 253, 0.4);
        }
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
                    <!-- STEP 2: ADD THE ID HERE -->
                    <div class="card" id="operator-form-card">
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
                                    <label for="operator_name" class="form-label">Driver Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="operator_name" name="operator_name" value="<?php echo htmlspecialchars($operator_to_edit['operator_name'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="contact_person" class="form-label">Conductor Name</label>
                                    <input type="text" class="form-control" id="contact_person" name="contact_person" value="<?php echo htmlspecialchars($operator_to_edit['contact_person'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="contact_email" class="form-label">Contact Email</label>
                                    <input type="email" class="form-control" id="contact_email" name="contact_email" value="<?php echo htmlspecialchars($operator_to_edit['contact_email'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="contact_phone" class="form-label">Contact Phone</label>
                                    <input type="tel" class="form-control" id="contact_phone" name="contact_phone" value="<?php echo htmlspecialchars($operator_to_edit['contact_phone'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($operator_to_edit['address'] ?? ''); ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="Active" <?php echo (isset($operator_to_edit) && $operator_to_edit['status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                                        <option value="Inactive" <?php echo (isset($operator_to_edit) && $operator_to_edit['status'] == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary w-100"><?php echo $operator_to_edit ? 'Update Operator' : 'Save Operator'; ?></button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Manage Operators Table Column -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">Existing Operators</div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Driver Name</th>
                                            <th>conductor Name</th>
                                            <th>Phone</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($operators)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center">No operators found.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php $count = 1; foreach ($operators as $operator): ?>
                                                <tr>
                                                    <td><?php echo $count++; ?></td>
                                                    <td><?php echo htmlspecialchars($operator['operator_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($operator['contact_person'] ?: 'N/A'); ?></td>
                                                    <td><?php echo htmlspecialchars($operator['contact_phone'] ?: 'N/A'); ?></td>
                                                    <td>
                                                        <span class="badge <?php echo $operator['status'] == 'Active' ? 'bg-success' : 'bg-secondary'; ?>">
                                                            <?php echo $operator['status']; ?>
                                                        </span>
                                                    </td>
                                                    <td class="table-actions">
                                                        <a href="add_operator.php?action=edit&id=<?php echo $operator['operator_id']; ?>" class="btn btn-sm btn-info" title="Edit"><i class="fas fa-edit"></i></a>
                                                        <a href="add_operator.php?action=delete&id=<?php echo $operator['operator_id']; ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this operator? This cannot be undone.');">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
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

<!-- STEP 3: ADD THIS SCRIPT BLOCK -->
<?php if ($operator_to_edit): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const formCard = document.getElementById('operator-form-card');
        if (formCard) {
            // 1. Scroll the form into view smoothly
            formCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
            // 2. Add the highlight class to make it "glow"
            formCard.classList.add('highlight-edit');
            // 3. Focus the cursor on the first input field
            document.getElementById('operator_name').focus();
            // 4. Remove the glow after 2 seconds
            setTimeout(() => {
                formCard.classList.remove('highlight-edit');
            }, 2000);
        }
    });
</script>
<?php endif; ?>

</body>
</html>
<?php pdo_close_conn($_conn_db); ?>