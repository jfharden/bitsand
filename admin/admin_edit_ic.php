<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File admin/admin_edit_ic.php
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

include ('../inc/inc_head_db.php');
include ('../inc/inc_admin.php');
include ('../inc/inc_forms.php');

//Get player ID of player to be edited
$admin_player_id = (int) $_GET ['pid'];
//Initialise error message
$sWarn = '';

$db_prefix = DB_PREFIX;

if ($_POST ['btnSubmit'] != '' && CheckReferrer ('admin_edit_ic.php')) {
	$sDataWarn = IC_Check ();

	if ($sDataWarn != '') {
		//Append warning to the IC Notes field if not already added
		$sNotes = ba_db_real_escape_string ($link, $_POST ['txtNotes']);
		if (stripos ($sNotes, "Illegal set of skills entered") === False)
			$sNotes .= "\nIllegal set of skills entered";
	}
	else
		$sNotes = ba_db_real_escape_string ($link, $_POST ['txtNotes']);

	//Character details - check if character exists
	$sql = "SELECT * FROM {$db_prefix}characters WHERE chPlayerID = $admin_player_id";
	$result = ba_db_query ($link, $sql);
	//If character does not exist insert a row so that UPDATE query will work
	if (ba_db_num_rows ($result) == 0) {
		$sql = "INSERT INTO {$db_prefix}characters (chPlayerID) VALUES ($admin_player_id)";
		if (! ba_db_query ($link, $sql)) {
			$sWarn = "There was a problem updating the IC details";
			LogError ("Error inserting player ID into characters table prior to running UPDATE query (admin_edit_ic.php). " .
				"Player ID: $admin_player_id");
		}
	}
	elseif (ba_db_num_rows ($result) > 1)
		LogWarning ("Multiple rows in characters table with player ID (admin_edit_ic.php) $admin_player_id");

	if ($_POST['selGroup'] == 'Other (enter name below)')
		$sSelGroupName = '';
	else
		$sSelGroupName = $_POST['selGroup'];
	if ($_POST['selAncestor'] == 'Other (enter name below)')
		$sSelAncestorName = '';
	else
		$sSelAncestorName = $_POST['selAncestor'];
	//Build up UPDATE query
	$sql = "UPDATE {$db_prefix}characters SET chName = '" . ba_db_real_escape_string ($link, $_POST ['txtCharName']) . "', " .
		"chPreferredName = '" . ba_db_real_escape_string($link, $_POST ['txtPreferredName']) . "', " .
		"chRace = '" . ba_db_real_escape_string ($link, $_POST ['selRace']) . "', " .
		"chGroupSel = '" . ba_db_real_escape_string ($link, $sSelGroupName) . "', " .
		"chGroupText = '" . ba_db_real_escape_string ($link, $_POST ['txtGroup']) . "', " .
		"chFaction = '" . ba_db_real_escape_string ($link, $_POST ['selFaction']) . "', " .
		"chAncestor = '" . ba_db_real_escape_string ($link, $_POST ['txtAncestor']) . "', " .
		"chAncestorSel = '" . ba_db_real_escape_string ($link, $sSelAncestorName) . "', " .
		"chLocation = '" . ba_db_real_escape_string ($link, $_POST ['selLocation']) . "', " .
		"chNotes = '" . $sNotes . "', " .
		"chOSP = '" . ba_db_real_escape_string ($link, $_POST ['txtOSP']) . "' " .
		"WHERE chPlayerID = $admin_player_id";
	//Run query
	if (! ba_db_query ($link, $sql)) {
		$sWarn = "There was a problem updating the IC details";
		LogError ("Error updating character details (admin_edit_ic.php). Player ID: $admin_player_id");
	}

	//Guilds list: Delete existing rows from guildmembers, then run INSERT queries
	$sql = "DELETE FROM {$db_prefix}guildmembers WHERE gmPlayerID = $admin_player_id";
	if (! ba_db_query ($link, $sql)) {
		$sWarn = "There was a problem updating the IC details";
		LogError ("Error deleting existing guilds from guildmembers table during update of IC information. Player ID: $admin_player_id");
	}
	else {
		//Run INSERT queries
		$iGuildCount = 1;
		$sGuild = "selGuild1";
		$aGuild = array();
		while ($_POST [$sGuild] != ' None') {
			if (!in_array($_POST [$sGuild], $aGuild))
			{
				$sql = "INSERT INTO {$db_prefix}guildmembers (gmPlayerID, gmName) VALUES ($admin_player_id, '" .
					ba_db_real_escape_string ($link, $_POST ["selGuild$iGuildCount"]) . "')";
				//Run the INSERT query
				if (! ba_db_query ($link, $sql)) {
					$sWarn = "There was a problem updating the IC details";
					LogError ("Error inserting guilds into guildmembers. Player ID: $admin_player_id");
				}
				$aGuild[] = $_POST [$sGuild];
			}
			$sGuild = "selGuild" . ++$iGuildCount;
		}
	}

	//Skills list: Delete existing rows from skillstaken, then run INSERT queries
	$sql = "DELETE FROM {$db_prefix}skillstaken WHERE stPlayerID = $admin_player_id";
	if (! ba_db_query ($link, $sql)) {
		$sWarn = "There was a problem updating the IC details";
		LogError ("Error deleting existing skills from skillstaken table during update of IC information (admin_edit_ic.php). " .
			"Player ID: $admin_player_id");
	}
	else {
		//Run INSERT queries. For each skill, check if box was ticked (or SELECT value was greater than 0). If it was, run INSERT
		for ($i = 1; $i <= 34; $i++) {
			if ($_POST ['sk' . $i] != '') {
				//Skill was selected. Set up and run INSERT query
				$sql = "INSERT INTO {$db_prefix}skillstaken (stPlayerID, stSkillID) VALUES ($admin_player_id, $i)";
				if ($sql != '') {
					//Run the INSERT query
					if (! ba_db_query ($link, $sql)) {
						$sWarn = "There was a problem updating the IC details";
						LogError ("Error inserting skills taken (admin_edit_ic.php). Player ID: $admin_player_id");
					}
				}
			}
		}
	}

	//OSPs list: Delete existing rows from ospstaken, then run INSERT queries
	$sql = "DELETE FROM {$db_prefix}ospstaken WHERE otPlayerID = $admin_player_id";
	if (! ba_db_query ($link, $sql)) {
		$sWarn = "There was a problem updating the IC details";
		LogError ("Error deleting existing OSPs from ospstaken table during update of IC information. Player ID: $admin_player_id");
	}
	else {
		$os = array();
		foreach ($_POST as $key => $value) {
			if (substr ($key, 0, 6) == "hospID") {
				$sql = "INSERT INTO {$db_prefix}ospstaken (otPlayerID, otOspID, otAdditionalText) VALUES ($admin_player_id, '" .ba_db_real_escape_string ($link, $value) . "', '".ba_db_real_escape_string ($link,$_POST ["ospAdditionalText{$value}"])."')";
				if ($sql != '' && !in_array($value, $os)) {
					$os[] = $value;
					//Run the INSERT query
					if (! ba_db_query ($link, $sql)) {
						$sWarn = "There was a problem updating the IC details";
						LogError ("Error inserting osps taken (admin_edit_ic.php). Player ID: $admin_player_id");
					}
				}
			}
		}
	}


	//Do not redirect if there are any database warnings
	if ($sWarn == '') {
		//Make up URL & redirect. Any warnings about data are encoded into URL for display on next page
		if ($sDataWarn != '')
			$sURL = fnSystemURL () . "admin_viewdetails.php?pid=$admin_player_id&warn=" . urlencode ("IC details updated<br>" . $sDataWarn);
		else
			$sURL = fnSystemURL () . "admin_viewdetails.php?pid=$admin_player_id&green=" . urlencode ("IC details updated");
		header ("Location: $sURL");
	}
}

