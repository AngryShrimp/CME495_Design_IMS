<?PHP
/***********************************************************************
 * 	Script: GenerateSID.php
 * 	Description: Generates an new SID for a client based on date, time and
 *	client IP.
 *
 *	Author: Craig Irvine (cri646@mail.usask.ca)
 *	Date: 08 January 2016
 *
 *	Inputs: 
 *
 *	Usage: GenerateSID.php
 ***********************************************************************/
  
include "IMSLog.php";

$ipaddress = "";
$date = "";
$message = "";
$SID = "";
 
try 
{
	$log = new IMSLog();

	//grab client IP
	if($_SERVER['REMOTE_ADDR'])
		$ipaddress = $_SERVER['REMOTE_ADDR'];
	else
		$ipaddress = 'UNKNOWN';
			
	$date = date("c");


	$message = $ipaddress.$date;

	$SID = hash("crc32",$message);
	
	$log->add_log($SID,"Information","Client connected from $ipaddress at $date.");
	
	echo $SID;
	
}
catch(Exception $e)
{
	$statusCode = '1';
	$statusMessage = 'GenerateSID Error: '. $e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);

}
?>