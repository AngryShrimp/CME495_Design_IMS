<?PHP
/***********************************************************************
 * 	Script: QueryAutocomplete.php
 * 	Description: Script retrieving data for a single part number for
 *      the quick access form.
 *
 *	Author: Craig Irvine ()
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
$runLevel = "";
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
	
	//Set IMSLog options
	$opt_debugLog = $sql->getOption('Debug');
	if($opt_debugLog === false)
		$log->add_log($sessionID,'Warning','QueryAutocomplete Warning: Debug Option missing or invalid.');
	else if($opt_debugLog == 'False')
		$log->opt_debug = false;	
	else 
		$log->opt_debug = true;
		
	$opt_logLoc = $sql->getOption('Log_File_Location');	
	if($opt_logLoc === false)
		$log->add_log($sessionID,'Warning','QueryAutocomplete Warning: Log_File_Location Option missing or invalid.');
	else 
		$log->set_log_location($opt_logLoc);
	
	$runLevel = $sql->verifySID($sessionID); //No Special privileges required.
	$IMSBase->verifyData($filter,"/^.+$/","Filter");
	
	
	$sqlQuery = "SELECT * FROM dbo.Inventory WHERE Name LIKE '%$filter%'";
				//."OR Description LIKE '%$filter%' OR \"Supplier Part Number\" LIKE '%$filter%'";
	
	
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
			
			$suggestionArray[] = array('Name' => $rowData['Name'],
									   'Description' => $rowData['Description'],
									   'Type' => $rowData['Type']);			
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
	$statusCode = $e->getCode();
	$statusMessage = 'QueryAutocomplete Error: '. $e->getMessage();
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
	$IMSBase->GenerateXMLResponse($sessionID,$statusArray,NULL,$suggestionArray,"QUERY_SUGGEST","SUGGESTION");
//}	
?>