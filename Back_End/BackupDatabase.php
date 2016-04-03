<?php
/***********************************************************************
 * 	Script: BackupDatabase.php
 * 	Description: Script for backing up IMS database.
 *
 *	Author: Justin Fraser (jaf470@mail.usask.ca), using
 *  Code written by: Robert Johnson
 *  Reference:
 *  https://social.msdn.microsoft.com/Forums/sqlserver/en-US/e0908b2f-4afa-4626-830d-9683486186c8/backup-database?forum=sqldriverforphp
 *  
 *	Date: 18 February 2016
 ***********************************************************************/

header('content-type: text/plain;charset=UTF-8');

include "IMSLog.php";
include "IMSBase.php";
include "IMSSql.php";

$statusMessage = '';
$statusCode = 0;
$sessionID = '';
$dataArray = '';


try {
	
	$IMSBase = new IMSBase();
	$log = new IMSLog();
	$sql = new IMSSql();
	
	if ($_SERVER["REQUEST_METHOD"] == "POST")
		$sessionID = $_POST["SID"];
	
		//Set IMSLog options
		$opt_debugLog = $sql->getOption('Debug');
		if($opt_debugLog === false)
			$log->add_log($sessionID,'Warning','CheckThresholds Warning: Debug Option missing or invalid.');
		else if($opt_debugLog == 'False')
			$log->opt_debug = false;
		else
			$log->opt_debug = true;
		
		$opt_logLoc = $sql->getOption('Log_File_Location');
		if($opt_logLoc === false)
			$log->add_log($sessionID,'Warning','CheckThresholds Warning: Log_File_Location Option missing or invalid.');
		else
			$log->set_log_location($opt_logLoc);
		
		$runLevel = $sql->verifySID($sessionID,"1");
							
	$arr = $sql->gatherSQLCredentials(); 

	$query = "
			BACKUP DATABASE IMS TO DISK = N".$arr["location"]."
			WITH NOFORMAT, INIT, NAME = N'dbname Database Backup Test',
			SKIP, NOREWIND, NOUNLOAD
			";
	
	$conn = sqlsrv_connect($arr["servername"],array('UID'=>$arr["username"], 'PWD'=>$arr["password"],'Database'=>$arr["db"],'CharacterSet'=>'UTF-8'));
	
	if ( !$conn )
	{
		print_r(sqlsrv_errors());
		exit;
	}
	
	sqlsrv_configure("WarningsReturnAsErrors", 0);
	if ( ($stmt = sqlsrv_query($conn, $query)) )
	{
		do 
		{
			echo "\n";
		    if( ($errors = sqlsrv_errors() ) != null) {
	        foreach( $errors as $error ) {
	            //echo ("SQLSTATE: ".$error[ 'SQLSTATE']."\n");
	            $dataArray .= "SQLSTATE: ".$error[ 'SQLSTATE']."\n";
	            //echo ("code: ".$error[ 'code']."\n");
	            $dataArray .= "code: ".$error[ 'code']."\n";
	            //echo ("message: ".$error[ 'message']."\n");
	            $dataArray .= "message: ".$error[ 'message']."\n";
	        }
		    }
			//print_r(sqlsrv_errors());
			$dataArray .= "* ---End of result --- *\r\n";
			//echo "* ---End of result --- *\r\n";
		} while ( sqlsrv_next_result($stmt) ) ;
		sqlsrv_free_stmt($stmt);
	}
	sqlsrv_configure("WarningsReturnAsErrors", 1);
	
	sqlsrv_close($conn);
}
catch(PDOException $e)
{

	$statusCode = 1;
	$statusMessage = 'BackupDatabase SQLError: '.$e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);
        echo "Error: " . $e->getMessage();
    sqlsrv_configure("WarningsReturnAsErrors", 1);
    sqlsrv_close($conn);
	
}
catch(Exception $e)
{

	$statusCode = $e->getCode();
	$statusMessage = 'BackupDatabase Error: '. $e->getMessage();
	if(!$log->add_log($sessionID,'Error',$statusMessage,"N/A",true))
	{
		$statusMessage = $statusMessage." **Logging Failed**";
	}
        echo "Error: " . $e->getMessage();
    sqlsrv_configure("WarningsReturnAsErrors", 1);
    sqlsrv_close($conn);

}

if ($statusCode == 0){
	$statusMessage = 'Database backup successful.';

	$log->add_log($sessionID,'Information',$statusMessage);
	$statusArray[0] = $statusCode;
	$statusArray[1] = $statusMessage;


	$IMSBase->GenerateXMLResponse($sessionID,$statusArray,NULL,$dataArray,"BROWSER","BROWSER_ENTRY");

}
?>