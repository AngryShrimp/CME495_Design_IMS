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
	private $php_options_file_loc = "IMS_Settings.ini";
	private $sql_driver = "sqlsrv";



	public function __construct($server="",$user="",$pass="")
	{
		if(file_exists($this->php_options_file_loc))
		{
			$options_file = parse_ini_file($this->php_options_file_loc,TRUE);	
			
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
	
	
}



?>