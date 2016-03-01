<?PHP
/***********************************************************************
 * 	Script: CreateNewItem_test.php
 * 	Description: Script for testing CreateNewItem.php, ModifyItem.php 
 *	and RetrieveItem.php
 *
 *	Author: Craig Irvine (cri646@mail.usask.ca)
 *	Date: 10 January 2016
 *
 ***********************************************************************/
 
include "IMSTest.php";

$test = new IMSTest(); 

$rand_item = $test->randomItem();

$PN = $rand_item["Name"];

foreach($rand_item as $k => $v)
{
	if($k == 'Name')	
	{
		echo "Creating Item Number: ".$rand_item['Name']."\n";
		$script_loc = "http://localhost/CreateNewItem.php";
		$script_opts =  "SID=ID&PartNumber=$PN";
	}
	else
	{
		echo "Modifying Item Number ".$rand_item['Name']." with Field:$k => Value:$v\n";
		$script_loc = "http://localhost/ModifyItem.php";
		$script_opts = "SID=ID&PartNumber=$PN&Field=$k&Value=$v";
	}

	$output = $test->curl_req($script_loc,$script_opts);
	
	//test the output for errors	
	$status_array = $test->translateXMLtoArray($output,"STATUS");
	
	$status_code = $status_array['STATUS_CODE'];
	$status_message = $status_array['STATUS_MESSAGE'];
	
	if($status_code != "0")
	{
		echo "***FAILED***XML Response indicated failure($status_code) with message: $status_message\n";
	}
		
}

echo "Checking Item\n";

$output = $test->curl_req("http://localhost/RetrieveItemData.php","SID=ID&PartNumber=$PN");


//test the output for errors	
$status_array = $test->translateXMLtoArray($output,"STATUS");

$status_code = $status_array['STATUS_CODE'];
$status_message = $status_array['STATUS_MESSAGE'];

if($status_code != "0")
{
	echo "***FAILED***XML Response indicated failure($status_code) with message: $status_message\n";
}


$item_array = $test->translateXMLtoArray($output,"QACCESS");

foreach($rand_item as $k => $v)
{	
	echo "Checking $k...";
	if($rand_item[$k] == $item_array[$k])
	{
		echo "Passed.\n";
	}
	else
	{
		echo "***FAILED*** ".$rand_item[$k]. " != " . $item_array[$k]."\n";
	} 
}

 
?>