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
  value = document.getElementById(id).value;		
	
  sendBackendRequest("Back_End/ModifyItem.php","SID="+getSID()+"&PartNumber="+ itemNumber + "&Field="+field + "&Value=" + value);
  return;

}