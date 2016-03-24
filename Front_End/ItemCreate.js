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
	  case "Integrated Circuit":
		partNumber = "IC";
		break;
	  default:
		partNumber = "O";
		break;
	}
	
  }
  
  if(document.getElementById("id_cvm_value").value != "")
  {
	var value = document.getElementById("id_cvm_value").value;
	while(value.length < 3)
	{
		value="0".concat(value);
	}  
	partNumber += value;
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
	return;
  }  
  
  //re-get the part number incase it was changed by CreateNewItem.php.
  partNumber = document.getElementById("id_cvm_itemNumber").value;

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
  
  //IMSError used as a message box in this case.
  IMSError("Part Number Creation","Part Number "+partNumber + " created successfully.");
  return;
  
}


function cvm_setupAddItemBatch()
{
	//File browser/read code
	var filesInput = document.getElementById("file");
	filesInput.addEventListener("change", function (event) 
	{
		var files = event.target.files;
		var file = files[0];
		var reader = new FileReader();
		reader.addEventListener("load", function (event) 
		{
			var textFile = event.target;
			cvm_addItemBatch(textFile.result);
		});
		reader.readAsText(file);
	});
}


function cvm_addItemBatch(fileText)
{

	var fileLines = fileText.split('\n');
	
	//check for edit privileges. Not very secure but back-end does a check as well.
	//This check just prevents a tonne of back-end errors.
	var permissionString = document.getElementById("id_main_RunLevelDisplay").innerHTML;
	if(permissionString != "Edit Mode")
	{
		IMSError("cvm_addItemBatch Error","Missing Permissions for Batch Item Add");
		return;
	}
	
	//verify file format is correct.
	if(fileLines[0].replace(/\s/g,'') != "Name,Quantity,Ordering Threshold,Part Type,Value,Location,Supplier Name,Supplier Part Number,Link,Description,C,E,L".replace(/\s/g,''))
	{
		IMSError("cvm_addItemBatch Error","Input file not in correct format.");
		return;	
	}	
	
	document.getElementById("id_cvm_addBatchProgressBar").style.width = '0%';		
	document.getElementById("id_cvm_addBatchProgressBar").innerHTML = '';
	
	for(i = 1;i<fileLines.length;i++)
	{
		itemDataSplit = fileLines[i].split(',');
		
		if(itemDataSplit.length != 13)
		{
			continue;
		}
		
		//Fill in form data
		//type
		var sel = document.getElementById('id_cvm_type');
		var opts = sel.options;
		
		for(var opt, j = 0; opt = opts[j]; j++) 
		{
			sel.selectedIndex = j;
			if(opt.value == itemDataSplit[3]) 
			{
				break;
			}	
		}

		//Value
		document.getElementById("id_cvm_value").value = itemDataSplit[4];	
		//description
		document.getElementById("id_cvm_desc").value = itemDataSplit[9];
		//Supplier Part Number
		document.getElementById("id_cvm_supplierPN").value = itemDataSplit[7];
		//Supplier Name
		document.getElementById("id_cvm_supplierName").value = itemDataSplit[6];
		//Link
		document.getElementById("id_cvm_link").value = itemDataSplit[8];
		//Quantity
		document.getElementById("id_cvm_qty").value = itemDataSplit[1];
		//Threshold
		document.getElementById("id_cvm_thresh").value = itemDataSplit[2];
		//Location
		document.getElementById("id_cvm_location").value = itemDataSplit[5];
		//Flags
		document.getElementById("id_cvm_flagConsumable").checked = itemDataSplit[10];
		document.getElementById("id_cvm_flagEquipment").checked = itemDataSplit[11];
		
		//Part Number
		//If part number is blank then run autoPN
		if(itemDataSplit[0] == "")
		{
			cdm_autoPN();
		}
		else
			document.getElementById("id_cvm_itemNumber").value = itemDataSplit[0];

		
		//create the item
		createNewItem();

		var progress = (i/fileLines.length)*100;
		document.getElementById("id_cvm_addBatchProgressBar").style.width = progress +'%';			
	}
	document.getElementById("id_cvm_addBatchProgressBar").style.width = '100%';		
	document.getElementById("id_cvm_addBatchProgressBar").innerHTML = 'Done';	
	
	return;

}

