<?php



function randomItem()
{
	//PartNumber Generation
	$part_type_array = array( "C" => "Capacitor", 
							  "R" => "Resistor", 
							  "I" => "Inductor", 
							  "E" => "Equipment",
							  "Q" => "Transistor", 
							  "T" => "Transformer", 
							  "O" => "Other" ); 

	$part_type = array_rand($part_type_array);
	$part_value = rand(0,9).rand(0,9).rand(0,9);
							  
	$part_number = $part_type.$part_value."-".rand(0,9).rand(0,9);


	//Manufacture Part Number
	$man_part_number = randomString(0,30);
	
	//Manufactures Name
	$man_name = randomString(0,30);



	//Item Description
	$item_description = randomString(0,100);

	//Quantity
	$item_quantity = rand(0,9999);



	//ordering threshold
	$ordering_threshold = rand(0,9999);



	//Location
	//B##/D## format

	$item_location = "B".rand(0,9).rand(0,9)."D".rand(0,9).rand(0,9);


	//Flags
	//each flag is represented by a single characters
	$item_flags = rand(0,1).rand(0,1).rand(0,1);


	//Http link
	//in URL format www.url.com

	$item_url = "www.".randomString(0,20).".com";


	$manual_request_value = 0;

	$manual_request_date = "DD/MM/YYYY";
	
	
	//gather data in an array
	$item_data_array = array( "PART_NUMBER" => $part_number,
							 "MAN_PART_NUMBER" => $man_part_number,
							 "MAN_NAME" => $man_name,
							 "ITEM_DESCRIPTION" => $item_description,
							 "QUANTITY" => $item_quantity,
							 "OTHRESHOLD" => $ordering_threshold,
							 "LOCATION" => $item_location,
							 "FLAGS" => $item_flags,
							 "LINK" => $item_url,
							 "PART_TYPE" => $part_type_array[$part_type],
							 "PART_VALUE" => $part_value,
							 "MANUAL_REQ_VAL" => $manual_request_value,
							 "MANUAL_REQ_DATE" => $manual_request_date );
							 
	return $item_data_array;
	
}









function randomString($min_length,$max_length) 
{
	$string = "";
	
	$length = rand($min_length,$max_length);
	
	
	$exclude_range = range(ord(":"),ord("@"));
	$exclude_range = array_merge($exclude_range,range(ord("["),ord("`")));

	for ($i = 0; $i < $length; $i++)
	{
		//48 to 122 corresponds to 0 to z in ASCII		
		$rand_num = 0;		
		do
		{
			$rand_num = rand(48,122);
		}while(in_array($rand_num,$exclude_range));
		
		$string .= chr($rand_num);
	}

	return $string;
}

?>
