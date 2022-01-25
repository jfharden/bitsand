<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File ic_form.php
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

include ('inc/inc_head_db.php');
include ('inc/inc_forms.php');

//Initialise error message
$sWarn = '';

$db_prefix = DB_PREFIX;

if ($_POST ['btnSubmit'] != '' && CheckReferrer ('ic_form.php')) {
	$sNameWarn = IC_Check ();

		//Character details - check if character exists
		$sql = "SELECT * FROM {$db_prefix}characters WHERE chPlayerID = $PLAYER_ID";
		$result = ba_db_query ($link, $sql);
		//If character does not exist insert a row so that UPDATE query will work
		if (ba_db_num_rows ($result) == 0) {
			$sql = "INSERT INTO {$db_prefix}characters (chPlayerID, chName, chRace, chGroupSel, chGroupText, chFaction, chAncestor, chLocation, chNotes, chOSP) VALUES ($PLAYER_ID, '', '', '', '', '', '', '', '', '')";
			if (!ba_db_query($link, $sql)) {
				$sWarn = "There was a problem updating your IC details";
				LogError ("Error inserting player ID into characters table prior to running UPDATE query.\nPlayer ID: $PLAYER_ID");
			}
		}
		elseif (ba_db_num_rows ($result) > 1)
			LogWarning ("Multiple rows in characters table with player ID $PLAYER_ID");
		if ($_POST['selGroup'] == 'Other (enter name below)')
			$sSelGroupName = '';
		else
			$sSelGroupName = $_POST['selGroup'];
		if ($_POST['selAncestor'] == 'Other (enter name below)')
			$sSelAncestorName = '';
		else
			$sSelAncestorName = $_POST['selAncestor'];
		//Build up UPDATE query


	if ($sNameWarn == '')
	{
		//IC Check passed try to save

		$sql = "UPDATE {$db_prefix}characters SET chName = '" . ba_db_real_escape_string ($link, $_POST ['txtCharName']) . "', " .
			"chPreferredName = '" . ba_db_real_escape_string($link, $_POST ['txtPreferredName']) . "', " .
			"chRace = '" . ba_db_real_escape_string ($link, $_POST ['selRace']) . "', " .
			"chGroupSel = '" . ba_db_real_escape_string ($link, $sSelGroupName) . "', " .
			"chGroupText = '" . ba_db_real_escape_string ($link, $_POST ['txtGroup']) . "', " .
			"chFaction = '" . ba_db_real_escape_string ($link, $_POST ['selFaction']) . "', " .
			"chAncestor = '" . ba_db_real_escape_string ($link, $_POST ['txtAncestor']) . "', " .
			"chAncestorSel = '" . ba_db_real_escape_string ($link, $sSelAncestorName) . "', " .
			"chLocation = '" . ba_db_real_escape_string ($link, $_POST ['selLocation']) . "', " .
			"chNotes = '" . ba_db_real_escape_string ($link, $_POST ['txtNotes']) . "', " .
			"chOSP = '" . ba_db_real_escape_string ($link, $_POST ['txtSpecial']) . "' " .
			"WHERE chPlayerID = $PLAYER_ID";

		//Run query
		if (! ba_db_query ($link, $sql)) {
			$sWarn = "There was a problem updating your IC details";
			LogError ("Error updating character details. Player ID: $PLAYER_ID");
		}

	}

		//Guilds list: Delete existing rows from guildmembers, then run INSERT queries
		$sql = "DELETE FROM {$db_prefix}guildmembers WHERE gmPlayerID = $PLAYER_ID";
		if (! ba_db_query ($link, $sql)) {
			$sWarn = "There was a problem updating your IC details";
			LogError ("Error deleting existing guilds from guildmembers table during update of IC information. Player ID: $PLAYER_ID");
		}
		else {
			//Run INSERT queries
			$iGuildCount = 1;
			$sGuild = "selGuild1";
			$aGuild = array();
			while ($_POST [$sGuild] != ' None') {
				if (!in_array($_POST [$sGuild], $aGuild))
				{
					$sql = "INSERT INTO {$db_prefix}guildmembers (gmPlayerID, gmName) VALUES ($PLAYER_ID, '" .
						ba_db_real_escape_string ($link, $_POST ["selGuild$iGuildCount"]) . "')";
					//Run the INSERT query
					if (! ba_db_query ($link, $sql)) {
						$sWarn = "There was a problem updating your IC details";
						LogError ("Error inserting guilds into guildmembers. Player ID: $PLAYER_ID");
					}
					$aGuild[] = $_POST [$sGuild];
				}
				$sGuild = "selGuild" . ++$iGuildCount;
			}
		}


	$sSkillWarn = IC_Skill_Check();

	if ($sSkillWarn== "")
	{
		//Skills list: Delete existing rows from skillstaken, then run INSERT queries
		$sql = "DELETE FROM {$db_prefix}skillstaken WHERE stPlayerID = $PLAYER_ID";
		if (! ba_db_query ($link, $sql)) {
			$sWarn = "There was a problem updating your IC details";
			LogError ("Error deleting existing skills from skillstaken table during update of IC information. Player ID: $PLAYER_ID");
		}
		else {
			//Run INSERT queries. For each skill, check if box was ticked (or SELECT value was greater than 0). If it was, run INSERT
			for ($i = 1; $i <= 34; $i++) {
				if ($_POST ['sk' . $i] != '') {
					//Skill was selected. Set up INSERT query. Initialise $sql to null string first
					$sql = '';
					if ($_POST ['sk' . $i] > 0)
						$sql = "INSERT INTO {$db_prefix}skillstaken (stPlayerID, stSkillID) VALUES ($PLAYER_ID, $i)";
					if ($sql != '') {
						//Run the INSERT query
						if (! ba_db_query ($link, $sql)) {
							$sWarn = "There was a problem updating your IC details";
							LogError ("Error inserting skills taken. Player ID: $PLAYER_ID");
						}
					}
				}
			}
		}
	}

		//OSPs list: Delete existing rows from ospstaken, then run INSERT queries
	$sql = "DELETE FROM {$db_prefix}ospstaken WHERE otPlayerID = $PLAYER_ID";
		if (! ba_db_query ($link, $sql)) {
			$sWarn = "There was a problem updating your IC details";
			LogError ("Error deleting existing OSPs from ospstaken table during update of IC information. Player ID: $PLAYER_ID");
		}
		else {
			$os = array();
			foreach ($_POST as $key => $value) {
				if (substr ($key, 0, 6) == "hospID") {
					$sql = "INSERT INTO {$db_prefix}ospstaken (otPlayerID, otOspID, otAdditionalText) VALUES ($PLAYER_ID, '" .ba_db_real_escape_string ($link, $value) . "', '".ba_db_real_escape_string ($link,$_POST ["ospAdditionalText{$value}"])."')";
					if ($sql != '' && !in_array($value, $os)) {
						$os[] = $value;
						//Run the INSERT query
						if (! ba_db_query ($link, $sql)) {
							$sWarn = "There was a problem updating the IC details";
							LogError ("Error inserting osps taken (ic_form.php). Player ID: $PLAYER_ID");
						}
					}
				}
			}
		}

		$sNonCriticalWarn = IC_Check_NonCritical();

		$sWarn .= $sNameWarn . $sSkillWarn . $sNonCriticalWarn;
		if ($sWarn != '')
		$sWarn = "The following problems were found:<br>\n" . $sWarn;


		//Do not redirect if there are any warnings (required fields not filled in, etc)
		if ($sWarn == '') {
			//Get user's e-mail address
			$result = ba_db_query ($link, "SELECT plFirstName, plSurname, plEmail FROM {$db_prefix}players WHERE plPlayerID = $PLAYER_ID");
			$row = ba_db_fetch_assoc ($result);
			$email = $row ['plEmail'];
			//Send e-mail
			$sBody = "Your IC details have been entered at " . SYSTEM_NAME . ".\n\n" .
				"Player ID: " . PID_PREFIX . sprintf ('%03s', $PLAYER_ID) . "\n" .
				"OOC Name: " . $row ['plFirstName'] . " " . $row ['plSurname'] . "\n\n" . fnSystemURL ();
			if ($bEmailICChange)
				mail ($email, SYSTEM_NAME . ' - IC details', $sBody, "From:" . SYSTEM_NAME . " <" . EVENT_CONTACT_MAIL . ">");
			//Make up URL & redirect
			$sURL = fnSystemURL () . 'start.php?green=' . urlencode ('Your IC details have been updated');
			header ("Location: $sURL");
		}
	}


