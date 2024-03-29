<?PHP
/***********************************************************************
 * 	Script: DeleteEmailAddress.php
 * 	Description: Script for deleting class data from the database.
 *				 Returns a refreshed table for display.
 *	Author: Craig Irvine ()
 *	Date: 27 Feb 2016
 *
 *	Inputs: SID: The session ID of the client
 *			ID: ID of the email record data to be deleted.
 *			
 *	Usage: DeleteEmailAddress.php?SID=<session ID>&ID=<Record ID>
 ***********************************************************************/
   
include "IMSBase.php";
include "IMSLog.php";
include "IMSSql.php";


$sessionID = "";
$id = "";

$statusMessage = "";
$statusCode = "";
$runLevel = "";
$dataArray=NULL;


try
{
	if ($_SERVER["REQUEST_METHOD"] == "POST") 
	{
		$sessionID = $_POST["SID"];
		$id = $_POST["ID"];  		
	}

	$IMSBase = new IMSBase();
	$log = new IMSLog();
	$sql = new IMSSql();
	
	//Set IMSLog options
	$opt_debugLog = $sql->getOption('Debug');
	if($opt_debugLog === false)
		$log->add_log($sessionID,'Warning','DeleteEmailAddress Warning: Debug Option missing or invalid.');
	else if($opt_debugLog == 'False')
		$log->opt_debug = false;	
	else 
		$log->opt_debug = true;
		
	$opt_logLoc = $sql->getOption('Log_File_Location');	
	if($opt_logLoc === false)
		$log->add_log($sessionID,'Warning','DeleteEmailAddress Warning: Log_File_Location Option missing or invalid.');
	else 
		$log->set_log_location($opt_logLoc);
	
	$runLevel = $sql->verifySID($sessionID,"1"); //1 = Requires edit privileges.
	$IMSBase->verifyData($id,"/^.+$/","Record ID");	
	
		
	//Delete record
	$sql->command("DELETE FROM dbo.Emails WHERE Id=$id;");
	
	$statusCode = '0';
	$statusMessage = "Email ID:$id has been deleted from the database.";
	$log->add_log($sessionID,'Information',$statusMessage);
	
	//retrieve new table.
	$sqlQuery = "SELECT * FROM dbo.Emails;";
	
	$stmt = $sql->prepare($sqlQuery);
	$stmt->execute();
	
	$dataArray = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
	
	
}
catch(PDOException $e)
{
	$statusCode = '1';
	$statusMessage = 'DeleteEmailAddress SQLError: '.$e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);
	
}
catch(Exception $e)
{
	$statusCode = $e->getCode();
	$statusMessage = 'DeleteEmailAddress Error: '. $e->getMessage();
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
	$IMSBase->GenerateXMLResponse($sessionID,$statusArray,NULL,$dataArray,"EMAIL_LIST","EMAIL_ENTRY");
//}	
?>