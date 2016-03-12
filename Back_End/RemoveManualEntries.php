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
        $statusMessage = "Deleted ". $N . " Item(s) from list: ";
        
        for($i=0; $i < $N; $i++)
        {
        	$statusMessage .= $aItem[$i] . " ";
        }
        
		$log->add_log($sessionID,'Info',$statusMessage);
    
        $statusArray[0] = $statusCode;
		$statusArray[1] = $statusMessage;
        
        
		$IMSBase->GenerateXMLResponse($sessionID,$statusArray, NULL, NULL, $dataArray);
}	
?>
