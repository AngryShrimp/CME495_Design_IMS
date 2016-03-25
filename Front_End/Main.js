function main_NavOpen() 
{
    document.getElementsByClassName("w3-sidenav")[0].style.display = "block";
}
function main_NavClose() 
{
    document.getElementsByClassName("w3-sidenav")[0].style.display = "none";
}

/****************************************************************
Function:  populateForms()
Description: This function run all data retrieval functions so 
that all data fields are populated on page load.  Also gets a 
SID from the server
*****************************************************************/
function populateForms()
{
	document.title = "Inventory Management System";

	if(setSID())
	{
		cdm_getClassData();
		main_loadBrowser();
		elm_getEmailList();
		main_loadLog();
		RetrievePurchaseReport();
		tableTimers();
		cvm_setupAddItemBatch();
	}
}

/****************************************************************
Function:  getSID()
Description: Gets a SID from the server and creates a local cookie.
*****************************************************************/
function setSID()
{  
  var current_sid = getSID();
  if(current_sid == "")
  {
	window.location = "default.php";
	return false;
  } 

  document.getElementById("id_main_SIDDisplay").innerHTML = current_sid;  
  return true;  
}

function getSID()
{

  var name = "SID=";
  var ca = document.cookie.split(';');
  for(var i=0; i<ca.length; i++) 
  {  
	var c = ca[i];
	while (c.charAt(0)==' ') 
		c = c.substring(1);
	if (c.indexOf(name) == 0) 
		return c.substring(name.length,c.length);
  }

  return "";
}

function renewSID()
{
	var SID = getSID();	
	
	var d = new Date();
    d.setTime(d.getTime() + (3600*1000)); //expire time
    var expires = "expires="+d.toUTCString();
    document.cookie = "SID=" + SID + "; " + expires;
	
}

/****************************************************************
Function:  main_loadBrowser()
Description: Requests an update to the item browser using settings
from the front end elements.
Author: Keenan Johnstone
Modified: Craig Irvine
*****************************************************************/
function main_loadBrowser()
{


  var filter = "";
  var searchBarVal = document.getElementById("id_search_bar").value;
  var brw_currentSort = document.getElementById("id_brw_sortInfo").innerHTML;
  if(searchBarVal != "")
  {
    filter = "&Filter=" + searchBarVal;
  }
  //Table is sorted, so use the sort Info to decide
  if(brw_currentSort != "None")
  {
    var brw_currentSortSplit = brw_currentSort.split(":");
    var brw_currentSortCol = brw_currentSortSplit[0];
    var brw_currentSortDir = brw_currentSortSplit[1];
    setTimeout(function(){sendBackendRequest("Back_End/RetrieveBrowserData.php","SID="+getSID()+filter + "&SortColumn=" + brw_currentSortCol + "&SortDirection=" + brw_currentSortDir)},250);  
  }
  //No sorting info
  else
  {
    //Timeout is to delay the browser read/refresh until other actions have been performed.
    setTimeout(function(){sendBackendRequest("Back_End/RetrieveBrowserData.php","SID="+getSID()+filter)},250);  
  }  
  return;
}
/****************************************************************
Function:  main_loadLog()
Description: Requests an update to the log table using settings
from the front end elements.
Author: Keenan Johnstone
Modified: Craig Irvine
*****************************************************************/
function main_loadLog()
{  

  //Timeout is to delay the log read/refresh until other actions have been performed.
  setTimeout(function(){sendBackendRequest("Back_End/RetrieveLog.php","SID="+getSID()+"&LogLevel=All");}, 250);

  return;
}

/****************************************************************
Function:  sendBackendRequest()
Description: Sets up and sends a XMLHttpRequest in POST mode to 
a specified PHP script.
*****************************************************************/
function sendBackendRequest(PHPscript,postOptions)
{
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() 
  {
    if(xhttp.status == 404)
	{
	  IMSError("sendBackendRequest Error","404 Error returned for: "+PHPscript + "?" + postOptions);
	}
	
	if(xhttp.status == 500)
	{
	  IMSError("sendBackendRequest Error","500 Error returned for: "+PHPscript + "?" + postOptions);
	}
  
    if (xhttp.readyState == 4 && xhttp.status == 200) 
    {
      parseXMLResponse(xhttp);
    }
  };
  xhttp.open("POST", PHPscript, true);
  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhttp.send(postOptions);
  
  renewSID(); 
  
  return;
}

