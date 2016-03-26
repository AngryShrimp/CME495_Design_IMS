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

function opt_GetOption(){
	sendBackendRequest("Back_End/GetOption.php","SID="+getSID());
}

function opt_displayOptions(options)
{

	var opt_entries = options[0].getElementsByTagName("OPT_ENTRY");
	
	for(i = 0; i < opt_entries.length;i++)
	{
		var option = opt_entries[i].getElementsByTagName("Option")[0].childNodes[0].nodeValue;
		var value_node = opt_entries[i].getElementsByTagName("Value")[0].childNodes[0];
		var value = "";
		
		if(value_node != null)		
			value = value_node.nodeValue;	
		
		switch(option)
		{
			case "Credential_Expiry_Time_Seconds":
				document.getElementById('id_opt_credTime').value = value;
			break;
			case "Debug":
				if(value == 'True')
					document.getElementById('id_opt_debug').checked = true;
				else
					document.getElementById('id_opt_debug').checked = false;
			break;
			case "Log_File_Location":
				document.getElementById('id_opt_logFileLoc').value = value;
			break;
			case "Thresholds_Enabled":
				if(value == 'True')
					document.getElementById('id_opt_enThresholds').checked = true;
				else
					document.getElementById('id_opt_enThresholds').checked = false;
			break;
			case "Automated_Backups_Enabled":
				if(value == 'True')
					document.getElementById('id_opt_autoBackups').checked = true;
				else
					document.getElementById('id_opt_autoBackups').checked = false;
			break;
			case "Backup_Frequency":
				document.getElementById('id_opt_backupFreq').value = value;
			break;
			case "Email_fromEmail":
				document.getElementById('id_opt_emailAddress').value = value;
			break;
			case "Email_fromName":
				document.getElementById('id_opt_emailDisplayName').value = value;
			break;
			case "Email_Server":
				document.getElementById('id_opt_emailServerAddress').value = value;
			break;
			case "Email_User":
				document.getElementById('id_opt_emailServerUser').value = value;
			break;
			case "Email_Pass":
				document.getElementById('id_opt_emailServerPass').value = value;
			break;
			case "SQL_LOCATION":
				document.getElementById('id_opt_SQLServerLoc').value = value;
			break;
			case "SQL_USER":
				document.getElementById('id_opt_SQLUser').value = value;
			break;
			case "SQL_PASS":
				document.getElementById('id_opt_SQLPass').value = value;
			break;
			case "SQL_DRIVER":
				document.getElementById('id_opt_SQLDriver').value = value;
			break;
			default:
				break;
		}
	}
	
	return;
}

function opt_modifyOption(id,option)
{
	var value;
	
	if(document.getElementById(id).type=="text")
	{
		value = document.getElementById(id).value
	}
	else if(document.getElementById(id).type=="checkbox")
	{
		if(document.getElementById(id).checked == true)
			value = "True";
		else
			value = "False";
	}
	
	sendBackendRequest("Back_End/ModifyOption.php","SID="+getSID()+"&Option="+option+"&Data="+value);
	document.getElementById(id).style.backgroundColor = "white";
	return;
}

function opt_editedField(id)
{
	document.getElementById(id).style.backgroundColor = "lightgreen";
}
