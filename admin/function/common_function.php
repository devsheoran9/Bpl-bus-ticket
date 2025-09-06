<?php
//date and time
date_default_timezone_set('Asia/Kolkata');
$dateTimeForInsert = date('Y-m-d H:i:s');
$TimeForInsert = date('H:i:s');
$dateTimeWithSecond = date('d-m-Y | h:i:s');
$showDatedmY = date('d-m-Y');
$showTimehiA = date('h:i A');
$showDateTime = date('d-m-Y | h:i A');
$dateTime = date('d-m-Y | h:i A');
$dateMonth = date('d-M');
$date = date('d');
$month = date('M');
$year = date('Y');
$currentTime = time();
$midnight_12am_next = strtotime('tomorrow midnight');
$midnight_12am_today = strtotime('today midnight');
//ip
$ip = $_SERVER['REMOTE_ADDR'];// Obtains the IP address
$localIp = gethostbyaddr($ip);//local ip
//for link

$scheme = $_SERVER['REQUEST_SCHEME'].'://';//http or https
$http = $_SERVER['REQUEST_SCHEME'];//http or https
$basename = basename($_SERVER['PHP_SELF']);//filename
$host = $_SERVER['HTTP_HOST'];//web name
$uri = rtrim(dirname($basename),'/\\');// after web name all folders
$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : ''; //detect mobile/computer/tab and browser

$parsed_url = parse_url($_SERVER['PHP_SELF']);
$parsed_url_path =  $parsed_url['path'];
preg_match('@/(?<path>[^/]+)@', $parsed_url_path, $m);
$url_folder = $m['path'];//1st folder name
$pageName = rtrim($basename,'.php');
$full_location = "$scheme$host/$url_folder";//full location with url
$full_location_share = "$scheme$host/$url_folder";//full location with url
$limit = 8;
$quotation_limit = 9;
$location = dirname($parsed_url_path);// Append the requested resource location to the URL
function user_has_permission($permission) {
    // Check if user is logged in and has permissions array
    if (!isset($_SESSION['user']['login']) || !isset($_SESSION['user']['permissions'])) {
        return false;
    }

    // A main admin with "all_access" can do anything
    if (!empty($_SESSION['user']['permissions']['all_access'])) {
        return true;
    }

    // Check for the specific permission
    return !empty($_SESSION['user']['permissions'][$permission]);
}

/**
 * Secures a page by checking for a permission. If the user doesn't have it,
 * it redirects them to the dashboard with an error message.
 *
 * @param string $permission  
 */
function check_permission($permission) {
    if (!user_has_permission($permission)) {
        // You can set a session flash message here to show an error
        $_SESSION['flash_message'] = [
            'type' => 'danger',
            'message' => 'You do not have permission to access this page.'
        ];
        header('Location: dashboard.php');
        exit();
    }
}
function session_security_check() {
    global $_conn_db;

    // यदि सत्र लॉगिन चर नहीं है, तो लॉगिन पेज पर पुनर्निर्देशित करें
    if (!isset($_SESSION['user']['login']) || $_SESSION['user']['login'] !== 'true' || !isset($_SESSION['user']['session_token'])) {
        header('Location: index.php');
        exit();
    }

    try {
        $stmt = $_conn_db->prepare("SELECT session_token FROM admin WHERE id = ?");
        $stmt->execute([$_SESSION['user']['id']]);
        $db_token = $stmt->fetchColumn();

        // यदि डेटाबेस टोकन NULL है (जबरन लॉगआउट) या सत्र से मेल नहीं खाता है, तो सत्र को नष्ट कर दें।
        if ($db_token === NULL || $db_token !== $_SESSION['user']['session_token']) {
            session_destroy();
            header('Location: index.php?reason=invalid_session');
            exit();
        }
    } catch (PDOException $e) {
        // डेटाबेस त्रुटि की स्थिति में, उपयोगकर्ता को लॉग आउट करना सुरक्षित है।
        error_log("Session security check failed: " . $e->getMessage());
        session_destroy();
        header('Location: index.php?reason=session_error');
        exit();
    }
}

include_once ('other_functions.php');
?>