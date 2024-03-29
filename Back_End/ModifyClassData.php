<?PHP
/***********************************************************************
 * 	Script: ModifyClassData.php
 * 	Description: Script for modifying one data field in the inventory table
 *	for an existing item.
 *
 *	Author: Craig Irvine ()
 *	Date: 23 Feb 16
 *
 *	Inputs: SID: The session ID of the client
 *			ID: The record ID of the class data
 *  		Field: The data field to modify.
 *  		Value: The modification value.
 *			SortColumn: Column to sort the data by.
 *			SortDirection: Sort direction of the column. Must be ASC or DESC
 *
 *	Usage: ModifyClassData.php?SID=<session ID>&ID=<Record ID>&
 *			Field=<Field to Modify>&Value=<modification value>&
 *			SortColumn=<sort column>&SortDirection=<ASC/DESC>
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
	
	//Set IMSLog options
	$opt_debugLog = $sql->getOption('Debug');
	if($opt_debugLog === false)
		$log->add_log($sessionID,'Warning','ModifyClassData Warning: Debug Option missing or invalid.');
	else if($opt_debugLog == 'False')
		$log->opt_debug = false;	
	else 
		$log->opt_debug = true;
		
	$opt_logLoc = $sql->getOption('Log_File_Location');	
	if($opt_logLoc === false)
		$log->add_log($sessionID,'Warning','ModifyClassData Warning: Log_File_Location Option missing or invalid.');
	else 
		$log->set_log_location($opt_logLoc);

	$runLevel = $sql->verifySID($sessionID,"1"); //1 = Requires edit privileges.
	$IMSBase->verifyData($recordID,"/^.+$/","Record ID");
	$IMSBase->verifyData($field,"/^.+$/","Record Field");
	$IMSBase->verifyData($value,"/^.+$/","Record Value");
	$IMSBase->verifyData($sortColumn,"/^.*$/","Sort Column");
	if($sortColumn != "")
		$IMSBase->verifyData($sortDirection,"/^(ASC|DESC)$/","Sort Direction");

	
		
	if($field == 'Part')
	{
		$stmt = $sql->prepare("SELECT [Part] FROM dbo.Class_Data WHERE [id]='$recordID';");
		$stmt->execute();
		
		$oldPN_array = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$oldPN = $oldPN_array[0]['Part'];

		//remove lab part flag from old part number
		$sql->command("UPDATE dbo.Inventory SET [Lab_Part_Flag]='0' WHERE [Name]='$oldPN';");	
		//add lab part flag from new part number
		$sql->command("UPDATE dbo.Inventory SET [Lab_Part_Flag]='1' WHERE [Name]='$value';");	
		
	}
	
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
	
	$partNumber = "N/A";
	//Find Part Number
	foreach($dataArray as $entry)
	{
		if($entry['Id'] == $recordID)
			$partNumber = $entry['Part'];
	}
	
	
	$statusCode = '0';
	$statusMessage = "Class Data Record($recordID) - $field was updated with $value";
	$log->add_log($sessionID,'Information',$statusMessage,$partNumber);
	
	
}
catch(PDOException $e)
{
	$statusCode = '1';
	$statusMessage = 'ModifyClassData SQLError: '.$e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);
	
}
catch(Exception $e)
{
	$statusCode = $e->getCode();
	$statusMessage = 'ModifyClassData Error: '. $e->getMessage();
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
	$IMSBase->GenerateXMLResponse($sessionID,$statusArray,NULL,$dataArray,"CLASS_DATA","CLASS_ENTRY");
//}	
?>