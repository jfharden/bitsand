<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File bookinglist.php
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

//Do not need login check for this page
$bLoginCheck = False;

include ('inc/inc_head_db.php');
include ('inc/inc_head_html.php');
include ('inc/inc_commonqueries.php');
$db_prefix = DB_PREFIX;

$eventinfo = getEventDetails($_GET['EventID'], 0, 'bookinglist.php');
$eventid = $eventinfo['evEventID'];

//Get list of players that have paid
$sql = "SELECT plFirstName, " .
	"plSurname, " .
	"bkBookAs, " .
	"chName, chPreferredName, chGroupSel, chGroupText, chFaction, chMonsterOnly, " .
	"bkDatePaymentConfirmed " .
	"FROM {$db_prefix}players, {$db_prefix}characters, {$db_prefix}bookings " .
	"WHERE plPlayerID = chPlayerID " .
	"AND chPlayerID = bkPlayerID " .
	"AND bkDatePaymentConfirmed <> '' " .
	"AND bkDatePaymentConfirmed <> '0000-00-00' " .
	"AND bkEventID = $eventid";
$result = ba_db_query ($link, $sql);
?>
<script src="inc/sorttable.js" type="text/javascript"></script>

<?php
echo "<h1>" . TITLE . " - Bookings</h1>\n";
echo "<h2>" . htmlentities ($eventinfo ['evEventName']) . "</h2>\n";
?>

<form action = 'bookinglist.php' method = 'get'>
Select event:
<select name = 'EventID'>
<?php
$sql = "SELECT evEventID, evEventName, evEventDate FROM events";
$events = ba_db_query ($link, $sql);
while ($eventrow = ba_db_fetch_assoc ($events)) {
	$d = explode ("-", $eventrow ['evEventDate']);
	echo "<option value = '{$eventrow ['evEventID']}'>{$eventrow ['evEventName']} (";
	echo "{$d [2]}/{$d [1]}/{$d [0]})</option>\n";
}
?>
</select>
<input type = 'submit' value = 'View'>
</form>

<?php
if ((BOOKING_LIST_IF_LOGGED_IN && $PLAYER_ID != 0) || !BOOKING_LIST_IF_LOGGED_IN)
{
	echo "<p>The people below have confirmed IC &amp; OOC details, and been marked as paid. Click on a column header to sort by that column.</p>";
   	echo "	<table border = '1' class='sortable'><tr><th>OOC First Name</th><th>OOC Surname</th><th>IC Name</th>";

	if (LIST_GROUPS_LABEL != '')
		echo "<th>Group</th>";

	echo "<th>Faction</th>";
	echo "<th>Booking As</th></tr>\n";

	while ($row = ba_db_fetch_assoc ($result)) {
		echo "<tr class = 'highlight'>\n";
		echo "<td>" . htmlentities (stripslashes ($row ['plFirstName'])) . "</td>\n";
		echo "<td>" . htmlentities (stripslashes ($row ['plSurname'])) . "</td>\n";

		if ($row['chMonsterOnly'] == 0)
		{
			if (strlen($row ['chPreferredName']) == 0)
			{
				echo "<td>" . htmlentities (stripslashes ($row ['chName'])) . "</td>\n";
			}
			else
			{
				echo "<td>" . htmlentities (stripslashes ($row ['chPreferredName'])) . "</td>\n";
			}
			if (LIST_GROUPS_LABEL != '') {
				echo "<td>\n";
				if ($row ['chGroupText'] == 'Enter name here if not in above list' || $row ['chGroupText'] == '')
					echo htmlentities (stripslashes ($row ['chGroupSel']));
				else
					echo "Other (" . htmlentities (stripslashes ($row ['chGroupText'])) . ")";
				echo "</td>\n";
			}

			echo "<td>". htmlentities (stripslashes ($row ['chFaction'])) ."</td>";

		}
		else
		{
			echo "<td></td>";
			if (LIST_GROUPS_LABEL != '')
				echo "<td></td>";
			echo "<td></td>";
		}
		echo "<td>" . htmlentities (stripslashes ($row ['bkBookAs'])) . "</td>\n</tr>\n";
	}

	echo "</table>";

	if (ALLOW_MONSTER_BOOKINGS) {
		$sql = "SELECT plPlayerID, " .
			"bkBookAs, " .
			"bkDatePaymentConfirmed " .
			"FROM {$db_prefix}players, {$db_prefix}bookings " .
			"WHERE bkBookAs LIKE 'Monster' AND plPlayerID = bkPlayerID AND bkDatePaymentConfirmed <> '' AND bkDatePaymentConfirmed <> '0000-00-00'";
		$result = ba_db_query ($link, $sql);
		$iMonsters = ba_db_num_rows ($result);
	}
	else
		$iMonsters = 0;
	$sql = "SELECT plPlayerID, " .
		"bkBookAs, " .
		"bkDatePaymentConfirmed " .
		"FROM {$db_prefix}players, {$db_prefix}bookings " .
		"WHERE bkBookAs LIKE 'Player' AND plPlayerID = bkPlayerID AND bkDatePaymentConfirmed <> '' AND bkDatePaymentConfirmed <> '0000-00-00'";
	$result = ba_db_query ($link, $sql);
	$iPlayers = ba_db_num_rows ($result);
	$sql = "SELECT plPlayerID, " .
		"bkBookAs, " .
		"bkDatePaymentConfirmed " .
		"FROM {$db_prefix}players, {$db_prefix}bookings " .
		"WHERE bkBookAs LIKE 'Staff' AND plPlayerID = bkPlayerID AND bkDatePaymentConfirmed <> '' AND bkDatePaymentConfirmed <> '0000-00-00'";
	$result = ba_db_query ($link, $sql);
	$iStaff = ba_db_num_rows ($result);
	$iTotal = $iMonsters + $iPlayers + $iStaff;
	echo "<p>\n";
	if (ALLOW_MONSTER_BOOKINGS)
		echo "$iMonsters monsters, ";
	echo "$iPlayers players, $iStaff staff. ($iTotal total)\n</p>";

}
else
{
	echo "<p>The list of bookings for this event is only available if you are logged into the system.</p>";
}
include ('inc/inc_foot.php');