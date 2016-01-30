<?PHP
/***********************************************************************
 * 	Script: QueryAutocomplete.php
 * 	Description: Script retrieving data for a single part number for
 *      the quick access form.
 *
 *	Author: Craig Irvine (cri646@mail.usask.ca)
 *	Date: 13 January 2016
 *
 *	Inputs: SID: The session ID of the client
 * 			Filter: String to filter browser data on.
 *
 *	Usage: 	QueryAutocomplete?SID=<session id>&Filter=<filter string>
 ***********************************************************************/
  
include "IMSBase.php";
include "IMSLog.php";
include "IMSSql.php";


$sessionID = "";
$filter = "";

$statusMessage = "";
$statusCode = "";
$suggestionArray=NULL;


try
{
	if ($_SERVER["REQUEST_METHOD"] == "POST") 
	{
		$sessionID = $_POST["SID"];
		$filter = $_POST["Filter"];
	}
	
	$IMSBase = new IMSBase();
	$log = new IMSLog();
	$sql = new IMSSql();
	
	$IMSBase->verifyData($sessionID,"/^.+$/");
	$IMSBase->verifyData($filter,"/^.+$/");
	
	
	$sqlQuery = "SELECT * FROM dbo.Inventory WHERE Name LIKE '%$filter%'"
				."OR Description LIKE '%$filter%' OR \"Supplier Part Number\" LIKE '%$filter%'";
	
	
	$stmt = $sql->prepare($sqlQuery);
	$stmt->execute();
	
	$dataArray = $stmt->fetchAll(PDO::FETCH_ASSOC);      
	
	$rowCount = count($dataArray);
	
	
	if($rowCount == 0)
	{
		$suggestionArray[] = "No Suggestion.";
	}
	else
	{
		foreach($dataArray as $rowData)
		{
			$suggestionArray[] = $rowData['Name']." - ".$rowData['Description']." - "
								.$rowData['Supplier Part Number'];
		}
	}
        
        
	$statusCode = '0';
	$statusMessage = "QueryAutocomplete: $rowCount auto-complete suggestions supplied for ($filter).";
	$log->add_log($sessionID,'Debug',$statusMessage);
        
	
}
catch(PDOException $e)
{
	$statusCode = '1';
	$statusMessage = 'QueryAutocomplete SQLError: '.$e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);
	
}
catch(Exception $e)
{
	$statusCode = '1';
	$statusMessage = 'QueryAutocomplete Error: '. $e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);

}	
//finally()  PHP 5.5+, currently using 5.3.
//{
	$statusArray[0] = $statusCode;
	$statusArray[1] = $statusMessage;
	$IMSBase->GenerateXMLResponse($sessionID,$statusArray,$suggestionArray);
//}	
?>