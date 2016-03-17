<?PHP
/***********************************************************************
 * 	Script: AddEmailAddress.php
 * 	Description: Add a new email address to the database.
 *
 *	Author: Craig Irvine (cri646@mail.usask.ca)
 *	Date: 27 Feb 2016
 *
 *	Inputs: SID: The session ID of the client
 *			Email: The Email Address to create the record for.
 *
 *	Usage: AddEmailAddress.php?SID=<session ID>&Email=<Email Address>
 ***********************************************************************/
  
include "IMSBase.php";
include "IMSLog.php";
include "IMSSql.php";


$sessionID = "";
$emailAddress = "";
$dataArray=NULL;

$statusMessage = "";
$statusCode = "";
$runLevel = "";


try
{
	if ($_SERVER["REQUEST_METHOD"] == "POST") 
	{
		$sessionID = $_POST["SID"];
		$emailAddress = $_POST["Email"];  		
	}


	$IMSBase = new IMSBase();
	$log = new IMSLog();
	$sql = new IMSSql();
	
	//Set IMSLog options
	$opt_debugLog = $sql->getOption('Debug');
	if($opt_debugLog === false)
		$log->add_log($sessionID,'Warning','AddEmailAddress Warning: Debug Option missing or invalid.');
	else if($opt_debugLog == 'False')
		$log->opt_debug = false;	
	else 
		$log->opt_debug = true;
		
	$opt_logLoc = $sql->getOption('Log_File_Location');	
	if($opt_logLoc === false)
		$log->add_log($sessionID,'Warning','AddEmailAddress Warning: Log_File_Location Option missing or invalid.');
	else 
		$log->set_log_location($opt_logLoc);
		

	$runLevel = $sql->verifySID($sessionID,"1"); //1 = Requires edit privileges.
	$IMSBase->verifyData($emailAddress,"/^.+@.+\..+$/","Email Address");	
	
	
	
	//add new item
	$sql->command("INSERT INTO dbo.Emails (Recipients,Email) VALUES ('$emailAddress','None');");	
	
	//retrieve new table.
	$sqlQuery = "SELECT * FROM dbo.Emails;";
	
	$stmt = $sql->prepare($sqlQuery);
	$stmt->execute();
	
	$dataArray = $stmt->fetchAll(PDO::FETCH_ASSOC);	
	
	$statusCode = '0';
	$statusMessage = "Email ($emailAddress) added to database.";
	$log->add_log($sessionID,'Information',$statusMessage);

}
catch(PDOException $e)
{
	$statusCode = '1';
	$statusMessage = 'AddEmailAddress SQLError: '.$e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);
	
}
catch(Exception $e)
{
	$statusCode = $e->getCode();
	$statusMessage = 'AddEmailAddress Error: '. $e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);

}	
//finally()  PHP 5.5+, currently using 5.3.
//{
	$statusArray[0] = $statusCode;
	$statusArray[1] = $statusMessage;
	$statusArray[2] = $runLevel;
	$IMSBase->GenerateXMLResponse($sessionID,$statusArray,NULL,$dataArray,"EMAIL_LIST","EMAIL_ENTRY");
//}	
?>