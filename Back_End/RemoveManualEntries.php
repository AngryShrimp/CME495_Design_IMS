<?PHP
/***********************************************************************
 * 	Script: AddPurchaseListItem.php
 * 	Description: Script for adding an item to the purchase list.
 *
 *	Author: Justin Fraser (jaf470@mail.usask.ca)
 *	Date: 18 February 2016
 *
 *	Inputs:     SID: The session ID of the client
 *
 *
 *	Usage: CreateNewItem.php?SID=<session ID>$SN=<Alphanumeric>&IL=<Alphanumeric>&QN=<Integer>
 ***********************************************************************/
  
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "IMSBase.php";
include "IMSLog.php";
include "IMSSql.php";


$sessionID = "";

$statusMessage = "";
$statusCode = 0;

$supplierNumber = "";
$itemLink = "";
$quantity = "";
$somevalue = "";
$dataArray = "";
$aItem = "";
try
{
	$sql = new IMSSql();
	$IMSBase = new IMSBase();
	$log = new IMSLog();
	
	//Set IMSLog options
	$opt_debugLog = $sql->getOption('Debug');
	if($opt_debugLog === false)
		$log->add_log($sessionID,'Warning','RemoveManualEntries Warning: Debug Option missing or invalid.');
	else if($opt_debugLog == 'False')
		$log->opt_debug = false;	
	else 
		$log->opt_debug = true;
		
	$opt_logLoc = $sql->getOption('Log_File_Location');	
	if($opt_logLoc === false)
		$log->add_log($sessionID,'Warning','RemoveManualEntries Warning: Log_File_Location Option missing or invalid.');
	else 
		$log->set_log_location($opt_logLoc);
	
	if ($_SERVER["REQUEST_METHOD"] == "POST"){
		$aItem = $_POST['itemList'];
		$sessionID = $_POST['SID'];
	}

	
	if(empty($aItem))
	{
		//echo("No items to delete");
		$N = 0;
	}
	else
	{
		$N = count($aItem);

		
		for($i=0; $i < $N; $i++)
		{
			
			$sqlQuery = "DELETE FROM dbo.Purchase_List WHERE Supplier_Part_Number = '" . $aItem[$i] . "';";
			$stmt = $sql->prepare($sqlQuery);
			$stmt->execute();
		}
		/*
		echo("You Deleted $N items(s): ");
		for($i=0; $i < $N; $i++)
		{
			echo($aItem[$i] . " ");
		}
		*/
	}
	
	$sqlQuery = "SELECT Supplier_Part_Number, Item_Link, Quantity FROM dbo.Purchase_List;";
	
	$stmt = $sql->prepare($sqlQuery);
	$stmt->execute();
	
	
	$dataArray = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
catch(PDOException $e)
{
	$statusCode = 1;
	$statusMessage = 'RemoveManualEntries SQLError: '.$e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);
    echo "Error: " . $e->getMessage();
	
}
catch(Exception $e)
{
	$statusCode = $e->getCode();
	$statusMessage = 'RemoveManualEntries SQLError: '. $e->getMessage();
	if(!$log->add_log($sessionID,'Error',$statusMessage,"N/A",true))
	{
		$statusMessage = $statusMessage." **Logging Failed**";
	}
    echo "Error: " . $e->getMessage();

}	
if ($statusCode == 0){
        $statusMessage = "Deleted ". $N . " Item(s) from list: ";
        
        for($i=0; $i < $N; $i++)
        {
        	$statusMessage .= $aItem[$i] . " ";
        }
        
		$log->add_log($sessionID,'Information',$statusMessage);
    
        $statusArray[0] = $statusCode;
		$statusArray[1] = $statusMessage;
        
        
		$IMSBase->GenerateXMLResponse($sessionID,$statusArray, NULL, NULL, $dataArray);
}	
?>
