<?php
function validate_rhyno_data($data_rhyno,$adsls = 'true'){
  global $_conn_db;
  if(isset($data_rhyno)) {
      $data_rhyno = trim($data_rhyno ?? '');
      if($adsls == 'true') {
         $data_rhyno = addslashes($data_rhyno ?? '');
      }
      $data_rhyno = strip_tags($data_rhyno ?? '');
      $data_rhyno = htmlspecialchars($data_rhyno ?? '', ENT_QUOTES);
  } else {
      $data_rhyno='';
  }
  return $data_rhyno;    
 }

function validate_rhyno_number($data_rhyno){
  global $_conn_db;
  $data_rhyno = mysqli_real_escape_string($_conn_db,$data_rhyno ?? '');
  $data_rhyno = addslashes($data_rhyno ?? '');
  $data_rhyno = strip_tags($data_rhyno ?? '');
  $data_rhyno = htmlspecialchars($data_rhyno ?? '', ENT_QUOTES);
    $data_rhyno = trim($data_rhyno ?? '');
    if(empty($data_rhyno)){
        $data_rhyno = '0.00';
    }
  $data_rhyno = number_format($data_rhyno, 2, '.', '');
  return $data_rhyno;
 }
function show_rhyno_number($data_rhyno, $point = 'ok'){
    //$data_rhyno = str_replace(array('/-','/',','), '', $data_rhyno ?? '');
    $data_rhyno = preg_replace(array('/[^0-9.]+/'), array(''), $data_rhyno ?? '');
    $data_rhyno = nl2br($data_rhyno ?? '');
    $data_rhyno = stripslashes($data_rhyno ?? '');
    $data_rhyno = trim($data_rhyno ?? '');
    $data_rhyno = strip_tags($data_rhyno ?? '');
    $data_rhyno = htmlspecialchars_decode($data_rhyno ?? '', ENT_QUOTES);
    if(empty($data_rhyno)){
        $data_rhyno = (int)0;
    }
    if((int)$data_rhyno) {
        if($point == 'ok') {
            $data_rhyno = number_format($data_rhyno ?? 0, 2, '.', '');
        }else {(int)$data_rhyno;}
    }
    return $data_rhyno;
}
function show_rhyno_data($data_rhyno){
    $data_rhyno = str_replace(array('\r\n','\\r\\n', '\r',  '\\r', '\\n', '\n', '\\t', '\t'), ' ', $data_rhyno ?? '');
    $data_rhyno = nl2br($data_rhyno ?? '');
    $data_rhyno = stripslashes($data_rhyno ?? '');
    $data_rhyno = trim($data_rhyno ?? '');
    $data_rhyno = strip_tags($data_rhyno ?? '');
    $data_rhyno = html_entity_decode(htmlentities($data_rhyno ?? '', ENT_QUOTES, 'UTF-8'), ENT_QUOTES , 'ISO-8859-15');
    $data_rhyno = htmlspecialchars_decode($data_rhyno ?? '', ENT_QUOTES);
    if(empty($data_rhyno)){
        $data_rhyno = '';
    }
    return $data_rhyno;
}

function rhyno_date_dmy($data_rhyno){
    if(!empty($data_rhyno) || $data_rhyno != "") {
        $data_rhyno = date('d-m-Y', strtotime($data_rhyno ?? ''));
    }
    return $data_rhyno;
};
function rhyno_date_dM($data_rhyno){
    $data_rhyno = date('d-M',strtotime($data_rhyno ?? ''));
    return $data_rhyno;
};
function rhyno_date_ymd($data_rhyno){
    $data_rhyno = date('Y-m-d',strtotime($data_rhyno ?? ''));
    return $data_rhyno;
}
function rhyno_time_seconds($data_rhyno){
    $data_rhyno = date('h:i:s',strtotime($data_rhyno ?? ''));
    return $data_rhyno;
}
function rhyno_time_24($data_rhyno){
    $data_rhyno = date('H:i',strtotime($data_rhyno ?? ''));
    return $data_rhyno;
}
function rhyno_datetime_show($data_rhyno){
    $data_rhyno = date('d-m-Y | h:i A',strtotime($data_rhyno ?? ''));
    return $data_rhyno;
}
function rhyno_datemonth_show($data_rhyno){
    $data_rhyno = date('d-M',strtotime($data_rhyno ?? ''));
    return $data_rhyno;
}
function rhyno_date_show($data_rhyno){
    $data_rhyno = date('d',strtotime($data_rhyno ?? ''));
    return $data_rhyno;
}
function rhyno_time_show($data_rhyno){
    $data_rhyno = date('h:i A',strtotime($data_rhyno ?? ''));
    return $data_rhyno;
}
function rhyno_month_show($data_rhyno){
    $data_rhyno = date('M',strtotime($data_rhyno ?? ''));
    return $data_rhyno;
}
function rhyno_date_by_time($data_rhyno){
    $data_rhyno = date("d-m-Y | h:i A", $data_rhyno ?? '');
    return $data_rhyno;
}
function rhyno_date_by_time_dmy($data_rhyno){
    $data_rhyno = date("d-m-Y", $data_rhyno ?? '');
    return $data_rhyno;
}
function rhyno_ymd_by_time_dmy($data_rhyno){
    $data_rhyno = date("Y-m-d", $data_rhyno ?? '');
    return $data_rhyno;
}
function rhyno_by_time($data_rhyno){
    $data_rhyno = date("h:i A", $data_rhyno ?? '');
    return $data_rhyno;
}

