<?PHP
/***********************************************************************
 * 	Script: ModifyEmailAddress.php
 * 	Description: Script for modifying one data field in the inventory table
 *	for an existing item.
 *
 *	Author: Craig Irvine (cri646@mail.usask.ca)
 *	Date: 27 Feb 16
 *
 *	Inputs: SID: The session ID of the client
 *			ID: The record ID of the class data
 *  		Field: The data field to modify.
 *  		Value: The modification value.
 *
 *	Usage: ModifyEmailAddress.php?SID=<session ID>&ID=<Record ID>&
			Field=<Field to Modify>&Value=<modification value>
 ***********************************************************************/
   
include "IMSBase.php";
include "IMSLog.php";
include "IMSSql.php";


$sessionID = "";
$recordID = "";
$field = "";
$value = "";

$statusMessage = "";
$statusCode = "";
$runLevel = "";
$dataArray = NULL;


try
{
	if ($_SERVER["REQUEST_METHOD"] == "POST") 
	{
		$sessionID = $_POST["SID"];
		$recordID = $_POST["ID"];  
		$field = $_POST["Field"];
		$value = $_POST["Value"];		
	}


	$IMSBase = new IMSBase();
	$log = new IMSLog();
	$sql = new IMSSql();

	$runLevel = $sql->verifySID($sessionID,"1"); //1 = Requires edit privileges.
	$IMSBase->verifyData($recordID,"/^.+$/","Record ID");
	$IMSBase->verifyData($field,"/^.+$/","Record Field");
	$IMSBase->verifyData($value,"/^.+$/","Record Value");

	$sql->command("UPDATE dbo.Emails SET [$field]='$value' WHERE ID='$recordID';");	
	
	//retrieve new table.
	$sqlQuery = "SELECT * FROM dbo.Emails;";
	
	
	$stmt = $sql->prepare($sqlQuery);
	$stmt->execute();
	
	$dataArray = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
	$statusCode = '0';
	$statusMessage = "Email record($recordID) - $field was updated with $value";
	$log->add_log($sessionID,'Information',$statusMessage);
	
	
}
catch(PDOException $e)
{
	$statusCode = '1';
	$statusMessage = 'ModifyEmailAddress SQLError: '.$e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);
	
}
catch(Exception $e)
{
	$statusCode = $e->getCode();
	$statusMessage = 'ModifyEmailAddress Error: '. $e->getMessage();
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