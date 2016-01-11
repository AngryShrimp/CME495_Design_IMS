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


$IMSBase = new IMSBase();
$log = new IMSLog();
$sql = new IMSSql("localhost","user","password");

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
/*	$IMSBase->verifyData($partNumber,"^.?&");

	$sql->connect();
	
	$sql->command('INSERT INTO itemTable (PartNumber) VALUES (\''.$partNumber.'\');');
	
	$statusMessage = "Item creation successful. (".$partNumber.")";*/
	
	echo $partNumber.'\n';
	
}
catch(PDOException $e)
{
	$statusMessage = 'Failed';
	$errorMessage = e->getMessage();

}
catch(Exception $e)
{
	$statusMessage = 'Failed';
	$errorMessage = e->getMessage();

}	
finally
{
	$IMSBase->GenerateXMLResponse($sessionID,$status_array);
}	
	

?>