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

$statusMessage = '';
$statusCode = 0;
$sessionID = '';
$dataArray = '';
$query = array("USE master",
		"ALTER DATABASE IMS SET SINGLE_USER WITH ROLLBACK IMMEDIATE",
		"RESTORE DATABASE IMS FROM DISK = N'C:\\backup\IMS_Backup.bak'",
		"ALTER DATABASE IMS SET MULTI_USER"
		
);

$server = 'JUSTIN-PC\SQLEXPRESS';
$login = 'IMSBackup';
$password = 'backup';
$DB = 'IMS';

try {
	
	$IMSBase = new IMSBase();
	$log = new IMSLog();
	
	if ($_SERVER["REQUEST_METHOD"] == "POST")
		$sessionID = $_POST["SID"];
	

	$conn = sqlsrv_connect($server,array('UID'=>$login, 'PWD'=>$password,'Database'=>$DB,'CharacterSet'=>'UTF-8'));
	
	if ( !$conn )
	{
		print_r(sqlsrv_errors());
		exit;
	}
	
	sqlsrv_configure("WarningsReturnAsErrors", 0);
foreach ($query as $current){
	if ( ($stmt = sqlsrv_query($conn, $current)) )
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
}
	sqlsrv_configure("WarningsReturnAsErrors", 1);
	
	sqlsrv_close($conn);
}
catch(PDOException $e)
{

	$statusCode = 1;
	$statusMessage = 'RestoreDatabase SQLError: '.$e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);
        echo "Error: " . $e->getMessage();
    sqlsrv_configure("WarningsReturnAsErrors", 1);
    sqlsrv_close($conn);
	
}
catch(Exception $e)
{

	$statusCode = $e->getCode();
	$statusMessage = 'RestoreDatabase Error: '. $e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);
        echo "Error: " . $e->getMessage();
    sqlsrv_configure("WarningsReturnAsErrors", 1);
    sqlsrv_close($conn);

}

if ($statusCode == 0){
	$statusMessage = 'Database restore successful.';

	$log->add_log($sessionID,'Info',$statusMessage);
	$statusArray[0] = $statusCode;
	$statusArray[1] = $statusMessage;


	$IMSBase->GenerateXMLResponse($sessionID,$statusArray,NULL,$dataArray,"BROWSER","BROWSER_ENTRY");
}
?>
