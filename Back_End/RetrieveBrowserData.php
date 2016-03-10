<?PHP
/***********************************************************************
 * 	Script: RetrieveBroswerData.php
 * 	Description: Script retrieving data for a single part number for
 *      the quick access form.
 *
 *	Author: Craig Irvine (cri646@mail.usask.ca)
 *	Date: 13 January 2016
 *
 *	Inputs: SID: The session ID of the client
 *			SortColumn: The data column to sort. (Optional)
 *                  SortDirection: The sort direction (Ascending or Descending). (Optional)
 *              Filter: String to filter browser data on. (Optional)
 *
 *	Usage: 	RetrieveBroswerData?SID=<session id>&SortColumn=<sort column>&
 *              SortDirection=<ASC/DESC>&Filter=<filter string>
 ***********************************************************************/
  
include "IMSBase.php";
include "IMSLog.php";
include "IMSSql.php";


$sessionID = "";
$sortCoumn = "";
$sortDirection = "ASC"; //ascending is default.
$filter = "";

$statusMessage = "";
$statusCode = "";
$runLevel = "";
$dataArray=NULL;


try
{
	if ($_SERVER["REQUEST_METHOD"] == "POST") 
	{
		$sessionID = $_POST["SID"];
		$sortColumn = $_POST["SortColumn"];  
		$sortDirection = $_POST["SortDirection"];
		$filter = $_POST["Filter"];
	}
	
	$IMSBase = new IMSBase();
	$log = new IMSLog();
	$sql = new IMSSql();
	
	$runLevel = $sql->verifySID($sessionID); //No special permissions required.
	$IMSBase->verifyData($sortColumn,"/^.*$/","Sort Column");
	if($sortColumn != "")
		$IMSBase->verifyData($sortDirection,"/^(ASC|DESC)$/","Sort Direction");
	$IMSBase->verifyData($filter,"/^.*$/");
	
	
	$sqlQuery = "SELECT * FROM dbo.Inventory";
	
	//Build SQL Query	
	if($filter != "")
	{
            $sqlQuery = $sqlQuery." WHERE Name LIKE '%$filter%' or Description LIKE '%filter%'"
						." or [Supplier_Part_Number] LIKE '%$filter%' or Type LIKE '%$filter%'"
						." or Value LIKE '%$filter%'";
	}
	
	if($sortColumn != "")
	{
            $sqlQuery = $sqlQuery." ORDER BY $sortColumn $sortDirection";
	}
	
	$sqlQuery .= ";";
	
	
	
	
	$stmt = $sql->prepare($sqlQuery);
	$stmt->execute();
	
	
	$dataArray = $stmt->fetchAll(PDO::FETCH_ASSOC);      	
		
	$statusCode = '0';
	$statusMessage = "RetrieveBroswerData: Browser data filtered by ($filter) and sorted by ($sortColumn, $sortDirection) completed successfully.";
	$log->add_log($sessionID,'Debug',$statusMessage);
	
	
}
catch(PDOException $e)
{
	$statusCode = '1';
	$statusMessage = 'RetrieveBroswerData SQLError: '.$e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);
	
}
catch(Exception $e)
{
	$statusCode = '1';
	$statusMessage = 'RetrieveBroswerData Error: '. $e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);

}	
//finally()  PHP 5.5+, currently using 5.3.
//{
	$statusArray[0] = $statusCode;
	$statusArray[1] = $statusMessage;
	$statusArray[2] = $runLevel;
	//$dataArray will be null unless it was filled by $stmt->fetch()
	$IMSBase->GenerateXMLResponse($sessionID,$statusArray,NULL,$dataArray,"BROWSER","BROWSER_ENTRY");
//}	
?>