include ('inc/inc_head_html.php');
include ('inc/inc_js_forms.php');

//Get existing details if there are any
$sql = "SELECT * FROM {$db_prefix}characters WHERE chPlayerID = $PLAYER_ID";
$result = ba_db_query ($link, $sql);
$row = ba_db_fetch_assoc ($result);
$sNotes = $row ['chNotes'];
$sOSP = $row ['chOSP'];
?>

<h1><?php echo TITLE?> - IC Details</h1>

<?php
if ($sWarn != '')
	echo "<p class = 'warn'>" . $sWarn . "</p>";
?>

<p>
<i>Required fields are <span class = "req_colour">shaded</span></i>. Details will appear on your character card <i>exactly</i> as you type them - if you don't use capitals, capitals won't appear on your character card.
</p>

<form method='POST' action='ic_confirmclear.php'>
<p>Clear character details : <input type = 'submit' value = 'Clear' name = 'btnSubmit' /></p>
</form>

<p>
<form action = "ic_form.php" method = "post" name = "ic_form" onsubmit = "return ic_js_check ()" accept-charset="iso-8859-1">

<table class="characterDisplay"><tr>
<td>Character Name:</td>
<td><input type = "text" name = "txtCharName" class = "required" value = "<?php echo htmlentities (stripslashes ($row ['chName']))?>"></td>
</tr><tr>
<td>Preferred Character Name:</td>
<td class="charactertext"><input type = "text" name = "txtPreferredName" class = 'text' value = "<?php echo htmlentities (stripslashes ($row ['chPreferredName']))?>"><?php HelpLink ('help_preferred_name.php'); ?></td>
</tr><tr>
<td>Race</td>
<td>
<select class = "req_colour" name = "selRace">
<?php
$sValue = $row ['chRace'];
$asOptions = array ('Ancestral', 'Beastkin', 'Daemon', 'Drow', 'Dwarves', 'Elemental', 'Elves', 'Fey', 'Halfling', 'Human', 'Mineral', 'Ologs', 'Plant', 'Umbral', 'Urucks');
foreach ($asOptions as $sOption) {
	echo "<option value = '$sOption'";
	if ($sOption == $sValue)
		echo ' selected';
	echo ">" . htmlentities (stripslashes ($sOption)) . "</option>\n";
}
?>
</select>
</td>
</tr>
<?php
if (LIST_GROUPS_LABEL != '') {
	echo "<tr><td>" . LIST_GROUPS_LABEL . "</td><td>";
	echo "<select name = 'selGroup'>";
	if ($row ['chGroupSel'] != '')
		ListNames ($link, DB_PREFIX . 'groups', 'grName', stripslashes ($row ['chGroupSel']));
	else
		ListNames ($link, DB_PREFIX . 'groups', 'grName', 'Other (enter name below)');
	echo "</select>&nbsp;";
	HelpLink ('help_group.php');
	echo "<br>";
	if ($row ['chGroupText'] != '')
		echo "<input type = 'text' class = 'text' name = 'txtGroup' value = \"" . htmlentities (stripslashes ($row ['chGroupText'])) . "\" onfocus = \"fnClearValue ('txtGroup', 'Enter name here if not in above list')\">";
	else
		echo "<input type = 'text' class = 'text' name = 'txtGroup' value = 'Enter name here if not in above list' onfocus = \"fnClearValue ('txtGroup', 'Enter name here if not in above list')\">";
	echo "</td></tr>";
}
else {
	//Write out hidden fields so that queries don't get broken
	echo "<input type = 'hidden' name = 'selGroup' value = ''>";
	echo "<input type = 'hidden' name = 'txtGroup' value = ''>";
}
?>
<tr>
<td>Faction</td>
<td><select name = "selFaction" class = "req_colour">
<?php
if ($row ['chFaction'] != '')
	ListNames ($link, DB_PREFIX . 'factions', 'faName', htmlentities (stripslashes ($row ['chFaction'])));