/****************************************************************
Function:  brw_tableSort()
Description: Sorts the browser table based on clicked header.
Author: Keenan Johnstone
*****************************************************************/
function brw_tableSort(column)
{ 
  var filter = "";
  var searchBarVal = document.getElementById("id_search_bar").value;
  var brw_sortDir = "ASC";
  var brw_currentSort = document.getElementById("id_brw_sortInfo").innerHTML;

  if(searchBarVal != "")
  {
    filter = "&Filter=" + searchBarVal;
  }
  
  if(brw_currentSort != "None")
  {
    var brw_currentSortSplit = brw_currentSort.split(":");
    var brw_currentSortCol = brw_currentSortSplit[0];
    var brw_currentSortDir = brw_currentSortSplit[1];
  
    if(brw_currentSortCol == column)
    {
      if(brw_currentSortDir == "ASC")
        brw_sortDir = "DESC";
      else
        brw_sortDir = "ASC";
    }
    else
    {
      brw_sortDir = "ASC";
    }
  
  }

  sendBackendRequest("Back_End/RetrieveBrowserData.php","SID="+ getSID() + "&SortColumn=" + column + "&SortDirection=" + brw_sortDir + filter);
  
  document.getElementById("id_brw_sortInfo").innerHTML = column + ":"+ brw_sortDir;  
}
function main_QUQtyInput()
{
	document.getElementById('id_qa_Quantity').style.backgroundColor = 'lightgreen';
	return;
}
/****************************************************************
Function:  quickBar_modify()
Description: Modifies a field for the currently loaded item. In
The quick update bar on the side.
Author: Keenan Johnstone
*****************************************************************/
function quickBar_modifyItem()
{
  
  var quantity = document.getElementById("id_qa_QuantityOrginal").value;
  var qty_input = document.getElementById("id_qa_Quantity").value;
  var description = document.getElementById("id_qa_Description").value;
  var itemNumber = document.getElementById("id_qa_ID").value; 

  if(itemNumber == "")
  {
    //Nothing there? do nothing!
    return;
  }

  //Check that the values aren't missing and replace them with safe values
  if(quantity == "")
  {
    IMSError("Quick Update Error","Cannot have an empty Quantity field");
    return;
  }

  if(description == "")
  {
    IMSError("Quick Update Error","Cannot have an empty Description field");
    return;
  }
  
  if(qty_input.charAt(0) == "+")
  {
	qty_input = parseInt(quantity) + parseInt(qty_input.substr(1));
  }
  else if(qty_input.charAt(0) == "-")
  {
	qty_input = parseInt(quantity) - parseInt(qty_input.substr(1));
  }
  else if(isNaN(qty_input))
  {
	IMSError("Quick Update Error","Quantity change must be a number and/or +/-.");
	return;
  }

  if(parseInt(qty_input) < 0)
  {
    IMSError("Quick Update Error","Number of part remaining must be greater than 0");
	return;
  }

  //Replace any newlines with spaces
  description = description.replace(/(?:\r\n|\r|\n)/g, ' ');
  quantity = quantity.replace(/(?:\r\n|\r|\n)/g, ' ');

  sendBackendRequest("Back_End/ModifyItem.php","SID="+getSID()+"&PartNumber=" + itemNumber + "&Field=Quantity&Value=" + qty_input);
  document.getElementById('id_qa_Quantity').style.backgroundColor = 'white';
  setTimeout(function(){main_getQuickUpdateData(itemNumber)},250);
  setTimeout(main_checkThreshold,250); //See if threshold was violated with the item update
  main_loadBrowser();
  return;
}

/****************************************************************
Function:  quickBar_clear()
Description: Clears the quick Access fields
Author: Keenan Johnstone
*****************************************************************/
function quickBar_clear()
{
  document.getElementById("id_qa_ID").value = "";
  document.getElementById("id_qa_Description").value = "";
  document.getElementById("id_qa_Quantity").value = "";
}

