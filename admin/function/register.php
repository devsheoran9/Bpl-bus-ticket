<?php
include('_db.php');
$__data_action = 'false';
$errData =$errData_show= $__go_to ='';

$yourname = validate_rhyno_data($_POST["yourname"]);
$mobile = validate_rhyno_data($_POST["mobile"]);
$email= validate_rhyno_data($_POST["email"]);
$password_salt = validate_rhyno_data($_POST["password"]);
$password = password_hash($password_salt, PASSWORD_BCRYPT, ['cost' => 12,]);

if($errData=='') {
	$query =  mysqli_query($_conn_db, "SELECT * FROM users WHERE email = '$email' or mobile = '$mobile'");
	$row = mysqli_num_rows($query);
		if($row > 0){
            $__notif_title = 'User already exist';
            $__notif_disc = 'Please change mobile number/email';
            $__notif_type = 'danger';

		}else{

            mysqli_query($_conn_db, "INSERT INTO users( name, mobile, email, password, password_salt, ip_address) VALUES ('$yourname','$mobile','$email','$password','$password_salt','$ip')");
            $newid = $_conn_db->insert_id;

            $_SESSION['user']['token'] = rhyno_new_token($newid);

            $_SESSION['user']['id'] = show_rhyno_data($newid);
            $_SESSION['user']['login'] = 'true';
            $_SESSION['user']['name'] = show_rhyno_data($yourname);
            $_SESSION['user']['email'] = show_rhyno_data($email);
            $_SESSION['user']['mobile'] = show_rhyno_data($mobile);

            $__notif_title = 'Login successfully!';
            $__notif_disc = 'Please wait for dashboard...';
            $__notif_type = 'success';
            $__data_action = 'true';
            $__go_to = 'dashboard';
		}
 }
	  else
 {
   $__notif_title = 'Oops!';
   $__notif_disc = $errData_show;
   $__notif_type = 'warning';
 };

$dataResult = array('page' => 'login', 'res' => $__data_action, 'notif_title' => $__notif_title, 'notif_desc' => $__notif_disc,  'notif_type' => $__notif_type, 'goTo' => $__go_to);
echo json_encode($dataResult);  

?>