include ('../inc/inc_head_html.php');
include ('../inc/inc_js_forms.php');

//Get existing details if there are any
$sql = "SELECT * FROM {$db_prefix}characters WHERE chPlayerID = $admin_player_id";
$result = ba_db_query ($link, $sql);
$row = ba_db_fetch_assoc ($result);
$sNotes = $row ['chNotes'];
$sOSP = $row ['chOSP'];
// Get OOC name
$sql = "SELECT plFirstName, plSurname FROM {$db_prefix}players WHERE plPlayerID = $admin_player_id";
$nameresult = ba_db_query ($link, $sql);
$namerow = ba_db_fetch_assoc ($nameresult);
?>

<h1><?php echo TITLE?> - IC Details</h1>

<?php
if ($sWarn != '')
	echo "<p class = 'warn'>" . $sWarn . "</p>";
?>

<p>
<i>Required fields are <span class = "req_colour">shaded</span></i>. Details will appear on the character card <i>exactly</i> as typed.<br>
</p>

<div class = 'warn'>
<?php
echo "Note that you are editing the IC details for " .
	htmlentities ($namerow ['plFirstName']) . " " . htmlentities ($namerow ['plSurname']) .
	" (" . PID_PREFIX . sprintf ('%03s', $admin_player_id) . ")";
?>
<br>
It is possible to select illegal combinations of skills. BE CAREFUL!
</div>

<p>
<form action = "admin_edit_ic.php?pid=<?php echo $admin_player_id?>" method = "post" name ='ic_form' onsubmit = "return ic_js_check ()" accept-charset="iso-8859-1">