/****************************************************************
Function:  parseXMLResponse()
Description: Requests an update to the item browser using settings
from the front end elements.
Author: Keenan Johnstone, Craig Irvine
*****************************************************************/
function parseXMLResponse(xml)
{

  var i;
  var xmlDoc = xml.responseXML;
  var tableBrowser;
  var tableLog;

  //error check for null XML response.
  if(xmlDoc == null)
  {
    IMSError("parseXMLResponse Error","Returned XML Doc is Null");
	return false;    
  }   
  


  //handle xml entries
  var status = xmlDoc.getElementsByTagName("STATUS");
  var browser = xmlDoc.getElementsByTagName("BROWSER");
  var log = xmlDoc.getElementsByTagName("LOG");
  var autoComplete = xmlDoc.getElementsByTagName("QUERY_SUGGEST");
  var quickAccess = xmlDoc.getElementsByTagName("QACCESS");
  var classData = xmlDoc.getElementsByTagName("CLASS_DATA");
  var emailList = xmlDoc.getElementsByTagName("EMAIL_LIST");
  var createItemChange = xmlDoc.getElementsByTagName("CREATEITEM");

  if(status.length > 0)
  {
	var statusCode = status[0].getElementsByTagName("STATUS_CODE")[0].childNodes[0].nodeValue;
	var statusMessage = status[0].getElementsByTagName("STATUS_MESSAGE")[0].childNodes[0].nodeValue;
	var runMode = status[0].getElementsByTagName("RUN_LEVEL")[0].childNodes[0].nodeValue;
  
	document.getElementById("status_code").innerHTML = statusCode;
	document.getElementById("status_message").innerHTML = statusMessage;
  	document.getElementById("id_main_RunLevelDisplay").innerHTML = runMode;

	
	if(statusCode == "2")
	{
	  IMSError("parseXMLResponse Error","SID Invalid or Missing, refreshing in 2 seconds\n" + statusMessage);
	  setTimeout(function(){window.location = "default.php";}, 2000);
	  return false;

	}	
	if(statusCode == "1")
	{
	  IMSError("parseXMLResponse Error","Server Error with message:" + statusMessage);
	  return false;
	}  
  }
  else //Bad XML Response format if STATUS is missing
  {
    IMSError("parseXMLResponse Error","Bad XML Response Format");
	return false;	
  }
  
  
  if(createItemChange.length > 0)
  {
	var change = createItemChange[0].getElementsByTagName("CHANGE");
  
	document.getElementById("id_cvm_itemNumber").value = change[0].getElementsByTagName("Part")[0].childNodes[0].nodeValue;
  }
  
  if(autoComplete.length > 0)
  {
	var autoCompleteList = autoComplete[0].getElementsByTagName("SUGGESTION");	
	var autoCompleteHtml;
	
	//check for null data
    if(autoCompleteList == null)
    {
      IMSError("parseXMLResponse Error","AutoComplete List is NULL");
	  return false;	
    }
	
    for( i = 0; i < autoCompleteList.length; i++)
	{
		autoCompleteHtml += "<option value=\""
		+autoCompleteList[i].getElementsByTagName("Name")[0].childNodes[0].nodeValue 
		+"\" style=\"color:black\">"
		+autoCompleteList[i].getElementsByTagName("Name")[0].childNodes[0].nodeValue 
		+" - "
		+autoCompleteList[i].getElementsByTagName("Type")[0].childNodes[0].nodeValue 
		+" - "
		+autoCompleteList[i].getElementsByTagName("Description")[0].childNodes[0].nodeValue
		+"</option>";
	}	
	
	//Main page search bar.
    document.getElementById("id_search_queryList").innerHTML = autoCompleteHtml;
	
	//Add Class Data Part Number
	document.getElementById("id_cdm_partList").innerHTML = autoCompleteHtml;
  }
  

  if(quickAccess.length > 0)
  {
	//quick access form
	document.getElementById("id_qa_ID").value = quickAccess[0].getElementsByTagName("Name")[0].childNodes[0].nodeValue;
	document.getElementById("id_qa_Description").value = quickAccess[0].getElementsByTagName("Description")[0].childNodes[0].nodeValue;
	document.getElementById("id_qa_Quantity").value = quickAccess[0].getElementsByTagName("Quantity")[0].childNodes[0].nodeValue;
    document.getElementById("id_qa_QuantityOrginal").value = quickAccess[0].getElementsByTagName("Quantity")[0].childNodes[0].nodeValue;

	
	//add item modal dialog
	document.getElementById("id_ivm_itemNumber").value = quickAccess[0].getElementsByTagName("Name")[0].childNodes[0].nodeValue;
	document.getElementById("id_ivm_desc").value = quickAccess[0].getElementsByTagName("Description")[0].childNodes[0].nodeValue;
	document.getElementById("id_ivm_supplierPN").value = quickAccess[0].getElementsByTagName("Supplier_Part_Number")[0].childNodes[0].nodeValue;
	document.getElementById("id_ivm_qty").value = quickAccess[0].getElementsByTagName("Quantity")[0].childNodes[0].nodeValue;
	document.getElementById("id_ivm_location").value = quickAccess[0].getElementsByTagName("Location")[0].childNodes[0].nodeValue;
	document.getElementById("id_ivm_type").value = quickAccess[0].getElementsByTagName("Type")[0].childNodes[0].nodeValue;
	document.getElementById("id_ivm_thresh").value = quickAccess[0].getElementsByTagName("Ordering_Threshold")[0].childNodes[0].nodeValue;
	document.getElementById("id_ivm_value").value = quickAccess[0].getElementsByTagName("Value")[0].childNodes[0].nodeValue;
	document.getElementById("id_ivm_supplierName").value = quickAccess[0].getElementsByTagName("Suppliers_Name")[0].childNodes[0].nodeValue;
	document.getElementById("id_ivm_link").value = quickAccess[0].getElementsByTagName("Item_Link")[0].childNodes[0].nodeValue;
	
	if(quickAccess[0].getElementsByTagName("Consumable_Flag")[0].childNodes[0].nodeValue == "1")
		document.getElementById("id_ivm_flagConsumable").checked = true;
	else
		document.getElementById("id_ivm_flagConsumable").checked = false;

	if(quickAccess[0].getElementsByTagName("Equipment_Flag")[0].childNodes[0].nodeValue == "1")
		document.getElementById("id_ivm_flagEquipment").checked = true;
	else
		document.getElementById("id_ivm_flagEquipment").checked = false;
		
	if(quickAccess[0].getElementsByTagName("Lab_Part_Flag")[0].childNodes[0].nodeValue == "1")
		document.getElementById("id_ivm_flagLabPart").checked = true;
	else
		document.getElementById("id_ivm_flagLabPart").checked = false;	
  }  
  

  /*************
  Log Table Data
  **************/
  if(log.length > 0)
  {
	//Function is in log.js
    log_displayTable(log);
  }
  

  /*****************
  Browser Table Data
  ******************/
  if(browser.length > 0) //Browser section present
  { 
    var browser_entry = browser[0].getElementsByTagName("BROWSER_ENTRY");
	var tableBrowserData = "";
    var tableBrowserHeader = "";

    //check for null data
    if(browser_entry == null)
    {
      IMSError("parseXMLResponse Error","Broswer List is NULL");
	  return false;	
    }

    var browserHeaderLabelName = "Name";
    var browserHeaderLabelQuantity = "Qty";
    var browserHeaderLabelType = "Part Type";
    var browserHeaderLabelValue = "Value";
    var browserHeaderLabelLocation = "Location";
    var browserHeaderLabelSupplierName = "Supplier Name";
    var browserHeaderLabelPartNumber = "Supplier Part Number";
    var browserHeaderLabelOrderingThreshold = "Ordering Threshold";
    var browserHeaderLabelDescription = "Description";
    var browserHeaderLabelURL = "URL";
    var browserHeaderLabelConsumableFlag = "C";
    var browserHeaderLabelEquipmentFlag = "E";
    var browserHeaderLabelLabpartFlag = "L";

    var brw_currentSort = document.getElementById("id_brw_sortInfo").innerHTML;
    
    if(brw_currentSort != "None")
    {
      var brw_currentSortSplit = brw_currentSort.split(":");
      var brw_currentSortCol = brw_currentSortSplit[0];
      var brw_currentSortDir = brw_currentSortSplit[1];

      if(brw_currentSortCol == "Name")
      {
        if(brw_currentSortDir == "ASC")
        {
          browserHeaderLabelName = "Name&#9650;"; //Arrow up
        }
        else
        {
          browserHeaderLabelName = "Name&#9660;";//Arrow Down
        } 
      }
      if(brw_currentSortCol == "Quantity")
      {
        if(brw_currentSortDir == "ASC")
        {
          browserHeaderLabelQuantity = "Qty&#9650;"; //Arrow up
        }
        else
        {
          browserHeaderLabelQuantity = "Qty&#9660;";//Arrow Down
        } 
      }
      if(brw_currentSortCol == "Type")
      {
        if(brw_currentSortDir == "ASC")
        {
          browserHeaderLabelType = "Part Type&#9650;"; //Arrow up
        }
        else
        {
          browserHeaderLabelType = "Part Type&#9660;";//Arrow Down
        } 
      }
      if(brw_currentSortCol == "Value")
      {
        if(brw_currentSortDir == "ASC")
        {
          browserHeaderLabelValue = "Value&#9650;"; //Arrow up
        }
        else
        {
          browserHeaderLabelValue = "Value&#9660;";//Arrow Down
        } 
      }
      if(brw_currentSortCol == "Location")
      {
        if(brw_currentSortDir == "ASC")
        {
          browserHeaderLabelLocation = "Location&#9650;"; //Arrow up
        }
        else
        {
          browserHeaderLabelLocation = "Location&#9660;";//Arrow Down
        } 
      }
	  if(brw_currentSortCol == "Suppliers_Name")
      {
        if(brw_currentSortDir == "ASC")
        {
          browserHeaderLabelSupplierName = "Supplier Name&#9650;"; //Arrow up
        }
        else
        {
          browserHeaderLabelSupplierName = "Supplier Name&#9660;";//Arrow Down
        } 
      }
      if(brw_currentSortCol == "Supplier_Part_Number")
      {
        if(brw_currentSortDir == "ASC")
        {
          browserHeaderLabelPartNumber = "Supplier Part Number&#9650;"; //Arrow up
        }
        else
        {
          browserHeaderLabelPartNumber = "Supplier Part Number&#9660;";//Arrow Down
        } 
      }
      if(brw_currentSortCol == "Ordering_Threshold")
      {
        if(brw_currentSortDir == "ASC")
        {
          browserHeaderLabelOrderingThreshold = "Ordering Threshold&#9650;"; //Arrow up
        }
        else
        {
          browserHeaderLabelOrderingThreshold = "Ordering Threshold&#9660;";//Arrow Down
        } 
      }
      if(brw_currentSortCol == "Description")
      {
        if(brw_currentSortDir == "ASC")
        {
          browserHeaderLabelDescription = "Description&#9650;"; //Arrow up
        }
        else
        {
          browserHeaderLabelDescription = "Description&#9660;";//Arrow Down
        } 
      }
	  if(brw_currentSortCol == "Item_Link")
      {
        if(brw_currentSortDir == "ASC")
        {
          browserHeaderLabelURL = "URL&#9650;"; //Arrow up
        }
        else
        {
          browserHeaderLabelURL = "URL&#9660;";//Arrow Down
        } 
      }
      if(brw_currentSortCol == "Consumable_Flag")
      {
        if(brw_currentSortDir == "ASC")
        {
          browserHeaderLabelConsumableFlag = "C &#9650;"; //Arrow up
        }
        else
        {
          browserHeaderLabelConsumableFlag = "C &#9660;";//Arrow Down
        } 
      }
      if(brw_currentSortCol == "Equipment_Flag")
      {
        if(brw_currentSortDir == "ASC")
        {
          browserHeaderLabelEquipmentFlag = "E &#9650;"; //Arrow up
        }
        else
        {
          browserHeaderLabelEquipmentFlag = "E &#9660;";//Arrow Down
        } 
      }
      if(brw_currentSortCol == "Lab_Part_Flag")
      {
        if(brw_currentSortDir == "ASC")
        {
          browserHeaderLabelLabpartFlag = "L &#9650;"; //Arrow up
        }
        else
        {
          browserHeaderLabelLabpartFlag = "L &#9660;";//Arrow Down
        } 
      }
    }
	
	var colWidths = "<col width=\"7%\"><col width=\"5%\"><col width=\"7%\"><col width=\"6%\"><col width=\"6%\"><col width=\"6%\"><col width=\"6%\"><col width=\"6%\"><col width=\"10%\"><col width=\"10%\"><col width=\"2%\"><col width=\"2%\"><col width=\"2%\">";
    
    tableBrowserHeader = "<table class=\"w3-table w3-bordered w3-border w3-striped w3-hoverable\" style=\"table-layout:fixed; width=100%;\">" +
			colWidths +
            "<tr><th class=\"w3-border\" onclick=\"brw_tableSort('Name')\">"+browserHeaderLabelName+"</th>" +
            "<th class=\"w3-border\" onclick=\"brw_tableSort('Quantity')\">"+browserHeaderLabelQuantity+"</th>" + 
            "<th class=\"w3-border\" onclick=\"brw_tableSort('Type')\">"+browserHeaderLabelType+"</th>" + 
            "<th class=\"w3-border\" onclick=\"brw_tableSort('Value')\">"+browserHeaderLabelValue+"</th>" +
            "<th class=\"w3-border\" onclick=\"brw_tableSort('Location')\">"+browserHeaderLabelLocation+"</th>" + 
            "<th class=\"w3-border\" onclick=\"brw_tableSort('Suppliers_Name')\">"+browserHeaderLabelSupplierName+"</th>" + 
            "<th class=\"w3-border\" onclick=\"brw_tableSort('Supplier_Part_Number')\">"+browserHeaderLabelPartNumber+"</th>" + 
            "<th class=\"w3-border\" onclick=\"brw_tableSort('Ordering_Threshold')\">"+browserHeaderLabelOrderingThreshold+"</th>" + 
            "<th class=\"w3-border\" onclick=\"brw_tableSort('Description')\">"+browserHeaderLabelDescription+"</th>" + 
            "<th class=\"w3-border\" onclick=\"brw_tableSort('Item_Link')\">"+browserHeaderLabelURL+"</th>" + 		
		    "<th class=\"w3-border\" onclick=\"brw_tableSort('Consumable_Flag')\">"+browserHeaderLabelConsumableFlag+"</th>" +
		    "<th class=\"w3-border\" onclick=\"brw_tableSort('Equipment_Flag')\">"+browserHeaderLabelEquipmentFlag+"</th>" +
		    "<th class=\"w3-border\" onclick=\"brw_tableSort('Lab_Part_Flag')\">"+browserHeaderLabelLabpartFlag+"</th></tr></table>";

	tableBrowserData = "<table class=\"w3-table w3-bordered w3-border w3-striped w3-hoverable\" style=\"table-layout:fixed; width=100%;\">" +
			           colWidths;

    for( i = 0; i < browser_entry.length; i++)
    {
	  var partNumber = browser_entry[i].getElementsByTagName("Name")[0].childNodes[0].nodeValue;
	  
	  var consFlag = "";
	  var eqFlag = "";
	  var labFlag = "";
	  
	  if(browser_entry[i].getElementsByTagName("Consumable_Flag")[0].childNodes[0].nodeValue == "1")
	  {
		consFlag = "&#9873;";
	  }
	  if(browser_entry[i].getElementsByTagName("Equipment_Flag")[0].childNodes[0].nodeValue == "1")
	  {
		eqFlag = "&#9873;";
	  }
	  if(browser_entry[i].getElementsByTagName("Lab_Part_Flag")[0].childNodes[0].nodeValue == "1")
	  {
		labFlag = "&#9873;";
	  }
	  

      tableBrowserData += "<tr onclick=\"main_getQuickUpdateData('"+partNumber+"')\" ondblclick=\"main_loadItemEdit()\">" + 
                        "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + partNumber + "</td>" +
                        "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + browser_entry[i].getElementsByTagName("Quantity")[0].childNodes[0].nodeValue + "</td>" +
                        "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + browser_entry[i].getElementsByTagName("Type")[0].childNodes[0].nodeValue + "</td>" +
                        "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + browser_entry[i].getElementsByTagName("Value")[0].childNodes[0].nodeValue + "</td>" +
                        "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + browser_entry[i].getElementsByTagName("Location")[0].childNodes[0].nodeValue + "</td>" +                        
                        "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + browser_entry[i].getElementsByTagName("Suppliers_Name")[0].childNodes[0].nodeValue + "</td>" +
                        "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + browser_entry[i].getElementsByTagName("Supplier_Part_Number")[0].childNodes[0].nodeValue + "</td>" +
                        "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + browser_entry[i].getElementsByTagName("Ordering_Threshold")[0].childNodes[0].nodeValue + "</td>" +
                        "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + browser_entry[i].getElementsByTagName("Description")[0].childNodes[0].nodeValue + "</td>" +
                        "<td class=\"w3-border\" style=\"overflow:hidden; width:10%\"><a href=\"" +
						browser_entry[i].getElementsByTagName("Item_Link")[0].childNodes[0].nodeValue +"\" target=\"_blank\">" + 
						browser_entry[i].getElementsByTagName("Item_Link")[0].childNodes[0].nodeValue + "</a></td>" +
                        "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + consFlag + "</td>" +
                        "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + eqFlag + "</td>" +
                        "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + labFlag + "</td>" +
                        "</tr>";
    }
    tableBrowserData += "</table>"
    document.getElementById("id_main_browserHeader").innerHTML = tableBrowserHeader;	
    document.getElementById("id_main_browserData").innerHTML = tableBrowserData;	
  } 
  

  /***************
  Email Table Data
  ****************/
  if(emailList.length > 0)
  { 
	//Function is in EmailList.js
    elm_tableDisplay(emailList);
  }  
  

  /***************
  Class Table Data
  ****************/
  if(classData.length > 0)
  { 
	//Function is in ClassData.js
    cdm_displayTable(classData);
  }    

  return true; 
  
}


