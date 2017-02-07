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
		$body .= "<tr style='".$trStyle."'>";
		$body .= "<td>".$i."</td><td style='text-align:center'>".$d['datetime_create']."</td><td>".$d['name']."</td><td>".$d['phone']."</td><td>".$address."</td><td>".$mapslocation."</td><td>".$btslocation."</td>";
		$body .= "</tr>";
		
	}
}
?>

<body style="font-size:12px;">

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
</body>
