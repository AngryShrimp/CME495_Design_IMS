/****************************************************************
Function:  cdm_autoPN()
Description: Generates an Item Number suggestion based on form 
inputs.
*****************************************************************/
function cdm_autoPN()
{

  var partNumber = "";
  if(document.getElementById("id_cvm_type").value != "")
  {
	switch(document.getElementById("id_cvm_type").value)
	{
	  case "Capacitor":
		partNumber = "C";
		break;
	  case "Resistor":
		partNumber = "R";
		break;
	  case "Inductor":
		partNumber = "I";
		break;
	  case "Equipment":
		partNumber = "E";
		break;
	  case "Transistor":
		partNumber = "Q";
		break;
	  case "Transformer":
		partNumber = "T";
		break;
	  default:
		partNumber = "O";
		break;
	}
	
  }
  
  if(document.getElementById("id_cvm_value").value != "")
  {
	partNumber += document.getElementById("id_cvm_value").value;
  }
  else
  {
	partNumber += "XXX";
  }
  
  partNumber += "-01";
  document.getElementById("id_cvm_itemNumber").value = partNumber;
  return;
}


/****************************************************************
Function:  createNewItem()
Description: Sends new Item data to the server for saving. 
Synchronous mode is used to ensure previous processes are completed
before the next is started. 
*****************************************************************/
function createNewItem()
{


  //Input Verification code goes here.
  var partNumber = document.getElementById("id_cvm_itemNumber").value;
  var qty = document.getElementById("id_cvm_qty").value;
  var threshold = document.getElementById("id_cvm_thresh").value;
  var location = document.getElementById("id_cvm_location").value;
  var description = document.getElementById("id_cvm_desc").value;
  var type = document.getElementById("id_cvm_type").value;
  var value = document.getElementById("id_cvm_value").value;
  var supplierName = document.getElementById("id_cvm_supplierName").value;
  var supplierPN = document.getElementById("id_cvm_supplierPN").value;
  var link = document.getElementById("id_cvm_link").value;
  var flagConsumable = document.getElementById("id_cvm_flagConsumable").checked;
  var flagEquipment = document.getElementById("id_cvm_flagEquipment").checked;
  var flagLabPart = document.getElementById("id_cvm_flagLabPart").checked;

	
  //Create new Item record
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() 
  {
    if (xhttp.readyState == 4 && xhttp.status == 200) 
    {
      parseXMLResponse(xhttp);
    }
  };
  
  xhttp.open("POST", "Back_End/CreateNewItem.php", false);
  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhttp.send("SID="+getSID()+"&PartNumber="+partNumber); 
  
  var returnVal = parseXMLResponse(xhttp);
  
  if(returnVal == false)
  {
	alert("Part Number Creation Error");
	return;
  }  

  if(qty != "")
  {  
    xhttp.open("POST", "Back_End/ModifyItem.php", false);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");  
	xhttp.send("SID="+getSID()+"&PartNumber="+partNumber+"&Field=Quantity&Value="+qty);
  }  
  
  if(threshold != "")
  {  
    xhttp.open("POST", "Back_End/ModifyItem.php", false);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhttp.send("SID="+getSID()+"&PartNumber="+partNumber+"&Field=Ordering_Threshold&Value="+threshold);
  }

  
  if(location != "")
  {  
    xhttp.open("POST", "Back_End/ModifyItem.php", false);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhttp.send("SID="+getSID()+"&PartNumber="+partNumber+"&Field=Location&Value="+location);
  }
  
  if(description != "")
  {  
    xhttp.open("POST", "Back_End/ModifyItem.php", false);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhttp.send("SID="+getSID()+"&PartNumber="+partNumber+"&Field=Description&Value="+description);
  }
  
  if(type != "")
  {  
    xhttp.open("POST", "Back_End/ModifyItem.php", false);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhttp.send("SID="+getSID()+"&PartNumber="+partNumber+"&Field=Type&Value="+type);
  }
  
  if(value != "")
  {  
    xhttp.open("POST", "Back_End/ModifyItem.php", false);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhttp.send("SID="+getSID()+"&PartNumber="+partNumber+"&Field=Value&Value="+value);
  }
  
  if(supplierName != "")
  { 
    xhttp.open("POST", "Back_End/ModifyItem.php", false);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhttp.send("SID="+getSID()+"&PartNumber="+partNumber+"&Field=Suppliers_Name&Value="+supplierName);
  }
  
  if(supplierPN != "")
  {  
    xhttp.open("POST", "Back_End/ModifyItem.php", false);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhttp.send("SID="+getSID()+"&PartNumber="+partNumber+"&Field=Supplier_Part_Number&Value="+supplierPN);
  }
  
  if(link != "")
  {  
    xhttp.open("POST", "Back_End/ModifyItem.php", false);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhttp.send("SID="+getSID()+"&PartNumber="+partNumber+"&Field=Item_Link&Value="+link);
  }
  
  if(flagConsumable != 0)
  {
    xhttp.open("POST", "Back_End/ModifyItem.php", false);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhttp.send("SID="+getSID()+"&PartNumber="+partNumber+"&Field=Consumable_Flag&Value="+flagConsumable);  
  }
  
  if(flagEquipment != 0)
  {
    xhttp.open("POST", "Back_End/ModifyItem.php", false);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhttp.send("SID="+getSID()+"&PartNumber="+partNumber+"&Field=Equipment_Flag&Value="+flagEquipment);  
  }
  
  if(flagLabPart != 0)
  {
    xhttp.open("POST", "Back_End/ModifyItem.php", false);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhttp.send("SID="+getSID()+"&PartNumber="+partNumber+"&Field=Lab_Part_Flag&Value="+flagLabPart);  
  }
  
  main_loadLog(); //refresh the log
  return;
  
}