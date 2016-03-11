function RemoveEntry(){
	var x = document.getElementsByName("formItem");
	
	var a = 0;
	var b = 0;
	
	var options = "SID="+getSID();
	for (a = 0; a < x.length; a++){
		if (x[a].checked == true){
			options += "&itemList["+b+"]="+x[a].value;
			b++;
		}
	}
	var xhttp = new XMLHttpRequest();
	  xhttp.onreadystatechange = function() 
	  {
	    if (xhttp.readyState == 4 && xhttp.status == 200) 
	    {
	    }
	  };
	  xhttp.open("POST", "Back_End/RemoveManualEntries.php", true);
	  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	  xhttp.send(options);
	  
	  
	  
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


/****************************************************************
Function:  shp_addClassDataEntry()
Description: Sends new class data to the server to be saved.
*****************************************************************/
function shp_addClassDataEntry()
{

  var SupplierNumber = document.getElementById("id_shp_SupplierNumber").value;
  var ItemLink = document.getElementById("id_shp_ItemLink").value;
  var Quantity = document.getElementById("id_shp_Quantity").value;


  
  sendBackendRequest("Back_End/AddPurchaseListItem.php","SID="+getSID()+"&SN="+SupplierNumber+"&IL="+ItemLink+"&QN="+Quantity);
  //clear entries
  document.getElementById("id_cdm_idDisplay").innerHTML = "New";
  document.getElementById("id_shp_SupplierNumber").value = "";
  document.getElementById("id_shp_ItemLink").value = "";	
  document.getElementById("id_shp_Quantity").value = "";
  
  return;
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


	    tableBrowser = 	"<table id=\"ManualPurchaseData\">" +
							"<colgroup>" + 
							"<col span=\"1\" width=\"5%\">" +
	    					"<tr>" +
								"<th class=\"w3-border\">SEL</th>" + 
	            				"<th class=\"w3-border\">Supplier Part Number</th>" + 
	            				"<th class=\"w3-border\">Item Link</th>" + 
	            				"<th class=\"w3-border\">Quantity</th>" +
	            			"</tr>";

	    for( i = 0; i < browser_entry.length; i++)
	    {

	    	var supplier_part_number = browser_entry[i].getElementsByTagName("Supplier_Part_Number")[0].childNodes[0].nodeValue;
	    	var item_link = browser_entry[i].getElementsByTagName("Item_Link")[0].childNodes[0].nodeValue;
	    	var quantity = browser_entry[i].getElementsByTagName("Quantity")[0].childNodes[0].nodeValue;
	    	
	      tableBrowser += "<tr>" + 
	      						"<td class=\"w3-border\"><input class=\"w3-check\" type=\"checkbox\" name=\"formItem\"" +
	      						" value="+supplier_part_number+ "></input>" + 
								"<td class=\w3-border\">" + supplier_part_number +"</td>" +
	                        	"<td class=\"w3-border\">" + item_link + "</td>" +
	                        	"<td class=\"w3-border\">" + quantity + "</td>" +
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

            "<th class=\"w3-border\">Supplier Part Number</th>" + 
            "<th class=\"w3-border\">Item Link</th>" + 	
            "<th class=\"w3-border\">Quantity</th>";

    for( i = 0; i < browser_entry.length; i++)
    {

    	var supplier_part_number = browser_entry[i].getElementsByTagName("Supplier_Part_Number")[0].childNodes[0].nodeValue;
    	var item_link = browser_entry[i].getElementsByTagName("Item_Link")[0].childNodes[0].nodeValue;
    	var quantity = browser_entry[i].getElementsByTagName("Quantity")[0].childNodes[0].nodeValue;
    	
      tableBrowser += "<tr>" + 
      						"<td class=\"w3-border\">" + supplier_part_number + "</td>" +
                        	"<td class=\"w3-border\">" + item_link + "</td>" +
                        	"<td class=\"w3-border\">" + quantity + "</td>" +
                      "</tr>";
    }

    tableBrowser += "</table>"
    document.getElementById("PurchaseListData").innerHTML = tableBrowser;	
  } 
   
  

  return true; 
  
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
  xhttp.open("POST", "Back_End/GeneratePurchaseReport.php", true);
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
  xhttp2.open("POST", "Back_End/GeneratePurchaseReport.php", true);
  xhttp2.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhttp2.send("SID="+getSID()+"&type=manual");
  
}

function RemoveEntryAndRefresh()
{
	RemoveEntry();
	setTimeout(RetrievePurchaseReport, 100);
}

function shp_addClassDataEntryAndClose(){
	shp_addClassDataEntry();
	setTimeout(RetrievePurchaseReport, 100);
}