function main_loadItemEdit()
{
	document.getElementById('ItemViewModal').style.display='block';
	return;
}


/****************************************************************
Function:  IMSError()
Description: Launches an error modal dialog box. 
*****************************************************************/
function IMSError(title,message)
{
  document.getElementById('id_iem_title').innerHTML = title;
  document.getElementById('id_iem_message').innerHTML = message;
  document.getElementById('IMSErrorModal').style.display='block';
  
  return;
}

/****************************************************************
Function:  main_getQuickUpdateData()
Description: Requests data for a single item number.
*****************************************************************/
function main_getQuickUpdateData(partNumber)
{
  sendBackendRequest("Back_End/RetrieveItemData.php","SID="+getSID()+"&PartNumber="+ partNumber);
  main_loadLog();
  return;
}


/****************************************************************
Function:  search_showAutocomplete()
Description: Creates a datalist to show all autocomplete matches
for a partial search string.
*****************************************************************/
function search_showAutocomplete(partialStr)
{


  //Check for a selection	
  //from http://stackoverflow.com/questions/30022728/perform-action-when-clicking-html5-datalist-option
  var val = document.getElementById("id_search_bar").value;
  var opts = document.getElementById('id_search_queryList').childNodes;
  
  if(val == "") //blank or removed entry, delete the datalist.
  {
    document.getElementById("id_search_queryList").innerHTML = "";
	return;
  }
  
  for (var i = 0; i < opts.length; i++) 
  {
	if (opts[i].value === val) 
	{
	  // An item was selected from the list!
	  //Remove datalists and launch item load functions.
	  document.getElementById("id_cdm_partList").innerHTML = "";
    document.getElementById("id_search_queryList").innerHTML = "";
	  main_getQuickUpdateData(val);
	  return;
	}
  }
  
  sendBackendRequest("Back_End/QueryAutocomplete.php","SID="+getSID()+"&Filter="+ partialStr);
  return;
}

