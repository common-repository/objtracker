/**
 * Submit page on change
 *
 * @package objtracker
 * @category File
 * @author    Dan Roller <bsdanroller@gmail.com>
 * @license   GPL-2.0+
 */
function BsOnChange(urlprefix,dropdown1,part2,dropdown2)
{
    var newURL  = urlprefix + dropdown1.value;
	if (part2 != '') {
		newURL = newURL + part2 + dropdown2.value; 
	}
    top.location.href = newURL;
    
    return true;
}
function BsOnChange1(urlprefix,dropdown1)
{
    var newURL  = urlprefix + dropdown1.value;
    top.location.href = newURL;
    
    return true;
}
function BsSetButtonStatus(sender, target) {
	if (sender.value.length >= 5)
    	document.getElementById(target).disabled = false;
    else
        document.getElementById(target).disabled = true;
}

function changeAllCheckBoxes(sender) {
  var gridViewRows = GetParentElementByTagName(sender, "TABLE").rows;
  for (var i = 1; i < gridViewRows.length; ++i) {
      gridViewRows[i].cells[0].childNodes[0].checked = sender.checked;
  }
}

function GetParentElementByTagName(element, tagName) {
  var element = element;
  while (element.tagName != tagName)
      element = element.parentNode;
  return element;
} 

