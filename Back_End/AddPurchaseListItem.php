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
  
include "IMSBase.php";
include "IMSLog.php";
include "IMSSql.php";


$sessionID = "";

$statusMessage = "";
$statusCode = 0;
$runLevel = "";


$supplierNumber = "";
$itemLink = "";
$quantity = "";


try
{
	if ($_SERVER["REQUEST_METHOD"] == "POST") 
	{
		$sessionID = $_POST["SID"];
		$supplierNumber = $_POST["SN"];
		$itemLink = $_POST["IL"];
		$quantity = $_POST["QN"];		
	}
	
	$IMSBase = new IMSBase();
	$log = new IMSLog();
	$sql = new IMSSql();
	
	//Set IMSLog options
	$opt_debugLog = $sql->getOption('Debug');
	if($opt_debugLog === false)
		$log->add_log($sessionID,'Warning','AddPurchaseListItem Warning: Debug Option missing or invalid.');
	else if($opt_debugLog == 'False')
		$log->opt_debug = false;	
	else 
		$log->opt_debug = true;
		
	$opt_logLoc = $sql->getOption('Log_File_Location');	
	if($opt_logLoc === false)
		$log->add_log($sessionID,'Warning','AddPurchaseListItem Warning: Log_File_Location Option missing or invalid.');
	else 
		$log->set_log_location($opt_logLoc);
	

	$runLevel = $sql->verifySID($sessionID,"1"); //1 = Requires edit privileges.
        
	$sqlQuery = "INSERT INTO dbo.Purchase_List (Supplier_Part_Number, Item_Link, Quantity) VALUES ('" . 
					$supplierNumber . "','" .
					$itemLink . "','" . 
					$quantity . "');";
	
	
	$stmt = $sql->prepare($sqlQuery);
	$stmt->execute();
	
	
	
	
}
catch(PDOException $e)
{
	$statusCode = 1;
	$statusMessage = 'AddPurchaseListItem SQLError: '.$e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);
        echo "Error: " . $e->getMessage();
	
}
catch(Exception $e)
{
	$statusCode = $e->getCode();
	$statusMessage = 'AddPurchaseListItem SQLError: '. $e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);
        echo "Error: " . $e->getMessage();

}	
if ($statusCode == 0){
        $statusMessage = "Item added to purchase list.";
		$log->add_log($sessionID,'Info',$statusMessage);
    
        $statusArray[0] = $statusCode;
		$statusArray[1] = $statusMessage;
		$statusArray[2] = $runLevel;
        
        
		$IMSBase->GenerateXMLResponse($sessionID,$statusArray);
}	
?>