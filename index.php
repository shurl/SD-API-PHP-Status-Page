<?php
	namespace serverdensity;
	use DateTime;
	require __DIR__.'/vendor/autoload.php';
	use serverdensity\Client;
	include "config.php";
	$client = new Client();
	$client->authenticate($TOKEN);
	$currTime = time();
	$allDevices = $client->api('devices')->all();
	$allServices = $client->api('services')->all();		
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
		<meta name="description" content="">
		<meta name="author" content="">
		<title><? echo $brandName;?> Status Page</title>
		<!-- Latest compiled and minified CSS -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" integrity="sha512-dTfge/zgoMYpP7QbHy4gWMEGsbsdZeCXz7irItjcC3sPUFtf0kuFbDz/ixG7ArTxmDjLXDmezHubeNikyKGVyQ==" crossorigin="anonymous">
		<!-- Optional theme -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css" integrity="sha384-aUGj/X2zp5rLCbBxumKTCw2Z50WgIr1vs/PFN4praOTvYXWlVyh2UtNUU0KAUhAX" crossorigin="anonymous">
		<!-- Custom styles for this template -->
		<link href="styles.css" rel="stylesheet">
		<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
		<!--[if lt IE 9]>
			<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
			<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>
	<body>
		<nav class="navbar navbar-inverse navbar-fixed-top">
			<div class="container">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="<? echo $brandURL;?>"><? echo $brandName;?></a>
				</div>
				<div id="navbar" class="collapse navbar-collapse">
					<ul class="nav navbar-nav">
						<li><a href="#servers">Servers</a></li>
						<li><a href="#services">Services</a></li>
					</ul>
				</div><!--/.nav-collapse -->
			</div>
		</nav>
		<div class="container">
			<div class="row">
				<div id="" class="col-md-8 col-md-offset-2">
					<h1><? echo $pageH1;?></h1>
				</div>	
				<div id="servers" class="col-md-8 col-md-offset-2">
				<h2>Servers</h2>
				<?php	  
					foreach($allDevices as $value){
							$devId = $value["_id"];
							$alertsArr = $client->api('alerts')->triggered($closed=false, 'device', $devId);
							$lastAlert = $client->api('alerts')->triggered($closed=true, 'device', $devId);	
							$alertTimes = array(); 
							foreach ($lastAlert as $alert) {    
								$alertTimes[] = $alert["firstTriggeredAt"]["sec"];
							}
							array_multisort($alertTimes, SORT_DESC, $lastAlert);
							$openAlerts = array(); 
							foreach ($alertsArr as $alert) {    
								$openAlerts[] = $alert["firstTriggeredAt"]["sec"];
							}
							array_multisort($openAlerts, SORT_DESC, $alertsArr);
							$lastPayloadAt = $value["lastPayloadAt"]["sec"];
							$lastAlertTime = $lastAlert[0]["firstTriggeredAt"]["sec"];
							$lastFixTime = $lastAlert[0]["fixedAt"]["sec"];
							$isMon = $value["isMonitored"];			
							if($lastPayloadAt > $currTime - 80){
								$status = 'panel-success';
								$statusText = 'Server is online';								
								$statusIcon = '<span class="glyphicon glyphicon-ok-sign" aria-hidden="true"></span> ';
								$isDown = 'false';								
							}
							else{
								$status = 'panel-danger';
								$statusIcon = '<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> ';
								$statusText = 'Server is DOWN!';
								$isDown = 'true';
							}
							if ($value["isMonitored"] == 1){
								echo "<div class=\"panel ". $status ."\">
								<div class=\"panel-heading\">		
								<h3 class=\"panel-title\">". $statusIcon . $value["name"] ."</h3>   
								</div>
								<div class=\"panel-body\">
								<p class=\"\">". $statusText ."</p>	";
							if($lastAlertTime != 0 && $isDown != 'true'){
								echo "<p class=\"\">Last alert at: ". date('H:i:s d/m/Y',$lastAlertTime) ."</p>";
							}
							if($lastFixTime != 0 && $isDown != 'true'){
								echo "<p class=\"\">Last recovery at: ". date('H:i:s d/m/Y',$lastFixTime) ."</p>";
							}
							if($lastAlertTime != 0 && $lastFixTime === null ){
								echo "<p class=\"\"><b> Alert Currently Open! <br> ". $alertMessage ."</b> <br> Alert has been open since: ". date('H:i:s d/m/Y',$alertsArr[0]["firstTriggeredAt"]["sec"]) ." </p>";
							}													
							if($lastFixTime === null && $lastAlertTime === null){
								echo "<p class=\"\"><b> No Previous Alerts!</b></p>";
							}																				
							echo "<p class=\"pull-right small\">Last Update from Server: ". date('H:i:s d/m/Y', $lastPayloadAt) ."</p>
									</div>
									</div>";
							}
					}        
				?>	
				</div> 
			</div>
			<div class="row">
				<div id="services" class="col-md-8 col-md-offset-2">
					<h2>Services</h2>
				<?php	  
					foreach($allServices as $value){
							$serviceStatus = $value["currentStatus"];
							$servId = $value["_id"];
							$serviceArr = json_decode(file_get_contents("https://api.serverdensity.io/service-monitor/meta/". $servId ."?token=" . $TOKEN), true);
							if($serviceStatus === 'up'){
								$status = 'panel-success';
								$statusText = 'Service is online';								
								$statusIcon = '<span class="glyphicon glyphicon-ok-sign" aria-hidden="true"></span> ';
							}
							elseif($serviceStatus === 'slow'){
								$status = 'panel-warning';
								$statusText = 'Service is slow';								
								$statusIcon = '<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> ';
							}
							else{
								$status = 'panel-danger';
								$statusIcon = '<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> ';
								$statusText = 'Service is DOWN!';
							}
							echo "<div class=\"panel ". $status ."\">
									<div class=\"panel-heading\">		
									<h3 class=\"panel-title\">". $statusIcon . $value["name"] ." - ". $value["checkUrl"]."</h3>   
									</div>
									<div class=\"panel-body\">
									<p class=\"\">". $statusText ."</p>										
									<p class=\"\">24 Hour Uptime: <b>". round((float)$serviceArr["metrics"]["status"] * 100 )  . "%" ."  </b></p>
									<p class=\"\">Avg Response Time: ". round($serviceArr["metrics"]["time"], 3) ."s</p>											
									</div>
									</div>";
					}        
				?>	
				</div> 
			</div>		
		<?php
		// For Debugging
		
		if ($debug == "true"){
			$arr = get_defined_vars();
			echo "<br>";	
			echo "<pre>"; 
			print_r($arr); 
			echo "</pre>"; 	
			}
		else
		{}
		?>		
		</div><!-- /.container -->
		<!-- Bootstrap core JavaScript
		================================================== -->
		<!-- Placed at the end of the document so the pages load faster -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
		<!-- Latest compiled and minified JavaScript -->
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js" integrity="sha512-K1qjQ+NcF2TYO/eI3M6v8EiNYZfA95pQumfvcVrTHtwQVDG+aHRqLi/ETn2uB+1JqwYqVG3LIvdm9lj6imS/pQ==" crossorigin="anonymous"></script>
	</body>
</html>