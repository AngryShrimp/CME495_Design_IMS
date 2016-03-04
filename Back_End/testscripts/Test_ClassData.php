<?PHP
/***********************************************************************
 * 	Script: Test_ClassData.php
 * 	Description: Script for testing AddNewClassData.php, ModifyClassData.php 
 *	,RetrieveClassData.php and RetrieveBrowserData.php
 *
 *	Author: Craig Irvine (cri646@mail.usask.ca)
 *	Date: 24 Feb 2016
 *
 ***********************************************************************/
 
include "IMSTest.php";

$test = new IMSTest(); 
 
$rand_class = $test->randomClassData();


//retrieve browser data for current part numbers
$browser = $test->curl_req("http://localhost/Back_End/RetrieveBrowserData.php","SID=id");
$browserEntryArray = $test->translateXMLtoArray($browser,"BROWSER");

//Take random existing part number
$rand_class['Part'] = $browserEntryArray[mt_rand(0,count($browserEntryArray)-1)]['Name'];

echo "Adding Class Data\n";
foreach($rand_class as $k => $v)
{
	echo "$k => $v\n";		
}

$add_options = "SID=id&Class=".$rand_class['Class']."&PartNumber=".$rand_class['Part']."&Quantity=".$rand_class['Quantity']."&Date=".$rand_class['Date'];
$output = $test->curl_req("http://localhost/Back_End/AddNewClassData.php",$add_options);

//test the output for errors	
$status_array = $test->translateXMLtoArray($output,"STATUS");

$status_code = $status_array['STATUS_CODE'];
$status_message = $status_array['STATUS_MESSAGE'];

if($status_code != "0")
{
	echo "***FAILED***XML Response indicated failure($status_code) with message: $status_message\n";
}



echo "Checking Class Data\n";

//Check class data entry
$output = $test->curl_req("http://localhost/Back_End/RetrieveClassData.php","SID=id");


//test the output for errors	
$status_array = $test->translateXMLtoArray($output,"STATUS");

$status_code = $status_array['STATUS_CODE'];
$status_message = $status_array['STATUS_MESSAGE'];

if($status_code != "0")
{
	echo "***FAILED***XML Response indicated failure($status_code) with message: $status_message\n";
}

$class_data_array = $test->translateXMLtoArray($output,"CLASS_DATA");

foreach(array_reverse($class_data_array) as $class_data_entry)
{
	if($rand_class['Class'] == $class_data_entry['Class'])
	{
		echo "Found Class Entry...checking\n";
		foreach($class_data_entry as $k => $v)
		{
			if($k != "Id")
			{
				if((string)$class_data_entry[$k] == (string)$rand_class[$k])
				{
					echo "$k matches.\n";
				}
				else
				{
					echo "***FAILED***Class data does not match for record: ".$class_data_entry['Id'].
					" Field: ".$k." ".$class_data_entry[$k]." != ".$rand_class[$k]."\n";					
				}
			}
		}		
	}
}





 
?>