else
	ListNames ($link, DB_PREFIX . 'factions', 'faName', DEFAULT_FACTION);
?>
</select>&nbsp;
<?php
HelpLink ('help_no_faction.php');
?>
</td>
</tr><tr>
<td>Ancestor:</td>
<?php
if (ANCESTOR_DROPDOWN)
{
	echo "<td>";
	echo "<select name = 'selAncestor'>";
	if ($row ['chAncestorSel'] != '')
		ListNames ($link, DB_PREFIX . 'ancestors', 'anName', stripslashes ($row ['chAncestorSel']));
	else
		ListNames ($link, DB_PREFIX . 'ancestors', 'anName', 'Other (enter name below)');
	echo "</select>&nbsp; </td></tr><tr><td></td>";
		echo "<td>";
	if ($row ['chAncestor'] != '')
		echo "<input type = 'text' class = 'text' name = 'txtAncestor' value = \"" . htmlentities (stripslashes ($row ['chAncestor'])) . "\" onfocus = \"fnClearValue ('txtAncestor', 'Enter name here if not in above list')\">";
	else
		echo "<input type = 'text' class = 'text' name = 'txtAncestor' value = 'Enter name here if not in above list' onfocus = \"fnClearValue ('txtAncestor', 'Enter name here if not in above list')\">";
	echo "</td></tr>";

}
else
{
echo '<td><input type = "text" class = "text" name = "txtAncestor" value = "'.htmlentities (stripslashes ($row ['chAncestor'])).'"></td></tr>';
echo "<input type = 'hidden' name = 'selAncestor' value = ''>";

}
?>
</tr>
<?php
if (LOCATIONS_LABEL == '')
	//Write a hidden field so that INSERT/UPDATE query does not break
	echo "<input type = 'hidden' name = 'selLocation' value = ''>";
