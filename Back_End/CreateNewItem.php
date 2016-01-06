<?PHP

include "IMSBase.php";
include "IMSLog.php";


$IMSBase = new IMSBase();
$log = new IMSLog();

$servername = "localhost";
$username = "username";
$password = "password";

try {
    $conn = new PDO("mssql:host=$servername;dbname=myDB", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$sql = "CREATE DATABASE myDBPDO";
    // use exec() because no results are returned
    $conn->exec($sql);
    echo "Connected successfully"; 
    }
catch(PDOException $e)
    {
    echo "Connection failed: " . $e->getMessage();
    }
	
	
	
echo Done;



?>