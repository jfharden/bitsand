<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File inc/inc_config_dist.php.php
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
include ('../inc/inc_head_html.php');
include ('../inc/inc_commonqueries.php');

$db_prefix = DB_PREFIX;
$key = CRYPT_KEY;

$eventinfo = getEventDetails($_GET['EventID'], 0, 'admin.php');
$eventid = $eventinfo['evEventID'];

if ($_POST ['btnSubmit'] != '' && CheckReferrer ('admin_marshal.php'))
foreach ($_POST as $key => $value) {
	if (substr ($key, 0, 7) == "hPlayer") {
		$iPlayerID = (int) $value;
		$refnumber = (int) $_POST ["txtRefNumber{$value}"];
		$marshal = stripslashes($_POST ["cboMarshal{$value}"]);
		$sql_update = "UPDATE {$db_prefix}players SET plRefNumber = $refnumber, plMarshal = '$marshal' WHERE plPlayerID = " . $iPlayerID;
		ba_db_query ($link, $sql_update);
	}
}

//Get list of players that have confirmed their booking
$sql = "SELECT bkPlayerID, " .
	"plFirstName, " .
	"plSurname, " .
	"bkBookAs, " .
	"plMarshal, ".
	"plRefNumber ".
	"FROM {$db_prefix}players, {$db_prefix}bookings " .
	"WHERE plPlayerID = bkPlayerID and bkEventID = $eventid";
$result = ba_db_query ($link, $sql);
?>
<script src="../inc/sorttable.js" type="text/javascript"></script>

<h1><?php echo TITLE?> - Marshals</h1>
<p>
<a href = 'admin_manageevent.php?EventID=<?php echo $eventinfo['evEventID'];?>'>Return to event management for - <?php echo htmlentities (stripslashes ($eventinfo['evEventName']));?></a>
</p>

<h2><?php echo htmlentities (stripslashes ($eventinfo['evEventName']));?></h2>

<p>
The following people have confirmed a booking. Click on a column header to sort by that column. You can use this page to record who is a Marshal or Ref, and what their Ref number is.
</p>
<form action = 'admin_marshal.php?EventID=<?php echo $eventinfo['evEventID'];?>' method = 'post'>

<table border = '1' class="sortable">
<tr>
<th>Player ID</th>
<th>OOC First Name</th>
<th>OOC Surname</th>
<th>Booking As</th>
<th>Marshal Type</th>
<th>Ref Number</th>
<th>Edit Marshal Type</th>
<th>Edit Ref Number</th>
</tr>

<?php
while ($row = ba_db_fetch_assoc ($result)) {
	echo "<tr class = 'highlight'>";
	echo "<input type = 'hidden' name = 'hPlayer" . $row ['bkPlayerID'] . "' value = '" . $row ['bkPlayerID'] . "'>";
	echo "<td>";
	echo PID_PREFIX . sprintf ('%03s', $row ['bkPlayerID']);
	echo "</td>\n";
	echo "<td>" . htmlentities (stripslashes ($row ['plFirstName'])) . "</td>\n";
	echo "<td>" . htmlentities (stripslashes ($row ['plSurname'])) . "</td>\n";
	echo "<td>" . htmlentities (stripslashes ($row ['plBookAs'])) . "</td>\n";
	echo "<td>" . htmlentities (stripslashes ($row ['plMarshal'])) . "</td>\n";
	echo "<td>" . htmlentities (stripslashes ($row ['plRefNumber'])) . "</td>\n";
	echo "<td><select name='cboMarshal". $row ['bkPlayerID'] ."'>";
	echo "<option "; if ($row ['plMarshal']== "No") { echo "selected"; }; echo " >No</option>";
	echo "<option "; if ($row ['plMarshal']== "Marshal") { echo "selected"; }; echo " >Marshal</option>";
	echo "<option "; if ($row ['plMarshal']== "Referee") { echo "selected"; }; echo " >Referee</option>";
	echo "<option "; if ($row ['plMarshal']== "Senior Referee") { echo "selected"; }; echo " >Senior Referee</option>";
	echo "</select></td>\n";
	echo "<td><input type=text name='txtRefNumber". $row ['bkPlayerID'] ."' size=5 value='" . htmlentities (stripslashes ($row ['plRefNumber'])) . "'/></td>\n";


	echo "</tr>\n";
}
?>

</table>

<p>
<input type = 'submit' value = 'Submit' name = 'btnSubmit'>&nbsp;
<input type = 'reset' value = 'Reset'>
</p>
</form>

<?php
include ('../inc/inc_foot.php');