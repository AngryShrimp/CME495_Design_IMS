<?php


$log = new IMSLog();


$log->add_log("QQQQ","WARN","HELLO NURSE");
$log->add_log("QQQQ","ERROR","HELLO BOB");





class IMSLog
{

	private $log_file_loc;

	
	public function __construct($input_loc = "")
	{
		if(!($input_loc == ""))
		{
			$this->$log_file_loc = $input_loc;
		}
		else
		{
			//default log location
			$this->log_file_loc = "log\IMSLog.csv";
		}

	}
	
	
	
	
	public function add_log($SID,$Level,$Message)
	{

		//block while logfile is locked.
		while($this->is_log_locked());
			
		$lock_file = fopen($this->log_file_loc.".lock",'w+');	
		fwrite($lock_file,"Locked");
		fclose($lock_file);
		
		$log_file = fopen($this->log_file_loc,'a');
		
		$log_entry = date("c").",".$SID.",".$Level.",".$Message."\n";
		
		fwrite($log_file,$log_entry);
		
		fclose($log_file);
		
		unlink($this->log_file_loc.".lock");		
		
	}

	private function is_log_locked()
	{

		if(file_exists($this->log_file_loc.".lock"))
			return TRUE;
			
		return FALSE;

	}
	

	
}



?>