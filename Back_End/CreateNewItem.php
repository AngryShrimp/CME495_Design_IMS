<?PHP
/***********************************************************************
 * 	Script: CreateNewItem.php
 * 	Description: Script for adding a new item to the IMS systems database.
 *
 *	Author: Craig Irvine (cri646@mail.usask.ca)
 *	Date: 08 January 2016
 *
 *	Inputs: SID: The session ID of the client
 *			PartNumber: The part number to create.
 *
 *	Usage: CreateNewItem.php?SID=<session ID>&PartNumber=<part number>
 ***********************************************************************/
  
include "IMSBase.php";
include "IMSLog.php";
include "IMSSql.php";


$sessionID = "";
$partNumber = "";

$statusMessage = "";
$statusCode = "";
$runLevel = "";


try
{
	if ($_SERVER["REQUEST_METHOD"] == "POST") 
	{
		$sessionID = $_POST["SID"];
		$partNumber = $_POST["PartNumber"];  
	}

	$IMSBase = new IMSBase();
	$log = new IMSLog();
	$sql = new IMSSql();
	
	//Set IMSLog options
	$opt_debugLog = $sql->getOption('Debug');
	if($opt_debugLog === false)
		$log->add_log($sessionID,'Warning','CreateNewItem Warning: Debug Option missing or invalid.');
	else if($opt_debugLog == 'False')
		$log->opt_debug = false;	
	else 
		$log->opt_debug = true;
		
	$opt_logLoc = $sql->getOption('Log_File_Location');	
	if($opt_logLoc === false)
		$log->add_log($sessionID,'Warning','CreateNewItem Warning: Log_File_Location Option missing or invalid.');
	else 
		$log->set_log_location($opt_logLoc);
		
		

	$runLevel = $sql->verifySID($sessionID,"1"); //1 = Requires edit privileges.
	$IMSBase->verifyData($partNumber,"/^.+$/","Part Number");

	if($sql->exists($partNumber,'dbo.Inventory') == TRUE)
	{
		$statusCode = '1';
		$statusMessage = "CreateNewItem Error: $partNumber already exits in database.";
		$log->add_log($sessionID,'Information',$statusMessage);
	}
	else
	{
		$sql->command('INSERT INTO dbo.Inventory (Name) VALUES (\''.$partNumber.'\');');
		
		$statusCode = '0';
		$statusMessage = 'Item ('.$partNumber.') created successfully. ';
		$log->add_log($sessionID,'Information',$statusMessage,$partNumber);
	}
	
}
catch(PDOException $e)
{
	$statusCode = '1';
	$statusMessage = 'CreateNewItem SQLError: '.$e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);
	
}
catch(Exception $e)
{
	$statusCode = $e->getCode();
	$statusMessage = 'CreateNewItem Error: '. $e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);

}	
//finally()  PHP 5.5+, currently using 5.3.
//{
	$statusArray[0] = $statusCode;
	$statusArray[1] = $statusMessage;
	$statusArray[2] = $runLevel;
	$IMSBase->GenerateXMLResponse($sessionID,$statusArray);
//}	
?>