/* ***** BEGIN LICENSE BLOCK *****
 * Licensed under Version: MPL 1.1/GPL 2.0/LGPL 2.1
 * Full Terms at http://mozile.mozdev.org/license.html
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 * The Original Code is Playsophy code (www.playsophy.com).
 *
 * The Initial Developer of the Original Code is Playsophy
 * Portions created by the Initial Developer are Copyright (C) 2002-2003
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *	Karl Guertin <grayrest@grayrest.com>
 *
 * ***** END LICENSE BLOCK ***** */

/*******************************************************************************************************
 * Simple, html-based editing toolbar for mozile: it appears once an editable area is 
 * selected: V0.52
 *
 * POST05: 
 * - experiment with "select" events after reselection (cp) to indent/dedent buttons
 * - print: hide this toolbar (call disable)
 * - move bar to bottom or side of screen if trying to edit the top?
 * (see: http://devedge.netscape.com/toolbox/examples/2002/xb/xbPositionableElement/)
 * - do equivalent in XUL
 *   - add as proper toolbar: http://devedge.netscape.com/viewsource/2002/toolbar/
 *******************************************************************************************************/

// image should be in the same directory as this file. This file is in mozile_root_dir. The loader
// sets this constant.
const buttonImgLoc = MOZILE_ROOT_DIR + "/buttons.png";
var preloadthebutton = new Image();
preloadthebutton.src = buttonImgLoc;

var ptbStyles = new Array(
			"border", "solid grey 1px",
			"height", "30px",
			"background-color", "#C0C0C0",
			"border", "solid grey 1px",
			"position", "fixed",
			"z-index", "999", // important to be higher than all else on page
			"-moz-user-select", "none",
			"-moz-user-modify", "read-only",
			"-moz-user-input", "enabled", // means enabled: overrides page default which could be "disabled"
			"top", "0px",
			"margin-left", "20px");

// button definitions
var buttons=new Array();

buttons=[
    [120,140,20,20],//width,height and button width,height in px
     ["Background_Color",0,0,false], //["button name",column,row,show]
    ["Text_Color",1,0,false],
    ["Larger_Text",2,0,false],
    ["Smaller_Text",3,0,false],
    ["Bold",1,1,true],
    ["Italic",0,1,true],
    ["Underline",2,1,true],
    ["Strikethrough",3,1,false],
    ["Subscript",4,1,true],
    ["Superscript",5,1,true],
	["CleanInline",0,6,true],
    ["Link",0,5,true],
    ["Unlink",1,5,true],
    ["Create_Table",2,5,true],
    ["Outdent",1,2,true],
    ["Indent",0,2,true],
    ["Unordered_List",3,2,true],
    ["Ordered_List",2,2,true],
    ["Left",0,3,false],
    ["Center",1,3,false],
    ["Right",2,3,false],
    ["Justify",3,3,false],
    ["New_Page",0,6,false],
    ["Copy",0,4,true],
    ["Cut",1,4,true],
    ["Paste",2,4,true],
    ["Image",3,5,true],
    ["HR",4,5,true],
    ["Save",1,6,false],
    ["Raw_HTML",2,6,false],
    ["Undo",3,6,false],
    ["Redo",4,6,false]
];

var buttonStyles = new Array(
	"height", "20px", 
	"width", "20px", 
	"border", "solid 1px #C0C0C0",
	"background-color", "#C0C0C0",
	"-moz-user-modify", "read-only",
	"-moz-user-input", "disabled",
	"-moz-user-select", "none",
	"background-image","url("+buttonImgLoc+")");

var formatBlock = new Array("formatBlock",
			    "Format", "Format", 
			    "P", "Paragraph <P>",
			    "H1", "Heading 1 <H1>", 
			    "H2", "Heading 2 <H2>", 
			    "H3", "Heading 3 <H3>", 
			    "H4", "Heading 4 <H4>", 
			    "H5", "Heading 5 <H5>", 
			    "H6", "Heading 6 <H6>", 
			    "BLOCKQUOTE", "Blockquote",
			    "Address", "Address <ADDR>",
			    "PRE", "Pre <PRE>",
			    "Unformatted", "Unformatted");

var fontFamily = new Array("font-family",
			   "Font", "Font",
			   "serif", "Serif",
			   "Arial", "Arial",
		 	   "Courier", "Courier",
			   "Times", "Times");

var fontSize = new Array("font-size",
			 "Size", "Size",
			 "xx-small", "1",
			 "x-small", "2",
			 "small", "3",
			 "medium", "4",
			 "large", "5",
			 "x-large", "6",
			 "xx-large", "7");

var color = new Array("color",
		      "color", "Color",
		      "rgb(0, 0, 0)", "black",
		      "rgb(255, 255, 255)", "white",
		      "rgb(255, 0, 0)", "red",
		      "rgb(0, 255, 0)", "green",
		      "rgb(0, 0, 255)", "blue",
		      "rgb(255, 255, 0)", "yellow",
		      "rgb(0, 255, 255)", "cyan",
		      "rgb(255, 0, 255)", "magenta");

