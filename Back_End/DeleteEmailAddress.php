<?PHP
/***********************************************************************
 * 	Script: DeleteEmailAddress.php
 * 	Description: Script for deleting class data from the database.
 *				 Returns a refreshed table for display.
 *	Author: Craig Irvine (cri646@mail.usask.ca)
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
	
	$runLevel = $sql->verifySID($sessionID,"1"); //1 = Requires edit privileges.
	$IMSBase->verifyData($id,"/^.+$/");	
	
		
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
	$statusCode = '1';
	$statusMessage = 'DeleteEmailAddress Error: '. $e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);

}	
//finally()  PHP 5.5+, currently using 5.3.
//{
	$statusArray[0] = $statusCode;
	$statusArray[1] = $statusMessage;
	$statusArray[2] = $runLevel;
	$IMSBase->GenerateXMLResponse($sessionID,$statusArray,NULL,$dataArray,"EMAIL_LIST","EMAIL_ENTRY");
//}	
?>