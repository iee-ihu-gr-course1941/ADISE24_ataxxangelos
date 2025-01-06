<?php
$DB_HOST = 'localhost'; 
$DB_USER = 'root'; 
$DB_PASS = '';  
$DB_NAME = 'ataxx';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if(gethostname()=='users.iee.ihu.gr') {
	$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, null,'/home/student/iee/2019/iee2019002/mysql/run/mysql.sock');
} else {
		$DB_PASS='';
        $mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
}

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$mysqli->set_charset("utf8");
?>