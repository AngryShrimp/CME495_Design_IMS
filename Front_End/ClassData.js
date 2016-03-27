/****************************************************************
Function:  cdm_tableSort()
Description: Sorts the class data table based on clicked header.
*****************************************************************/
function cdm_tableSort(column)
{ 

  var sortDir = "ASC";
  
  var currentSort = document.getElementById("id_cmd_sortInfo").innerHTML;
  
  if(currentSort != "None")
  {
	var currentSortSplit = currentSort.split(":");
	var currentSortCol = currentSortSplit[0];
	var currentSortDir = currentSortSplit[1];
	
	if(currentSortCol == column)
	{
	  if(currentSortDir == "ASC")
		sortDir = "DESC";
	  else
	    sortDir = "ASC";
	}
	else
	{
	  sortDir = "ASC";
	}
  
  }

  sendBackendRequest("Back_End/RetrieveClassData.php","SID="+getSID()+"&SortColumn="+column+"&SortDirection="+sortDir);
  
  document.getElementById("id_cmd_sortInfo").innerHTML = column + ":"+sortDir;  
}

/****************************************************************
Function:  cdm_loadRow()
Description: Loads a row of data into the edit box.
*****************************************************************/
function cdm_loadRow(id,className,part,qty,date)
{
	document.getElementById("id_cdm_idDisplay").innerHTML = id;

	document.getElementById("id_cdm_classInput").value = className;
	document.getElementById("id_cdm_classInputOrginal").innerHTML = className;

	document.getElementById("id_cdm_PNInput").value = part;
	document.getElementById("id_cdm_PNInputOrginal").innerHTML = part;

	document.getElementById("id_cdm_qtyInput").value = qty;
	document.getElementById("id_cdm_qtyInputOrginal").innerHTML = qty;

	document.getElementById("id_cdm_dateInput").value = date;
	document.getElementById("id_cdm_dateInputOrginal").innerHTML = date;

	document.getElementById("id_cdm_modifyBtn").disabled = false;

	return;
}

/****************************************************************
Function:  cdm_clearEdit()
Description: Clears the edit box if a row is loaded into it.
*****************************************************************/
function cdm_clearEdit()
{
	if(document.getElementById("id_cdm_idDisplay").innerHTML != "New")
	{
		document.getElementById("id_cdm_idDisplay").innerHTML = "New";
		document.getElementById("id_cdm_classInput").value = "";
		document.getElementById("id_cdm_PNInput").value = "";
		document.getElementById("id_cdm_qtyInput").value = "";
		document.getElementById("id_cdm_dateInput").value = "";	
		
        document.getElementById("id_cdm_partList").innerHTML = "";

		document.getElementById("id_cdm_modifyBtn").disabled = true;

	}
	
	return;
}

/****************************************************************
Function:  cdm_deleteClassDataEntry()
Description: Deletes a selected class data entry from the database.
*****************************************************************/
function cdm_deleteClassDataEntry()
{
	var id = document.getElementById("id_cdm_idDisplay").innerHTML;
	var sortCommand ="";
	if(id != "New")
	{
		var currentSort = document.getElementById("id_cmd_sortInfo").innerHTML;
		if(currentSort != "None")
		{
			var currentSortSplit = currentSort.split(":");
			var currentSortCol = currentSortSplit[0];
			var currentSortDir = currentSortSplit[1];
			
			sortCommand = "&SortColumn="+currentSortCol+"&SortDirection="+currentSortDir;
			
		}	
		
		sendBackendRequest("Back_End/DeleteClassData.php","SID="+getSID()+"&ID="+id+sortCommand);
		main_loadLog(); //refresh the log
	}
	else
	{
		IMSError("cdm_deleteClassDataEntry Error","No Class data record selected.");
	    return false;	
	}
	
	return;
}

