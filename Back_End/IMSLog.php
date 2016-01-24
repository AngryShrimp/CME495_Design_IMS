<?php
/***********************************************************************
 * 	Class: IMSLog
 * 	Description: Class used to create and modify log files for the IMS
 * 	system.
 *
 *	Author: Craig Irvine (cri646@mail.usask.ca)
 *	Date: 08 January 2016
 *
 ***********************************************************************/


class IMSLog
{
	//TODO: Add Error Checking for file access.
	private $log_file_loc;

	
	public function __construct($input_loc = "")
	{
	
	
		if(!($input_loc == ""))
		{
			$this->log_file_loc = $input_loc;
		}
		else
		{
			//Check that log folder exits
			if(!file_exists("log"))
			{
				mkdir("log");
			}

		
			//default log location
			$this->log_file_loc = "log\IMSLog.csv";
		}

	}
	
	
	
	
	public function add_log($SID,$Level,$Message)
	{

		//block while logfile is locked.
		//TODO: Add timeout for loop to prevent lockups.
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
	
	
	public function read_log($levelFilter)
	{
            $logData = array();
	
            //block while logfile is locked.
            //TODO: Add timeout for loop to prevent lockups.
            while($this->is_log_locked());
                
            $lock_file = fopen($this->log_file_loc.".lock",'w+');	
            fwrite($lock_file,"Locked");
            fclose($lock_file);

            $log_file = fopen($this->log_file_loc,'r');


            while(! feof($log_file))
            {
                $csvData = fgetcsv($log_file);
                
                if(($csvData[2] == $levelFilter) || ($levelFilter == "All"))
                {            
                    $logArray['Date'] = $csvData[0];
                    $logArray['SID'] = $csvData[1];
                    $logArray['Level'] = $csvData[2];
                    $logArray['Description'] = $csvData[3];
                    
                
                    $logData[] = $logArray;
                }
            }
            
            
            
            fclose($log_file);

            unlink($this->log_file_loc.".lock");	
            
            return $logData;
            
            
	}
	

	private function is_log_locked()
	{

		if(file_exists($this->log_file_loc.".lock"))
			return TRUE;
			
		return FALSE;

	}
	

	
}



?>