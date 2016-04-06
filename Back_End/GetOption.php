<?php
/***********************************************************************
 * 	Script: GetOption.php
 * 	Description: Script for reading all the IMS Options.
 *      Reference: 7.1.9 of IMS System Design Document
 *
 *	Author: Justin Fraser ()
 *	Date: 27 January 2016
 *
 *	Modified by: Craig Irvine
 *	Date: March 2016
 *
 *	Inputs:     SID: The session ID of the client.             
 *
 *	Usage: GetOption.php?SID=<session ID>
 ***********************************************************************/
  
include "IMSBase.php";
include "IMSLog.php";
include "IMSSql.php";


$sessionID = "";

$statusMessage = "";
$statusCode = 0;
$dataArray = NULL;
$runLevel = "0";

try
{
	if ($_SERVER["REQUEST_METHOD"] == "POST") 
	{
		$sessionID = $_POST["SID"];
	}


	$IMSBase = new IMSBase();
	$log = new IMSLog();
	$sql = new IMSSql();
	
	//Set IMSLog options
	$opt_debugLog = $sql->getOption('Debug');
	if($opt_debugLog === false)
		$log->add_log($sessionID,'Warning','GetOption Warning: Debug Option missing or invalid.');
	else if($opt_debugLog == 'False')
		$log->opt_debug = false;	
	else 
		$log->opt_debug = true;
	
	$opt_logLoc = $sql->getOption('Log_File_Location');	
	if($opt_logLoc === false)
		$log->add_log($sessionID,'Warning','GetOption Warning: Log_File_Location Option missing or invalid.');
	else 
		$log->set_log_location($opt_logLoc);	
	

	$runLevel = $sql->verifySID($sessionID); 
	
	$sqlQuery = "SELECT * FROM dbo.Options";
	
	$stmt = $sql->prepare($sqlQuery);
	$stmt->execute();	
	
	$dataArray = $stmt->fetchAll(PDO::FETCH_ASSOC);  
	
	$php_options_file_loc = $_SERVER['DOCUMENT_ROOT']."\Back_End\IMS_Settings.ini";
	
	if(file_exists($php_options_file_loc))
	{
		$options_file = parse_ini_file($php_options_file_loc,TRUE);	
		
		$dataArray[count($dataArray)+1]['Option'] = "SQL_LOCATION";
		$dataArray[count($dataArray)]['Value'] = $options_file["SQL_SERVER"]["SQL_LOCATION"];
		$dataArray[count($dataArray)+1]['Option'] = "SQL_USER";
		$dataArray[count($dataArray)]['Value'] = $options_file["SQL_SERVER"]["SQL_USER"];
		$dataArray[count($dataArray)+1]['Option'] = "SQL_PASS";
		$dataArray[count($dataArray)]['Value'] = $options_file["SQL_SERVER"]["SQL_PASS"];
		$dataArray[count($dataArray)+1]['Option'] = "SQL_DRIVER";
		$dataArray[count($dataArray)]['Value'] = $options_file["SQL_SERVER"]["SQL_DRIVER"];
	}	
	
	if($runLevel < 2) //blank password entries from display
	{
		for($i = 0;$i < count($dataArray);$i++)
		{
			if(($dataArray[$i]['Option'] == 'SQL_PASS')||($dataArray[$i]['Option'] == 'Email_Pass'))
			{
				$dataArray[$i]['Value'] = "******";
			}
		}
	}
	
	$statusCode = "0";
	$statusMessage = "Option table fetched.";
	$log->add_log($sessionID,'Debug',$statusMessage);
	
}
catch(PDOException $e)
{
	$statusCode = 1;
	$statusMessage = 'ReadOptions SQLError: '.$e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);
	
}
catch(Exception $e)
{
	$statusCode = $e->getCode();
	$statusMessage = 'ReadOptions Error: '. $e->getMessage();
	if(!$log->add_log($sessionID,'Error',$statusMessage,"N/A",true))
	{
		$statusMessage = $statusMessage." **Logging Failed**";
	}

}



$statusArray[0] = $statusCode;
$statusArray[1] = $statusMessage;
$statusArray[2] = $runLevel;
	
$IMSBase->GenerateXMLResponse($sessionID,$statusArray,NULL,$dataArray,"OPTIONS","OPT_ENTRY");

?>