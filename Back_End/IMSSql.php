<?php
/***********************************************************************
 * 	Class: IMSSql
 * 	Description: Class used to interface with a sql server using the PDO
 * 	object.
 *
 *	Authors: Craig Irvine ()
 *			 Justin Fraser ()
 *
 *	Date: 08 January 2016
 *
 ***********************************************************************/

include "IMSEmail.php";

class IMSSql {


	
	private $l_servername = "";
	private $l_username = "";
	private $l_password = "";
	private $conn;
	private $sql_driver = "sqlsrv";

	private $b_servername = "";
	private $b_username = "";
	private $b_password = "";
	private $b_db = "";	
	
	
	
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
			
			$this->b_servername = $options_file["SQL_BACKUP"]["SQL_LOCATION"];
			$this->b_username = $options_file["SQL_BACKUP"]["SQL_USER"];
			$this->b_password = $options_file["SQL_BACKUP"]["SQL_PASS"];
			$this->b_db = $options_file["SQL_BACKUP"]["SQL_DB"];
			$this->b_backuplocation = $options_file["SQL_BACKUP"]["SQL_BACKUP_LOCATION"];
		}	
	
		if($server !="")
			$this->l_servername = $server;
		if($user != "")
			$this->l_username = $user;
		if($pass != "")
			$this->l_password = $pass;

		$this->connect();
		
	}

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
	
	public function checkThresholds(){
		
		$email = new IMSEmail();
		$cmd0 = "SELECT Value from dbo.Options WHERE [Option]='Thresholds_Enabled'";
		$cmd = 'SELECT * FROM dbo.Inventory';		
		$cmd1 = "SELECT Part, Quantity FROM dbo.Class_Data";
		$cmd2 = "SELECT * FROM dbo.Purchase_List";
		$belowCount = 0;
		$aboveCount = 0;
		
		
		//Get purchase table data and update inventory table
		foreach ($this->conn->query($cmd1) as $row1){
			$Name1 = $row1['Part'];
			$Quantity1 = $row1['Quantity'];
			
			$stmt = $this->conn->prepare("UPDATE dbo.Inventory SET Lab_Quantity=$Quantity1 WHERE Name='$Name1'");
			$stmt->execute();
		}
		
		/*
		 * If thresholds are disabled this block will execute fully.
		 * email list will not get updated but it will still check for items that have been replenished
		 */
		foreach ($this->conn->query($cmd0) as $row0){
			if ($row0['Value'] == "False" || $row0['Value'] == "false"){
				foreach ($this->conn->query($cmd) as $row){
					$difference = $row['Quantity'] - $row['Lab_Quantity'];
					if ($row['Quantity'] >= $row['Ordering_Threshold'] && $row['Threshold_Reported'] == 1 && $difference >= $row['Ordering_Threshold']){
						$Name = $row['Name'];
						$stmt = $this->conn->prepare("UPDATE dbo.Inventory SET Threshold_Reported=0 WHERE Name='$Name'");
						$stmt->execute();
						$aboveCount++;							
					}
				}
				
				$message[0] = "Threshold checking is not enabled in options";
				$message[1] = "Number of items restocked above threshold: $aboveCount";
			
				return $message;
			}
		}
		
		/*
		 * If thresholds are enabled this block will execute fully.
		 * Items under threshold are added to email list and will check for items that have been replenished
		 */
		foreach ($this->conn->query($cmd) as $row){
			
			$Name = $row['Name'];
			$difference = $row['Quantity'] - $row['Lab_Quantity'];

			//Check for normal violations
			if ($row['Quantity'] < $row['Ordering_Threshold'] && $row['Threshold_Reported'] == 0){				
				$email->add_email($row['Supplier_Part_Number'], $row['Item_Link'], $row['Quantity'], $row['Ordering_Threshold']);
				$stmt = $this->conn->prepare("UPDATE dbo.Inventory SET Threshold_Reported=1 WHERE Name='$Name'");
				$stmt->execute();
				$belowCount++;
				
			}
			//Offset quantity by lab equipment requirements and see if it violates the threshold
			if ($row['Lab_Part_Flag'] == 1 && $row['Quantity'] >= $row['Ordering_Threshold'] && $row['Threshold_Reported'] == 0){
					if ($difference < $row['Ordering_Threshold']){
						$email->add_email($row['Supplier_Part_Number'], $row['Item_Link'], $row['Quantity'], $row['Ordering_Threshold']);
						$stmt = $this->conn->prepare("UPDATE dbo.Inventory SET Threshold_Reported=1 WHERE Name='$Name'");
						$stmt->execute();
						$belowCount++;
					}
			}
			
			//Check for replenished quantities and remove the threshold violation flag
			if ($row['Quantity'] >= $row['Ordering_Threshold'] && $row['Threshold_Reported'] == 1 && $difference >= $row['Ordering_Threshold']){
				$Name = $row['Name'];
				$stmt = $this->conn->prepare("UPDATE dbo.Inventory SET Threshold_Reported=0 WHERE Name='$Name'");
				$stmt->execute();
				$aboveCount++;
			
			}
		}
		
		//Check for entries on the Purchase List table that haven't been reported
		//No check is required for replenished quantities as it's assumed the user will delete
		//the item from the manual entry table
		foreach ($this->conn->query($cmd2) as $row2){
			
			if ($row2['Threshold_Reported'] == 0){
				$Name = $row2['Supplier_Part_Number'];
				$email->add_email($row2['Supplier_Part_Number'], $row2['Item_Link'], $row2['Quantity'], "Manual addition");
				$stmt = $this->conn->prepare("UPDATE dbo.Purchase_List SET Threshold_Reported=1 WHERE Supplier_Part_Number='$Name'");
				$stmt->execute();
				$belowCount++;
			}
		}
		$message[0] = "Number of entries added to list: $belowCount";
		$message[1] = "Number of items restocked above threshold: $aboveCount";
		
		if ($email->emailNeedsToBeSent()){
			$recipients = $this->getEmailList();
			$credentials = $this->getEmailCredentials();
			$title = "IMS Low Stock Notification";
			$message[2] = $email->sendEmail($recipients,$title,$credentials);
		}
		
		return $message;
		
	}
	
	private function getEmailCredentials(){
		
				
		$cmd[0] = "SELECT Value from dbo.Options WHERE [Option]='Email_fromEmail'";
		$cmd[1] = "SELECT Value from dbo.Options WHERE [Option]='Email_fromName'";
		$cmd[2] = "SELECT Value from dbo.Options WHERE [Option]='Email_Pass'";
		$cmd[3] = "SELECT Value from dbo.Options WHERE [Option]='Email_Server'";
		$cmd[4] = "SELECT Value from dbo.Options WHERE [Option]='Email_User'";
		
		$array[0] = "from_email";
		$array[1] = "from_name";
		$array[2] = "password";
		$array[3] = "server";
		$array[4] = "user";
		
		$CMDSIZE = sizeof($cmd);
		
		if ($CMDSIZE != sizeof($array)) die ("IMSSql->getEmailCredentials() error: cmd | array size mismatch");
		
		
		for ($x=0; $x < $CMDSIZE; $x++){		
			foreach ($this->conn->query($cmd[$x]) as $row){
				$list[$array[$x]] = $row['Value'];
			}
		}
		
		return $list;
	}
	
	private function getEmailList(){
		
		$cmd = "SELECT Recipients FROM dbo.Emails";
		$x = 0;
		
		foreach ($this->conn->query($cmd) as $row){
			$list[$x] = $row['Recipients'];
			$x++;			
		}
		
		return $list;
		 
	}
	public function changeOption($selectedOption){
	
		$stmt = $this->conn->prepare("SELECT Value FROM dbo.Options WHERE [Option]='$selectedOption'");
		$stmt->execute();			
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
		print_r($result[0]['Value']);
	}
	
	public function retrieveOption($selectedOption){
	
		$stmt = $this->conn->prepare("SELECT Value FROM dbo.Options WHERE [Option]='$selectedOption'");
		$stmt->execute();
			
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
		print_r($result[0]['Value']);
	}
	
	/*
	 * @Function: retrieveOptions
	 * @Description: Retrieves the options row from the database
	 * @Output: Options in XML format
	 * @author: Justin Fraser
	 */
	public function retrieveOptions()
	{
		
		try 
		{
			
									
			$cmd = 'SELECT [Option], Value FROM dbo.Options';
			
			$optionOutput = "";
			$XMLData = "<?xml version='1.0' encoding='UTF-8'?>\n";
			$XMLData .= "<Options>\n";
			
			foreach ($this->conn->query($cmd) as $row){                 
			
			$optionOutput .= $row['Option']."     ".$row['Value']."\n";
			$XMLData .= "<Option>".$row['Option']."</Option>\n";                        
			$XMLData .= "<Value>".$row['Value']."</Value>\n";
                      
			
			}
			$XMLData .= "</Options>";
			
			$xml=simplexml_load_string($XMLData) or die("Error: Cannot create object");
			//print_r($xml);
			print_r($optionOutput);
				
				
		} 
		catch (Exception $e) 
		{
				
			echo "Exception in Retrieve Options in IMSSql.php";
			throw $e;
		}
	}
	
	 /*******************************************************************
	 * Function: exists()
	 * Description: Checks to see if a Name exists in a specified table.
	 *
	 * Input: $partNumber = The part number to check for.
	 * 		  $table = The name of the table to check.
	 *
	 * Returned Value:  Returns true of part number exits else false.
	 *					Throws an exception PDO Error.
	 ********************************************************************/
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
        
    /*******************************************************************
	 * Function: prepare()
	 * Description: Creates a SQL Prepare statement and returns the object.
	 *
	 * Input: $SQLStatement = The SQL Statement to use.
	 *
	 * Returned Value:  PDO prepare object.
	 *					Throws an exception PDO Error.
	 ********************************************************************/    
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
	
	
	/*******************************************************************
	 * Function: set_sid()
	 * Description: Adds a SID to the database with the proper permissions.
	 *
	 * Input: $sid = SID to add.
	 *		  $date = The current date.
	 *		  $ip = The IP of the connected client for logging.
	 * 		  $key = The value passed to the default.php script, used for
	 * 				determining run level.
	 *
	 * Returned Value:  None.
	 *					Throws an exception PDO Error.
	 ********************************************************************/
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

			$exp_time_opt = $this->getOption("Credential_Expiry_Time_Seconds");
			

			$exp_date = date("Y-m-d H:i:s",time()+intval($exp_time_opt));
			
			$this->command("INSERT INTO dbo.SID_List (SID,CLIENT_IP,EXPIRE,LEVEL) VALUES ('$sid','$ip','$exp_date','$runlevel');");

		}
		catch (PDOException $e)
		{
			throw $e;
		}
		return;	
	}
	
	/*******************************************************************
	 * Function: verifySID()
	 * Description: Verifies that the SID has the required permissions.
	 *
	 * Input: $SID = SID to check
	 *		  $runLevel = The run level to check against. 
	 *
	 * Returned Value:  Returns the run level of the passed SID.
	 *					Throws an exception on invalid run level.
	 ********************************************************************/
	public function verifySID($SID,$runLevel = "0")
	{
		try
		{
			$stmt = $this->prepare("SELECT * FROM dbo.SID_List;");
			$stmt->execute();	
			
			$dataArray = $stmt->fetchAll(PDO::FETCH_ASSOC); 
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		
		foreach($dataArray as $data)
		{
			if($data['SID'] == $SID)
			{
				//we found the SID, renew it.
				$this->renewSID();			
			
				if($data['LEVEL'] < $runLevel)
				{
					throw new Exception("verifySID: SID($SID) Missing permissions.",1);
				}
				else
				{
					return $data['LEVEL'];
				}
			}
		}	
		
		//SID passed is not valid.
		throw new Exception("verifySID: Invalid SID. ($SID)",2);
		
		return "0";
	}
	
	
	/*******************************************************************
	 * Function: renewSID()
	 * Description: Renews the permissions assigned to a SID for amount
	 *				of time specified in the Credential_Expiry_Time_Seconds
	 *				option.
	 *
	 * Input: None
	 *
	 * Returned Value:  None
	 *					Throws an exception on SQL error.
	 ********************************************************************/
	public function renewSID()
	{

		try
		{

			if(!isset($_COOKIE["SID"]))
			{
				throw new Exception("renewSID: SID missing from cookie",2);
			}	
			
			$exp_time = $this->getOption("Credential_Expiry_Time_Seconds");
			
			$SID = $_COOKIE["SID"];
		
			$stmt = $this->prepare("SELECT * FROM dbo.SID_List;");
			$stmt->execute();	
			
			$dataArray = $stmt->fetchAll(PDO::FETCH_ASSOC); 	
			
			foreach($dataArray as $entry)
			{
				if($entry["SID"] == $SID)
				{
					$exp_date = date("Y-m-d H:i:s",time()+intval($exp_time));			
					$this->command("UPDATE dbo.SID_List SET EXPIRE='$exp_date' WHERE SID='$SID';");
					return;				
				}			
			}
			
			throw new Exception("renewSID: Expected SID not present in SID List.",2);		
			
		}
		catch (PDOException $e)
		{
			throw $e;
		}
		return;
		
	}
	
	
	/*******************************************************************
	 * Function: getOption()
	 * Description: Retrieves value of a specified option.
	 *
	 * Input: $option - String containing the requested option.
	 *
	 * Returned Value:  The Value of the requested option or false on error.
	 *					Throws an exception on SQL error.
	 ********************************************************************/
	public function getOption($option)
	{
		try
		{
			$stmt = $this->prepare("SELECT [Value] FROM dbo.Options WHERE [Option]='$option';");
			$stmt->execute();	
				
			$dataArray = $stmt->fetchAll(PDO::FETCH_ASSOC); 
			
			if(count($dataArray) != 1)
			{
				return false;
			}
			
			return trim($dataArray[0]['Value']);
		}
		catch (PDOException $e)
		{
			return false;
		}
	
	}
	
	
	/*******************************************************************
	 * Function: gatherSQLCredentials()
	 * Description: Retrieves the server backup credentials from the class
	 *
	 * Input: None
	 *
	 * Returned Value: $array - An array of server backup credentials.
	 ********************************************************************/
	public function gatherSQLCredentials(){
		
		$array["servername"] = $this->b_servername;
		$array["username"] = $this->b_username;
		$array["password"] = $this->b_password;
		$array["db"] = $this->b_db;
		$array["location"] = $this->b_backuplocation;
		
		return $array;
		
	}
	
}



?>