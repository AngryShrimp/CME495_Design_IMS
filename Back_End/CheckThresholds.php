<?php

include "IMSSql.php";
include "IMSLog.php";
include "IMSBase.php";

$statusArray="";
$dataArray="";
$sessionID="";
$statusCode = 0;
try{

	$IMSBase = new IMSBase();
	$log = new IMSLog();
	$sql = new IMSSql();
	
	if ($_SERVER["REQUEST_METHOD"] == "POST")
	{
		$sessionID = $_POST["SID"];
	}
	//Set IMSLog options
	$opt_debugLog = $sql->getOption('Debug');
	if($opt_debugLog === false)
		$log->add_log($sessionID,'Warning','CheckThresholds Warning: Debug Option missing or invalid.');
	else if($opt_debugLog == 'False')
		$log->opt_debug = false;	
	else 
		$log->opt_debug = true;
		
	$opt_logLoc = $sql->getOption('Log_File_Location');	
	if($opt_logLoc === false)
		$log->add_log($sessionID,'Warning','CheckThresholds Warning: Log_File_Location Option missing or invalid.');
	else 
		$log->set_log_location($opt_logLoc);

	$runLevel = $sql->verifySID($sessionID,"0");

	$dataArray = $sql->checkThresholds();
	
}catch(PDOException $e)
{
	$statusMessage = 'CheckThresholds SQLError: '.$e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);
	$statusCode = 1;
    echo "Error: " . $e->getMessage();
	
}
catch(Exception $e)
{
	$statusCode = $e->getCode();
	$statusMessage = 'CheckThresholds Error: '. $e->getMessage();
	if(!$log->add_log($sessionID,'Error',$statusMessage,"N/A",true))
	{
		$statusMessage = $statusMessage." **Logging Failed**";
	}
    echo "Error: " . $e->getMessage();

}

if ($statusCode == 0){
    $statusMessage = "CheckThresholds completed successfully.";
	$log->add_log($sessionID,'Info',$statusMessage);
    
    $statusArray[0] = $statusCode;
	$statusArray[1] = $statusMessage;
	$statusArray[2] = $runLevel;
        
	$IMSBase->GenerateXMLResponse($sessionID,$statusArray,NULL,$dataArray,"THRESHOLDS","THRESHOLDS_ENTRY");
}
?>