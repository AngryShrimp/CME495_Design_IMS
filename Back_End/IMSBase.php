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
require_once "vendor/autoload.php";
 
 
 
class IMSBase
{
	private $php_options_file_loc = "IMS_Settings.ini";



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
	
	
	public function sendEmail($to_array,$subject,$message)
	{
		
	
		$host = "";
		$username = "";
		$password = "";
		$fromemail = "";
		$fromname = "";
		
		if(file_exists($this->php_options_file_loc))
		{
			$options_file = parse_ini_file($this->php_options_file_loc,TRUE);	
			
			$host = $options_file["EMAIL_SETTINGS"]["EMAIL_HOST"];
			$username = $options_file["EMAIL_SETTINGS"]["EMAIL_USER"];
			$password = $options_file["EMAIL_SETTINGS"]["EMAIL_PASS"];
			$fromemail = $options_file["EMAIL_SETTINGS"]["EMAIL_FROMEMAIL"];
			$fromname = $options_file["EMAIL_SETTINGS"]["EMAIL_FROMNAME"];
		}
		else
		{
			throw new Exception("IMSBase->sendEmail: Could not find IMS_Settings.ini");
		}
		
		
		if($host == "")
		{
			throw new Exception("IMSBase->sendEmail: SMTP Host Name Missing");
		}
		if($username == "")
		{
			throw new Exception("IMSBase->sendEmail: SMTP User Name Missing");
		}
		if($password == "")
		{
			throw new Exception("IMSBase->sendEmail: SMTP Password Missing");
		}
		if($fromemail == "")
		{
			throw new Exception("IMSBase->sendEmail: SMTP From Email Missing");
		}
		if($fromname == "")
		{
			throw new Exception("IMSBase->sendEmail: SMTP From Name Missing");
		}	
	
	
		$mail = new PHPMailer(true); //Throw exceptions on error
		
		$mail->SMTPDebug = 0;
		$mail->isSMTP();
		$mail->Host = $host;		
		$mail->SMTPAuth=true;
		$mail->Username = $username;                 
		$mail->Password = $password;
		$mail->SMTPSecure = "tls";
		$mail->Port = 587;
		
		
		
		$mail->From = $fromemail;
		$mail->FromName = $fromname;
		
		
		foreach($to_array as $to)
		{
			$mail->addAddress($to);
		}
		
		
		$mail->isHTML(true);
		
		$mail->Subject = $subject;
		$mail->Body = $message;
		$mail->AltBody = "Plain Text Email";	
	
		$mail->send();
		
		echo "mail sent";
		
	}
	
	

}
?>