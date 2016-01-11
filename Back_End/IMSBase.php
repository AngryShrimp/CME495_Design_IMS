<?php
/***********************************************************************
 * 	Class: IMSBase
 * 	Description: Class that contains all the base functions for use in
 *	IMS Back end scripts.
 *
 *	Author: Craig Irvine (cri646@mail.usask.ca)
 *	Date: 08 January 2016
 *
 ***********************************************************************/

class IMSBase
{

	public function verifyData($data,$RegEx)
	{
		
		$invalid_char_array = array( "<",
									 ">",
									 "(",
									 ")", );
									 
									 

		foreach(str_split($data) as $char)
			if(in_array($char,$invalid_char_array))
				throw new Exception('Invalid character in data');
				
		if(preg_match($RegEx,$data) == TRUE)
			return;
		
		throw new Exception('Data did not match regEx');
	}



	public function GenerateXMLResponse($session_ID,
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

}
?>