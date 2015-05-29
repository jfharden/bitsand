<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File inc/inc_js_forms.php
 |     Author: Russell Phillips
 |  Copyright: (C) 2006 - 2015 The Bitsand Project
 |             (http://github.com/PeteAUK/bitsand)
 |
 | Bitsand is free software; you can redistribute it and/or modify it under the
 | terms of the GNU General Public License as published by the Free Software
 | Foundation, either version 3 of the License, or (at your option) any later
 | version.
 |
 | Bitsand is distributed in the hope that it will be useful, but WITHOUT ANY
 | WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 | FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 | details.
 |
 | You should have received a copy of the GNU General Public License along with
 | Bitsand.  If not, see <http://www.gnu.org/licenses/>.
 +---------------------------------------------------------------------------*/
?>
<script type = 'text/javascript'>
<!--
//Clear the value of a text box, if it still has default text. To be called when a user selects the text box
function fnClearValue (sElement, sDefault) {
	 for(i=0; i<=document.forms.length; i++){
		if (document.forms [i].elements [sElement] != null)
		{
			if (document.forms [i].elements [sElement].value == sDefault)
			{
				document.forms [i].elements [sElement].value = '';
				return;
			}
		}
	}
}

// Show or hide medical info text area
function fnShowMedical () {
	if (document.forms [0].elements ['chkMedical'].checked)
		$('#spMedicalInfo').show('slow');
	else
		$('#spMedicalInfo').hide('slow');
}

// Toggle visible state of an element
// sElementID is ID of element to have visible state toggled
function fnShowHide (sElementID) {
	if ($('#' + sElementID).is(":visible"))
		$('#' + sElementID).show('slow');
	else
		$('#' + sElementID).show('hide');
}

/* On IC form, show/hide guild select boxes as appropriate
	iGuild is the number of the guild that has been changed.
		If it is "None" then all following guilds are set to "None" and hidden
		If it is not "None", then the following guild is shown
*/
function fnGuilds (iGuild) {
	//Number of guilds
	iNumGuilds = <?php echo NUM_GUILDS;?>;
	if (document.forms ['ic_form'].elements ['selGuild' + iGuild].selectedIndex == 0) {
		//"None" selected. All subsequent guilds are set to "None" and hidden
		for (i = iGuild + 1; i <= iNumGuilds; i++) {
			document.forms ['ic_form'].elements ['selGuild' + i].selectedIndex = 0;
			document.getElementById ('spnGuild' + i).style.display = 'none';
		}
		return;
	}
	else {
		//Ensure following guild is displayed
		document.getElementById ('spnGuild' + (iGuild + 1)).style.display = 'inline'
	}

	var sGuildName = document.forms ['ic_form'].elements ['selGuild' + iGuild].value;
	for(i = 1; i<= iNumGuilds; i++)
	{
		if (i!= iGuild && sGuildName == document.forms ['ic_form'].elements ['selGuild' + i].value)
		{
			//Move everything up one. Clear the last.
			for (z = i; z < iNumGuilds; z++)
			{
				document.forms ['ic_form'].elements ['selGuild' + z].selectedIndex = document.forms ['ic_form'].elements ['selGuild' + (z + 1)].selectedIndex;
				if (document.forms ['ic_form'].elements ['selGuild' + z].selectedIndex == 0) {document.getElementById ('spnGuild' + (z + 1)).style.display = 'none';}
			}
			document.forms ['ic_form'].elements ['selGuild' + iNumGuilds].selectedIndex = 0;
			document.getElementById ('spnGuild' + iNumGuilds).style.display = 'none';
			return
		}
	}
}

/* On IC form, show/hide OSP select boxes as appropriate
	iOSP is the number of the OSP that has been changed.
		If it is "None" then all following OSPs are set to "None" and hidden
		If it is not "None", then the following OSP is shown
*/
function fnOSPs (iOSP) {
	//Number of OSPs that a single character can have
	iNumOSPs = <?php echo MAX_OSPS;?>;
	if (document.forms ['ic_form'].elements ['selOSP' + iOSP].selectedIndex == 0) {
		//"None" selected. All subsequent OSPs are set to "None" and hidden
		for (i = iOSP + 1; i <= iNumOSPs; i++) {
			document.forms ['ic_form'].elements ['selOSP' + i].selectedIndex = 0;
			document.getElementById ('spnOSP' + i).style.display = 'none';
		}
		return
	}
	else {
		//Ensure following OSP is displayed
		document.getElementById ('spnOSP' + (iOSP + 1)).style.display = 'inline'
	}

	var sOSPName = document.forms ['ic_form'].elements ['selOSP' + iOSP].value;
	for(i = 1; i<= iNumOSPs; i++)
	{
		if (i!= iOSP && sOSPName == document.forms ['ic_form'].elements ['selOSP' + i].value)
		{
			//Move everything up one. Clear the last.
			for (z = i; z < iNumOSPs; z++)
			{
				document.forms ['ic_form'].elements ['selOSP' + z].selectedIndex = document.forms ['ic_form'].elements ['selOSP' + (z + 1)].selectedIndex;
				if (document.forms ['ic_form'].elements ['selOSP' + z].selectedIndex == 0) {document.getElementById ('spnOSP' + (z + 1)).style.display = 'none';}
			}
			document.forms ['ic_form'].elements ['selOSP' + iNumOSPs].selectedIndex = 0;
			document.getElementById ('spnOSP' + iNumOSPs).style.display = 'none';
			return
		}
	}
}

//Calculate character points spent and update span
function fnCalculate () {
	iCost = 0
	//Loop through all elements in the form
	for (i = 0; i < document.forms ['ic_form'].length; i++) {
		//Elements that need to be counted have name prefixed with 'sk'
		if (document.forms ['ic_form'].elements [i].name.slice (0,2) == 'sk') {
			//Only count checkboxes if they are checked
			if (document.forms ['ic_form'].elements [i].checked)
				iCost = iCost + parseInt (document.forms ['ic_form'].elements [i].value)
		}
	}
	//Maximum number of points to spend
	iMax = <?php echo MAX_CHAR_PTS;?>
	//Display current cost
	if (iCost > iMax)
		document.getElementById ('spCost').innerHTML = '<span style = "color: red; font-weight: bold">Points used: ' + iCost + '<\/span>'
	else if (iCost == iMax)
		document.getElementById ('spCost').innerHTML = '<span style = "color: green; font-weight: bold">Points used: ' + iCost + '<\/span>'
	else
		document.getElementById ('spCost').innerHTML = 'Points used: ' + iCost
	//Return iCost so that function can be used for checking as well as updating spCost span
	return iCost
}

//Check for common errors on IC form
function ic_js_check () {
	fMain = document.forms ['ic_form']
	sMsg = ''
	if (fnCalculate () < iMax) {
		if (sMsg != '')
			sMsg += "\n"
		sMsg += "You have used fewer character points than you are allowed.\n"
	}
	if (fMain.elements ['sk10'].checked || fMain.elements ['sk12'].checked || fMain.elements ['sk14'].checked || fMain.elements ['sk16'].checked)
		if (!fMain.elements ['sk21'].checked && !fMain.elements ['sk23'].checked && !fMain.elements ['sk25'].checked && !fMain.elements ['sk27'].checked && !fMain.elements ['sk29'].checked && !fMain.elements ['sk31'].checked)
			sMsg += "You have selected one or more power skills, but no card-ripping skills.\n"
	if (sMsg != '') {
		sMsg += "\nIf this is correct, click OK. If not, click Cancel to go back and make changes"
		return confirm (sMsg)
	}
	//If the script gets to here, then there are no errors. Just return true to submit form
	return true
}

// -->
</script>
