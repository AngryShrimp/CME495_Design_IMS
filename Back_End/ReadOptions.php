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
		$log->add_log($sessionID,'Warning','RetrieveBroswerData Warning: Debug Option missing or invalid.');
	else if($opt_debugLog == 'False')
		$log->opt_debug = false;	
	else 
		$log->opt_debug = true;

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
	$log->add_log($sessionID,'Error',$statusMessage);
        echo "Error: " . $e->getMessage();

}

if ($statusCode == 0){
        $statusMessage = "Option $option changed successfully.";
	$log->add_log($sessionID,'Info',$statusMessage);
    
        $statusArray[0] = $statusCode;
	$statusArray[1] = $statusMessage;
        
        
        echo "Script execution successful\n\n\n";
	$IMSBase->GenerateXMLResponse($sessionID,$statusArray);
}
?>