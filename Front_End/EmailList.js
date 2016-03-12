/****************************************************************
Function:  elm_getEmailList()
Description: Sends a request to the server to retrieve the email
list for display.
*****************************************************************/
function elm_getEmailList()
{
  sendBackendRequest("Back_End/RetrieveEmailAddress.php","SID="+getSID());	
}

/****************************************************************
Function:  elm_loadRow()
Description: Loads a row of data into the edit box.
*****************************************************************/
function elm_loadRow(id,email)
{
	document.getElementById("id_elm_idDisplay").innerHTML = id;

	document.getElementById("id_elm_emailAddress").value = email;
	document.getElementById("id_elm_emailAddressOriginal").innerHTML = email;

	document.getElementById("id_elm_modifyBtn").disabled = false;

	return;
}


/****************************************************************
Function:  elm_modifyEmailEntry()
Description: Modifies an existing email record.
*****************************************************************/
function elm_modifyEmailEntry()
{
	var id = document.getElementById("id_elm_idDisplay").innerHTML;
	
	if(id != "New")
	{
		var email_new = document.getElementById("id_elm_emailAddress").value;
		var email_old = document.getElementById("id_elm_emailAddressOriginal").innerHTML;
				
		if(	email_new != email_old)
		{
			sendBackendRequest("Back_End/ModifyEmailAddress.php","SID="+getSID()+"&ID="+id+"&Field=Recipients&Value="+email_new);
			document.getElementById("id_elm_emailAddressOriginal").innerHTML = email_new;
		}		
	}
	else
	{
		IMSError("elm_modifyEmailEntry Error","No record selected to modify.");
	}
	return;
}


/****************************************************************
Function:  elm_addEmailEntry()
Description: Adds a new Email entry to the email list.
*****************************************************************/
function elm_addEmailEntry()
{

  var email = document.getElementById("id_elm_emailAddress").value;  

  sendBackendRequest("Back_End/AddEmailAddress.php","SID="+getSID()+"&Email="+email);
  
  //clear entries
  document.getElementById("id_elm_idDisplay").innerHTML = "New";
  document.getElementById("id_elm_emailAddress").value = "";
  document.getElementById("id_elm_emailAddressOriginal").innerHTML = "";
  
  return;
}

/****************************************************************
Function:  elm_deleteEmailAddress(id)
Description: Deletes a selected email address from the database.
*****************************************************************/
function elm_deleteEmailAddress(id)
{		
  sendBackendRequest("Back_End/DeleteEmailAddress.php","SID="+getSID()+"&ID="+id);
	
  return;
}
