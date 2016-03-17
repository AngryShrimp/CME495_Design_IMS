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
	public $log_file_loc;
	public $opt_debug = true;  //default, log debug entries
	
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
			if(!mkdir(dirname($this->log_file_loc,0777,true)))
			{
				throw new Exception("Could not make log directory. ($this->log_file_loc)",1);
			}
		}
		
		if(!is_writable($path['dirname']))
		{
			throw new Exception("Log directory ($this->log_file_loc) is not writeable.",1);
		}	
	}
	
	/*******************************************************************
	 * Function: set_log_location()
	 * Description: Sets the log file location after objection creation.
	 *
	 * Input: $file_location - A string containing a path to the log file
	 *			location.
	 *
	 * Returned Value: None.
	 *				   Throws Exception on error.
	 ********************************************************************/
	public function set_log_location($file_location)
	{
		$file_location = $file_location."IMSLog.csv";
		//Check that log folder exists and check write permissions.
		$path = pathinfo($file_location);
		if(!file_exists($path['dirname']))
		{
			if(!mkdir($path['dirname'],0777,true))
			{
				throw new Exception("Could not make log directory. Reverting to default. ($file_location)",1);
			}
		}
		
		if(!is_writable($path['dirname']))
		{
			throw new Exception("Log directory ($file_location) is not writeable. Reverting to default.",1);
		}

		$this->log_file_loc = $file_location;

		return;			
	}
	
	
	public function add_log($SID,$Level,$Message,$ItemNum = "N/A")
	{
	
		//prevent debug messages from being written if option not set.
		if(($this->opt_debug == false) && ($Level=="Debug"))
			return;


		$this->waitForLock();
			
		$lock_file = fopen($this->log_file_loc.".lock",'w+');	
		fwrite($lock_file,"Locked");
		fclose($lock_file);
		
		$log_file = fopen($this->log_file_loc,'a');
		
		if($log_file == FALSE)
		{
			unlink($this->log_file_loc.".lock");
			throw new Exception("IMSLog->add_log: Log file could not be opened",1);
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
				throw new Exception("IMSLog->read_log: Log file could not be opened",1);
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
			if($wait_counter > 100) //10 second time out.
			{
				throw new Exception("Logging time-out waiting for lock",1);
			}
			time_nanosleep(0, 100000000); //sleep for a 10th of a second.
		}		
		return;
	}	
}



?>