else {
	echo "<tr><td>" . LOCATIONS_LABEL . "</td><td><select name = 'selLocation'>";
	ListNames ($link, DB_PREFIX . 'locations', 'lnName', htmlentities (stripslashes ($row ['chLocation'])));
	echo "</select></td></tr>";
}
?>


</table>

<script type = 'text/javascript'>
function NewfnGuilds (iGuild) {
	//Number of guilds
	iNumGuilds = <?php echo NUM_GUILDS?>;
	if (document.forms [0].elements ['NewselGuild' + iGuild].selectedIndex == 0) {
		//"None" selected. All subsequent guilds are set to "None" and hidden
		for (i = iGuild + 1; i <= iNumGuilds; i++) {
			document.forms [0].elements ['NewselGuild' + i].selectedIndex = 0
			document.getElementById ('spnGuild' + i).style.display = 'none'
		}
	}
	else {
		//Ensure following guild is displayed
		document.getElementById ('spnGuild' + (iGuild + 1)).style.display = 'inline'
	}
}
</script>

<p>
<b>Guilds</b><br>
<?php
//Get character's guilds. Fill an array with the details. The array can then be queried, avoiding repeated DB queries
$result = ba_db_query ($link, "SELECT gmName FROM {$db_prefix}guildmembers WHERE gmPlayerID = $PLAYER_ID ORDER BY gmName");
//$asGuild will hold the guilds
$asGuild = array ();
while ($row = ba_db_fetch_assoc ($result))
	$asGuild [] = $row ['gmName'];

//Get list of system guilds
$asGuildSystem = array();
$result = ba_db_query ($link, "SELECT guName FROM {$db_prefix}guilds ORDER BY guName");
while ($row = ba_db_fetch_assoc ($result))
	$asGuildSystem [] = $row ['guName'];

//Write out the guild select boxes
for ($iGuildCount = 1; $iGuildCount <= NUM_GUILDS; $iGuildCount++) {
	//Find out if character is in this guild
	if (count ($asGuild) >= $iGuildCount)
		//Find out which guild
		$sGuild = $asGuild [$iGuildCount - 1];
	else
		$sGuild = " None";
	//Following IF statement is used to determine if this guild drop-down box is displayed
	if ($iGuildCount > count ($asGuild) + 1)
		$sDisplay = 'none';
	else
		$sDisplay = 'inline';
	echo "<!-- SPAN is used to hide/show SELECTs. JavaScript is used to write SPAN tags so that, if JS is disabled, SELECT is always shown -->\n";
	echo "<script type = 'text/javascript'>\n<!--\n";
	echo "document.write (\"<span id = 'spnGuild$iGuildCount' style = 'display: $sDisplay'>\")\n// -->\n</script>\n";
	echo "Guild:\n";
	echo "<select name = 'selGuild$iGuildCount' onchange = 'fnGuilds ($iGuildCount)'>\n";
	ListNamesFromArray ($asGuildSystem, $sGuild);
	echo "</select><br>\n";
	echo "<script type = 'text/javascript'>\n<!--\ndocument.write ('<\/span>')\n// -->\n</script>\n";
}
?>
</p>

<p>
<table>
<tr><th colspan = "4">Skills</th></tr>
<?php
//Get character's skills. Fill an array with the skills. This array can then be queried, avoiding repeated DB queries
$result = ba_db_query ($link, "SELECT * FROM {$db_prefix}skillstaken WHERE stPlayerID = '$PLAYER_ID'");
$aiSkillID = array ();
while ($row = ba_db_fetch_assoc ($result))
	$aiSkillID [] = $row ['stSkillID'];

