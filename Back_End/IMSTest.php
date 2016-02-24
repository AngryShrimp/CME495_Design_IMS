<?php
/***********************************************************************
 * 	Class: IMSTest
 * 	Description: Class used to test php scripts used in the IMS system.
 *
 *	Author: Craig Irvine (cri646@mail.usask.ca)
 *	Date: 08 January 2016
 *
 ***********************************************************************/

class IMSTest
{

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
		$man_part_number = $this->randomString(1,30);
		
		//Manufactures Name
		$man_name = $this->randomString(1,30);



		//Item Description
		$item_description = $this->randomString(1,100);

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

		$item_url = "www.".$this->randomString(1,20).".com";


			
		
		//gather data in an array
		$item_data_array = array( "Name" => $part_number,
								 "Supplier_Part_Number" => $man_part_number,
								 "Suppliers_Name" => $man_name,
								 "Description" => $item_description,
								 "Quantity" => $item_quantity,
								 "Ordering_Threshold" => $ordering_threshold,
								 "Location" => $item_location,
								 //"Flags" => $item_flags,
								 "Item_Link" => $item_url,
								 "Type" => $part_type_array[$part_type],
								 "Value" => $part_value );
								 
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
	
	
	function translateXMLtoArray($xml,$section)
	{
		$XMLData = new SimpleXMLElement($xml);

		$array;
		foreach($XMLData->children() as $level1)
		{
			if($level1->getName() == $section)
			{
				foreach($level1->children() as $level2)
				{
					$array[$level2->getName()] = $level2;		
				}
			
			}
		}
		return $array;
	}
	
}

?>
