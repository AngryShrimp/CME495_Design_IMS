<?php
/***********************************************************************
 * 	Class: IMSBase
 * 	Description: Class that contains all the base functions for use in
 *	IMS Back end scripts.
 *
 *	Author: Craig Irvine ()
 *	Date: 08 January 2016
 *
 ***********************************************************************/
require_once "vendor/autoload.php"; //loads PHPMailer for use in sendEmail() 
 
class IMSBase
{


	/*******************************************************************
	 * Function: verifyData()
	 * Description: Performs sanity checks on input data and compares
	 *				it to a passed regex expressions.  On mismatch it 
	 *				throws an exception.
	 *
	 * Input: $data = The input to check.
	 * 		  $RegEx = The regex to compare the input to.
	 *		  $optMessage = A tag to add to error messages if required.
	 *
	 * Returned Value:  None
	 *					Throws an exception PDO Error.
	 ********************************************************************/
	public function verifyData($data,$RegEx,$optMessage = "")
	{
		$invalid_char_array = array( "<",
									 ">",
									 "(",
									 ")", );
									 
									 

		foreach(str_split($data) as $char)
			if(in_array($char,$invalid_char_array))
				throw new Exception("Invalid character string ($data)($optMessage)",1);
		
		
		if(preg_match($RegEx,$data) == TRUE)
		{
			return;
		}
		throw new Exception("Input $optMessage did not match expected value. ($data != $RegEx)",1);
	}


	/*******************************************************************
	 * Function: GenerateXMLResponse()
	 * Description: Checks to see if a Name exists in a specified table.
	 *
	 * Input: $session_ID = Session ID.
	 * 		  $status_array = A Array of Strings that contain the status of the calling script.
	 *							status_array[0] = Exit status code of script
								status_array[1] = Exit status message of script.
								status_array[2] = Run Level of SID that called script.
	 * 		  $q_access_array = Data for a single item.
	 * 		  $associative_array = A double nested array of strings
	 * 		  $section_name = Section name for the first level of the associative_array
	 * 		  $subsection_name = Section name for the second level of the associative_array 
	 *
	 * Returned Value:  None
	 ********************************************************************/
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
				if(count($status_array) == 3)
				{
					$xml->startElement("RUN_LEVEL");
						if($status_array[2] == "1")
							$xml->text("Edit Mode");		
						else
							$xml->text("View Mode");
					$xml->endElement();
				}
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