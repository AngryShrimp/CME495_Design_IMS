/****************************************************************
Function:  sendBackendRequest()
Description: Sets up and sends a XMLHttpRequest in POST mode to 
a specified PHP script.
*****************************************************************/
function sendSettingsTextAreaRequest(PHPscript)
{
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() 
  {
    if (xhttp.readyState == 4 && xhttp.status == 200) 
    {
    	document.getElementById("textareacode").innerHTML = xhttp.responseText;;
    }
  };
  xhttp.open("POST", PHPscript, true);
  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhttp.send();
  
  return;
}

function Backup(){
	sendSettingsTextAreaRequest("Back_End/BackupDatabase.php");
}

function Restore(){
	document.getElementById("textareacode").innerHTML = "DO NOT CLOSE THIS BOX UNTIL A DATABASE RESTORED CONFIRMATION IS DISPLAYED!!!";
	sendSettingsTextAreaRequest("Back_End/RestoreDatabase.php");
	
}