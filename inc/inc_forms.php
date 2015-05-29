<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File inc/inc_forms.php
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

// inc_forms.php - form-related functions

//Check for common errors on IC form
function IC_Check () {
	$sReturn = '';
	//asElements is an array of element names to check
	$asElements = array ('txtCharName', 'selRace');
	//asElementNames is an array of descriptive names used in warning message
	$asElementNames = array ('Character Name not entered', 'Race not entered');
	//Initialise counter
	$iCount = 0;
	foreach ($asElements as $sElement) {
		$sValue = $_POST [$sElement];
		if ($sValue == '')
			$sReturn .= $asElementNames [$iCount] . "<br>\n";
		$iCount++;
	}

	return $sReturn;
}


function IC_Skill_Check()
{
	$sReturn = '';

	//Check if player spent too many points
	if ($_POST ['chkNPC'] == '' && Points () > MAX_CHAR_PTS)
		$sReturn .= "You cannot spend more than " . MAX_CHAR_PTS . " character points<br>\n";
	elseif (Points () > MAX_NPC_PTS)
		$sReturn .= "You cannot spend more than " . MAX_NPC_PTS . " character points<br>\n";
	//If Ritual Magic 1, 2 or 3 was selected, check they have healing, incantation or spellcasting
	//sk33, sk2, sk4 are Ritual Magic 1, 2 & 3
	if ((int) $_POST ['sk33'] > 0 || (int) $_POST ['sk2'] > 0 || (int) $_POST ['sk4'] > 0) {
		//sk21, sk23, sk29, sk31, sk25, 2k27 are Healing, Incanting, Spellcasting 1/2. If they add up to zero, raise error
		if ((int) $_POST ['sk21'] + (int) $_POST ['sk23'] + (int) $_POST ['sk29'] + (int) $_POST ['sk31'] + (int) $_POST ['sk25'] + (int) $_POST ['sk27'] == 0)
			$sReturn .= "You cannot take Ritual Magic unless you also take Healing, Spellcasting or Incantation (of any level)<br>\n";
	}
	//Cannot have more than one magic skill at level 2
	$iMagicSkills = 0;
	if ((int) $_POST ['sk23'] > 0)
		$iMagicSkills++;
	if ((int) $_POST ['sk27'] > 0)
		$iMagicSkills++;
	if ((int) $_POST ['sk31'] > 0)
		$iMagicSkills++;
	if ($iMagicSkills > 1)
		$sReturn .= "You cannot take more than one magic skill (healing, incanting, spellcasting) at level 2 or above.<br>\n";
	//Cannot have a magic skill at level 2 and the same type at level 1
	if ((int) $_POST ['sk23'] > 0 && (int) $_POST ['sk21'] > 0)
		$sReturn .= "You cannot take Healing at level 2 and at level 1.<br>\n";
	if ((int) $_POST ['sk27'] > 0 && (int) $_POST ['sk25'] > 0)
		$sReturn .= "You cannot take Spellcasting at level 2 and at level 1.<br>\n";
	if ((int) $_POST ['sk31'] > 0 && (int) $_POST ['sk29'] > 0)
		$sReturn .= "You cannot take Incantation at level 2 and at level 1.<br>\n";
	//Cannot have more than one power skill
	$iPowerSkills = 0;
	if ((int) $_POST ['sk10'] > 0)
		$iPowerSkills++;
	if ((int) $_POST ['sk12'] > 0)
		$iPowerSkills++;
	if ((int) $_POST ['sk14'] > 0)
		$iPowerSkills++;
	if ((int) $_POST ['sk16'] > 0)
		$iPowerSkills++;
	if ($iPowerSkills > 1)
		$sReturn .= "You cannot take more than one power skill.<br>\n";
	//Cannot have both body dev 1 & body dev 2
	if ((int) $_POST ['sk11'] > 0 && (int) $_POST ['sk13'] > 0)
		$sReturn .= "You cannot take both body development 1 <b>and</b> body development 2.<br>\n";

	return $sReturn;
}

function IC_Check_NonCritical()
{
	$sReturn = '';

	//Any failures here will still allow the character details to be saved.
	if ($_POST ['chkNPC'] != '' && $_POST ['txtNotes'] == '')
		$sReturn .= "Details of your NPC status (in the &quot;" . IC_NOTES_TEXT . "&quot; box)<br>\n";
	if ($_POST ['selFaction'] != DEFAULT_FACTION && $_POST ['txtNotes'] == '' && DEFAULT_FACTION != '' && NON_DEFAULT_FACTION_NOTES)
		$sReturn .= "You must enter the name of the person that invited you, or your reason for attending (in the Notes box)<br>\n";

	return $sReturn;

}

//Work out how many character points were spent
function Points () {
	$iPoints = 0;
	for ($i = 1; $i <= 34; $i++) {
		if ($_POST ['sk' . $i] != '') {
			//Skill was selected
			$iPoints = $iPoints + (int) $_POST ['sk' . $i];
		}
	}
	return $iPoints;
}

