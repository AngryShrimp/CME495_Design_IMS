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

	public function verifyData($data,$RegEx,$optMessage = "")
	{
		$invalid_char_array = array( "<",
									 ">",
									 "(",
									 ")", );
									 
									 

		foreach(str_split($data) as $char)
			if(in_array($char,$invalid_char_array))
				throw new Exception("Invalid character string ($data)($optMessage)");
		
		
		if(preg_match($RegEx,$data) == TRUE)
		{
			return;
		}
		
		throw new Exception("String ($data) did not match RegEx ($RegEx)($optMessage)");
	}



	public function GenerateXMLResponse($session_ID,
								 $status_array,						
								 $q_access_array = NULL,								
								 $associative_array = NULL,
								 $section_name = NULL,
								 $subsection_name = NULL)
	{
		$xml = new XMLWriter();
		
		$xml->openMemory();
		$xml->startDocument('1.0','UTF-8');
		
		$xml->startElement("IMS_RESPONSE");
			
			$xml->startElement("SID");
				$xml->text($session_ID);
			$xml->endElement();
			
			$xml->startElement("STATUS");
				$xml->startElement("STATUS_CODE");
					$xml->text($status_array[0]);
				$xml->endElement();
				$xml->startElement("STATUS_MESSAGE");
					$xml->text($status_array[1]);		
				$xml->endElement();
			$xml->endElement();			
		
			
			if(!($q_access_array === NULL))
			{
				$xml->startElement("QACCESS");
					foreach($q_access_array as $key => $data)
						{
							$xml->startElement(str_replace(' ','',$key));
								$xml->text(trim($data));
							$xml->endElement();					
						}				
				$xml->endElement();
			
			}			
			
			if(!($associative_array === NULL))
			{
				$xml->startElement($section_name);
					foreach($associative_array as $array_entry)
					{
						$xml->startElement($subsection_name);
							foreach($array_entry as $key => $data)
							{
								$xml->startElement(str_replace(' ','',$key));
									$xml->text($data);
								$xml->endElement();					
							}
						$xml->endElement();
					}				
				$xml->endElement();			
			}
			
		
		$xml->endElement();
		
		header("Content-type: text/xml");

		echo $xml->outputMemory(true);
		
}

}
?>