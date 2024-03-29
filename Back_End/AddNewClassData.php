<?PHP
/***********************************************************************
 * 	Script: AddNewClassData.php
 * 	Description: Script for adding a new Class data to the IMS systems database.
 *
 *	Author: Craig Irvine ()
 *	Date: 13 January 2016
 *
 *	Inputs: SID: The session ID of the client
 *			Class: The Class create the record for.
 * 			PartNumber: The part to add to the record.
 *			Quantity: The quantity required.
 *			Date: The required date.
 * 			SortColumn: The data column to sort. (Optional)
 *          SortDirection: The sort direction (Ascending or Descending). (Optional)
 *
 *	Usage: AddNewClassData.php?SID=<session ID>&Class=<class>&PartNumber=<part number>&
 *			Quantity=<quantity>&Date=<date>&SortColumn=<sort column>&SortDirection=<ASC/DESC>
 ***********************************************************************/
  
include "IMSBase.php";
include "IMSLog.php";
include "IMSSql.php";


$sessionID = "";
$classNumber = "";
$partNumber = "";
$quantity = "";
$date = "";
$sortColumn = "";
$sortDirection = "";
$dataArray=NULL;

$statusMessage = "";
$statusCode = "";
$runLevel = "";


try
{
	if ($_SERVER["REQUEST_METHOD"] == "POST") 
	{
		$sessionID = $_POST["SID"];
		$classNumber = $_POST["Class"];  
		$partNumber = $_POST["PartNumber"];
		$quantity = $_POST["Quantity"];
		$date = $_POST["Date"];
		$sortColumn = $_POST["SortColumn"];  
		$sortDirection = $_POST["SortDirection"];		
	}


	$IMSBase = new IMSBase();
	$log = new IMSLog();
	$sql = new IMSSql();
	
	//Set IMSLog options
	$opt_debugLog = $sql->getOption('Debug');
	if($opt_debugLog === false)
		$log->add_log($sessionID,'Warning','AddNewClassData Warning: Debug Option missing or invalid.');
	else if($opt_debugLog == 'False')
		$log->opt_debug = false;	
	else 
		$log->opt_debug = true;
		
	$opt_logLoc = $sql->getOption('Log_File_Location');	
	if($opt_logLoc === false)
		$log->add_log($sessionID,'Warning','AddNewClassData Warning: Log_File_Location Option missing or invalid.');
	else 
		$log->set_log_location($opt_logLoc);
	
	$runLevel = $sql->verifySID($sessionID,"1"); //1 = Requires edit privileges.
	$IMSBase->verifyData($classNumber,"/^.+$/","Class");
	$IMSBase->verifyData($partNumber,"/^.+$/","Part Number");	
	$IMSBase->verifyData($quantity,"/^[0-9]+$/","Quantity");	
	$IMSBase->verifyData($date,"/^[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]$/", "Date");
	$IMSBase->verifyData($sortColumn,"/^.*$/","Sort Column");
	if($sortColumn != "")
		$IMSBase->verifyData($sortDirection,"/^(ASC|DESC)$/","Sort Direction");

	
	if($sql->exists($partNumber,'dbo.Inventory') == FALSE)
	{
		$statusCode = '1';
		$statusMessage = "AddNewClassData Error: $partNumber does not exist in database.";
		$log->add_log($sessionID,'Error',$statusMessage);
	}
	else
	{	
		//add new item
		$sql->command("INSERT INTO dbo.Class_Data (Class,Part,Quantity,Date) VALUES ('$classNumber','$partNumber',$quantity,'$date');");
		
		//set flag in dbo.Inventory
		$sql->command("UPDATE dbo.Inventory SET [Lab_Part_Flag]='1' WHERE Name='$partNumber';");
		
		
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
		$statusMessage = "$partNumber added to Class: $classNumber data.";
		$log->add_log($sessionID,'Information',$statusMessage,$partNumber);
		
	}

	
}
catch(PDOException $e)
{
	$statusCode = '1';
	$statusMessage = 'AddNewClassData SQLError: '.$e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);
	
}
catch(Exception $e)
{
	$statusCode = $e->getCode();
	$statusMessage = 'AddNewClassData Error: '. $e->getMessage();
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