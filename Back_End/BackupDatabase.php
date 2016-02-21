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

$query = "
BACKUP DATABASE IMS TO DISK = N'C:\\backup\IMS_Backup.bak' 
WITH NOFORMAT, NOINIT, NAME = N'dbname Database Backup Test', 
SKIP, NOREWIND, NOUNLOAD
";

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
if ( ($stmt = sqlsrv_query($conn, $query)) )
{
	do 
	{
		print_r(sqlsrv_errors());
		echo " * ---End of result --- *\r\n";
	} while ( sqlsrv_next_result($stmt) ) ;
	sqlsrv_free_stmt($stmt);
}
sqlsrv_configure("WarningsReturnAsErrors", 1);
sqlsrv_close($conn);
?>