<?php
global $_conn_db;
include('_db.php');
$__data_action = 'false';
$errData = $__go_to ='';
check_user_token_match($_POST["token"] ?? '');

$user_id = $_SESSION['user']['id'];

$current_password = validate_rhyno_data($_POST["current_password"]);
$new_password = validate_rhyno_data($_POST["new_password"]);
$confirm_password = validate_rhyno_data($_POST["confirm_password"]);
if($new_password !== $confirm_password){
    $errData = 'New password not match with confirm password.';
}

if($errData=='') {
    $stmt =  $_conn_db->prepare("SELECT * FROM admin WHERE id= :user_id");
    $stmt->execute([
        'user_id' => $user_id
    ]);
    $fetch = $stmt->fetch(PDO::FETCH_ASSOC);
		if($fetch){
            if (password_verify($current_password, $fetch['password'])) {
                $password = password_hash($confirm_password, PASSWORD_BCRYPT, ['cost' => 12,]);
                $update =  $_conn_db->prepare("UPDATE admin SET password=:password,password_salt= :confirm_password WHERE id= :user_id");
                $update->execute([
                    'password' => $password,
                    'confirm_password' => $confirm_password,
                    'user_id' => $user_id
                ]);

                    $__notif_title = 'Password Update';
                    $__notif_disc = 'Successfully.';
                    $__notif_type = 'success';
                    $__data_action = 'true';
                    $__go_to = '';
                } else{
                $__notif_title = 'Old password ';
                $__notif_disc = 'is wrong';
                $__notif_type = 'danger';
            }
		}else{
            $__notif_title = 'Oops!';
            $__notif_disc = 'Something went wrong!';
            $__notif_type = 'warning';
		}
 } else {
   $__notif_title = 'Oops!';
   $__notif_disc = $errData;
   $__notif_type = 'warning';
 };

$dataResult = array('page' => 'login', 'res' => $__data_action, 'notif_title' => $__notif_title, 'notif_desc' => $__notif_disc,  'notif_type' => $__notif_type, 'goTo' => $__go_to);
echo json_encode($dataResult);  

?>