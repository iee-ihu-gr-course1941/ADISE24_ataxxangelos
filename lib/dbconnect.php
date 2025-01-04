<?php
// Database connection variables
$DB_HOST = 'localhost'; // Database host, typically localhost
$DB_USER = 'root';      // Database username (change it as needed)
$DB_PASS = '';          // Database password (change it as needed)
$DB_NAME = 'test';// Database name (replace with your actual database name)

// Create a new connection to the MySQL database
$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if(gethostname()=='users.iee.ihu.gr') {
	$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME,null,'/home/staff/asidirop/mysql/run/mysql.sock');
} else {
		$DB_PASS='';
        $mysqli = new mysqli($host, $user, $pass, $db);
}

// Check if the connection was successful
if ($mysqli->connect_error) {
    // If connection fails, show an error message
    die("Connection failed: " . $mysqli->connect_error);
}

// Set character encoding to UTF-8 to handle any special characters
$mysqli->set_charset("utf8");
?>