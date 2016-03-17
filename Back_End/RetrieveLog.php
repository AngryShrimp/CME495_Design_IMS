<?PHP
/***********************************************************************
 * 	Script: RetrieveLog.php
 * 	Description: Script for retrieving all log entries that match
 *      passed filters.
 *
 *	Author: Craig Irvine (cri646@mail.usask.ca)
 *	Date: 14 January 2016
 *
 *	Inputs: SID: The session ID of the client
 *			LogLevel: The log level to retrieve logs for.
 *
 *	Usage: 	RetrieveLog.php?SID=<session id>&LogLevel=<log level>
 ***********************************************************************/
  
include "IMSBase.php";
include "IMSLog.php";
include "IMSSql.php";


$sessionID = "";
$logLevel = "";

$statusMessage = "";
$statusCode = "";
$runLevel = "";


try
{
	if ($_SERVER["REQUEST_METHOD"] == "POST") 
	{
		$sessionID = $_POST["SID"];
		$logLevel = $_POST["LogLevel"]; 
	}
	
	$IMSBase = new IMSBase();
	$log = new IMSLog();
	$sql = new IMSSql();
	
	//Set IMSLog options
	$opt_debugLog = $sql->getOption('Debug');
	if($opt_debugLog === false)
		$log->add_log($sessionID,'Warning','RetrieveLog Warning: Debug Option missing or invalid.');
	else if($opt_debugLog == 'False')
		$log->opt_debug = false;	
	else 
		$log->opt_debug = true;
		
	$opt_logLoc = $sql->getOption('Log_File_Location');	
	if($opt_logLoc === false)
		$log->add_log($sessionID,'Warning','RetrieveLog Warning: Log_File_Location Option missing or invalid.');
	else 
		$log->set_log_location($opt_logLoc);
		

	$runLevel = $sql->verifySID($sessionID); //No special permission required.	
	
	$IMSBase->verifyData($logLevel,"/^.+$/","Log Level");
		
	$logArray = $log->read_log($logLevel);
		
	$statusCode = '0';
	$statusMessage = 'RetrieveLog, successfully retrieved log data.';
	$log->add_log($sessionID,'Debug',$statusMessage);
        
}
catch(PDOException $e)
{
	$statusCode = '1';
	$statusMessage = 'RetrieveLog SQLError: '.$e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);
	
}
catch(Exception $e)
{
	$statusCode = $e->getCode();
	$statusMessage = 'RetrieveLog Error: '. $e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);

}	
//finally()  PHP 5.5+, currently using 5.3.
//{
	$statusArray[0] = $statusCode;
	$statusArray[1] = $statusMessage;
	$statusArray[2] = $runLevel;
	$IMSBase->GenerateXMLResponse($sessionID,$statusArray,NULL,$logArray,"LOG","LOG_ENTRY");
//}	
?>