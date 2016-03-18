/****************************************************************
Function:  ivm_modifyItem(type)
Description: Modifies a field for the currently loaded item.
*****************************************************************/
function ivm_modifyItem(id,field)
{
  var itemNumber = document.getElementById("id_ivm_itemNumber").value;
  var value = "";

  if(itemNumber == "") // nothing loaded.
  {
	return;
  }
  
  if((document.getElementById(id).type == "text") || (document.getElementById(id).type == "select-one"))
	value = document.getElementById(id).value;		
  else if(document.getElementById(id).type == "checkbox")
  	if(document.getElementById(id).checked == true)
		value = "1";
	else
		value = "0";  
	
  sendBackendRequest("Back_End/ModifyItem.php","SID="+getSID()+"&PartNumber="+ itemNumber + "&Field="+field + "&Value=" + value);
  main_loadLog(); //refresh the log
  return;

}


function ivm_deleteItem()
{

	var partNumber = document.getElementById('id_ivm_itemNumber').value;
	
	var response = window.confirm("Are you sure you wish to delete item: " + partNumber + "?");
	
	if(response == true)	
	{
		sendBackendRequest("Back_End/DeleteItem.php","SID="+getSID()+"&PartNumber="+partNumber);	
		document.getElementById('ItemViewModal').style.display='none';
	}
	
	main_loadLog(); //refresh the log
	main_loadBrowser(); //refresh the item browser.
	return;
}