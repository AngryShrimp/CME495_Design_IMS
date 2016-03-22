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
            main_loadLog(); //refresh the log

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
  
  main_loadLog(); //refresh the log

  return;
}

/****************************************************************
Function:  elm_deleteEmailAddress(id)
Description: Deletes a selected email address from the database.
*****************************************************************/
function elm_deleteEmailAddress(id)
{		
  sendBackendRequest("Back_End/DeleteEmailAddress.php","SID="+getSID()+"&ID="+id);
  main_loadLog(); //refresh the log
  
  return;
}



function elm_tableDisplay(emailList)
{


	var email_entry = emailList[0].getElementsByTagName("EMAIL_ENTRY");
	var tableEmailListHeader = "";
	var tableEmailListData = "";
	
    //check for null data
    if(email_entry == null)
    {
      IMSError("parseXMLResponse Error","Class Data Entry is NULL");
      return false;	
    }    
	
	var colWidth = "<col width=\"85%\"><col width=\"15%\">";
	
	//Table header
	tableEmailListHeader = "<table class=\"w3-table w3-bordered w3-border w3-striped w3-hoverable w3-small\" style=\"table-layout:fixed; width=100%;\"><tr>" +
	        colWidth+
            "<th class=\"w3-border\">Email Address</th>" + 
            "<th class=\"w3-border\">Delete</th></tr></table>";
			

	//table data
	tableEmailListData = "<table class=\"w3-table w3-bordered w3-border w3-striped w3-hoverable w3-small\" style=\"table-layout:fixed; width=100%;\">"
						+colWidth;
	for( i = 0; i < email_entry.length; i++)
    {	
		var id = email_entry[i].getElementsByTagName("Id")[0].childNodes[0].nodeValue;
		var emailAddress = email_entry[i].getElementsByTagName("Recipients")[0].childNodes[0].nodeValue;	
	
		tableEmailListData += "<tr onclick=\"elm_loadRow('" + 
						id + "','" +
						emailAddress +
						"')\">" + 
						"<td class=\"w3-border\" style=\"word-wrap: break-word\">" + emailAddress + "</td>" +
						"<td class=\"w3-border\" style=\"word-wrap: break-word\">" + 
						"<button class=\"w3-btn w3-tiny w3-red w3-border w3-round-large\" type=\"button\" "+
						"onclick=\"elm_deleteEmailAddress('"+id+"')\">&times</button></td>" +
                        "</tr>";								
	}

    tableEmailListData += "</table>"

	document.getElementById("id_elm_tableHeader").innerHTML = tableEmailListHeader;

	document.getElementById("id_elm_tableData").innerHTML = tableEmailListData;
	
	return;

}