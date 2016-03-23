<?PHP
/***********************************************************************
 * 	Script: DeleteClassData.php
 * 	Description: Script for deleting class data from the database.
 *				 Returns a refreshed table for display.
 *	Author: Craig Irvine (cri646@mail.usask.ca)
 *	Date: 13 Feb 2016
 *
 *	Inputs: SID: The session ID of the client
 *			ID: ID of the class record data to be deleted.
 *			SortColumn: The data column to sort. (Optional)
 *          SortDirection: The sort direction (Ascending or Descending). (Optional)
 *
 *	Usage: DeleteClassData.php?SID=<session ID>&PartNumber=<ItemNumber>
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
		$log->add_log($sessionID,'Warning','DeleteItem Warning: Debug Option missing or invalid.');
	else if($opt_debugLog == 'False')
		$log->opt_debug = false;	
	else 
		$log->opt_debug = true;
		
	$opt_logLoc = $sql->getOption('Log_File_Location');	
	if($opt_logLoc === false)
		$log->add_log($sessionID,'Warning','DeleteItem Warning: Log_File_Location Option missing or invalid.');
	else 
		$log->set_log_location($opt_logLoc);
	
	$runLevel = $sql->verifySID($sessionID,"1"); //1 = Requires edit privileges.
	$IMSBase->verifyData($partNumber,"/^.+$/","Part Number");
	
	//remove the item number from Class_Data
	$sql->command("DELETE FROM dbo.Class_Data WHERE [Part]='$partNumber'");
	//remove the item number from Purchase_List
	//$sql->command("DELETE FROM dbo.Purchase_List WHERE [Part]='$partNumber'");
	
	//remove the item of Inventory
	$sql->command("DELETE FROM dbo.Inventory WHERE [Name]='$partNumber'");

	$statusCode = '0';
	$statusMessage = "Item:$partNumber has been deleted from the database.";
	$log->add_log($sessionID,'Information',$statusMessage);	
	
}
catch(PDOException $e)
{
	$statusCode = '1';
	$statusMessage = 'DeleteItem SQLError: '.$e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);
	
}
catch(Exception $e)
{
	$statusCode = $e->getCode();
	$statusMessage = 'DeleteItem Error: '. $e->getMessage();
	if(!$log->add_log($sessionID,'Error',$statusMessage,"N/A",true))
	{
		$statusMessage = $statusMessage." **Logging Failed**";
	}

}	
//finally()  PHP 5.5+, currently using 5.3.
//{
	$statusArray[0] = $statusCode;
	$statusArray[1] = $statusMessage;
	$statusArray[2] = $runLevel;
	$IMSBase->GenerateXMLResponse($sessionID,$statusArray);
//}	
?>