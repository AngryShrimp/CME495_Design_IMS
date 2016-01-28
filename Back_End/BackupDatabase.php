<?php
/***********************************************************************
 * 	Script: BackupDatabase.php
 * 	Description: Script for backing up IMS database.
 *
 *	Author: Justin Fraser (jaf470@mail.usask.ca)
 *	Date: 27 January 2016
 *
 *	Inputs:     (int) SID: The session ID of the client.
 *                  (String) Path: Path to save database file
 *                  (String) Filename: Name of the backup
 *
 *	Usage: BackupDatabase.php?SID=<session ID>&Path=<eg. C:\backup>
 *             &Filename=<backup.bak>
 * 
 * 
 *      Issues: Permissions issues prevent script from running.
 ***********************************************************************/
  
include "IMSBase.php";
include "IMSLog.php";
include "IMSSql.php";


$sessionID = "";
$path = "";
$filename = "";
$database = IMS;

$statusMessage = "";
$statusCode = 0;


try
{
	if ($_SERVER["REQUEST_METHOD"] == "POST") 
	{
		$sessionID = $_POST["SID"];
		$path = $_POST["Path"];  
                $filename = $_POST["Filename"];
	}


	$IMSBase = new IMSBase();
	$log = new IMSLog();
	$sql = new IMSSql();

	$IMSBase->verifyData($path,"/^.+$/");
	$IMSBase->verifyData($sessionID,"/^.+$/");
        $IMSBase->verifyData($filename,"/^.+$/");	
                
        $fullPath = $path;
        $fullPath .= '\\';
        $fullPath .= $filename;
        
        $fullPath = "'$fullPath'";        
        
        $sql->command("BACKUP DATABASE $database TO DISK = $fullPath");
		
	
}
catch(PDOException $e)
{
	$statusCode = 1;
	$statusMessage = 'BackupDatabase SQLError: '.$e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);
        echo "Error: " . $e->getMessage();
	
}
catch(Exception $e)
{
	$statusCode = 1;
	$statusMessage = 'BackupDatabase Error: '. $e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);
        echo "Error: " . $e->getMessage();

}

if ($statusCode == 0){
        $statusMessage = "Database backed up to $fullPath successfully";
	$log->add_log($sessionID,'Info',$statusMessage);
    
        $statusArray[0] = $statusCode;
	$statusArray[1] = $statusMessage;
        
        
        echo "Script execution successful\n\n\n";
	$IMSBase->GenerateXMLResponse($sessionID,$statusArray);
}
?>