<?php

include "Back_End/IMSLog.php";
include "Back_End/IMSSql.php";

$ipaddress = "";
$date = "";
$message = "";
$SID = "";
$key = "";
$timeout = 3600; //default is 1 hour (3600 seconds)
 
try 
{
	$log = new IMSLog();
	$sql = new IMSSql();	
	
	//Set IMSLog options
	$opt_debugLog = $sql->getOption('Debug');
	if($opt_debugLog === false)
		$log->add_log($sessionID,'Warning','Default Warning: Debug Option missing or invalid.');
	else if($opt_debugLog == 'False')
		$log->opt_debug = false;	
	else 
		$log->opt_debug = true;	
		
	//Get Credential Time out Option
	$opt_timeout = $sql->getOption('Credential_Expiry_Time_Seconds');
	$log->add_log($sessionID,'Warning',"Default Warning: $opt_timeout");

	if($opt_timeout === false)
		$log->add_log($sessionID,'Warning','Default Warning: Credential_Expiry_Time_Seconds Option missing or invalid.');
	else 
		$timeout = intval($opt_timeout);
	

	if ($_SERVER["REQUEST_METHOD"] == "GET") 
	{
		$key = $_GET["Key"];
	}	
	
	//grab client IP
	if($_SERVER['REMOTE_ADDR'])
		$ipaddress = $_SERVER['REMOTE_ADDR'];
	else
		$ipaddress = 'UNKNOWN';		
		
	$date = date("Y-m-d H:i:s");

	$message = $ipaddress.$date;

	$SID = hash("crc32",$message);
	
	$sql->set_sid($SID,$date,$ipaddress,$key);

	
}
catch(PDOException $e)
{
	$statusCode = '1';
	$statusMessage = 'GenerateSID Error: '. $e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);
}
catch(Exception $e)
{
	$statusCode = $e->getCode();
	$statusMessage = 'GenerateSID Error: '. $e->getMessage();
	//$log->add_log($sessionID,'Error',$statusMessage);
}

	setcookie("SID", $SID, time() + ($timeout), "/");
	$log->add_log($SID,"Information","Client connected from $ipaddress at $date.");
	header('Location: MainPage.html');

?>
