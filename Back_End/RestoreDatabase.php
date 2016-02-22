<?php
/***********************************************************************
 * 	Script: RestoreDatabase.php
* 	Description: Script for restoring IMS database.
* 	
* 	Precondition: Database must be setup for SIMPLE recovery
*
*	Author: Justin Fraser (jaf470@mail.usask.ca), using
*
*	Date: 21 February 2016
***********************************************************************/

header('content-type: text/plain;charset=UTF-8');


$query = array("USE master",
		"ALTER DATABASE IMS SET SINGLE_USER WITH ROLLBACK IMMEDIATE",
		"RESTORE DATABASE IMS FROM DISK = N'C:\\backup\IMS_Backup.bak'",
		"ALTER DATABASE IMS SET MULTI_USER"
);

$server = 'JUSTIN-PC\SQLEXPRESS';
$login = 'IMSBackup';
$password = 'backup';
$DB = 'IMS';

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
            echo ("SQLSTATE: ".$error[ 'SQLSTATE']."\n");
            echo ("code: ".$error[ 'code']."\n");
            echo ("message: ".$error[ 'message']."\n");
        }
	    };
		
	} while ( sqlsrv_next_result($stmt) ) ;
	sqlsrv_free_stmt($stmt);
}
}

sqlsrv_configure("WarningsReturnAsErrors", 1);
sqlsrv_close($conn);
?>