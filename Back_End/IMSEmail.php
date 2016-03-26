<?php 

require_once "vendor/autoload.php"; //loads PHPMailer for use in sendEmail()

class IMSEmail{
		



		public $email_file_loc;
		public $opt_debug = true;  //default, log debug entries
		
		private function waitForLock($failSafe = false)
		{
			$wait_counter = 0;
		
			while(file_exists($this->email_file_loc.".lock"))
			{
				$wait_counter++;
				if($wait_counter > 100) //10 second time out.
				{
					if(!$failSafe)
						throw new Exception("Email time-out waiting for lock",1);
						else
							return false;
				}
				time_nanosleep(0, 100000000); //sleep for a 10th of a second.
			}
			
			
			return true;
		}
		
		public function __construct($input_loc = "")
		{
		
			if(!($input_loc == ""))
			{
				$this->email_file_loc = $input_loc;
			}
			else
			{
				//default log location
				$this->email_file_loc = trim($_SERVER['DOCUMENT_ROOT']).'\Back_End\email\IMSEmail.html';
					
			}
		
			//Check that log folder exists and check write permissions.
			$path = pathinfo($this->email_file_loc);
			if(!file_exists($path['dirname']))
			{
				mkdir($path['dirname'],0777,true);
			}
		}
		

		private function assembleEmailHeaders(){
		
			$myfile = fopen("email/IMSEmailTemplate.html", "r") or die ("IMSEmail: unable to open file in function assembleEmailHeaders().");
			$message = fread($myfile, filesize("email/IMSEmailTemplate.html"));
			fclose($myfile);
			return $message;
		}
		
		
		private function assembleBody($Supplier_Part_Number,$Item_Link,$Quantity){
			
			$body = "<tr>\n".
					"<td>".$Supplier_Part_Number."</td>\n".
					"<td>".$Item_Link."</td>\n".
					"<td>".$Quantity."</td>\n".
					"<td>".date("d-m-Y")."   ".date("h:ia")."</td>";
			
			$body .= "</tr>\n";
		
			return $body;
		}
		

		
		
		public function add_email($Supplier_Part_Number,$Item_Link,$Quantity,$failSafe = false)
		{
				
		
				$this->waitForLock($failSafe);
					
				$lock_file = fopen($this->email_file_loc.".lock",'w+');
				fwrite($lock_file,"Locked");
				fclose($lock_file);
				$exists = file_exists($this->email_file_loc);
				
				
					
				$email_file = fopen($this->email_file_loc,'a');
		
				if($email_file == FALSE)
				{
					unlink($this->email_file_loc.".lock");
						
					if(!$failSafe)
						throw new Exception("IMSEmail->add_email: Email file could not be opened. ($this->email_file_loc)",1);
						else
							return false;
				}
		
				//Add headers if file didn't exist
				if (!$exists){
					$headers = $this->assembleEmailHeaders();					
					fwrite($email_file,$headers);
				}
				
				//check for empty inputs.
				if($Supplier_Part_Number == "")
				{
					$Supplier_Part_Number = "Unknown";
				}
				if($Item_Link == "")
				{
					$Item_Link = "Unknown";
				}
				if($Quantity == "")
				{
					$Quantity = "Unknown";
				}
		
				$email_entry = $this->assembleBody($Supplier_Part_Number,$Item_Link,$Quantity);				
				
				fwrite($email_file,$email_entry);
		
				fclose($email_file);
		
				unlink($this->email_file_loc.".lock");
		
				return true;
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
		public function sendEmail($to_array,$subject,$credentials)
		{
			if($credentials["server"] == NULL)
			{
				throw new Exception("IMSEmail->sendEmail: SMTP Host Name Missing",1);
			}
			if($credentials["user"] == NULL)
			{
				throw new Exception("IMSEmail->sendEmail: SMTP User Name Missing",1);
			}
			if($credentials["password"] == NULL)
			{
				throw new Exception("IMSEmail->sendEmail: SMTP Password Missing",1);
			}
			if($credentials["from_email"] == NULL)
			{
				throw new Exception("IMSEmail->sendEmail: SMTP From Email Missing",1);
			}
			if($credentials["from_name"] == NULL)
			{
				throw new Exception("IMSEmail->sendEmail: SMTP From Name Missing",1);
			}
					
			$mail = new PHPMailer(true); //Throw exceptions on error
		
			$mail->SMTPDebug = 0;
			$mail->isSMTP();
			$mail->Host = $credentials["server"];
			$mail->SMTPAuth=true;
			$mail->Username = $credentials["user"];
			$mail->Password = $credentials["password"];
			$mail->SMTPSecure = "tls";
			$mail->Port = 587;
		
		
		
			$mail->From = $credentials["from_email"];
			$mail->FromName = $credentials["from_name"];
			
		
			$footers = "</table>\n</body>\n</head>\n</html>";
			
			foreach($to_array as $to)
			{
				$mail->addAddress($to);
			}

			$mail->isHTML(true);
		
			$mail->Subject = $subject;
			
			$emailmessage = fopen("email/IMSEmail.html", "r") or die ("IMSEmail: unable to open file in function sendEmail().");
			$mail->Body = fread($emailmessage, filesize("email/IMSEmail.html"));
			fclose($emailmessage);
			$mail->Body .= $footers;
			
			$mail->AltBody = "If you can't read this email, please use the Shopping List page on the IMS page.";
		
			if(!$mail->send())
			{
				return "Mailer Error: " . $mail->ErrorInfo;
			}
			else
			{
				unlink($this->email_file_loc);
				
				return "Email sent";
			}
			
			return;
		}
		
}
?>