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
 
echo "Creating Part Number: ".$rand_item['PART_NUMBER']."\n";
 
//$run_php = "CreateNewItem.php?SID=ID&PartNumber=".$rand_item['PART_NUMBER'];
	
curl_setopt($ch, CURLOPT_URL, "http://localhost/CreateNewItem.php");
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, "SID=ID&PartNumber=".$rand_item['PART_NUMBER']);



$output = curl_exec($ch);
echo $output;

 
?>