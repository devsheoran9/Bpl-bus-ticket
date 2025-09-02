<?php
session_start();

// Alisin sa set ang lahat ng mga variable ng session
$_SESSION = array();

// Sirain ang session
session_destroy();

// Mag-redirect sa home page
header("Location: index.php");
exit;
?>