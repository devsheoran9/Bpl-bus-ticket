<?php
include('_db.php');
$__data_action = 'false';
$errData = $__go_to ='';

$username = validate_rhyno_data($_POST["username"]);
$password = validate_rhyno_data($_POST["password"]);

if ($errData == '') {
    $stmt = $_conn_db->prepare("SELECT * FROM users WHERE email = :emails OR mobile = :mobiles");
    $stmt->execute([
        'emails' => $username,
        'mobiles' => $username
    ]);
    $fetch = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($fetch) {
        if (password_verify($password, $fetch['password'])) {
            $status = show_rhyno_data($fetch['status']);
            if ($status == '1') {
                $login_id = $_SESSION['user']['id'] = show_rhyno_data($fetch['id']);
                $login_token = $_SESSION['user']['token'] = rhyno_new_token($login_id);

                $_SESSION['user']['login']  = 'true';
                $_SESSION['user']['name']   = show_rhyno_data($fetch['name']);
                $_SESSION['user']['email']  = show_rhyno_data($fetch['email']);
                $_SESSION['user']['mobile'] = show_rhyno_data($fetch['mobile']);

                $__notif_title  = 'Login successfully!';
                $__notif_disc   = 'Please wait for dashboard...';
                $__notif_type   = 'success';
                $__data_action  = 'true';
                $__go_to        = 'dashboard';
            } else {
                $__notif_title = 'Your account is temporary deactivate';
                $__notif_disc  = 'Please contact us for reactivate your account';
                $__notif_type  = 'danger';
            }
        } else {
            $__notif_title = 'Invalid password';
            $__notif_disc  = 'Please enter a valid password';
            $__notif_type  = 'danger';
        }
    } else {
        $__notif_title = 'Invalid mobile number or email';
        $__notif_disc  = 'Please enter a valid number or email';
        $__notif_type  = 'danger';
    }
}else {
   $__notif_title = 'Oops!';
   $__notif_disc = $errData;
   $__notif_type = 'warning';
 }

$dataResult = array('page' => 'login', 'res' => $__data_action, 'notif_title' => $__notif_title, 'notif_desc' => $__notif_disc,  'notif_type' => $__notif_type, 'goTo' => $__go_to);
echo json_encode($dataResult);  

?>