/****************************************************************
Function:  cdm_deleteSelClassDataEntry()
Description: Deletes all check entries in the class data form.
*****************************************************************/
function cdm_deleteSelClassDataEntry()
{
	var idList = document.getElementById("id_cdm_deletionList").innerHTML;
	var sortCommand ="";
	if(idList != "None")
	{
		var currentSort = document.getElementById("id_cmd_sortInfo").innerHTML;
		if(currentSort != "None")
		{
			var currentSortSplit = currentSort.split(":");
			var currentSortCol = currentSortSplit[0];
			var currentSortDir = currentSortSplit[1];
			
			sortCommand = "&SortColumn="+currentSortCol+"&SortDirection="+currentSortDir;				
		}	
	
		idListSplit = idList.split(",");
		for(i=0;i<idListSplit.length;i++)
		{		
			sendBackendRequest("Back_End/DeleteClassData.php","SID="+getSID()+"&ID="+idListSplit[i]+sortCommand);
		}
		document.getElementById("id_cdm_deletionList").innerHTML = "None";
		main_loadLog(); //refresh the log

	}
	else
	{
		IMSError("cdm_deleteSelClassDataEntry Error","No Class data records selected.");
	    return;	
	}
	
	return;
}

/****************************************************************
Function:  cdm_addClassDataEntry()
Description: Sends new class data to the server to be saved.
*****************************************************************/
function cdm_addClassDataEntry()
{

  var className = document.getElementById("id_cdm_classInput").value;
  var partNumber = document.getElementById("id_cdm_PNInput").value;
  var qty = document.getElementById("id_cdm_qtyInput").value;
  var date = document.getElementById("id_cdm_dateInput").value;
  var sortCommand = "";
  
  var currentSort = document.getElementById("id_cmd_sortInfo").innerHTML;
  if(currentSort != "None")
  {
	var currentSortSplit = currentSort.split(":");
	var currentSortCol = currentSortSplit[0];
	var currentSortDir = currentSortSplit[1];
	
	sortCommand = "&SortColumn="+currentSortCol+"&SortDirection="+currentSortDir;	
  }	  

  sendBackendRequest("Back_End/AddNewClassData.php","SID="+getSID()+"&Class="+className+"&PartNumber="+partNumber+"&Quantity="+qty+"&Date="+date+sortCommand);
  
  //clear entries
  document.getElementById("id_cdm_idDisplay").innerHTML = "New";
  document.getElementById("id_cdm_classInput").value = "";
  document.getElementById("id_cdm_PNInput").value = "";
  document.getElementById("id_cdm_qtyInput").value = "";
  document.getElementById("id_cdm_dateInput").value = "";	
  document.getElementById("id_cdm_partList").innerHTML = "";
  
  setTimeout(main_checkThreshold,250);
  
  main_loadLog(); //refresh the log
  
  return;
}

/****************************************************************
Function:  cdm_modifyClassDataEntry()
Description: Modifies an existing class data record.
*****************************************************************/
function cdm_modifyClassDataEntry()
{
	var id = document.getElementById("id_cdm_idDisplay").innerHTML;
	var sortCommand = "";
	
	if(id != "New")
	{
		var currentSort = document.getElementById("id_cmd_sortInfo").innerHTML;
		if(currentSort != "None")
		{
			var currentSortSplit = currentSort.split(":");
			var currentSortCol = currentSortSplit[0];
			var currentSortDir = currentSortSplit[1];
			
			sortCommand = "&SortColumn="+currentSortCol+"&SortDirection="+currentSortDir;	
		}	
	
	
		var class_new = document.getElementById("id_cdm_classInput").value;
		var class_old = document.getElementById("id_cdm_classInputOrginal").innerHTML;

		var PN_new = document.getElementById("id_cdm_PNInput").value;
		var PN_old = document.getElementById("id_cdm_PNInputOrginal").innerHTML;

		var qty_new = document.getElementById("id_cdm_qtyInput").value;
		var qty_old = document.getElementById("id_cdm_qtyInputOrginal").innerHTML;

		var date_new = document.getElementById("id_cdm_dateInput").value;
		var date_old = document.getElementById("id_cdm_dateInputOrginal").innerHTML;
		
		if(	class_new != class_old)
		{
			sendBackendRequest("Back_End/ModifyClassData.php","SID="+getSID()+"&ID="+id+"&Field=Class&Value="+class_new+sortCommand);
			document.getElementById("id_cdm_classInputOrginal").innerHTML = class_new;
		}
		if(	PN_new != PN_old)
		{
			sendBackendRequest("Back_End/ModifyClassData.php","SID="+getSID() + "&ID="+id+"&Field=Part&Value="+PN_new+sortCommand);
			document.getElementById("id_cdm_PNInputOrginal").innerHTML = PN_new;
		}
		if(	qty_new != qty_old)
		{
			sendBackendRequest("Back_End/ModifyClassData.php","SID="+getSID() + "&ID="+id+"&Field=Quantity&Value="+qty_new+sortCommand);
			document.getElementById("id_cdm_qtyInputOrginal").innerHTML = qty_new;
		}
		if(	date_new != date_old)
		{
			sendBackendRequest("Back_End/ModifyClassData.php","SID="+getSID() + "&ID="+id+"&Field=Date&Value="+date_new+sortCommand);
			document.getElementById("id_cdm_dateInputOrginal").innerHTML = date_new;
		}
		
		setTimeout(main_checkThreshold,500);
		main_loadLog(); //refresh the log

	}
	else
	{
		IMSError("cdm_modifyClassDataEntry Error","No record selected to modify.");
	}
	return;
}

