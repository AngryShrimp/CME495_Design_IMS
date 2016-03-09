<?PHP
/***********************************************************************
 * 	Script: ModifyClassData.php
 * 	Description: Script for modifying one data field in the inventory table
 *	for an existing item.
 *
 *	Author: Craig Irvine (cri646@mail.usask.ca)
 *	Date: 23 Feb 16
 *
 *	Inputs: SID: The session ID of the client
 *			ID: The record ID of the class data
 *  		Field: The data field to modify.
 *  		Value: The modification value.
 *
 *	Usage: ModifyClassData.php?SID=<session ID>&ID=<Record ID>&
			Field=<Field to Modify>&Value=<modification value>&
			SortColumn=<sort column>&SortDirection=<ASC/DESC>
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
$sortColumn = "";
$sortDirection = "";

try
{
	if ($_SERVER["REQUEST_METHOD"] == "POST") 
	{
		$sessionID = $_POST["SID"];
		$recordID = $_POST["ID"];  
		$field = $_POST["Field"];
		$value = $_POST["Value"];
		$sortColumn = $_POST["SortColumn"];  
		$sortDirection = $_POST["SortDirection"];
	}


	$IMSBase = new IMSBase();
	$log = new IMSLog();
	$sql = new IMSSql();

	$runLevel = $sql->verifySID($sessionID,"1"); //1 = Requires edit privileges.
	$IMSBase->verifyData($recordID,"/^.+$/","recordID");
	$IMSBase->verifyData($field,"/^.+$/","field");
	$IMSBase->verifyData($value,"/^.+$/","value");
	$IMSBase->verifyData($sortColumn,"/^.*$/","sortColumn");
	if($sortColumn != "")
		$IMSBase->verifyData($sortDirection,"/^(ASC|DESC)$/","sortDirection");

	$sql->command("UPDATE dbo.Class_Data SET [$field]='$value' WHERE ID='$recordID';");	
	
	//retrieve new table.
	$sqlQuery = "SELECT * FROM dbo.Class_Data";
	
	if($sortColumn != "")
	{
		$sqlQuery = $sqlQuery." ORDER BY $sortColumn $sortDirection";
	}
	
	$sqlQuery = $sqlQuery.";";
	
	$stmt = $sql->prepare($sqlQuery);
	$stmt->execute();
	
	$dataArray = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
	$statusCode = '0';
	$statusMessage = "Class Data Record($recordID) $field was updated with $value";
	$log->add_log($sessionID,'Information',$statusMessage);
	
	
}
catch(PDOException $e)
{
	$statusCode = '1';
	$statusMessage = 'ModifyClassData SQLError: '.$e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);
	
}
catch(Exception $e)
{
	$statusCode = '1';
	$statusMessage = 'ModifyClassData Error: '. $e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);

}	
//finally()  PHP 5.5+, currently using 5.3.
//{
	$statusArray[0] = $statusCode;
	$statusArray[1] = $statusMessage;
	$statusArray[2] = $runLevel;
	$IMSBase->GenerateXMLResponse($sessionID,$statusArray,NULL,$dataArray,"CLASS_DATA","CLASS_ENTRY");
//}	
?>