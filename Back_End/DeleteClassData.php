<?PHP
/***********************************************************************
 * 	Script: DeleteClassData.php
 * 	Description: Script for deleting class data from the database.
 *				 Returns a refreshed table for display.
 *	Author: Craig Irvine ()
 *	Date: 13 Feb 2016
 *
 *	Inputs: SID: The session ID of the client
 *			ID: ID of the class record data to be deleted.
 *			SortColumn: The data column to sort. (Optional)
 *          SortDirection: The sort direction (Ascending or Descending). (Optional)
 *
 *	Usage: DeleteClassData.php?SID=<session ID>&ID=<Record ID>&
 *							   SortColumn=<sort column>&SortDirection=<ASC/DESC>
 ***********************************************************************/
  
include "IMSBase.php";
include "IMSLog.php";
include "IMSSql.php";


$sessionID = "";
$id = "";
$sortColumn = "";
$sortDirection = "";

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
		$sortColumn = $_POST["SortColumn"];  
		$sortDirection = $_POST["SortDirection"];		
	}


	$IMSBase = new IMSBase();
	$log = new IMSLog();
	$sql = new IMSSql();
	
	//Set IMSLog options
	$opt_debugLog = $sql->getOption('Debug');
	if($opt_debugLog === false)
		$log->add_log($sessionID,'Warning','DeleteClassData Warning: Debug Option missing or invalid.');
	else if($opt_debugLog == 'False')
		$log->opt_debug = false;	
	else 
		$log->opt_debug = true;
		
	$opt_logLoc = $sql->getOption('Log_File_Location');	
	if($opt_logLoc === false)
		$log->add_log($sessionID,'Warning','DeleteClassData Warning: Log_File_Location Option missing or invalid.');
	else 
		$log->set_log_location($opt_logLoc);
	
	$runLevel = $sql->verifySID($sessionID,"1"); //1 = Requires edit privileges.
	$IMSBase->verifyData($id,"/^.+$/","Record ID");	
	$IMSBase->verifyData($sortColumn,"/^.*$/","Sort Column");
	if($sortColumn != "")
		$IMSBase->verifyData($sortDirection,"/^(ASC|DESC)$/","Sort Direction");
	
	
	//get part number from ID
	$sqlQuery = "SELECT [Part] FROM dbo.Class_Data WHERE Id=$id";
	
	$stmt = $sql->prepare($sqlQuery);
	$stmt->execute();	
	$dataArray = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$partNumber = $dataArray[0]['Part'];
	
	$sql->command("UPDATE dbo.Inventory SET [Lab_Part_Flag]='0' WHERE Name='$partNumber';");	
		
	//Delete record
	$sql->command("DELETE FROM dbo.Class_Data WHERE Id=$id;");
	
	$statusCode = '0';
	$statusMessage = "$partNumber has been removed from Class data. (Record $id)";
	$log->add_log($sessionID,'Information',$statusMessage,$partNumber);
	
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
	
	
	
}
catch(PDOException $e)
{
	$statusCode = '1';
	$statusMessage = 'DeleteClassData SQLError: '.$e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);
	
}
catch(Exception $e)
{
	$statusCode = $e->getCode();
	$statusMessage = 'DeleteClassData Error: '. $e->getMessage();
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