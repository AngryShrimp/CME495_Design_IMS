<?PHP
include "IMSBase.php";
include "IMSLog.php";
include "IMSSql.php";


$sessionID = "";

$statusMessage = "";
$statusCode = "";



	if ($_SERVER["REQUEST_METHOD"] == "POST") 
	{
		$sessionID = $_POST["SID"];
	}
	
	$IMSBase = new IMSBase();
	$log = new IMSLog();
	$sql = new IMSSql();

	$IMSBase->verifyData($sessionID,"/^.+$/");
	
	$sql->command('DELETE FROM dbo.Purchase_List');
	
	$sql->command('INSERT INTO dbo.Purchase_List ([Vendor Part Number]) SELECT 
	[Supplier Part Number] FROM dbo.Inventory WHERE Quantity < 1000');
	
	$statusCode = '0';
	$statusMessage = 'Purchase Report Generated';
	
	$log->add_log($sessionID,'Info',$statusMessage);
	
	$statusArray[0] = $statusCode;
	$statusArray[1] = $statusMessage;
	$IMSBase->GenerateXMLResponse($sessionID,$statusArray);
?>
