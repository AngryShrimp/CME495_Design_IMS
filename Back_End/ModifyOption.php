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
 *                  (String) Option: The part number to create.
 *                           Data: the value of the option.
 *                  
 *
 *	Usage: ModifyOption.php?SID=<session ID>&Option=<option name>
 *             &Data=<value>
 ***********************************************************************/
  
include "IMSBase.php";
include "IMSLog.php";
include "IMSSql.php";


$sessionID = "";
$option = "";
$data = "";

$statusMessage = "";
$statusCode = 0;
$runlevel = "";


try
{
	if ($_SERVER["REQUEST_METHOD"] == "POST") 
	{
		$sessionID = $_POST["SID"];
		$option = $_POST["Option"];  
        $data = $_POST["Data"];
	}


	$IMSBase = new IMSBase();
	$log = new IMSLog();
	$sql = new IMSSql();
	
	//Set IMSLog options
	$opt_debugLog = $sql->getOption('Debug');
	if($opt_debugLog === false)
		$log->add_log($sessionID,'Warning','ModifyOption Warning: Debug Option missing or invalid.');
	else if($opt_debugLog == 'False')
		$log->opt_debug = false;	
	else 
		$log->opt_debug = true;
		
	$opt_logLoc = $sql->getOption('Log_File_Location');	
	if($opt_logLoc === false)
		$log->add_log($sessionID,'Warning','ModifyOption Warning: Log_File_Location Option missing or invalid.');
	else 
		$log->set_log_location($opt_logLoc);

	$runLevel = $sql->verifySID($sessionID,"1"); //1 = Requires edit privileges.
	$IMSBase->verifyData($option,"/^.+$/");
	$IMSBase->verifyData($data,"/^.+$/");	
			
	
	if ($sql->IdExists('dbo.Options') == FALSE)
		$sql->command("INSERT INTO dbo.Options ($option) VALUES ($data)");
	else
		$sql->command("UPDATE dbo.Options SET $option=$data WHERE Id LIKE '%'");		
		
	
}
catch(PDOException $e)
{
	$statusCode = 1;
	$statusMessage = 'ModifyOption SQLError: '.$e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);
    echo "Error: " . $e->getMessage();
	
}
catch(Exception $e)
{
	$statusCode = $e->getCode();
	$statusMessage = 'ModifyOption Error: '. $e->getMessage();
	if(!$log->add_log($sessionID,'Error',$statusMessage,"N/A",true))
	{
		$statusMessage = $statusMessage." **Logging Failed**";
	}
    echo "Error: " . $e->getMessage();

}

if ($statusCode == 0){
        $statusMessage = "Option $option changed successfully.";
	$log->add_log($sessionID,'Info',$statusMessage);
    
    $statusArray[0] = $statusCode;
	$statusArray[1] = $statusMessage;
	$statusArray[2] = $runLevel;
        
        
    echo "Script execution successful\n\n\n";
	$IMSBase->GenerateXMLResponse($sessionID,$statusArray);
}
?>