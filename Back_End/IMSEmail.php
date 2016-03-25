<?php 

require_once "vendor/autoload.php"; //loads PHPMailer for use in sendEmail()

class IMSEmail{
		


		//Settings for sendEmail function, need to be set before calling.
		public $email_host = "";
		public $email_username = "";
		public $email_password = "";
		public $email_fromemail = "";
		public $email_fromname = "";

		
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
				$this->email_file_loc = trim($_SERVER['DOCUMENT_ROOT']).'\Back_End\email\IMSEmail.csv';
					
			}
		
			//Check that log folder exists and check write permissions.
			$path = pathinfo($this->email_file_loc);
			if(!file_exists($path['dirname']))
			{
				mkdir($path['dirname'],0777,true);
			}
		}
		
		public function add_email($Supplier_Part_Number,$Item_Link,$Quantity,$failSafe = false)
		{
				
		
				$this->waitForLock($failSafe);
					
				$lock_file = fopen($this->email_file_loc.".lock",'w+');
				fwrite($lock_file,"Locked");
				fclose($lock_file);
		
				$email_file = fopen($this->email_file_loc,'a');
		
				if($email_file == FALSE)
				{
					unlink($this->email_file_loc.".lock");
						
					if(!$failSafe)
						throw new Exception("IMSEmail->add_email: Email file could not be opened. ($this->email_file_loc)",1);
						else
							return false;
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
		
		
				$email_entry = date("c").",".$Supplier_Part_Number.",".$Item_Link.",".$Quantity."\n";
		
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
		public function sendEmail($to_array,$subject,$message)
		{
			if($this->email_host == "")
			{
				throw new Exception("IMSEmail->sendEmail: SMTP Host Name Missing",1);
			}
			if($this->email_username == "")
			{
				throw new Exception("IMSEmail->sendEmail: SMTP User Name Missing",1);
			}
			if($this->email_password == "")
			{
				throw new Exception("IMSEmail->sendEmail: SMTP Password Missing",1);
			}
			if($this->email_fromemail == "")
			{
				throw new Exception("IMSEmail->sendEmail: SMTP From Email Missing",1);
			}
			if($this->email_fromname == "")
			{
				throw new Exception("IMSEmail->sendEmail: SMTP From Name Missing",1);
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