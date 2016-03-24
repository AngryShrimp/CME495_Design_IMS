/****************************************************************
Function:  sendBackendRequest()
Description: Sets up and sends a XMLHttpRequest in POST mode to 
a specified PHP script.
*****************************************************************/
function sendSettingsTextAreaRequest(PHPscript, id, options)
{
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() 
  {
    if (xhttp.readyState == 4 && xhttp.status == 200) 
    {
    	document.getElementById(id).innerHTML = xhttp.responseText;;
    }
  };
  xhttp.open("POST", PHPscript, true);
  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhttp.send(options);
  
  return;
}

function Backup(){
	sendSettingsTextAreaRequest("Back_End/BackupDatabase.php","textareacode");
}

function Restore(){
	document.getElementById("textareacode").innerHTML = "Restoring Database...";
	sendSettingsTextAreaRequest("Back_End/RestoreDatabase.php","textareacode");
	
}

function LogFileLocation(){
	sendSettingsTextAreaRequest("Back_End/ReadOptions.php","textareacode","SID="+getSID());
}

function GetOption(){
	var selection = document.getElementById("id_opt_option").value;
	//document.getElementById("textareacode").innerHTML = selection;
	sendSettingsTextAreaRequest("Back_End/GetOption.php","textareacode","SID="+getSID()+"&option="+selection);
}

function SubmitChange(){
	var selection = document.getElementById("id_opt_option").value;
	var change = document.getElementById("id_opt_value").value;
	sendSettingsTextAreaRequest("Back_End/ModifyOption.php","textareacode","SID="+getSID()+"&Data="+change+"&Option="+selection);
}