<?php

include "IMSEmail.php";

$Supplier_Part_Number = "R2D2";
$Item_Link = "www.emailtest.com";
$Quantity = "42";

$statusCode = 0;

try {
	
	$IMSEmail = new IMSEmail();
	
	$IMSEmail->add_email($Supplier_Part_Number, $Item_Link, $Quantity);
	
	$statusCode = 1;
	
} catch (Exception $e){
	echo "Exception thrown with $e";
}
if ($statusCode == 1){
	echo "Success!";
}
?>