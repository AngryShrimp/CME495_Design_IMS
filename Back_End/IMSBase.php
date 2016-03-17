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
require_once "vendor/autoload.php"; //loads PHPMailer for use in sendEmail() 
 
class IMSBase
{

	//Settings for sendEmail function, need to be set before calling.
	public $email_host = "";
	public $email_username = "";
	public $email_password = "";
	public $email_fromemail = "";
	public $email_fromname = "";



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
		throw new Exceptoin("Input $optMessage did not match expected value. ($data != $RegEx)",1);
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
	
	/******************************************************************
	 * Function: sendEmail()
	 * Description: Sends an html formatted email to all addresses contained
	 * in the $to_array.
	 *
	 *	Inputs: $to_array - An array containing the email address to send to.
	 *			$subject - A string containing the subject of the email.
	 *			$message - A html formatted string containing the message body.
	 *
	 *	Returned Value: Returns nothing on seccuess. 
	 *					Throws a phpmailerException() on PHPMailer error.
	 *					Throws a Exception() on all other errors.	 
	 *
	 * Notes: Class variables email_host,email_username,email_password,email_fromemail,
	 *			and email_fromname must be set before calling function. 
	 *******************************************************************/
	public function sendEmail($to_array,$subject,$message)
	{		
		if($this->email_host == "")
		{
			throw new Exception("IMSBase->sendEmail: SMTP Host Name Missing",1);
		}
		if($this->email_username == "")
		{
			throw new Exception("IMSBase->sendEmail: SMTP User Name Missing",1);
		}
		if($this->email_password == "")
		{
			throw new Exception("IMSBase->sendEmail: SMTP Password Missing",1);
		}
		if($this->email_fromemail == "")
		{
			throw new Exception("IMSBase->sendEmail: SMTP From Email Missing",1);
		}
		if($this->email_fromname == "")
		{
			throw new Exception("IMSBase->sendEmail: SMTP From Name Missing",1);
		}	
	
	
		$mail = new PHPMailer(true); //Throw exceptions on error
		
		$mail->SMTPDebug = 0;
		$mail->isSMTP();
		$mail->Host = $this->email_host;		
		$mail->SMTPAuth=true;
		$mail->Username = $this->email_username;                 
		$mail->Password = $this->email_password;
		$mail->SMTPSecure = "tls";
		$mail->Port = 587;
		
		
		
		$mail->From = $this->email_fromemail;
		$mail->FromName = $this->email_fromname;
		
		
		foreach($to_array as $to)
		{
			$mail->addAddress($to);
		}
		
		
		$mail->isHTML(true);
		
		$mail->Subject = $subject;
		$mail->Body = $message;
		$mail->AltBody = "If you can't read this email, please use the Shopping List page on the IMS page.";	
	
		$mail->send();
		
		return;
	}

}
?>