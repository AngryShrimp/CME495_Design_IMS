/****************************************************************
Function:  populateForms()
Description: This function run all data retrieval functions so 
that all data fields are populated on page load.  Also gets a 
SID from the server
*****************************************************************/
function populateForms()
{
	setSID();
	cdm_getClassData();
	main_loadBrowser();
	elm_getEmailList();
	loadLog();
	RetrievePurchaseReport();
	tableTimers();

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
  } 

  document.getElementById("id_main_SIDDisplay").innerHTML = current_sid;
  
  return;
  
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
*****************************************************************/
function main_loadBrowser()
{

  var filter = "";
  
  var searchBarVal = document.getElementById("id_search_bar").value;
  
  if(searchBarVal != "")
  {
	filter = "&Filter=" + searchBarVal;
  }

  sendBackendRequest("Back_End/RetrieveBrowserData.php","SID="+getSID()+filter);  
  
  return;
}

function loadLog()
{  

  sendBackendRequest("Back_End/RetrieveLog.php","SID="+getSID()+"&LogLevel=All");  

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
	  IMSError("parseXMLResponse Error","SID Invalid or Missing, refreshing in 5 seconds\n" + statusMessage);
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
	document.getElementById("id_qa_ID").innerHTML = quickAccess[0].getElementsByTagName("Name")[0].childNodes[0].nodeValue;
	document.getElementById("id_qa_Description").innerHTML = quickAccess[0].getElementsByTagName("Description")[0].childNodes[0].nodeValue;
	document.getElementById("id_qa_Quantity").innerHTML = quickAccess[0].getElementsByTagName("Quantity")[0].childNodes[0].nodeValue;
	
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
  

  
  if(log.length > 0)
  {
    var log_entry = log[0].getElementsByTagName("LOG_ENTRY");
	var tableLogHeader = "";
	var tableLogData = "";
	var tableItemLogHeader = "";
	var tableItemLogData = "";
	
    //check for null data
    if(log_entry == null)
    {
      IMSError("parseXMLResponse Error","Log List is NULL");
	    return false;	
    }

    var logHeaderLabelDate = "Date";
    var logHeaderLabelSID = "SID";
    var logHeaderLabelPN = "Item Number";
    var logHeaderLabelLevel = "Log Type";
    var logHeaderLabelDescription = "Description";

    /*Add sort stuff here*/

    tableLogHeader = "<table class=\"w3-table w3-bordered w3-border w3-striped w3-hoverable\" style=\"table-layout:fixed; width=100%;\"><tr>" +
            "<col width=\"10%\"><col width=\"12%\"><col width=\"15%\"><col width=\"10%\"><col width=\"20%\">"+
            "<th class=\"w3-border\" onclick=\"\">"+logHeaderLabelDate+"</th>" +
            "<th class=\"w3-border\" onclick=\"\">"+logHeaderLabelSID+"</th>" + 
            "<th class=\"w3-border\" onclick=\"\">"+logHeaderLabelPN+"</th>" + 
            "<th class=\"w3-border\" onclick=\"\">"+logHeaderLabelLevel+"</th>" + 
            "<th class=\"w3-border\" onclick=\"\">"+logHeaderLabelDescription+"</th></table>";
		
			
	tableItemLogHeader = tableLogHeader;
	var tableRow = "";
	   
	tableLogData = "<table class=\"w3-table w3-bordered w3-border w3-striped w3-hoverable\" style=\"table-layout:fixed; width=100%;\">" + 
					"<col width=\"10%\"><col width=\"12%\"><col width=\"15%\"><col width=\"10%\"><col width=\"20%\">";
	tableItemLogData = tableLogData;
	

    for( i = 0; i < log_entry.length; i++)
    {

      tableRow = "<tr>" + 
				  "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + log_entry[i].getElementsByTagName("Date")[0].childNodes[0].nodeValue + "</td>" +
                  "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + log_entry[i].getElementsByTagName("SID")[0].childNodes[0].nodeValue + "</td>" +
                  "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + log_entry[i].getElementsByTagName("Item")[0].childNodes[0].nodeValue + "</td>" +
                  "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + log_entry[i].getElementsByTagName("Level")[0].childNodes[0].nodeValue + "</td>" +
                  "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + log_entry[i].getElementsByTagName("Description")[0].childNodes[0].nodeValue + "</td>" +
                  "</tr>";
 
	  tableLogData += tableRow;
 
      var ItemViewPN = document.getElementById("id_ivm_itemNumber").value;
	  if(ItemViewPN != "")
	  {
		var xmlPN = log_entry[i].getElementsByTagName("Item")[0].childNodes[0].nodeValue;
		if(ItemViewPN == xmlPN)
		{
		  tableItemLogData += tableRow;
		}
	  }				  
    }

    tableLogData += "</table>";
	tableItemLogData += "</table>";

    document.getElementById("id_main_logHeader").innerHTML = tableLogHeader;
    document.getElementById("id_main_logData").innerHTML = tableLogData;
	
	document.getElementById("id_ivm_tableHeader").innerHTML = tableItemLogHeader;
	document.getElementById("id_ivm_tableData").innerHTML = tableItemLogData;


  }
  

  
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
    var browserHeaderLabelPartNumber = "Supplier Part Number";
    var browserHeaderLabelOrderingThreshold = "Ordering Threshold";
    var browserHeaderLabelDescription = "Description";
    var browserHeaderLabelConsumableFlag = "C";
    var browserHeaderLabelEquipmentFlag = "E";
    var browserHeaderLabelLabpartFlag = "L";


    /*Add Sort stuff here*/
    
    tableBrowserHeader = "<table class=\"w3-table w3-bordered w3-border w3-striped w3-hoverable\" style=\"table-layout:fixed; width=100%;\">" +
			"<col width=\"12%\"><col width=\"6%\"><col width=\"12%\"><col width=\"10%\"><col width=\"11%\"><col width=\"11%\"><col width=\"13%\"><col width=\"18%\"><col width=\"2%\"><col width=\"2%\"><col width=\"2%\">" +
            "<tr><th class=\"w3-border\" onclick=\"\">"+browserHeaderLabelName+"</th>" +
            "<th class=\"w3-border\" onclick=\"\">"+browserHeaderLabelQuantity+"</th>" + 
            "<th class=\"w3-border\" onclick=\"\">"+browserHeaderLabelType+"</th>" + 
            "<th class=\"w3-border\" onclick=\"\">"+browserHeaderLabelValue+"</th>" +
            "<th class=\"w3-border\" onclick=\"\">"+browserHeaderLabelLocation+"</th>" + 
            "<th class=\"w3-border\" onclick=\"\">"+browserHeaderLabelPartNumber+"</th>" + 
            "<th class=\"w3-border\" onclick=\"\">"+browserHeaderLabelOrderingThreshold+"</th>" + 
            "<th class=\"w3-border\" onclick=\"\">"+browserHeaderLabelDescription+"</th>" + 
			"<th class=\"w3-border\" onclick=\"\">"+browserHeaderLabelConsumableFlag+"</th>" +
			"<th class=\"w3-border\" onclick=\"\">"+browserHeaderLabelEquipmentFlag+"</th>" +
			"<th class=\"w3-border\" onclick=\"\">"+browserHeaderLabelLabpartFlag+"</th></tr></table>";

	tableBrowserData = "<table class=\"w3-table w3-bordered w3-border w3-striped w3-hoverable\" style=\"table-layout:fixed; width=100%;\">" +
			            "<col width=\"12%\"><col width=\"6%\"><col width=\"12%\"><col width=\"10%\"><col width=\"11%\"><col width=\"11%\"><col width=\"13%\"><col width=\"18%\"><col width=\"2%\"><col width=\"2%\"><col width=\"2%\">";

    for( i = 0; i < browser_entry.length; i++)
    {

      tableBrowserData += "<tr>" + 
                        "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + browser_entry[i].getElementsByTagName("Name")[0].childNodes[0].nodeValue + "</td>" +
                        "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + browser_entry[i].getElementsByTagName("Quantity")[0].childNodes[0].nodeValue + "</td>" +
                        "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + browser_entry[i].getElementsByTagName("Type")[0].childNodes[0].nodeValue + "</td>" +
                        "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + browser_entry[i].getElementsByTagName("Value")[0].childNodes[0].nodeValue + "</td>" +
                        "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + browser_entry[i].getElementsByTagName("Location")[0].childNodes[0].nodeValue + "</td>" +                        
                        "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + browser_entry[i].getElementsByTagName("Supplier_Part_Number")[0].childNodes[0].nodeValue + "</td>" +
                        "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + browser_entry[i].getElementsByTagName("Ordering_Threshold")[0].childNodes[0].nodeValue + "</td>" +
                        "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + browser_entry[i].getElementsByTagName("Description")[0].childNodes[0].nodeValue + "</td>" +
                        "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + browser_entry[i].getElementsByTagName("Consumable_Flag")[0].childNodes[0].nodeValue + "</td>" +
                        "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + browser_entry[i].getElementsByTagName("Equipment_Flag")[0].childNodes[0].nodeValue + "</td>" +
                        "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + browser_entry[i].getElementsByTagName("Lab_Part_Flag")[0].childNodes[0].nodeValue + "</td>" +
                        "</tr>";
    }
    tableBrowserData += "</table>"
    document.getElementById("id_main_browserHeader").innerHTML = tableBrowserHeader;	
    document.getElementById("id_main_browserData").innerHTML = tableBrowserData;	
  } 
  

  
  if(emailList.length > 0)
  { 
    var email_entry = emailList[0].getElementsByTagName("EMAIL_ENTRY");
	var tableEmailList = "";
	
    //check for null data
    if(email_entry == null)
    {
      IMSError("parseXMLResponse Error","Class Data Entry is NULL");
      return false;	
    }    
	
	//Table header
	tableEmailList = "<table class=\"w3-table w3-bordered w3-border w3-striped w3-hoverable\" style=\"table-layout:fixed; width=100%;\"><tr>" +
	        "<col width=\"85%\">"+
			"<col width=\"15%\">"+
            "<th class=\"w3-border\">Email Address</th>" + 
            "<th class=\"w3-border\">Delete</th></tr>";
			

	//table data
	for( i = 0; i < email_entry.length; i++)
    {	
		var id = email_entry[i].getElementsByTagName("Id")[0].childNodes[0].nodeValue;
		var emailAddress = email_entry[i].getElementsByTagName("Recipients")[0].childNodes[0].nodeValue;	
	
		tableEmailList += "<tr onclick=\"elm_loadRow('" + 
						id + "','" +
						emailAddress +
						"')\">" + 
						"<td class=\"w3-border\" style=\"word-wrap: break-word\">" + emailAddress + "</td>" +
						"<td class=\"w3-border\" style=\"word-wrap: break-word\">" + 
						"<button class=\"w3-btn w3-tiny w3-red w3-border w3-round-large\" type=\"button\" "+
						"onclick=\"elm_deleteEmailAddress('"+id+"')\">&times</button></td>" +
                        "</tr>";								
	}

    tableEmailList += "</table>"

	
	document.getElementById("id_elm_table").innerHTML = tableEmailList;

  }  
  
  if(classData.length > 0)
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
	tableClassDataHeader = "<table class=\"w3-table w3-bordered w3-border w3-striped w3-hoverable\" style=\"table-layout:fixed; width:100%; overflow-y:scroll;\"><tr>" +
			"<col width=\"8%\"></col><col width=\"23%\"></col><col width=\"23%\"></col><col width=\"23%\"></col><col width=\"23%\">" +
			"<th class=\"w3-border\">SEL</th>" +
            "<th class=\"w3-border\" onclick=\"cdm_tableSort('Class')\">"+headerLabelClass+"</th>" + 
            "<th class=\"w3-border\" onclick=\"cdm_tableSort('Part')\">"+headerLabelPN+"</th>" + 
            "<th class=\"w3-border\" onclick=\"cdm_tableSort('Quantity')\">"+headerLabelQty+"</th>" +
            "<th class=\"w3-border\" onclick=\"cdm_tableSort('Date')\">"+headerLabelDate+"</th></table>";
	//table data
	tableClassData = "<table class=\"w3-table w3-bordered w3-border w3-striped w3-hoverable\" style=\"table-layout:fixed; width:100%;\">"+
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

  }  
  

  return true; 
  
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
Function:  getQuickUpdateData()
Description: Requests data for a single item number.
*****************************************************************/
function getQuickUpdateData(partNumber)
{
  sendBackendRequest("Back_End/RetrieveItemData.php","SID="+getSID()+"&PartNumber="+ partNumber);
  
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
	  getQuickUpdateData(val);
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

function tableTimers(){
	setInterval(main_loadBrowser, 600000);
	setInterval(loadLog, 600000);
}