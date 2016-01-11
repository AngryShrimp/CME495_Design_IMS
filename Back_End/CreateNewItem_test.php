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
 
 
 $rand_item = test->randomItem();
 
 
 $run_php = "CreateNewItem.php?PartNumber=".$rand_item['PART_NUMBER'];
 
  
?>