<table class='characterDisplay'><tr>
<td>Character Name:</td>
<td><input type = "text" name = "txtCharName" class = "required" value = "<?php echo htmlentities (stripslashes ($row ['chName']))?>"></td>
</tr><tr>
<td>Preferred Character Name:</td>
<td><input type = "text" name = "txtPreferredName" class = 'text' value = "<?php echo htmlentities (stripslashes ($row ['chPreferredName']))?>"></td>
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
	echo "</select><br>";
	if ($row ['chGroupText'] != '')
		echo "<input type = 'text' class = 'text' name = 'txtGroup' value = '" . htmlentities (stripslashes ($row ['chGroupText'])) . "'>";
	else
		echo "<input type = 'text' class = 'text' name = 'txtGroup' value = 'Enter name here if not in above list'>";
	echo "</td></tr>";
}
else {
	//Write out hidden fields so that queries don't get broken
	echo "<input type = 'hidden' name = 'selGroup' value = ''>";
	echo "<input type = 'hidden' name = 'txtGroup' value = ''>";
}
?>
<tr>
<td>Faction:</td>
<td><select name = "selFaction" class = "req_colour">
<?php
if ($row ['chFaction'] != '')
	ListNames ($link, DB_PREFIX . 'factions', 'faName', htmlentities (stripslashes ($row ['chFaction'])));
else
	ListNames ($link, DB_PREFIX . 'factions', 'faName', DEFAULT_FACTION);
?>
</select>
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
		echo "<input type = 'text' class = 'text' name = 'txtAncestor' value = '" . htmlentities (stripslashes ($row ['chAncestor'])) . "'>";
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
</p>

<p>
<b>Guilds</b><br>
<?php
//Get character's guilds. Fill an array with the details. The array can then be queried, avoiding repeated DB queries
$result = ba_db_query ($link, "SELECT gmName FROM {$db_prefix}guildmembers WHERE gmPlayerID = $admin_player_id");
//$asGuild will hold the guilds
$asGuild = array ();
while ($row = ba_db_fetch_assoc ($result))
	$asGuild [] = $row ['gmName'];

//Write out the guild select boxes
for ($iGuildCount = 1; $iGuildCount <= NUM_GUILDS; $iGuildCount++) {
	//Find out if character is in this guild
	if (count ($asGuild) >= $iGuildCount)
		//Find out which guild to select
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
	ListNames ($link, DB_PREFIX . 'guilds', 'guName', $sGuild);
	echo "</select><br>\n";
	echo "<script type = 'text/javascript'>\n<!--\ndocument.write ('</span>')\n";
	echo "// -->\n</script>\n";
}
?>
</p>

<p>
<table>
<tr><th colspan = "4">Skills</th></tr>
<?php
//Get character's skills. Fill an array with the skills. This array can then be queried, avoiding repeated DB queries
$result = ba_db_query ($link, "SELECT * FROM {$db_prefix}skillstaken WHERE stPlayerID = $admin_player_id");
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

<tr><td colspan = '4'><span id = 'spCost'></span></td></tr>
<tr><td colspan = '4'>&nbsp;</td></tr>
<tr><td colspan = '4'><?php echo IC_NOTES_TEXT ?><br>
<textarea name = "txtNotes"><?php echo htmlentities (stripslashes ($sNotes))?></textarea>
</td></tr>
<tr><td colspan = '4'><b>Special items/powers/creatures</b> (you must provide photcopies<br>
or scans for them to be valid at the event). Please enter one per line.<br>
<textarea name = "txtOSP"><?php echo htmlentities (stripslashes ($sOSP))?></textarea>
</td></tr>
</table>

<p>
<b>OSPs</b><br>
<?php
//New and exciting way
//Get character's OSPs. Fill an array with the details. The array can then be queried, avoiding repeated DB queries
$result = ba_db_query ($link, "SELECT * FROM {$db_prefix}ospstaken, {$db_prefix}osps WHERE otPlayerID = $admin_player_id AND ospID = otOspID");
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

Add Occupational Skill: <input type='text' id='addos' name='addos' />
<script type='text/javascript'>

function removeosp(ospid) {
	$('#osp' + ospid).remove();
}

$().ready(function() {
$("#addos").autocomplete({
			source: "../inc/inc_ossearch.php?pid=<?php echo $admin_player_id; ?>&",
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
<tr><td colspan = '2'>
<div class = "warn">Note that you are editing the IC details for player ID <?php echo PID_PREFIX . sprintf ('%03s', $admin_player_id)?><br>
Illegal combinations of skills are allowed (a note will be added to the IC Notes). BE CAREFUL!</div>
</td></tr>
<tr><td class = 'mid'><input type = 'submit' value = 'Submit' name = 'btnSubmit'></td>
<td class = 'mid'>
<script type = 'text/javascript'>
<!--
//Use a button to reset the form, so that fnCalculate can be called *after* the reset
document.write ("<input type = 'button' value = 'Reset' onclick = 'document.forms [0].reset (); fnCalculate ()'>")
// -->
</script>
<noscript>
<input type = 'reset' value = 'Reset'>
</noscript>
</td></tr>
</table>

</form>

<script type = 'text/javascript'>
<!--
fnCalculate ()
// -->
</script>

<?php
include ('../inc/inc_foot.php');