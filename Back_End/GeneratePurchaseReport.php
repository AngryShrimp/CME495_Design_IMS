<?PHP
/***********************************************************************
 * 	Script: GeneratePurchaseReport.php
 * 	Description: Script for generating a purchase report.
 *
 *	Author: Justin Fraser (jaf470@mail.usask.ca)
 *	Date: 27 January 2016
 *
 *	Inputs:     SID: The session ID of the client
 *				type: Type of report.  Can be full or manual entries only.
 *
 *	Usage: CreateNewItem.php?SID=<session ID>&type=<manual or blank>
 ***********************************************************************/
  
include "IMSBase.php";
include "IMSLog.php";
include "IMSSql.php";


$sessionID = "";

$statusMessage = "";
$statusCode = 0;

$tableType = "";


try
{
	if ($_SERVER["REQUEST_METHOD"] == "POST") 
	{
		$sessionID = $_POST["SID"];
		$tableType = $_POST["type"];
	}
	
	$IMSBase = new IMSBase();
	$log = new IMSLog();
	$sql = new IMSSql();
	
	//Set IMSLog options
	$opt_debugLog = $sql->getOption('Debug');
	if($opt_debugLog === false)
		$log->add_log($sessionID,'Warning','RetrieveBroswerData Warning: Debug Option missing or invalid.');
	else if($opt_debugLog == '0')
		$log->opt_debug = false;	
	else 
		$log->opt_debug = true;

	$IMSBase->verifyData($sessionID,"/^.+$/");
        
	if ($tableType == "manual")
		$sqlQuery = "SELECT Supplier_Part_Number, Item_Link, Quantity FROM dbo.Purchase_List;";	
	else
		$sqlQuery = "SELECT Supplier_Part_Number, Item_Link, Quantity FROM dbo.Inventory WHERE Quantity < Ordering_Threshold UNION 
			SELECT Supplier_Part_Number, Item_Link, Quantity FROM dbo.Purchase_List;";
	
	
	$stmt = $sql->prepare($sqlQuery);
	$stmt->execute();
	
	
	$dataArray = $stmt->fetchAll(PDO::FETCH_ASSOC);	
	
	
}
catch(PDOException $e)
{
	$statusCode = 1;
	$statusMessage = 'GeneratePurchaseReport SQLError: '.$e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);
        echo "Error: " . $e->getMessage();
	
}
catch(Exception $e)
{
	$statusCode = $e->getCode();
	$statusMessage = 'GeneratePurchaseReport Error: '. $e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);
        echo "Error: " . $e->getMessage();

}	
if ($statusCode == 0){
        $statusMessage = "Purchase report generated.";
		$log->add_log($sessionID,'Info',$statusMessage);
    
        $statusArray[0] = $statusCode;
		$statusArray[1] = $statusMessage;
        
        
		$IMSBase->GenerateXMLResponse($sessionID,$statusArray,NULL,$dataArray,"BROWSER","BROWSER_ENTRY");
}	
?>