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

	private $l_servername = "localhost";
	private $l_username = "username";
	private $l_password = "password";
	private $conn;



	public function __construct($server,$user,$pass)
	{
		$this->l_servername = $server;
		$this->l_username = $user;
		$this->l_password = $pass;
		
		$this->connect();
		
	}


	private function connect() {

		try {
			$this->conn = new PDO("mssql:host=$servername;dbname=myDB", $username, $password);
			// set the PDO error mode to exception
			$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			echo "Connected successfully"; 
		}
		catch(PDOException $e)
		{
			echo "Connection failed: " . $e->getMessage();
		}

	}
	
	public function command($sql_command)
	{
		try {
			//$sql = "CREATE DATABASE myDBPDO";
			// use exec() because no results are returned
			$conn->exec($sql_command);
		}
		catch(PDOException $e)
		{
			echo "Connection failed: " . $e->getMessage();
		}
	
	
	}
}



?>