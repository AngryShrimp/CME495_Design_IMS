<?php

include "Back_End/IMSLog.php";
include "Back_End/IMSSql.php";

$ipaddress = "";
$date = "";
$message = "";
$SID = "";
$key = "";
 
try 
{

	$log = new IMSLog();
	$sql = new IMSSql();	
	
	
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
	
	
	$sql->command("DELETE FROM dbo.SID_List WHERE EXPIRE<'$date'");
	
	
	$stmt = $sql->prepare("SELECT * FROM dbo.SID_List;");
	$stmt->execute();	
	
	$dataArray = $stmt->fetchAll(PDO::FETCH_ASSOC);   		
	
	if($key == "update")
	{
		$runlevel = "1";
	}
	else if($key == "modify")
	{
		$runlevel = "2";
	}
	
	$exp_date = date("Y-m-d H:i:s",time()+3600);
		
	$sql->command("INSERT INTO dbo.SID_List (SID,CLIENT_IP,EXPIRE,LEVEL) VALUES ('$SID','$ipaddress','$exp_date','$runlevel');");

	
}
catch(PDOException $e)
{
	$statusCode = '1';
	$statusMessage = 'GenerateSID Error: '. $e->getMessage();
	//$log->add_log($sessionID,'Error',$statusMessage);
}

catch(Exception $e)
{
	$statusCode = '1';
	$statusMessage = 'GenerateSID Error: '. $e->getMessage();
	//$log->add_log($sessionID,'Error',$statusMessage);
}

	setcookie("", $SID, time() + (3600), "/"); // 3600 = 1 hour
	$log->add_log($SID,"Information","Client connected from $ipaddress at $date.");
	header('Location: MainPage.html');
	



?>
