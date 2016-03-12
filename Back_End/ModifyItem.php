<?PHP
/***********************************************************************
 * 	Script: ModifyItem.php
 * 	Description: Script for modifying one data field in the inventory table
 *	for an existing item.
 *
 *	Author: Craig Irvine (cri646@mail.usask.ca)
 *	Date: 12 January 2016
 *
 *	Inputs: SID: The session ID of the client
 *			PartNumber: The part number to modify.
 *  		Field: The data field to modify.
 *  		Value: The modification value.
 *
 *	Usage: CreateNewItem.php?SID=<session ID>&PartNumber=<part number>&
			Field=<Field to Modify>&Value=<modification value>
 ***********************************************************************/
  
include "IMSBase.php";
include "IMSLog.php";
include "IMSSql.php";


$sessionID = "";
$partNumber = "";
$field = "";
$value = "";

$statusMessage = "";
$statusCode = "";


try
{
	if ($_SERVER["REQUEST_METHOD"] == "POST") 
	{
		$sessionID = $_POST["SID"];
		$partNumber = $_POST["PartNumber"];  
		$field = $_POST["Field"];
		$value = $_POST["Value"];
	}


	$IMSBase = new IMSBase();
	$log = new IMSLog();
	$sql = new IMSSql();

	
	$runLevel = $sql->verifySID($sessionID,"1"); //1 = Requires edit privileges.
	$IMSBase->verifyData($partNumber,"/^.+$/","Part Number");
	$IMSBase->verifyData($field,"/^.+$/","Item Field");
	$IMSBase->verifyData($value,"/^.+$/","Item Value");

	if($sql->exists($partNumber,'dbo.Inventory') == FALSE)
	{
		$statusCode = '1';
		$statusMessage = "ModifyItem Error: Part Number, $partNumber, does not exist.";
		$log->add_log($sessionID,'Warning',$statusMessage);		
	
	}
	else	
	{
		$sql->command("UPDATE dbo.Inventory SET [$field]='$value' WHERE Name='$partNumber';");
		
		$statusCode = '0';
		$statusMessage = "Item($partNumber) $field was updated with $value";
		$log->add_log($sessionID,'Information',$statusMessage,$partNumber);
	}
	
}
catch(PDOException $e)
{
	$statusCode = '1';
	$statusMessage = 'ModifyItem SQLError: '.$e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);
	
}
catch(Exception $e)
{
	$statusCode = $e->getCode();
	$statusMessage = 'ModifyItem Error: '. $e->getMessage();
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