//Check for common errors on OOC form
function OOC_Check () {
	$sReturn = '';
	//$asElements is an array of element names to check
	//$asElementNames is an array of descriptive names used in warning message
	$asElements = array ('txtFirstName', 'txtSurname', 'txtAddress1', 'txtEmergencyName', 'txtEmergencyNumber', 'txtEmergencyRelationship', 'txtCarRegistration', 'selDiet');
	$asElementNames = array ('First name', 'Surname', 'Address', 'Emergency contact name', 'Emergency contact number', 'Relationship to emergency contact', 'Car registration (Enter &quot;N/A&quot; if you do not drive)', 'Dietary requirements');
	//Initialise counter
	$iCount = 0;
	foreach ($asElements as $sElement) {
		$sValue = $_POST [$sElement];
		if ($sValue == '' || $sValue == 'Select one' || $sValue == '("On site" is OK)' || $sValue == 'Enter NA if you do not drive')
			$sReturn .= $asElementNames [$iCount] . "<br>\n";
		$iCount++;
	}
	if ($sReturn != '')
		$sReturn = "You must provide the following:<br>\n" . $sReturn;
	return $sReturn;
}

function setBoolValue ($sPassedValue)
	{
		if ($sPassedValue == 'on') {return '1';}
		else {return '0';}
	}

function setBlankToNull($sPassedValue)
	{
		if (strlen($sPassedValue) == 0) {return 'NULL';}
		else {return $sPassedValue;}
	}

function DatePickerFullDate($sID, $dDefaultDate, $iYearsToShow, $iYearsInFuture)
{
	if ($dDefaultDate != '')
	{
		$iDateYear = substr ($dDefaultDate, 0, 4);
		$iMonth = substr ($dDefaultDate, 5, 2);
		$iDate = substr ($dDefaultDate, 8, 2);
		$iYear = getdate ();
		$iYear = $iDateYear - $iYear ['year'];
		DatePicker ($sID, $iYear, (int)$iMonth, (int)$iDate, $iYearsToShow,$iYearsInFuture);
	}
	else
	{
		DatePicker ($sID,0,1,1,$iYearsToShow,$iYearsInFuture);
	}
}

/*
Function to display date picker drop-downs
$sID: ID string. Name of each control is 'sel' + $sID + 'Date|Month|Year'
$iDefaultYear: Relative year to be default (eg -20 makes 20 years ago the default)
*/

function DatePicker ($sID, $iDefaultYear, $iDefaultMonth = 1, $iDefaultDay = 1, $iYearsToShow = 100, $iYearsInFuture = 0) {
	$asMonths = array ("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
	//Days drop-down. sprintf () is used to ensure that values are 2-digit numbers
	echo "<select name = 'sel" . $sID . "Date'>\n";
	for ($i = 1; $i <= 31; $i++)
		if ($i == $iDefaultDay)
			echo "<option selected value = '" . sprintf ('%02s', $i) . "'>$i</option>\n";
		else
			echo "<option value = '" . sprintf ('%02s', $i) . "'>$i</option>\n";
	echo "</select>\n";

	//Months drop-down. sprintf () is used to ensure that values are 2-digit numbers
	$iMon = 1;
	echo "<select name = 'sel" . $sID . "Month'>\n";
	foreach ($asMonths as $sMonth)
	{
		echo $iDefaultMonth;
		if ($iMon == $iDefaultMonth)
			echo "<option selected value = '" . sprintf ('%02s', $iMon++) . "'>$sMonth</option>\n";
		else
			echo "<option value = '" . sprintf ('%02s', $iMon++) . "'>$sMonth</option>\n";
	}
	echo "</select>\n";

	//Years drop-down: display iYearsToShow years
	$iYear = getdate ();
	$iYear = $iYear ['year'] + $iYearsInFuture;
	$i = $iYear - $iYearsToShow;
	$iDefault = $iYear + $iDefaultYear - $iYearsInFuture;

	echo "<select name = 'sel" . $sID . "Year'>\n";
	for ($i; $i <= $iYear; $i++)
		if ($i == $iDefault)
			echo "<option selected>$i</option>\n";
		else
			echo "<option>$i</option>\n";
	echo "</select>\n";
}

/*
Function to display drop-down list of names (factions; groups, etc). Only a list of <OPTION> tags is returned.
This allows extra <OPTION>s to be appended if required.
$link: database link
$sTable: table to query
$sColumn: column of names
$sDefault: name to be selected by default. Defaults to none
$iDefaultNum: number of option to be selected by default. Defaults to 0 (none)
*/
function ListNames ($link, $sTable, $sColumn, $sDefault = '', $iDefaultNum = 0) {
	//Initialise $iOptionNum
	$iOptionNum = 1;
	//Query database to get group names
	$result = ba_db_query ($link, "SELECT $sColumn FROM $sTable ORDER BY $sColumn");
	while ($row = ba_db_fetch_row ($result)) {
		//Note that " is used instead of ' in case there is a ' in any of the names
		echo '<option value = "' . htmlentities (stripslashes ($row [0])) . '"';
		if ($row [0] == $sDefault || $iOptionNum++ == $iDefaultNum)
			echo ' selected';
		echo ">" . htmlentities (stripslashes ($row [0])) . "</option>\n";
	}
}

/*
Function to display drop-down list of names (factions; groups, etc). Only a list of <OPTION> tags is returned.
This allows extra <OPTION>s to be appended if required.
$aNames: array of names
$sDefault: name to be selected by default. Defaults to none
$iDefaultNum: number of option to be selected by default. Defaults to 0 (none)
*/
function ListNamesFromArray ($aNames, $sDefault = '', $iDefaultNum = 0) {
	//Initialise $iOptionNum
	$iOptionNum = 1;
	//Query database to get group names
	foreach($aNames as $sName) {
		//Note that " is used instead of ' in case there is a ' in any of the names
		echo '<option value = "' . htmlentities (stripslashes ($sName)) . '"';
		if ($sName == $sDefault || $iOptionNum++ == $iDefaultNum)
			echo ' selected';
		echo ">" . htmlentities (stripslashes ($sName)) . "</option>\n";
	}
}