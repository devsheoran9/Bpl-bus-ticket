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
            <button class="btn btn-main-blue ladda-button p-1 p-1 submit-btn" data-style="zoom-in" type="submit" name="action"><span class="ladda-label">SUbmit<i id="icon-arrow" class="bx bx-right-arrow-alt"></i></span> <span class="ladda-spinner"></span> </button>

        </div>
    </div>
</div>
<?php include_once('foot.php');?>
</body>
</html>
<?php pdo_close_conn($_conn_db); ?>
