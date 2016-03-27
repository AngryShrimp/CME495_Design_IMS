<?php
/***********************************************************************
 * 	Script: CreateDatabase.php
 * 	Description: Script for creating the IMS database
 *
 *	Author: Justin Fraser (jaf470@mail.usask.ca), using
 *  
 *	Date: 26 March 2016
 ***********************************************************************/

header('content-type: text/plain;charset=UTF-8');

include "IMSLog.php";
include "IMSBase.php";

$statusMessage = '';
$statusCode = 0;
$sessionID = '';
$dataArray = '';
$query = array("USE Master",
		"CREATE DATABASE IMS",
		"CREATE TABLE [IMS].[dbo].[Class_Data] (
				Id int NOT NULL PRIMARY KEY IDENTITY (1,1),
				Class varchar(50) NOT NULL,
				Part varchar(50) NOT NULL,
				Quantity int NOT NULL,
				Date date NOT NULL)",
		"CREATE TABLE [IMS].[dbo].[Emails] (
				Id int NOT NULL PRIMARY KEY IDENTITY (1,1),
				Recipients varchar(MAX) NOT NULL,
				Email varchar(MAX) NOT NULL)",
		"CREATE TABLE [IMS].[dbo].[Inventory] (
				Id int NOT NULL IDENTITY (1,1),
				Name varchar(50) NOT NULL PRIMARY KEY DEFAULT 'Empty',
				Supplier_Part_Number varchar(50) NOT NULL DEFAULT 'Empty',
				Quantity int NOT NULL DEFAULT 0,
				Description varchar(MAX) NOT NULL DEFAULT 'Empty',
				Location varchar(50) NOT NULL DEFAULT 'Empty',
				Type varchar(50) NOT NULL DEFAULT 'Empty',
				Ordering_Threshold int NOT NULL DEFAULT 0,
				Value varchar(50) NOT NULL DEFAULT 'Empty',
				Suppliers_Name varchar(50) NOT NULL DEFAULT 'Empty',
				Item_Link varchar(MAX) NOT NULL DEFAULT 'Empty',
				Consumable_Flag bit NOT NULL DEFAULT 0,
				Equipment_Flag bit NOT NULL DEFAULT 0,
				Lab_Part_Flag bit NOT NULL DEFAULT 0,
				Threshold_Reported bit NOT NULL DEFAULT 0,
				Lab_Quantity int NOT NULL DEFAULT 0)",
		"CREATE TABLE [IMS].[dbo].[Options] (
				[Option] varchar(50) NOT NULL PRIMARY KEY,
				Value varchar(MAX) NOT NULL)",
		"CREATE TABLE [IMS].[dbo].[Purchase_List] (
				Supplier_Part_Number varchar(50) NOT NULL PRIMARY KEY,
				Item_Link varchar(MAX) NOT NULL,
				Quantity int NOT NULL DEFAULT 0,
				Threshold_Reported bit NOT NULL DEFAULT 0)",
		"CREATE TABLE [IMS].[dbo].[SID_List] (
				ID int NOT NULL IDENTITY (1,1),
				SID varchar(50) NOT NULL PRIMARY KEY,
				CLIENT_IP varchar(50) NOT NULL,
				EXPIRE datetime NOT NULL,
				[LEVEL] int NOT NULL)",
		"INSERT INTO [IMS].[dbo].[Options] ([Option], Value) VALUES 
		('Automated_Backups_Enabled', 'False'),
		('Backup_Frequency', '10'),
		('Credential_Expiry_Time_Seconds', '3500'),
		('Debug', 'True'),
		('Email_fromEmail', 'YourEmail@mail.usask.ca'),
		('Email_fromName', 'Your Name'),
		('Email_Pass', 'Email server password'),
		('Email_Server', 'smtp.usask.ca'),
		('Email_User', 'Email server login'),
		('Log_File_Location', 'C:\inetpub\wwwroot\Back_End\log\'),
		('Remote_Server_Enabled', 'True'),
		('Thresholds_Enabled', 'True')",
		"USE [IMS]",
		"CREATE USER [guest] WITHOUT LOGIN WITH DEFAULT SCHEMA=[dbo]",
		"GRANT CONNECT to guest",
		"GRANT DELETE to guest",
		"GRANT EXECUTE to guest",
		"GRANT INSERT to guest",
		"GRANT SELECT to guest",
		"GRANT UPDATE to guest",
		"CREATE USER [IMSBackup] WITHOUT LOGIN WITH DEFAULT SCHEMA=[dbo]",
		"EXEC db_addrolemember 'db_backupoperator', 'IMSBackup'",
		"EXEC db_addrolemember 'db_denydatareader', 'IMSBackup'",
		"EXEC db_addrolemember 'db_denydatawriter', 'IMSBackup'"
		
		
);

$server = 'JUSTIN-PC\SQLEXPRESS';
$login = 'IMSBackup';
$password = 'backup';
$DB = 'Master';

try {
	
	$IMSBase = new IMSBase();
	$log = new IMSLog();
	

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
	            $dataArray .= "SQLSTATE: ".$error[ 'SQLSTATE']."\n";
	            echo ("code: ".$error[ 'code']."\n");
	            $dataArray .= "code: ".$error[ 'code']."\n";
	            echo ("message: ".$error[ 'message']."\n");
	            $dataArray .= "message: ".$error[ 'message']."\n";
	        }
		    }
			print_r(sqlsrv_errors());
			$dataArray .= "* ---End of result --- *\r\n";
			echo "* ---End of result --- *\r\n";
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
	if(!$log->add_log($sessionID,'Error',$statusMessage,"N/A",true))
	{
		$statusMessage = $statusMessage." **Logging Failed**";
	}
	
    echo "Error: " . $e->getMessage();
    sqlsrv_configure("WarningsReturnAsErrors", 1);
    sqlsrv_close($conn);

}

if ($statusCode == 0){
	$statusMessage = 'Database creation successful.';

	$log->add_log($sessionID,'Info',$statusMessage);
	$statusArray[0] = $statusCode;
	$statusArray[1] = $statusMessage;


	$IMSBase->GenerateXMLResponse($sessionID,$statusArray,NULL,$dataArray,"BROWSER","BROWSER_ENTRY");
}
?>
