<?php
/***********************************************************************
 * 	Script: RemoveManualEntries.php
 * 	Description: Script for removing manual entries in the purchase report.
 *
 *	Author: Justin Fraser (jaf470@mail.usask.ca)
 *	Date: 18 February 2016
 *		
 *		
 *	References: http://www.html-form-guide.com/php-form/php-form-checkbox.html
 *
 *	Usage: <form action="RemoveManualEntries.php" method="post"> in ShoppingList.html
 ***********************************************************************/
  

include "IMSSql.php";

$sql = new IMSSql();

$aItem = $_POST['formItem'];

if(empty($aItem))
{
	echo("No items were selected");
}
else
{
	$N = count($aItem);

	echo("You Deleted $N items(s): ");
	for($i=0; $i < $N; $i++)
	{
		echo($aItem[$i] . " ");
		$sqlQuery = "DELETE FROM dbo.Purchase_List WHERE Supplier_Part_Number = '" . $aItem[$i] . "';";
		$stmt = $sql->prepare($sqlQuery);
		$stmt->execute();
	}
}
?>