var backgroundColor = new Array("background-color", // issue: default is "transparent"
				"background-color", "Background",
				"rgb(0, 0, 0)", "black",
				"rgb(255, 255, 255)", "white",
				"rgb(255, 0, 0)", "red",
				"rgb(0, 255, 0)", "green",
				"rgb(0, 0, 255)", "blue",
				"rgb(255, 255, 0)", "yellow",
				"rgb(0, 255, 255)", "cyan",
				"rgb(255, 0, 255)", "magenta");

//var selectors = new Array(formatBlock, fontFamily, fontSize, color, backgroundColor);
var selectors = new Array(formatBlock);

// Always create the toolbar but don't activate it or enable it.
var ptb = new PTB();

function PTB()
{
	// make bar as a table 
	var ptb = document.createElement("table");
	ptb.id = "playtoolbar";

	for(var i=0; i<ptbStyles.length; i=i+2)
		ptb.style.setProperty(ptbStyles[i], ptbStyles[i+1], "");

	// add tbody to table (required to avoid a bgcolor/background-color sync bug http://bugzilla.mozilla.org/show_bug.cgi?id=205705)
	var tbTB = document.createElement("tbody");
	ptb.appendChild(tbTB);

	// add a row of buttons and selectors
	var tbTR = document.createElement("tr");
	tbTB.appendChild(tbTR);
	for(var i=0; i<selectors.length; i++)
	{
		tbTR.appendChild(__createSelector(selectors[i]));
	}
	for(var i=1; i<buttons.length; i++)
	{
		if(buttons[i][3])			
			tbTR.appendChild(__createButton(i));
	}

	ptb.style.display = "table";
	ptb.style.display = "none"; // this sequence is needed to get over a Firebird bug
	ptb.style.display = "table";
	
	// now record states of this object
	this.__ptbActive = false;
	this.__ptbEnabled = false;
	this.__ptb = ptb;
}

/*
 * Activate the toolbar if need be; handle enable/disable from then on
 *
 * POST04: what if user scrolls or tabs out of an editable area?
 * - may need to make disable optional: blogs may not want it to work that way ...
 */
document.addEventListener("click", onclickPTBEnable, false);

function onclickPTBEnable(event)
{
	var eventTarget = event.target.parentElement;

	// if not user modifiable then disable toolbar
	if(!eventTarget.userModifiable)
	{
		if(ptb.containsTarget(eventTarget))
			return;

		ptb.ptbDisable();
		return;
	}

	// if user modifiable then enable the toolbar
	ptb.ptbEnable();		
}

PTB.prototype.ptbActivate = function()
{
	if(this.__ptbActive)
		return;

	document.body.appendChild(this.__ptb); // POST04: change to be XML ok

	this.__ptbActive = true;
}

PTB.prototype.ptbDeactivate = function()
{
	if(!this.__ptbActive)
		return;
	
	this.__ptb.parentNode.removeChild(this.__ptb);
	this.__ptbActive = false;
}

PTB.prototype.ptbEnable = function()
{
	if(this.__ptbEnabled)
		return;

	if(!this.__ptbActive)
		this.ptbActivate();

	this.__ptb.style.setProperty("display", 'table', '');
	this.__ptbEnabled = true;	
}	

PTB.prototype.ptbDisable = function()
{
	if(!this.__ptbActive || !this.ptbEnable)
		return; 

	this.__ptb.style.setProperty("display", 'none', '');
	this.__ptbEnabled = false;
}

PTB.prototype.containsTarget = function(target)
{
	var nodeToTest = target;

	do
	{
		if(nodeToTest == this.__ptb)
			break;
		nodeToTest = nodeToTest.parentNode;

	} while(nodeToTest);

	if(nodeToTest)
		return true;

	return false;
}

// turn into method of PTB as "addSelector"
function __createSelector(values)
{
	var selectortd = document.createElement("td");
	var selector = document.createElement("select");
	selector.setAttribute("unselectable", "on"); // POST04: seems to have some problem with this: apply selection then try up/down arrows!
	selector.setAttribute("id", values[0]);
	selector.style.setProperty("margin-bottom", "5px", "");

	selector.onchange = ptbSelect;				
	for(var i=1; i<values.length; i++)
	{
		var option = document.createElement("option");
		option.setAttribute("value", values[i]);
		i++;
		option.appendChild(document.createTextNode(values[i]));
		selector.appendChild(option);
	}	
	selectortd.appendChild(selector);
	return selectortd;
}

