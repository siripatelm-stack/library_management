<?php
// Enable clear error reporting so we can spot syntax bugs instantly
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Railway automatically feeds these dynamic variables to your server environment
$servername = getenv('MYSQLHOST');
$username   = getenv('MYSQLUSER');
$password   = getenv('MYSQLPASSWORD');
$dbname     = getenv('MYSQLDATABASE');
$port       = getenv('MYSQLPORT');

// Establish the connection including the required production network port
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Safely trap database connection faults without throwing a generic HTTP 500 error
if ($conn->connect_error) {
    die("Cloud Database connection failed: " . $conn->connect_error);
}

// Ensure the database handles your Unicode string characters smoothly (like email strings)
$conn->set_charset("utf8mb4");
?>
