<?PHP
/***********************************************************************
 * 	Script: AddNewClassData.php
 * 	Description: Script for adding a new Class data to the IMS systems database.
 *
 *	Author: Craig Irvine (cri646@mail.usask.ca)
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

	$IMSBase->verifyData($classNumber,"/^.+$/");
	$IMSBase->verifyData($sessionID,"/^.+$/");	
	$IMSBase->verifyData($partNumber,"/^.+$/");	
	$IMSBase->verifyData($quantity,"/^[0-9]+$/");	
	$IMSBase->verifyData($date,"/^[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]$/");	
	$IMSBase->verifyData($sortColumn,"/^.*$/");
	if($sortColumn != "")
		$IMSBase->verifyData($sortDirection,"/^(ASC|DESC)$/");

	//Add date verification?

	
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
		$statusMessage = "Class data for ($classNumber) part number ($partNumber) created successfully.";
		$log->add_log($sessionID,'Information',$statusMessage);
		
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
	$statusCode = '1';
	$statusMessage = 'AddNewClassData Error: '. $e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);

}	
//finally()  PHP 5.5+, currently using 5.3.
//{
	$statusArray[0] = $statusCode;
	$statusArray[1] = $statusMessage;
	$IMSBase->GenerateXMLResponse($sessionID,$statusArray,NULL,NULL,NULL,NULL,$dataArray,"CLASS_DATA","CLASS_ENTRY");
//}	
?>