function __createButton(buttonindex)
{
	var mybutton = buttons[buttonindex];
	var canvasdims = [buttons[0][0],buttons[0][1]];
	var icondims   = [buttons[0][2],buttons[0][3]];
	var clipoffset = 
	    [icondims[0]*mybutton[1], // left
	     icondims[1]*mybutton[2]]; //top

 	var button = document.createElement("td");
 	var buttonDIV = document.createElement("div");
 	button.appendChild(buttonDIV);
 	buttonDIV.setAttribute("class", "button");
	buttonDIV.setAttribute("id", mybutton[0]);
 	for(var i=0; i<buttonStyles.length; i=i+2)
 		buttonDIV.style.setProperty(buttonStyles[i], buttonStyles[i+1], "");
	buttonDIV.style.setProperty("background-position","-"+clipoffset[0]+"px -"+clipoffset[1]+"px","");

      	buttonDIV.onmouseover = ptbmouseover;
      	buttonDIV.onmouseout = ptbmouseout;
      	buttonDIV.onmousedown = ptbmousedown;
      	buttonDIV.onmouseup = ptbmouseup;
      	buttonDIV.onclick = ptbbuttonclick;

  	return button;
}

function ptbmouseover()
{
	this.style.border="outset 1px";
}

function ptbmousedown(event)
{
	this.style.border="inset 1px";
  	event.preventDefault();
}

function ptbmouseup()
{
	this.style.border="outset 1px";
}

function ptbmouseout()
{
	this.style.border="solid 1px #C0C0C0";
}

/*
 * take care of button operations - make one per button?
 */
function ptbbuttonclick()
{
	switch(this.id)
	{
		case "Unlink":
			window.getSelection().clearTextLinks();
			break;

		case 'Link': 
			if(window.getSelection().isCollapsed) // must have a selection or don't prompt
				return;
 	    		var href = prompt("Enter a URL:", "");
			if(href == null) // null href means prompt canceled - BUG FIX FROM Karl Guertin
				return;
			if(href != "") 
				window.getSelection().linkText(href);
			else
				window.getSelection().clearTextLinks();
			break;

		case "Save": 
			mozileSave();
			this.style.border="solid 1px #C0C0C0"; // POST05: generalize this in OO toolbar
			break;

		case 'Bold':
			//window.getSelection().toggleTextStyle('font-weight', 'bold', '400');
			window.getSelection().toggleTextClass("strong","http://www.w3.org/1999/xhtml");
			break;
		case 'Italic':
			window.getSelection().toggleTextClass("em",XHTMLNS);
			break;
		case 'Underline':
			window.getSelection().toggleTextClass("u",XHTMLNS);
			break;
		case 'Superscript':
			window.getSelection().toggleTextClass("sup",XHTMLNS);
			break;
		case 'Subscript':
			window.getSelection().toggleTextClass("sub",XHTMLNS);
			break;
			

			case 'Ordered_List':
			window.getSelection().toggleListLines("OL", "UL");
			break;
		case 'Unordered_List':
			window.getSelection().toggleListLines("UL", "OL");
			break;
		case 'CleanInline':
			bxe_CleanInline();
			break;
		case 'Indent':
			window.getSelection().indentLines();
			break;
		case 'Outdent':
			window.getSelection().outdentLines();
			break;
		case 'Left':
			window.getSelection().styleLines("text-align", "left");
			break;
		case 'Right':
			window.getSelection().styleLines("text-align", "right");
			break;
		case 'Center':
			window.getSelection().styleLines("text-align", "center");
			break;
		case 'Image': // need to replace with native file selection dialog
 	    		var imgref = prompt("Enter the image url or file name:", "");
			if(imgref == null) // null href means prompt canceled
				return;
			if(imgref == "") 
				return; // ok with no name filled in
			var img = documentCreateXHTMLElement("img");
			img.src = imgref; // any way to tell if it is valid?
			window.getSelection().insertNode(img);
			break;
		case 'HR':
			var hr = documentCreateXHTMLElement("hr");
			window.getSelection().insertNode(hr);
			break;
		case 'Create_Table':
			var rowno = prompt("number of rows");
			var colno = prompt("number of columns");
			var te = documentCreateXHTMLTable(rowno, colno);
			if(!te)
				alert("Can't create table: invalid data");
			else
				window.getSelection().insertNode(te);
			break;
		case 'Copy':
			window.getSelection().copy();
			break;
		case 'Cut':
			window.getSelection().cut();
			break;
		case 'Paste':
			window.getSelection().paste();
			break;
		case 'Raw_HTML':
			bx_toggleSource(window.getSelection().getEditableRange().top);
			break;
		default:
		alert(this.id);
		
	}
}

/*
 * take care of selections
 */
function ptbSelect()
{
	var cursel = this.selectedIndex;

  	if (cursel != 0) 
	{
		var selectName = this.id;
 		var selectValue = this.options[cursel].value;

		if(selectName == "formatBlock")
		{
			if(selectValue == "Unformatted")
				window.getSelection().removeLinesContainer();
			else
				window.getSelection().changeLinesContainer(selectValue);
		}
		// others all apply style
		else
			window.getSelection().styleText(selectName, selectValue);

		this.options[0].selected = true; // reset to first option	

		document.getElementById(selectName).blur(); // ensures focus goes back to editable area
	}			
}