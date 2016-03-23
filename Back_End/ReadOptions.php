<?php
/***********************************************************************
 * 	Script: ModifyOption.php
 * 	Description: Script for modifying IMS options.
 *      Reference: 7.1.9 of IMS System Design Document
 *
 *	Author: Justin Fraser (jaf470@mail.usask.ca)
 *	Date: 27 January 2016
 *
 *	Inputs:     (int) SID: The session ID of the client.             
 *
 *	Usage: ReadOptions.php?SID=<session ID>
 ***********************************************************************/
  
include "IMSBase.php";
include "IMSLog.php";
include "IMSSql.php";


$sessionID = "";

$statusMessage = "";
$statusCode = 0;


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
		$log->add_log($sessionID,'Warning','ReadOptions Warning: Debug Option missing or invalid.');
	else if($opt_debugLog == 'False')
		$log->opt_debug = false;	
	else 
		$log->opt_debug = true;
	
	$opt_logLoc = $sql->getOption('Log_File_Location');	
	if($opt_logLoc === false)
		$log->add_log($sessionID,'Warning','ReadOptions Warning: Log_File_Location Option missing or invalid.');
	else 
		$log->set_log_location($opt_logLoc);	
	

	$IMSBase->verifyData($sessionID,"/^.+$/");
                
    $sql->retrieveOptions();
		
		
	
}
catch(PDOException $e)
{
	$statusCode = 1;
	$statusMessage = 'ReadOptions SQLError: '.$e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);
        echo "Error: " . $e->getMessage();
	
}
catch(Exception $e)
{
	$statusCode = $e->getCode();
	$statusMessage = 'ReadOptions Error: '. $e->getMessage();
	if(!$log->add_log($sessionID,'Error',$statusMessage,"N/A",true))
	{
		$statusMessage = $statusMessage." **Logging Failed**";
	}
    echo "Error: " . $e->getMessage();

}

if ($statusCode == 0){
    $statusMessage = "Option $option changed successfully.";
	$log->add_log($sessionID,'Information',$statusMessage);
    
    $statusArray[0] = $statusCode;
	$statusArray[1] = $statusMessage;
        
        
    echo "Script execution successful\n\n\n";
	$IMSBase->GenerateXMLResponse($sessionID,$statusArray);
}
?>