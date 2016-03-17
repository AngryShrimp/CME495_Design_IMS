<?PHP
/***********************************************************************
 * 	Script: RetrieveEmailAddress.php
 * 	Description: Script retrieving data for a single part number for
 *      the quick access form.
 *
 *	Author: Craig Irvine (cri646@mail.usask.ca)
 *	Date: 27 Feb 2016
 *
 *	Inputs: SID: The session ID of the client
 *
 *	Usage: 	RetrieveEmailAddress.php?SID=<session id>
 ***********************************************************************/
   
include "IMSBase.php";
include "IMSLog.php";
include "IMSSql.php";


$sessionID = "";

$statusMessage = "";
$statusCode = "";
$runLevel = "";
$dataArray=NULL;


try
{
	if ($_SERVER["REQUEST_METHOD"] == "POST") 
	{
		$sessionID = $_POST["SID"];
	}
	
	$IMSBase = new IMSBase();
	$log = new IMSLog();
	$sql = new IMSSql();
	
	//Set IMSLog options
	$opt_debugLog = $sql->getOption('Debug');
	if($opt_debugLog === false)
		$log->add_log($sessionID,'Warning','RetrieveEmailAddress Warning: Debug Option missing or invalid.');
	else if($opt_debugLog == 'False')
		$log->opt_debug = false;	
	else 
		$log->opt_debug = true;
		
	$opt_logLoc = $sql->getOption('Log_File_Location');	
	if($opt_logLoc === false)
		$log->add_log($sessionID,'Warning','RetrieveEmailAddress Warning: Log_File_Location Option missing or invalid.');
	else 
		$log->set_log_location($opt_logLoc);

	$runLevel = $sql->verifySID($sessionID); //No special permissions required.

	//build the SQL statement
	$sqlQuery = "SELECT * FROM dbo.Emails;";


	$stmt = $sql->prepare($sqlQuery);
	$stmt->execute();
	
	$dataArray = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
	
	$statusCode = '0';
	$statusMessage = "RetrieveEmailAddress: ".count($dataArray)." Entries in Email Address Table.";
	$log->add_log($sessionID,'Information',$statusMessage);

	       
}
catch(PDOException $e)
{
	$statusCode = '1';
	$statusMessage = 'RetrieveEmailAddress SQLError: '.$e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);
	
}
catch(Exception $e)
{
	$statusCode = $e->getCode();
	$statusMessage = 'RetrieveEmailAddress Error: '. $e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);

}	
//finally()  PHP 5.5+, currently using 5.3.
//{
	$statusArray[0] = $statusCode;
	$statusArray[1] = $statusMessage;
	$statusArray[2] = $runLevel;
	//$dataArray will be null unless it was filled by $stmt->fetch()
	$IMSBase->GenerateXMLResponse($sessionID,$statusArray,NULL,$dataArray,"EMAIL_LIST","EMAIL_ENTRY");
//}	
?>