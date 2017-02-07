<?php

error_reporting(0);

session_start();

if (!isset($_SESSION['username']) || $_SESSION['username']==''){
	$_SESSION = [];
	session_destroy();
	header("Location: ./index.php");
	exit();
}


include ('./_dbconnect.inc.php');

$agent = "Opera/10.61 (J2ME/MIDP; Opera Mini/5.1.21219/19.999; en-US; rv:1.9.3a5) WebKit/534.5 Presto/2.6.30";

function getBTS($lon, $lat){
	//CURL
	if ($lon=='' || $lat=='') return null;
	
	$header[] = 'Content-type:application/json';
	$url = getenv('CURL_URL');
	$ch = curl_init();
	

	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLINFO_HEADER_OUT, false);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_USERAGENT, $GLOBALS['agent']);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_VERBOSE, true);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS,'{"latitude":'.$lon.',"longitude":'.$lat.'}'); 
	
	$result = json_decode(curl_exec($ch),true);
	//echo "HERE";
	$result = $result['data'];
	return $result;
}

$q = "SELECT * FROM customerdata ORDER BY idauto DESC;";
$r = mysqli_query($dblink,$q) or die(mysqli_error($dblink));

$dashboardTotal = mysqli_num_rows($r);
$dashboardToday = 0;
$dashboardFollowup = 0;

$today = date('Y-m-d',time());

$i=0;
$body = "";
if ($dashboardTotal>0){
	while($d=mysqli_fetch_assoc($r)){
		$i++;
		$trStyle = ($i%2==0)?'background-color: #fffbe7;color: #555;':'background-color: #ffffff;color: #555;';
		$id = $d['idauto'];
		$idauto[] = $id;
		$lonlat = explode(",",$d['lonlat']);
		//get BTS terdekat
		if ($d['lonlat']!=''){
			$btsData = getBTS($lonlat[0], $lonlat[1]);
			$btslocation = "4G:" . $btsData['siteName4g'] . "(".number_format($btsData['distance4g'],3)."Km) - " . "3G:" . $btsData['siteName3g'] . "(".number_format($btsData['distance3g'],3)."Km) " ;
		} else {
			$btslocation = "-";
		}
		
		if (date('Y-m-d',strtotime($d['datetime_create']))==$today){
			$dashboardToday++;
		}
		
		if ($d['statusfu_idauto']==2){
			$dashboardFollowup++;
		}
		
		$address = ($d['address']!=''?$d['address']:'-');
		$mapslocation = $d['lonlat']!=''?'<a target="_blank" href="http://maps.google.com/maps?&z=10&q='.$lonlat[0].'+'.$lonlat[1].'&ll='.$lonlat[0].'+'.$lonlat[1].'">Maps</a>':'-';
		$action = $d['statusfu_idauto']==1?'<button class="button" onclick="javascript:statusfu(this,'.$d['idauto'].',2);">FOLLOW UP</button>':'<button class="button button-green" onclick="javascript:statusfu(this,'.$d['idauto'].',1);">&#10004; DONE</button>';
		$body .= "<tr style='".$trStyle."'>";
		$body .= "<td>".$i."</td><td style='text-align:center'>".$d['datetime_create']."</td><td>".$d['name']."</td><td>".$d['phone']."</td><td>".$address."</td><td>".$mapslocation."</td><td>".$btslocation."</td><td style='text-align:center'>".$action."</td>";
		$body .= "</tr>";
		
	}
}
?>
<!DOCTYPE html>
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

  <!-- FONT
  ------------------------- -->
  <!--link href="//fonts.googleapis.com/css?family=Raleway:400,300,600" rel="stylesheet" type="text/css"-->

  <!-- CSS
  ------------------------- -->
  <link rel="stylesheet" href="normalize.css">
  <link rel="stylesheet" href="skeleton.css">

  <!-- Favicon
  ------------------------- -->
  <link rel="shortcut icon" type="image//vnd.microsoft.icon" href="favicon.ico">
  <script src="jquery.js"></script>
  <script language="javascript">
		statusfu = function(_this, _id, _statusid){
			$.ajax({
				url:'./statusfu.php',
				type:'post',
				dataType:'json',
				data:'&id='+ _id+ '&statusid=' + _statusid,
				success:function(json){
					if (json.status){
						$(_this).removeClass();
						if (_statusid==2){
							$(_this).html('&#10004; DONE');
							$(_this).addClass('button button-green');
							$(_this).attr('onclick','javascript:statusfu(this,'+ _id +',1);');
						} else {
							$(_this).html('FOLLOW UP');
							$(_this).addClass('button');
							$(_this).attr('onclick','javascript:statusfu(this,'+ _id +',2);');
						}
					} else {
						alert('[ERROR] Something went wrong!');
					}
				}
			});
		}
  </script>
</head>
<style>
.boxPrint{
	border: 1px solid #E1E1E1;
    border-radius: 4px;
	font-size:14px; 
	text-align:center;
	margin-top:20px;
	min-height:100px;
	padding:10px;
}

.dashboardFont{
	font-size:28px;
	font-weight:bold;
}
</style>
<body style="font-size:12px;">

<!-- Primary Page Layout
------------------------- -->
<div class="container">
	<div class="row">
		<div class="value-props row">
			<div class="boxPrint four columns value-prop">
				Total:
				<div class="dashboardFont" id="dashboardTotal"><?php echo $dashboardTotal;?></div>
			</div>
			<div class="boxPrint four columns value-prop">
				Followed up:
				<div class="dashboardFont" id="dashboardFollowup"><?php echo $dashboardFollowup;?></div>
			</div>
			<div class="boxPrint four columns value-prop">
				New Today:
				<div class="dashboardFont" id="dashboardToday"><?php echo $dashboardToday;?></div>
			</div>
		</div>
	</div>
	<div class="row">
		<center>
			<div class="column" style="margin-top: 3%">
				<div style="position:relative; overflow: show">
					<br /><br />
					<h6 style="color:#777; font-weight:bold;">Welcome <?php echo $_SESSION['username'];?> (<a href="./logout.php">Logout</a>),</h6>
					<table style='background-color: #ffe486; font-size:12px; font-family:tahoma' cellpadding='4' cellspacing='1' border='0'>
						<tr><th>No.</th><th style='text-align:center'>Datetime (Descending)</th><th>Name</th><th>Phone</th><th>Address</th><th>Maps</th><th>BTS</th><th style='text-align:center'>Follow Up</th></tr>
						<?php
						if ($dashboardTotal>0){
							echo $body;
						} else {
							?>
							<tr><td colspan="7"><i>-no data-</i></td></tr>
							<?php
						}
						?>
					</table>
				</div>
			</div>
		</center>
	</div>
</div>

<!-- End Document
  ------------------------- -->
</body>
</html>