function rhyno_new_token($login_id){
    global $_conn_db;
    global $ip;
    $token = rand(100000,999999);
    $data  = password_hash($token, PASSWORD_BCRYPT, ['cost' => 12,]);
    $stmt = $_conn_db->prepare(
        "INSERT INTO user_login_token (user_id, token, ip_address) VALUES (:user_id, :token, :ip_address)"
    );
    $stmt->execute([
        'user_id'    => $login_id,
        'token'      => $data,
        'ip_address' => $ip
    ]);
    return $data;
}
function user_logout(){

// Destroy all session variables and the session itself
    session_unset();  // Unset all session variables
    session_destroy();  // Destroy the session

// Redirect the user to the login page after logout
    echo "<script>window.location = 'index.php'</script>";
    exit();
}
function check_user_login($type = 'default', $describe = '')
{
    global $_conn_db ; // use global PDO instance

    if (!$_conn_db) {
        die('Database connection not established.');
    }

    if (
        isset($_SESSION['user']['token'], $_SESSION['user']['login'], $_SESSION['user']['id']) &&
        $_SESSION['user']['login'] === 'true'
    ) {
        $login_token = $_SESSION['user']['token'];
        $user_id = $_SESSION['user']['id'];

        $stmt = $_conn_db->prepare(
            "SELECT 1 FROM user_login_token WHERE user_id = :user_id AND token = :token AND status = '1' LIMIT 1"
        );
        $stmt->execute([
            'user_id' => $user_id,
            'token' => $login_token
        ]);
        $valid = $stmt->fetchColumn();

        if (!$valid) {
            handle_login_fail($type, $describe);
        }
    } else {
        handle_login_fail($type, $describe);
    }
}


function handle_login_fail($type, $describe = '')
{
    if ($type === 'default') {
        user_logout();
    } elseif ($type === 'json') {
        $dataResult = [
            'page'        => 'all',
            'res'         => 'false',
            'notif_title' => 'Oops!',
            'notif_desc'  => 'Please reload the page and try again',
            'notif_type'  => 'danger',
            'goTo'        => ''
        ];
        header('Content-Type: application/json');
        echo json_encode($dataResult);
        exit();
    } else {
        echo $describe;
    }
}

function check_user_token_match($token_get)
{
    // Check login, will auto-return JSON/exit on fail
    check_user_login('json');

    // Now check token in session vs provided token
    if (
        !isset($token_get)
        || !isset($_SESSION['user']['token'])
        || $token_get !== $_SESSION['user']['token']
    ) {
        $dataResult = [
            'page'        => 'all',
            'res'         => 'false',
            'notif_title' => 'Oops!',
            'notif_desc'  => 'Please reload the page and try again',
            'notif_type'  => 'danger',
            'goTo'        => ''
        ];
        header('Content-Type: application/json');
        echo json_encode($dataResult);
        exit();
    }
    // Valid!
    return true;
}
function user_login_index_check() {
    if (isset($_SESSION['user']['token'])) {
        echo "<script>window.location = 'dashboard.php'</script>";
        exit();
    }
}
function pdo_close_conn(&$conn) {
    $conn = null;
    exit();
}


?>
