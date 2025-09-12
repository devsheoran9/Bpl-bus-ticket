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
function user_has_role($role) {
    // Check if the user is logged in and the 'type' is set in the session
    if (isset($_SESSION['user']['login'], $_SESSION['user']['type']) && $_SESSION['user']['login'] === 'true') {
        // Return true if the user's type matches the required role
        return $_SESSION['user']['type'] === $role;
    }
    // Return false if the user is not logged in or the type is not set
    return false;
}
function check_user_login($allowed_roles = [])
{
    global $_conn_db;

    if (!$_conn_db) {
        // This is a fatal error, so die is appropriate.
        die('Database connection not established.');
    }

    // Check if the basic session variables are set
    if (
        !isset($_SESSION['user']['token'], $_SESSION['user']['login'], $_SESSION['user']['id'], $_SESSION['user']['type']) ||
        $_SESSION['user']['login'] !== 'true'
    ) {
        handle_login_fail(); // Redirect if not logged in at all
    }

    // --- Core Login Validation ---
    $login_token = $_SESSION['user']['token'];
    $user_id = $_SESSION['user']['id'];

    try {
        $stmt = $_conn_db->prepare(
            "SELECT 1 FROM user_login_token WHERE user_id = :user_id AND token = :token AND status = '1' LIMIT 1"
        );
        $stmt->execute([
            'user_id' => $user_id,
            'token' => $login_token
        ]);
        $is_valid_token = $stmt->fetchColumn();

        if (!$is_valid_token) {
            handle_login_fail(); // Redirect if token is invalid or expired
        }
    } catch (PDOException $e) {
        // Handle database errors during the check
        error_log("Login check failed: " . $e->getMessage());
        handle_login_fail();
    }
    
    // --- NEW: Role-Based Access Control ---
    // If the function was called with a list of allowed roles...
    if (!empty($allowed_roles)) {
        $user_type = $_SESSION['user']['type'];

        // Check if the user's role is in the list of allowed roles
        if (!in_array($user_type, $allowed_roles)) {
            // User is logged in, but does not have permission for this page
            $_SESSION['notif_type'] = 'error';
            $_SESSION['notif_title'] = 'Access Denied';
            $_SESSION['notif_desc'] = 'You do not have the required permissions to view this page.';
            
            // Redirect to a safe, default page like the dashboard
            header("Location: dashboard.php");
            exit();
        }
    }
    // If we reach here, the user is logged in and has the correct role (if one was required).
}


/**
 * Handles the session destruction and redirect on login failure.
 * (This helper function should be in the same file)
 */
function handle_login_fail()
{
    // Clear all session data to be safe
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();

    // Redirect to the login page
    header("Location: index.php"); // Or your login page
    exit();
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
