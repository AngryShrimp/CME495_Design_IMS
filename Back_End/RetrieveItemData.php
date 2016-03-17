<?PHP
/***********************************************************************
 * 	Script: RetrieveItemData.php
 * 	Description: Script retrieving data for a single part number for
 *      the quick access form.
 *
 *	Author: Craig Irvine (cri646@mail.usask.ca)
 *	Date: 13 January 2016
 *
 *	Inputs: SID: The session ID of the client
 *			PartNumber: The part number to retrieve data for.
 *
 *	Usage: 	RetrieveItemData.php?SID=<session id>&PartNumber=<part number>
 ***********************************************************************/
  
include "IMSBase.php";
include "IMSLog.php";
include "IMSSql.php";


$sessionID = "";
$partNumber = "";

$statusMessage = "";
$statusCode = "";
$runLevel = "";
$dataArray=NULL;


try
{
	if ($_SERVER["REQUEST_METHOD"] == "POST") 
	{
		$sessionID = $_POST["SID"];
		$partNumber = $_POST["PartNumber"];  
	}
	
	$IMSBase = new IMSBase();
	$log = new IMSLog();
	$sql = new IMSSql();
	
	//Set IMSLog options
	$opt_debugLog = $sql->getOption('Debug');
	if($opt_debugLog === false)
		$log->add_log($sessionID,'Warning','RetriveItem Warning: Debug Option missing or invalid.');
	else if($opt_debugLog == 'False')
		$log->opt_debug = false;	
	else 
		$log->opt_debug = true;
	
	$opt_logLoc = $sql->getOption('Log_File_Location');	
	if($opt_logLoc === false)
		$log->add_log($sessionID,'Warning','RetriveItem Warning: Log_File_Location Option missing or invalid.');
	else 
		$log->set_log_location($opt_logLoc);
		

	$runLevel = $sql->verifySID($sessionID); //No special permission required.

	$IMSBase->verifyData($partNumber,"/^.+$/","Part Number");
	
	
	$stmt = $sql->prepare("SELECT * FROM dbo.Inventory WHERE Name='$partNumber'");
	$stmt->execute();
	
	$result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
	
	if(count($result) == 0)
	{

		$statusCode = '1';
		$statusMessage = "RetriveItem: Part Number,$partNumber, does not exist in database.";
		$log->add_log($sessionID,'Warning',$statusMessage);

	}
	else if(count($result) > 1)
	{

		$statusCode = '1';
		$statusMessage = "RetriveItem: Part Number,$partNumber, has multiple instances in database.";
		$log->add_log($sessionID,'Warning',$statusMessage);
		
	}
	else
	{
	
		$dataArray = $stmt->fetch(PDO::FETCH_ASSOC);
		$statusCode = '0';
		$statusMessage = "RetriveItem: Part Number ,$partNumber, data has been retrieved.";
		$log->add_log($sessionID,'Debug',$statusMessage);
		
		
	}        
}
catch(PDOException $e)
{
	$statusCode = '1';
	$statusMessage = 'CreateNewItem SQLError: '.$e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);
	
}
catch(Exception $e)
{
	$statusCode = $e->getCode();
	$statusMessage = 'CreateNewItem Error: '. $e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);

}	
//finally()  PHP 5.5+, currently using 5.3.
//{
	$statusArray[0] = $statusCode;
	$statusArray[1] = $statusMessage;
	$statusArray[2] = $runLevel;
	//$dataArray will be null unless it was filled by $stmt->fetch()
	$IMSBase->GenerateXMLResponse($sessionID,$statusArray,$dataArray);
//}	
?>