/****************************************************************
Function:  cdm_getClassData()
Description: Sends a request to the server to retrieve the Class 
Data information for display.
*****************************************************************/
function cdm_getClassData()
{
  sendBackendRequest("Back_End/RetrieveClassData.php","SID="+getSID());	
}

/****************************************************************
Function:  cdm_showPNAutocomplete()
Description: Creates a datalist to show all autocomplete matches
for a partial search string.
*****************************************************************/
function cdm_showPNAutocomplete(partialStr)
{


  //Check for a selection	
  //from http://stackoverflow.com/questions/30022728/perform-action-when-clicking-html5-datalist-option
  var val = document.getElementById("id_cdm_PNInput").value;
  var opts = document.getElementById('id_cdm_partList').childNodes;
  
  if(val == "") //blank or removed entry, delete the datalist.
  {
    document.getElementById("id_cdm_partList").innerHTML = "";
	return;
  }
  
  for (var i = 0; i < opts.length; i++) 
  {
	if (opts[i].value === val) 
	{
	// An item was selected from the list!
	//remove datalists
	document.getElementById("id_cdm_partList").innerHTML = "";
    document.getElementById("id_search_queryList").innerHTML = "";
	return;
	}
  }
  
  sendBackendRequest("Back_End/QueryAutocomplete.php","SID="+getSID()+"&Filter="+ partialStr);
  return;
}


/****************************************************************
Function:  cdm_selCheck()
Description: Adds and deletes an ID from the delete selection string
based on checking and unchecking a box.
*****************************************************************/
function cdm_selCheck(checkboxID,recordID)
{
	var selState = document.getElementById(checkboxID).checked;
	var currentList = document.getElementById("id_cdm_deletionList").innerHTML;
	var newList = "None";

	if(selState) //Add
	{
		if(currentList == "None")
		{
			newList = recordID;
		}
		else
		{
			newList = currentList;
			newList += "," + recordID;
		}
	}
	else //Delete
	{
		splitArray = currentList.split(",");
		
		index = splitArray.indexOf(recordID);
		splitArray.splice(index,1);
		
		if(splitArray.length != 0)
		{
			newList = "";		
			for(i=0;i < splitArray.length;i++)
			{			
				newList += splitArray[i];
				if(i != splitArray.length-1)
				{
					newList += ",";
				}		
			}
		}
	}
	
    document.getElementById("id_cdm_deletionList").innerHTML = newList;

}


