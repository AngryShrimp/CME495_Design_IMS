<?PHP
/***********************************************************************
 * 	Script: CreateNewItem.php
 * 	Description: Script for adding a new item to the IMS systems database.
 *
 *	Author: Craig Irvine (cri646@mail.usask.ca)
 *	Date: 08 January 2016
 *
 ***********************************************************************/
 
 
 
include "IMSBase.php";
include "IMSLog.php";
include "IMSSql.php";


$sessionID = "";
$partNumber = "";

$statusMessage = "";
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$sessionID = $_POST["SID"];
	$partNumber = $_POST["PartNumber"];  
}

try
{
	$IMSBase = new IMSBase();
	$log = new IMSLog();
	$sql = new IMSSql("(local)\SQLEXPRESS","","");

	//$IMSBase->verifyData($partNumber,"^.*$");

	
	$sql->command('INSERT INTO dbo.Inventory (Name) VALUES (\''.$partNumber.'\');');
	
	$statusMessage = "Item creation successful. (".$partNumber.")";

	
}
catch(PDOException $e)
{
	echo 'PDOError '.$e->getMessage();
	$statusMessage = 'Failed';
	$errorMessage = $e->getMessage();

}
catch(Exception $e)
{
	echo 'Error '.$e->getMessage();

	$statusMessage = 'Failed';
	$errorMessage = $e->getMessage();

}	
//finally()  PHP 5.5+, currently using 5.3.
//{
	//$IMSBase->GenerateXMLResponse($sessionID,$status_array);
//}	
echo $statusMessage."\n";
echo 'Done';
?>