/****************************************************************
Function:  main_queryBarOnInput()
Description: Runs all functions required when the query bar has
an onInput event.
*****************************************************************/
function main_queryBarOnInput(element)
{
	search_showAutocomplete(element.value);
	main_loadBrowser();
}



/********************************************************
 * Function: RetrievePurchaseReport
 * Description: Retrieves items whose quantity is below threshold
 * Example of function: http://www.w3schools.com/xml/tryit.asp?filename=try_dom_xmlhttprequest_first
 ********************************************************/
function RetrievePurchaseReport()
{

  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() 
  {
    if (xhttp.readyState == 4 && xhttp.status == 200) 
    {
      createPurchaseReportTable(xhttp);
    }
  };
  xhttp.open("POST", "GeneratePurchaseReport.php", true);
  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhttp.send("SID="+getSID());
  
  
  
  
 

  var xhttp2 = new XMLHttpRequest();
  xhttp2.onreadystatechange = function() 
  {
    if (xhttp2.readyState == 4 && xhttp2.status == 200) 
    {
      createManualTable(xhttp2);
    }
  };
  xhttp2.open("POST", "GeneratePurchaseReport.php", true);
  xhttp2.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhttp2.send("SID="+getSID()+"&type=manual");
  
}


/**************************************************************
 * Name: createManualTable
 * Description: populates the manual purchase list table
 * Author: Justin Fraser, referencing code made by Craig Irvine
 **************************************************************/
 function createManualTable(xml)
 {
	  var i;
	  var xmlDoc = xml.responseXML;
	  var tableBrowser;
	  var tableLog;

	  //error check for null XML response.
	  if(xmlDoc == null)
	  {
	    IMSError("createManualTable Error","Returned XML Doc is Null");
		return false;    
	  }   
	  


	  //handle xml entries
	  var browser = xmlDoc.getElementsByTagName("BROWSER");


	  
	  if(browser.length > 0) //Browser section present
	  { 
	    var browser_entry = browser[0].getElementsByTagName("BROWSER_ENTRY");

	    //check for null data
	    if(browser_entry == null)
	    {
	      IMSError("parseXMLResponse Error","Broswer List is NULL");
		  return false;	
	    }


	    tableBrowser = 	"<table style=\"width:100%\" id=\"ManualPurchaseData\">" +
	    					"<tr>" +
	            				"<th>Supplier Part Number</th>" + 
	            				"<th>Item Link</th>" + 
	            				"<th>Quantity</th>" +
	            			"</tr>";

	    for( i = 0; i < browser_entry.length; i++)
	    {

	    	var supplier_part_number = browser_entry[i].getElementsByTagName("Supplier_Part_Number")[0].childNodes[0].nodeValue;
	    	var item_link = browser_entry[i].getElementsByTagName("Item_Link")[0].childNodes[0].nodeValue;
	    	var quantity = browser_entry[i].getElementsByTagName("Quantity")[0].childNodes[0].nodeValue;
	    	
	      tableBrowser += "<tr>" + 
	      						"<td><input type=\"checkbox\" name=\"formItem\"" +
	      						" value="+supplier_part_number+ "></input>" + supplier_part_number +"</td>" +
	                        	"<td>" + item_link + "</td>" +
	                        	"<td>" + quantity + "</td>" +
	                      "</tr>";
	    }


	    				
	    tableBrowser += "</table>";
	    
	    document.getElementById("ManualPurchaseData").innerHTML = tableBrowser;	
	  } 
	   
	  

	  return true; 
 }
 
 
