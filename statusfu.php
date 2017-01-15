<?php
//error_reporting(0);
session_start();
if (!isset($_SESSION['username']) || $_SESSION['username']==''){
	$_SESSION = [];
	session_destroy();
	header("Location: ./index.php");
	exit();
}

$id = $_POST['id'];
$statusid = $_POST['statusid'];

if ($id=='' || ($statusid!=1 && $statusid!=2)){
	exit();
}

include ('./_dbconnect.inc.php');

$q = "UPDATE customerdata SET statusfu_idauto=".$statusid." WHERE idauto=".$id;
$r = mysqli_query($dblink,$q) or die(mysqli_error($dblink));

$arrResult = array(
	"status"=> true,
	"msgCode"=> "00",
	"msg"=>"Success"
);
echo json_encode($arrResult);
?>