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


try
{
	if ($_SERVER["REQUEST_METHOD"] == "POST") 
	{
		$sessionID = $_POST["SID"];
		$partNumber = $_POST["PartNumber"];  
	}


	$IMSBase = new IMSBase();
	$log = new IMSLog();
	$sql = new IMSSql("(local)\SQLEXPRESS","","");

	$IMSBase->verifyData($partNumber,"/^.+$/");
	$IMSBase->verifyData($sessionID,"/^.+$/");
	
	if($sql->exists($partNumber,'dbo.Inventory') == TRUE)
	{
		$statusCode = '1';
		$statusMessage = "CreateNewItem Error: $partNumber already exits in database.";
		$log->add_log($sessionID,'Info',$statusMessage);
	}
	else
	{
		$sql->command('INSERT INTO dbo.Inventory (Name) VALUES (\''.$partNumber.'\');');
		
		$statusCode = '0';
		$statusMessage = 'Item ('.$partNumber.') created successfully. ';
		$log->add_log($sessionID,'Info',$statusMessage);
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
	$statusCode = '1';
	$statusMessage = 'CreateNewItem Error: '. $e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);

}	
//finally()  PHP 5.5+, currently using 5.3.
//{
	$statusArray[0] = $statusCode;
	$statusArray[1] = $statusMessage;
	$IMSBase->GenerateXMLResponse($sessionID,$statusArray);
//}	
?>