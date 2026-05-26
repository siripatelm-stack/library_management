<?php

session_start();

/* Remove all session variables */
$_SESSION = array();

/* Destroy session */
session_destroy();

/* Prevent browser cache */
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

/* Redirect to login page */
header("Location: ./admin_login.php");

exit();

?>