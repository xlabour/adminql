<?php
$DB_HOST = getenv('MYSQL_SERVICE_HOST');
$DB_NAME = getenv('MYSQL_DATABASE');
$DB_USERNAME = getenv('MYSQL_USER');
$DB_PASSWORD = getenv('MYSQL_PASSWORD');
$dblink = mysqli_connect($DB_HOST, $DB_USERNAME, $DB_PASSWORD) or die('Could not connect: ' . mysqli_error($dblink));
mysqli_select_db($dblink, $DB_NAME) or die('ERRDB');
?>
