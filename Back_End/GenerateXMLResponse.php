<?php

include "RandomItemGenerator.php";

$item_array = array(randomItem(),randomItem(),randomItem(),randomItem());

GenerateXMLResponse("QQQQ",
					 array("Error","Status"),
					 array("Craig","Jenny","George"),
					 NULL,
					 $item_array);


function GenerateXMLResponse($session_ID,
							 $status_array,
							 $query_suggest_array = NULL,
							 $q_access_array = NULL,
							 $browser_array = NULL,
							 $log_array = NULL )
{
	$xml = new XMLWriter();
	
	$xml->openMemory();
	$xml->startDocument('1.0','UTF-8');
	
	$xml->startElement("IMS_RESPONSE");
		
		$xml->startElement("SID");
			$xml->text($session_ID);
		$xml->endElement();
		
		$xml->startElement("STATUS");
			$xml->startElement("ERROR_CODE");
				$xml->text($status_array[0]);
			$xml->endElement();
			$xml->startElement("STATUS_CODE");
				$xml->text($status_array[1]);		
			$xml->endElement();
		$xml->endElement();	
		
		if(!($query_suggest_array == NULL))
		{
			$xml->startElement("QUERY_SUGGEST");
				foreach($query_suggest_array as $suggestion)
				{
					$xml->startElement("SUGGESTION");
						$xml->text($suggestion);			
					$xml->endElement();
				}
			$xml->endElement();
		}
		
		if(!($q_access_array == NULL))
		{
			$xml->startElement("QACCESS");
				$xml->startElement("ITEMID");
					$xml->writeAttribute("ITEMID",$q_access_array["ItemID"]);
				$xml->endElement();
				$xml->startElement("QUANITY");
					$xml->text($q_access_array["Quantity"]);
				$xml->endElement();
				$xml->startElement();
					$xml->text($q_access_array["Description"]);			
				$xml->endElement();
			$xml->endElement();
		
		}
		
		if(!($browser_array == NULL))
		{
			$xml->startElement("BROWSER");
			foreach($browser_array as $browser_entry)
			{
				$xml->startElement("BROWSER_ENTRY");
				
					foreach($browser_entry as $key => $data)
					{
						$xml->startElement($key);
							$xml->text($data);
						$xml->endElement();					
					}				
	
				$xml->endElement();
			}
			$xml->endElement();
		
		}
		
		
		if(!($$log_array == NULL))
		{
			$xml->startElement("LOG");
				foreach($log_array as $log_entry)
				{
					$xml->startElement("LOG_ENTRY");
						foreach($log_entry as $key => $data)
						{
							$xml->startElement($key);
								$xml->text($data);
							$xml->endElement();					
						}
					$xml->endElement();
				}
			
			
			$xml->endElement();
		
		}
		
	
	$xml->endElement();
	
	echo $xml->outputMemory(true);
	


}
?>