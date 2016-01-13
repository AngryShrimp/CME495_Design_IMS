<?PHP
/***********************************************************************
 * 	Script: CreateNewItem_test.php
 * 	Description: Script for testing CreateNewItem.php.
 *
 *	Author: Craig Irvine (cri646@mail.usask.ca)
 *	Date: 10 January 2016
 *
 ***********************************************************************/
 
include "IMSTest.php";
 
$test = new IMSTest();
 
 
$rand_item = $test->randomItem();
 
$ch = curl_init();
 
echo "Creating Part Number: ".$rand_item['Name']."\n";
 	
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);

$PN = $rand_item["Name"];

foreach($rand_item as $k => $v)
{
	
	if($k == 'Name')	
	{
		curl_setopt($ch, CURLOPT_URL, "http://localhost/CreateNewItem.php");
		curl_setopt($ch, CURLOPT_POSTFIELDS, "SID=ID&PartNumber=$PN");
	}
	else
	{
		curl_setopt($ch, CURLOPT_URL, "http://localhost/ModifyItem.php");
		curl_setopt($ch, CURLOPT_POSTFIELDS, "SID=ID&PartNumber=$PN&Field=$k&Value=$v");
	}

	if($k != 'Supplier Name' && $k != 'Flags' && $k != 'Link' && $k != 'MANUAL_REQ_VAL' && $k != 'MANUAL_REQ_DATE')
	{
		$output = curl_exec($ch);
		//echo $output."\n";
	}		
}

 
?>