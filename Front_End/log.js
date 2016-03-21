function log_displayTable(log)
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
	
	return;
}