function cdm_displayTable(classData)
{
	var class_entry = classData[0].getElementsByTagName("CLASS_ENTRY");
	
    //check for null data
    if(class_entry == null)
    {
      IMSError("parseXMLResponse Error","Class Data Entry is NULL");
      return false;	
    }
    var headerLabelClass = "Class Name";
    var headerLabelPN = "Part Number";
    var headerLabelQty = "Quantity";
    var headerLabelDate = "Date";
	
    var currentSort = document.getElementById("id_cmd_sortInfo").innerHTML;

	
    if(currentSort != "None")
    {
      var currentSortSplit = currentSort.split(":");
      var currentSortCol = currentSortSplit[0];
      var currentSortDir = currentSortSplit[1];	  
      
	  
      if(currentSortCol == "Class")
      {
		    if(currentSortDir == "ASC")
		    {
		      headerLabelClass = "Class Name&#9650;"; //Arrow up
		    }
		    else
		    {
		      headerLabelClass = "Class Name&#9660;";//Arrow Down
		    }		
      }
	  
      if(currentSortCol == "Part")
      {
		    if(currentSortDir == "ASC")
		    {
		      headerLabelPN = "Part Number&#9650;";
		    }
		    else
		    {
		      headerLabelPN = "Part Number&#9660;";
		    }		
      }
	  
	  
      if(currentSortCol == "Quantity")
      {
		    if(currentSortDir == "ASC")
		    {
		      headerLabelQty = "Quantity&#9650;";
		    }
		    else
		    {
		      headerLabelQty = "Quantity&#9660;";
		    }		
      }
	  
	  
  	  if(currentSortCol == "Date")
   	  {
    		if(currentSortDir == "ASC")
    		{
    		  headerLabelDate = "Date&#9650;";
     		}
  	  	else
    		{
		      headerLabelDate = "Date&#9660;";
    		}		
	    }	
	}

	var tableClassData = "";
	var tableClassDataHeader = "";
	
	//Table header
	tableClassDataHeader = "<table class=\"w3-table w3-bordered w3-border w3-striped w3-hoverable w3-small\" style=\"table-layout:fixed; width:100%; overflow-y:scroll;\"><tr>" +
			"<col width=\"8%\"></col><col width=\"23%\"></col><col width=\"23%\"></col><col width=\"23%\"></col><col width=\"23%\">" +
			"<th class=\"w3-border\">SEL</th>" +
            "<th class=\"w3-border\" onclick=\"cdm_tableSort('Class')\">"+headerLabelClass+"</th>" + 
            "<th class=\"w3-border\" onclick=\"cdm_tableSort('Part')\">"+headerLabelPN+"</th>" + 
            "<th class=\"w3-border\" onclick=\"cdm_tableSort('Quantity')\">"+headerLabelQty+"</th>" +
            "<th class=\"w3-border\" onclick=\"cdm_tableSort('Date')\">"+headerLabelDate+"</th></table>";
	//table data
	tableClassData = "<table class=\"w3-table w3-bordered w3-border w3-striped w3-hoverable w3-small\" style=\"table-layout:fixed; width:100%;\">"+
			"<col width=\"8%\"></col><col width=\"23%\"></col><col width=\"23%\"></col><col width=\"23%\"></col><col width=\"23%\"></col>" ;
	for( i = 0; i < class_entry.length; i++)
    {
	
		var id = class_entry[i].getElementsByTagName("Id")[0].childNodes[0].nodeValue;
		var className = class_entry[i].getElementsByTagName("Class")[0].childNodes[0].nodeValue;
		var part = class_entry[i].getElementsByTagName("Part")[0].childNodes[0].nodeValue;
		var qty = class_entry[i].getElementsByTagName("Quantity")[0].childNodes[0].nodeValue;
		var date = class_entry[i].getElementsByTagName("Date")[0].childNodes[0].nodeValue;
	
	
		tableClassData += "<tr onclick=\"cdm_loadRow('" + 
						id + "','" +
						className + "','" +
						part + "','" +
						qty + "','" +
						date +
						"')\">" + 
						"<td class=\"w3-border\" style=\"word-wrap: break-word\"><input class=\"w3-check\" type=\"checkbox\" "+
						"id=\"id_cdm_checkbox" + i + "\" " +
						"onchange=\"cdm_selCheck(this.id,'"  + id + "')\"></input></td>" +
						"<td class=\"w3-border\" style=\"word-wrap: break-word\">" + className + "</td>" +
                        "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + part + "</td>" +
                        "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + qty + "</td>" +
                        "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + date + "</td>" +                   
                        "</tr>";								
	}

    tableClassData += "</table>"

	document.getElementById("id_cdm_tableHeader").innerHTML = tableClassDataHeader;
	document.getElementById("id_cdm_table").innerHTML = tableClassData;
	
	return;
}