//$sTR is either "<tr class = 'highlight'>" or "" - used to switch between two pairs of columns
$sTR = "<tr class = 'highlight'>";
$result = ba_db_query ($link, "SELECT * FROM {$db_prefix}skills ORDER BY skID");
while ($row = ba_db_fetch_assoc ($result)) {
	//Find out if character has this skill
	$has = array_search ($row ['skID'], $aiSkillID);
	echo "$sTR<td>{$row ['skName']} ({$row ['skCost']})</td><td>";
	echo "<input name = 'sk" . $row ['skID'] . "' value = '" . $row ['skCost'] . "' ";
	if ($has !== False)
		//Character has this skill - tick the box
		echo "checked ";
	echo "type = 'checkbox' onclick = 'fnCalculate ()'>";
	echo "</td>";
	if ($sTR == "<tr class = 'highlight'>") {
		$sTR = "";
		echo "\n";
	}
	else {
		$sTR = "<tr class = 'highlight'>";
		echo "</tr>\n";
	}
}
?>
<tr><td colspan = '4'>&nbsp;</td></tr>
<tr><td colspan = '4'><span id = 'spCost'></span></td></tr>
<tr><td colspan = '4'>&nbsp;</td></tr>
<tr><td colspan = '4'><?php echo IC_NOTES_TEXT ?><br>
<textarea rows = "4" cols = "60" name = "txtNotes"><?php echo htmlentities (stripslashes ($sNotes))?></textarea>
</td></tr>
<tr><td colspan = '4'><b>Special items/powers/creatures</b> (you must have valid lammies<br>
in order to use them at the event). Please enter one per line.<br>
<textarea rows = "4" cols = "60" name = "txtSpecial"><?php echo htmlentities (stripslashes ($sOSP))?></textarea>
</td></tr>
</table>

<p>
<b>OSPs</b><br>
<?php
//New and exciting way
//Get character's OSPs. Fill an array with the details. The array can then be queried, avoiding repeated DB queries
$result = ba_db_query ($link, "SELECT * FROM {$db_prefix}ospstaken, {$db_prefix}osps WHERE otPlayerID = $PLAYER_ID AND ospID = otOspID");
//$asOSP will hold the OSP names, $aiOspID will hold the OSP ID numbers
$asOSP = array ();
$aiOspID = array ();
echo "<ul id='osplist'>";
while ($row = ba_db_fetch_assoc ($result)) {
	$asOSP [] = $row ['ospName'];
	$aiOspID [] = $row ['otOspID'];
	echo "<li id=osp".$row['ospID'].">".$row ['ospName'];
	echo "<input type='hidden' name='hospID".$row['ospID']."' value='".$row['ospID']."' />";
	if ($row['ospAllowAdditionalText'] == 1) { echo " (<input type='text' value='".$row ['otAdditionalText']."' name='ospAdditionalText".$row ['ospID']."' />)"; }
	echo " <input type='button' onclick='removeosp(".$row['ospID']."); return false;' value='x' /></li>\n";
}
echo "</ul>";

?>
</p>
<p>
Add Occupational Skill: <input type='text' id='addos' name='addos' />
<script type='text/javascript'>

function removeosp(ospid) {
	$('#osp' + ospid).remove();
}

$().ready(function() {
$("#addos").autocomplete({
			source: "inc/inc_ossearch.php?pid=<?php echo $PLAYER_ID; ?>&",
			minLength: 2,
			focus: function( event, ui ) {
				$( "#addos" ).val( ui.item.label );
				return false;
			},

			select: function( event, ui ) {
				var newosp = "<li id='osp"+ui.item.value+"'>" + ui.item.label;
				newosp += "<input type='hidden' name='hospID"+ui.item.value+"' value='"+ui.item.value+"' />";
				if (ui.item.allowadditional == "1") { newosp += " (<input type='text' value='' name='ospAdditionalText"+ ui.item.value +"' />)"; }
				newosp += " <input type='button' onclick='removeosp("+ui.item.value+"); return false;' value='x' /></li>";
				$("#osplist").append(newosp);
				$("#addos").val('');
				return false;
			}
	});
});
</script>
</p>

<table>
<tr><td class = 'mid'><input type = 'submit' value = 'Submit' name = 'btnSubmit'></td>
<td class = 'mid'>
<script type = 'text/javascript'>
<!--
//Use a button to reset the form, so that fnCalculate can be called *after* the reset
document.write ("<input type = 'button' value = 'Reset' onclick = 'location.reload();'>")
//Do a fnCalculate on load to give initial values
fnCalculate ();
// -->
</script>
<noscript>
<p><input type = 'reset' value = 'Reset'></p>
</noscript>
</td></tr>
</table>

</form>

<?php
include ('inc/inc_foot.php');