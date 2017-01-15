<?php
//error_reporting(0);
session_start();

if (isset($_SESSION['username']) && $_SESSION['username']!=''){
	header("Location: ./admin.php");
	exit();
}

$username = $_POST['username'];
$password = $_POST['password'];
$submit = $_POST['submit'];

if ($submit=='' || strtolower($submit)!='login' || ($username=='' || $password=='')){
	echo "Unknown Error";
	exit();
}

//do login
include ('./_dbconnect.inc.php');

$q = "
	SELECT * 
	FROM cs 
	WHERE 
		username='".mysqli_real_escape_string($dblink,$username)."' AND 
		password='".md5(mysqli_real_escape_string($dblink,$password))."' AND
		status=1
";

$r = mysqli_query($dblink,$q) or die(mysqli_error($dblink));

if (mysqli_num_rows($r)==1){
	$d = mysqli_fetch_assoc($r);
	$_SESSION['username']=$d['username'];
	$login = true;
	$redir = '<meta http-equiv="refresh" content="3; url=admin.php" />';
	$msg = '<h6><br/><center>Selamat datang <strong>'.$username.'</strong><br />Login Berhasil!<br /><a class="button button-primary" href="admin.php" >Halaman Admin &raquo;</a></center></h6>';
} else {
	$login=false;
	$redir = '<meta http-equiv="refresh" content="5; url=index.php" />';
	$msg = '<h6><br/><center>Login Gagal!<br /><a class="button button-primary" href="index.php">&laquo; Kembali</a></center></h6>';
}

mysqli_close($dblink);

?>
<html lang="en">
<head>

  <!-- Basic Page Needs
  ------------------------- -->
  <meta charset="utf-8">
  <title>XL m-Ads :: Admin</title>
  <meta name="description" content="">
  <meta name="author" content="">

  <!-- Mobile Specific Metas
  ------------------------- -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php echo $redir;?>
  <!-- FONT
  ------------------------- -->
  <!--link href="//fonts.googleapis.com/css?family=Raleway:400,300,600" rel="stylesheet" type="text/css"-->

  <!-- CSS
  ------------------------- -->
  <link rel="stylesheet" href="normalize.css">
  <link rel="stylesheet" href="skeleton.css">

  <!-- Favicon
  ------------------------- -->
  <link rel="icon" type="image/png" href="favicon.png">

</head>
<body>
<?php echo $msg;?>
<!-- End Document
  ------------------------- -->
</body>
</html>