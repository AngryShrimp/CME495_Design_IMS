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
	private $log_file_loc;
	
	public function __construct($input_loc = "")
	{
	
		if(!($input_loc == ""))
		{
			$this->log_file_loc = $input_loc;
		}
		else
		{		
			//default log location
			$this->log_file_loc = $_SERVER['DOCUMENT_ROOT']."\Back_End\log\IMSLog.csv";
			
		}

		//Check that log folder exists and check write permissions.
		$path = pathinfo($this->log_file_loc);
		if(!file_exists($path['dirname']))
		{
			if(!mkdir(dirname($this->log_file_loc)))
			{
				throw new Exception("Could not make log directory. ($this->log_file_loc)");
			}
		}
		
		if(!is_writable($path['dirname']))
		{
			throw new Exception("Log directory ($this->log_file_loc) is not writeable.");
		}	
		

	}
	
	
	
	
	public function add_log($SID,$Level,$Message,$ItemNum = "N/A")
	{


		$this->waitForLock();
			
		$lock_file = fopen($this->log_file_loc.".lock",'w+');	
		fwrite($lock_file,"Locked");
		fclose($lock_file);
		
		$log_file = fopen($this->log_file_loc,'a');
		
		if($log_file == FALSE)
		{
			unlink($this->log_file_loc.".lock");
			throw new Exception("IMSLog->add_log: Log file could not be opened");
			return;
		} 
		
		//check for empty inputs.
		if($SID == "")
		{
			$SID = "Unknown";
		}
		if($Level == "")
		{
			$Level = "Unknown";
		}
		if($SID == "")
		{
			$Message = "Unknown";
		}
		
		//Ensure Message does not have any commas.
		$Message = str_replace(',','-',$Message);
		
		
		$log_entry = date("c").",".$SID.",".$Level.",".$Message.",".$ItemNum."\n";
		
		fwrite($log_file,$log_entry);
		
		fclose($log_file);
		
		unlink($this->log_file_loc.".lock");		
		
	}
	
	
	public function read_log($levelFilter)
	{
            $logData = array();

            $this->waitForLock();

            $lock_file = fopen($this->log_file_loc.".lock",'w+');	
            fwrite($lock_file,"Locked");
            fclose($lock_file);

            $log_file = fopen($this->log_file_loc,'c+');

			if($log_file == FALSE)
			{
				unlink($this->log_file_loc.".lock");
				throw new Exception("IMSLog->read_log: Log file could not be opened");
				return;
			}

            while(!feof($log_file))
            {
                $csvData = fgetcsv($log_file);
                
				//Prevent invalid lines from creating a log entry in the response.
				if(count($csvData) == 5)
				{
				
					if(($csvData[2] == $levelFilter) || ($levelFilter == "All"))
					{            
						$logArray['Date'] = $csvData[0];
						$logArray['SID'] = $csvData[1];
						$logArray['Level'] = $csvData[2];
						$logArray['Description'] = $csvData[3];
						$logArray['Item'] = $csvData[4];
					
						//prepends the array, array entries are in newest to oldest order.
						array_unshift($logData,$logArray);
					}
				}
            }
            
            
            
            fclose($log_file);

            unlink($this->log_file_loc.".lock");	
            
            return $logData;
   
	}
	

	private function waitForLock()
	{
		$wait_counter = 0;
		
		while(file_exists($this->log_file_loc.".lock"))
		{
			$wait_counter++;
			if($wait_counter > 20) //two second time out.
			{
				throw new Exception("Logging time-out waiting for lock");
			}
			time_nanosleep(0, 100000000); //sleep for a 10th of a second.
		}		
		return;
	}	
}



?>