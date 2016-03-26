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
	
  if(value == "")
	value = " ";
	
  sendBackendRequest("Back_End/ModifyItem.php","SID="+getSID()+"&PartNumber="+ itemNumber + "&Field="+field + "&Value=" + value);
  main_loadLog(); //refresh the log
  document.getElementById(id).style.backgroundColor = "white";
  setTimeout(main_checkThreshold,250);
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


function ivm_editedField(elementID)
{
	document.getElementById(elementID).style.backgroundColor = "lightgreen";
	return;
}



function onchangeIE_ivm(evt)
{
	if(navigator.sayswho == "IE 11")
	{
		var evt = (evt) ? evt : ((event) ? event : null); 
		var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null); 
		if ((evt.keyCode == 13) && (node.type=="text"))  
		{	
			
			switch(node.id)
			{
				
				case "id_ivm_value":
					ivm_modifyItem(node.id,'Value');
				break;
				case "id_ivm_desc":
					ivm_modifyItem(node.id,'Description');
				break;
				case "id_ivm_supplierPN":
					ivm_modifyItem(node.id,'Supplier_Part_Number');
				break;
				case "id_ivm_supplierName":
					ivm_modifyItem(node.id,'Suppliers_Name');
				break;
				case "id_ivm_link":
					ivm_modifyItem(node.id,'Item_Link');
				break;
				case "id_ivm_qty":
					ivm_modifyItem(node.id,'Quantity');
				break;
				case "id_ivm_thresh":
					ivm_modifyItem(node.id,'Ordering_Threshold');
				break;
				case "id_ivm_location":
					ivm_modifyItem(node.id,'Location');
				break;
				default: 
					return;
			}		
			
		}
	}
}

