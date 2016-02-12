<?PHP
/***********************************************************************
 * 	Script: RetrieveClassData.php
 * 	Description: Script retrieving data for a single part number for
 *      the quick access form.
 *
 *	Author: Craig Irvine (cri646@mail.usask.ca)
 *	Date: 13 January 2016
 *
 *	Inputs: SID: The session ID of the client
 *			PartNumber: The part number to retrieve data for.
 *
 *	Usage: 	RetrieveClassData.php?SID=<session id>&SortColumn=<sort column>&
 *              SortDirection=<ASC/DESC>
 ***********************************************************************/
  
include "IMSBase.php";
include "IMSLog.php";
include "IMSSql.php";


$sessionID = "";
$partNumber = "";

$statusMessage = "";
$statusCode = "";
$dataArray=NULL;


try
{
	if ($_SERVER["REQUEST_METHOD"] == "POST") 
	{
		$sessionID = $_POST["SID"];
		$sortColumn = $_POST["SortColumn"];  
		$sortDirection = $_POST["SortDirection"];
	}
	
	$IMSBase = new IMSBase();
	$log = new IMSLog();
	$sql = new IMSSql();

	$IMSBase->verifyData($sessionID,"/^.+$/");	
	$IMSBase->verifyData($sortColumn,"/^.*$/");
	if($sortColumn != "")
		$IMSBase->verifyData($sortDirection,"/^(ASC|DESC)$/");
	
	//build the SQL statement
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
	$statusMessage = "RetrieveClassData: ".count($dataArray)." Entries in Class Data Table. ($sortColumn $sortDirection)";
	$log->add_log($sessionID,'Information',$statusMessage);

	       
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
	//$dataArray will be null unless it was filled by $stmt->fetch()
	$IMSBase->GenerateXMLResponse($sessionID,$statusArray,NULL,NULL,NULL,NULL,$dataArray);
//}	
?>