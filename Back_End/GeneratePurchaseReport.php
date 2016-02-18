<?PHP
/***********************************************************************
 * 	Script: GeneratePurchaseReport.php
 * 	Description: Script for generating a purchase report.
 *
 *	Author: Justin Fraser (jaf470@mail.usask.ca)
 *	Date: 27 January 2016
 *
 *	Inputs:     SID: The session ID of the client
 *		
 *
 *	Usage: CreateNewItem.php?SID=<session ID>
 ***********************************************************************/
  
include "IMSBase.php";
include "IMSLog.php";
include "IMSSql.php";


$sessionID = "";

$statusMessage = "";
$statusCode = 0;


try
{
        if ($_SERVER["REQUEST_METHOD"] == "POST") 
        {
		$sessionID = $_POST["SID"];
        }
	
	$IMSBase = new IMSBase();
	$log = new IMSLog();
	$sql = new IMSSql();

	$IMSBase->verifyData($sessionID,"/^.+$/");
        
	
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
	$statusCode = 1;
	$statusMessage = 'GeneratePurchaseReport SQLError: '. $e->getMessage();
	$log->add_log($sessionID,'Error',$statusMessage);
        echo "Error: " . $e->getMessage();

}	
if ($statusCode == 0){
        $statusMessage = "Purchase report generated.";
		$log->add_log($sessionID,'Info',$statusMessage);
    
        $statusArray[0] = $statusCode;
		$statusArray[1] = $statusMessage;
        
        
		$IMSBase->GenerateXMLResponse($sessionID,$statusArray,NULL,NULL,$dataArray);
}	
?>