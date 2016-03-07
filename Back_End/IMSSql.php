<?php
/***********************************************************************
 * 	Class: IMSSql
 * 	Description: Class used to interface with a sql server using the PDO
 * 	object.
 *
 *	Author: Craig Irvine (cri646@mail.usask.ca)
 *	Date: 08 January 2016
 *
 ***********************************************************************/

class IMSSql {

	private $l_servername = "";
	private $l_username = "";
	private $l_password = "";
	private $conn;
	private $sql_driver = "sqlsrv";



	public function __construct($server="",$user="",$pass="")
	{
		$php_options_file_loc = $_SERVER['DOCUMENT_ROOT']."\Back_End\IMS_Settings.ini";
	
		if(file_exists($php_options_file_loc))
		{
			$options_file = parse_ini_file($php_options_file_loc,TRUE);	
			
			$this->l_servername = $options_file["SQL_SERVER"]["SQL_LOCATION"];
			$this->l_username = $options_file["SQL_SERVER"]["SQL_USER"];
			$this->l_password = $options_file["SQL_SERVER"]["SQL_PASS"];
			$this->sql_driver = $options_file["SQL_SERVER"]["SQL_DRIVER"];
		}	
	
		if($server !="")
			$this->l_servername = $server;
		if($user != "")
			$this->l_username = $user;
		if($pass != "")
			$this->l_password = $pass;

		$this->connect();
		
	}

	//TODO: Add connection checks in all functions.

	private function connect() {

		try {
			$this->conn = new PDO("$this->sql_driver:server=$this->l_servername;Database=IMS","$this->l_username","$this->l_password");
			// set the PDO error mode to exception
			
			$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        
		}
		catch(PDOException $e)
		{
			//rethrow the exception
			throw $e;
		}

	}
	
	public function command($sql_command)
	{
		try {
			// use exec() because no results are returned
			$this->conn->exec($sql_command);
		}
		catch(PDOException $e)
		{
			//rethrow the exception
			throw $e;
		}	
	}
	

        /*
         * @Function: retrieveOptions
         * @Description: Retrieves the options row from the database
         * @Output: Options in XML format
         * @author: Justin Fraser
         */
        public function retrieveOptions()
        {
            
                try {
                    
                                            
                        $cmd = 'SELECT Remote_Server_Enabled, Backup_Frequency, Automated_Backups_Enabled, Thresholds_Enabled FROM dbo.Options';
                        
                        
                        $XMLData = "<?xml version='1.0' encoding='UTF-8'?>\n";
                        $XMLData .= "<Options>\n";
                        
                        foreach ($this->conn->query($cmd) as $row){                 
                        

                        $XMLData .= "<Remote_Server_Enabled>".$row['Remote_Server_Enabled']."</Remote_Server_Enabled>\n";                        
                        $XMLData .= "<Backup_Frequency>".$row['Backup_Frequency']."</Backup_Frequency>\n";
                        $XMLData .= "<Automated_Backups_Enabled>".$row['Automated_Backups_Enabled']."</Automated_Backups_Enabled>\n";
                        $XMLData .= "<Thresholds_Enabled>".$row['Thresholds_Enabled']."</Thresholds_Enabled>\n";                       
                        
                        }
                        $XMLData .= "</Options>";
                        
                        $xml=simplexml_load_string($XMLData) or die("Error: Cannot create object");
                        print_r($xml);
                        
                        
                } catch (Exception $e) {
                        
                        echo "Exception in Retrieve Options in IMSSql.php";
                        throw $e;
                }
        }
	public function exists($partNumber,$table)
	{
		try{
			$stmt = $this->conn->prepare("SELECT * FROM $table WHERE Name='$partNumber'");
			$stmt->execute();
			
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			if(count($result) == 0)
				return FALSE;
				
			return TRUE;
			
		}
		catch(PDOException $e)
		{
			//rethrow the exception
			throw $e;
		}
	
	}
	
        
        /*
         * @Function: IdExists
         * @Description: Finds any existing values in a table that uses Id
         * as a primary key.
         * @output: true if rows exist in table, false otherwise
         * @author: Justin Fraser
         */
        	public function IdExists($table)
	{
		try{
			$stmt = $this->conn->prepare("SELECT * FROM $table WHERE Id LIKE '%'");
			$stmt->execute();
			
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			if(count($result) == 0)
				return FALSE;
				
			return TRUE;
			
		}
		catch(PDOException $e)
		{
			//rethrow the exception
			throw $e;
		}
	
	}
        
        
	public function prepare($SQLStatement)
	{
		try{
			return $this->conn->prepare($SQLStatement);		
		}
		catch(PDOException $e)
		{
			//rethrow the exception
			throw $e;
		}	
	}
	
	public function set_sid($sid,$date,$ip,$key)
	{
	
		try
		{		
			$runlevel = "0";
		
			$this->command("DELETE FROM dbo.SID_List WHERE EXPIRE<'$date'");			

			$stmt = $this->prepare("SELECT * FROM dbo.SID_List;");
			$stmt->execute();	
			
			$dataArray = $stmt->fetchAll(PDO::FETCH_ASSOC);   		

			if($key == "update")
			{
				$runlevel = "1";
			}
			else if($key == "modify")
			{
				$runlevel = "2";
			}

			$exp_date = date("Y-m-d H:i:s",time()+3600);
			
			$this->command("INSERT INTO dbo.SID_List (SID,CLIENT_IP,EXPIRE,LEVEL) VALUES ('$sid','$ip','$exp_date','$runlevel');");

		}
		catch (PDOException $e)
		{
			throw $e;
		}
		return;	
	}
	
	
	public function getRunLevel($SID)
	{
		$stmt = $this->prepare("SELECT * FROM dbo.SID_List;");
		$stmt->execute();	
		
		$dataArray = $stmt->fetchAll(PDO::FETCH_ASSOC); 
		
		
		foreach($dataArray as $data)
		{
			if($data['SID'] == $SID)
			{
				return $data['LEVEL'];
			}
		}	
		
		return "0";
	}	
}



?>