<?php
global $_conn_db;
include_once('function/_db.php');
check_user_login();

$name = $_SESSION['user']['name'];
$email = $_SESSION['user']['email'];
$mobile = $_SESSION['user']['mobile'];

$current_page = basename($_SERVER['PHP_SELF']);
$is_money_active =$is_transaction_active=$is_cash_active=$is_contact_active='';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once('head.php');?>
</head>
<body>

<div id="wrapper">
    <?php include_once('sidebar.php');?>
    <div class="main-content">
        <?php include_once('header.php');?>
        <div class="container-fluid ">
            <h2 class="mb-4 mt-4 text-center">Add New Bus</h2>
            <form class="data-form" action="function/backend/bus_actions.php" method="POST" enctype="multipart/form-data" data-parsley-validate>
                <input type="hidden" name="action" value="add_bus">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="bus_name" class="form-label">Bus Name/Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="bus_name" name="bus_name" required data-parsley-trigger="change">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="registration_number" class="form-label">Registration Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="registration_number" name="registration_number" required data-parsley-trigger="change">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="operator_id" class="form-label">Bus Operator <span class="text-danger">*</span></label>
                        <select class="form-select" id="operator_id" name="operator_id" required>
                            <option value="">Select Operator</option>
                            <?php
                            try {
                                $stmt = $_conn_db->query("SELECT operator_id, operator_name FROM operators WHERE status = 'Active'");
                                while ($row = $stmt->fetch()) {
                                    echo '<option value="' . htmlspecialchars($row['operator_id']) . '">' . htmlspecialchars($row['operator_name']) . '</option>';
                                }
                            } catch (PDOException $e) {
                                error_log("Error fetching operators: " . $e->getMessage());
                                echo '<option value="">Error loading operators</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="bus_type" class="form-label">Bus Type <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="bus_type" name="bus_type" placeholder="e.g., AC Seater, Volvo Sleeper" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="total_seats" class="form-label">Total Seats (Initial) </label>
                        <input type="number" class="form-control" id="total_seats" name="total_seats" min="0" data-parsley-type="number" value="0">
                        <small class="text-muted">This will be updated by seat layout management.</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="seater_seats" class="form-label">Seater Seats (Initial)</label>
                        <input type="number" class="form-control" id="seater_seats" name="seater_seats" min="0" data-parsley-type="number" value="0">
                        <small class="text-muted">Will be updated by seat layout.</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="sleeper_seats" class="form-label">Sleeper Seats (Initial)</label>
                        <input type="number" class="form-control" id="sleeper_seats" name="sleeper_seats" min="0" data-parsley-type="number" value="0">
                        <small class="text-muted">Will be updated by seat layout.</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="bus_image" class="form-label">Bus Image</label>
                    <input type="file" class="form-control" id="bus_image" name="bus_image" accept="image/*">
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                        <option value="Under Maintenance">Under Maintenance</option>
                        <option value="Retired">Retired</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary ladda-button p-1 submit-btn" data-style="zoom-in"><span class="ladda-label">Add Bus & Continue to Seat Layout <i id="icon-arrow" class="bx bx-right-arrow-alt"></i></span> <span class="ladda-spinner"></span> </button>
            </form>
        </div>
    </div>
</div>
<?php include_once('foot.php');?>
</body>
</html>
<?php pdo_close_conn($_conn_db); ?>