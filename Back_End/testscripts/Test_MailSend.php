<?PHP
/***********************************************************************
 * 	Script: Test_MailSend.php
 * 	Description: Script for testing the SendEmail function in IMSBase.
 *
 *	Author: Craig Irvine ()
 *	Date: 24 Feb 2016
 *
 ***********************************************************************/
 
include "IMSTest.php";
include "../IMSBase.php";



try
{
	$base = new IMSBase();
	
	
	$subject = "Purchase Requirements";
	
	$message = 	"<p>This is a Purchase Requirements email</p>" .
				"<table><tr><th>Date</th><th>Part</th><th>MFG PN</th><th>Qty</th></tr>" .
			    "<tr><td>Date</td><td>Part</td><td>MFG PN</td><td>Qty</td></tr>" .
			    "<tr><td>Date</td><td>Part</td><td>MFG PN</td><td>Qty</td></tr>" .			   
			    "</table>";

	$base->sendEmail(array("uktena.ren@gmail.com"),$subject,$message);
}
catch (phpmailerException $e)
{
	echo "phpmailerException: ".$e->errorMessage();

}
catch (Exception $e)
{
	echo "Exception: ".$e->getMessage();
}


 
?>