/**************************************************************
 * Name: createPurchaseReportTable
 * Description: Parses xml data given by XMLHttpRequest object
 * Author: Craig Irvine
 * Modified by: Justin Fraser (latest modification: Feb 18, 2016)
 **************************************************************/
function createPurchaseReportTable(xml)
{

  var i;
  var xmlDoc = xml.responseXML;
  var tableBrowser;
  var tableLog;

  //error check for null XML response.
  if(xmlDoc == null)
  {
    IMSError("createPurchaseReportTable Error","Returned XML Doc is Null");
	return false;    
  }   
  


  //handle xml entries
  var browser = xmlDoc.getElementsByTagName("BROWSER");


  
  if(browser.length > 0) //Browser section present
  { 
    var browser_entry = browser[0].getElementsByTagName("BROWSER_ENTRY");

    //check for null data
    if(browser_entry == null)
    {
      IMSError("parseXMLResponse Error","Broswer List is NULL");
	  return false;	
    }
    
    tableBrowser = "<table><tr>" +
            "<th>Supplier Part Number</th>" + 
            "<th>Item Link</th>" + 
            "<th>Quantity</th>";

    for( i = 0; i < browser_entry.length; i++)
    {

    	var supplier_part_number = browser_entry[i].getElementsByTagName("Supplier_Part_Number")[0].childNodes[0].nodeValue;
    	var item_link = browser_entry[i].getElementsByTagName("Item_Link")[0].childNodes[0].nodeValue;
    	var quantity = browser_entry[i].getElementsByTagName("Quantity")[0].childNodes[0].nodeValue;
    	
      tableBrowser += "<tr>" + 
						"<td>" + supplier_part_number + "</td>" +
						"<td>" + item_link + "</td>" +
						"<td>" + quantity + "</td>" +
                      "</tr>";
    }

    tableBrowser += "</table>"
    document.getElementById("PurchaseListData").innerHTML = tableBrowser;	
  } 
   
  

  return true; 
  
}

function main_checkThreshold(){
	var xhttp = new XMLHttpRequest();
	  xhttp.onreadystatechange = function() 
	  {
	    if(xhttp.status == 404)
		{
		  IMSError("main_checkThreshold Error","404 Error returned for: "+"CheckThresholds.php");
		}
		
		if(xhttp.status == 500)
		{
		  IMSError("main_checkThreshold Error","500 Error returned for: "+"CheckThresholds.php");
		}
	  
	    if (xhttp.readyState == 4 && xhttp.status == 200) 
	    {
	      parseXMLResponse(xhttp);
	    }
	  };
	  xhttp.open("POST", "Back_End/CheckThresholds.php", true);
	  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	  xhttp.send("SID="+getSID()); 
	  
	  return;
}


function tableTimers(){
	setInterval(main_loadBrowser, 600000);
	setInterval(main_loadLog, 600000);
}

