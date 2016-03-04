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
}

/****************************************************************
Function:  getSID()
Description: Gets a SID from the server and creates a local cookie.
*****************************************************************/
function setSID()
{

  var xhttp = new XMLHttpRequest(); 
  xhttp.open("POST", "Back_End/GenerateSID.php", false);
  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhttp.send(); 
  
  
  var SIDResponse = xhttp.responseText;

  if(SIDResponse == "SIDError")
  {
    IMSError("setSID Error","SID Missing.");
	return;
  }
	
  document.cookie=SIDResponse;

  document.getElementById("id_main_SIDDisplay").innerHTML = SIDResponse;

  
  return;
  
}

function getSID()
{
  return document.cookie;
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
    if (xhttp.readyState == 4 && xhttp.status == 200) 
    {
      parseXMLResponse(xhttp);
    }
  };
  xhttp.open("POST", PHPscript, true);
  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhttp.send(postOptions);
  
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
  
	document.getElementById("status_code").innerHTML = statusCode;
	document.getElementById("status_message").innerHTML = statusMessage;
  
	//check status code and throw and alert if a fail occurred.
	if(statusCode != "0")
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

	
  }  
  

  
  if(log.length > 0)
  {
    var log_entry = log[0].getElementsByTagName("LOG_ENTRY");
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

    tableLog = "<table class=\"w3-table w3-bordered w3-border w3-striped w3-hoverable\" style=\"table-layout:fixed; width=100%;\"><tr>" +
            "<col width=\"250\">"+
            "<th class=\"w3-border\" onclick=\"\">"+logHeaderLabelDate+"</th>" +
            "<th class=\"w3-border\" onclick=\"\">"+logHeaderLabelSID+"</th>" + 
            "<th class=\"w3-border\" onclick=\"\">"+logHeaderLabelPN+"</th>" + 
            "<th class=\"w3-border\" onclick=\"\">"+logHeaderLabelLevel+"</th>" + 
            "<th class=\"w3-border\" onclick=\"\">"+logHeaderLabelDescription+"</th>";
			
			
	tableItemLog = tableLog;
	var tableRow = "";
			   
    for( i = 0; i < log_entry.length; i++)
    {
      document.getElementById("log").innerHTML = i;

      tableRow = "<tr>" + 
				  "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + log_entry[i].getElementsByTagName("Date")[0].childNodes[0].nodeValue + "</td>" +
                  "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + log_entry[i].getElementsByTagName("SID")[0].childNodes[0].nodeValue + "</td>" +
                  "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + log_entry[i].getElementsByTagName("Item")[0].childNodes[0].nodeValue + "</td>" +
                  "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + log_entry[i].getElementsByTagName("Level")[0].childNodes[0].nodeValue + "</td>" +
                  "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + log_entry[i].getElementsByTagName("Description")[0].childNodes[0].nodeValue + "</td>" +
                  "</tr>";
 
	  tableLog += tableRow;
 
      var ItemViewPN = document.getElementById("id_ivm_itemNumber").value;
	  if(ItemViewPN != "")
	  {
		var xmlPN = log_entry[i].getElementsByTagName("Item")[0].childNodes[0].nodeValue;
		if(ItemViewPN == xmlPN)
		{
		  tableItemLog += tableRow;
		}
	  }
				  
    }
    tableLog += "</table>";
	tableItemLog += "</table>";
    document.getElementById("log").innerHTML = tableLog;
	document.getElementById("id_ivm_table").innerHTML = tableItemLog;


  }
  

  
  if(browser.length > 0) //Browser section present
  { 
    var browser_entry = browser[0].getElementsByTagName("BROWSER_ENTRY");

    //check for null data
    if(browser_entry == null)
    {
      IMSError("parseXMLResponse Error","Broswer List is NULL");
	    return false;	
    }

    var browserHeaderLabelName = "Name";
    var browserHeaderLabelQuantity = "Quantity";
    var browserHeaderLabelType = "Part Type";
    var browserHeaderLabelValue = "Value";
    var browserHeaderLabelLocation = "Location";
    var browserHeaderLabelPartNumber = "Supplier Part Number";
    var browserHeaderLabelOrderingThreshold = "Ordering Threshold";
    var browserHeaderLabelDescription = "Description";

    /*Add Sort stuff here*/
    
    tableBrowser = "<table class=\"w3-table w3-bordered w3-border w3-striped w3-hoverable\" style=\"table-layout:fixed; width=100%;\"><tr>" +
            "<col width=\"150\">"+
            "<th class=\"w3-border\" onclick=\"\">"+browserHeaderLabelName+"</th>" +
            "<th class=\"w3-border\" onclick=\"\">"+browserHeaderLabelQuantity+"</th>" + 
            "<th class=\"w3-border\" onclick=\"\">"+browserHeaderLabelType+"</th>" + 
            "<th class=\"w3-border\" onclick=\"\">"+browserHeaderLabelValue+"</th>" +
            "<th class=\"w3-border\" onclick=\"\">"+browserHeaderLabelLocation+"</th>" + 
            "<th class=\"w3-border\" onclick=\"\">"+browserHeaderLabelPartNumber+"</th>" + 
            "<th class=\"w3-border\" onclick=\"\">"+browserHeaderLabelOrderingThreshold+"</th>" + 
            "<th class=\"w3-border\" onclick=\"\">"+browserHeaderLabelDescription+"</th></tr>";

    for( i = 0; i < browser_entry.length; i++)
    {

      tableBrowser += "<tr>" + 
                        "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + browser_entry[i].getElementsByTagName("Name")[0].childNodes[0].nodeValue + "</td>" +
                        "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + browser_entry[i].getElementsByTagName("Quantity")[0].childNodes[0].nodeValue + "</td>" +
                        "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + browser_entry[i].getElementsByTagName("Type")[0].childNodes[0].nodeValue + "</td>" +
                        "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + browser_entry[i].getElementsByTagName("Value")[0].childNodes[0].nodeValue + "</td>" +
                        "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + browser_entry[i].getElementsByTagName("Location")[0].childNodes[0].nodeValue + "</td>" +                        
                        "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + browser_entry[i].getElementsByTagName("Supplier_Part_Number")[0].childNodes[0].nodeValue + "</td>" +
                        "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + browser_entry[i].getElementsByTagName("Ordering_Threshold")[0].childNodes[0].nodeValue + "</td>" +
                        "<td class=\"w3-border\" style=\"word-wrap: break-word\">" + browser_entry[i].getElementsByTagName("Description")[0].childNodes[0].nodeValue + "</td>" +
                        "</tr>";
    }

    tableBrowser += "</table>"
    document.getElementById("browser").innerHTML = tableBrowser;	
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

	
	//Table header
	tableClassData = "<table class=\"w3-table w3-bordered w3-border w3-striped w3-hoverable\" style=\"table-layout:fixed; width=100%;\"><tr>" +
			"<col width=\"50\">"+
			"<th class=\"w3-border\">SEL</th>" +
            "<th class=\"w3-border\" onclick=\"cdm_tableSort('Class')\">"+headerLabelClass+"</th>" + 
            "<th class=\"w3-border\" onclick=\"cdm_tableSort('Part')\">"+headerLabelPN+"</th>" + 
            "<th class=\"w3-border\" onclick=\"cdm_tableSort('Quantity')\">"+headerLabelQty+"</th>" +
            "<th class=\"w3-border\" onclick=\"cdm_tableSort('Date')\">"+headerLabelDate+"</th>";
	//table data
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