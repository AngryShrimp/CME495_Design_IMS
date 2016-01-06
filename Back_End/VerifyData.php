<?php


$data = $_REQUEST["data"];
$pattern = $_REQUEST["regex"];

$ver_check = VerifyData($data,$pattern);

if ($ver_check == TRUE)
	echo "True";
else if ($ver_check == FALSE)
	echo "False";




?>