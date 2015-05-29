<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File bookings_rss.php
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
$db_prefix = DB_PREFIX;

//Send headers to tell browser that this is an RSS file
header("Content-Type: application/rss+xml");

$iEventID = (int) $_GET ['event'];

//Get list of players that have paid
$sql = "SELECT plPlayerID, " .
	"plFirstName, " .
	"plSurname, " .
	"chName, chPreferredName, chGroupSel, chGroupText, chFaction, chMonsterOnly, " .
	"bkDatePaymentConfirmed, " .
	"evEventName " .
	"FROM {$db_prefix}players, {$db_prefix}characters, {$db_prefix}bookings, {$db_prefix}events " .
	"WHERE bkEventID = evEventID AND plPlayerID = chPlayerID AND chPlayerID = bkPlayerID AND bkDatePaymentConfirmed <> '' AND bkDatePaymentConfirmed <> '0000-00-00'";
	if ($iEventID > 0)
		$sql .= " AND evEventID = $iEventID";

$result = ba_db_query ($link, $sql);

if (!BOOKING_LIST_IF_LOGGED_IN) {
	// XML header
	echo "<?xml version='1.0'?>\n<rss version='2.0' xmlns:atom='http://www.w3.org/2005/Atom'>\n<channel>\n";
	echo "<atom:link href='" . SYSTEM_URL . "bookings_rss.php' rel='self' type='application/rss+xml' />\n";
	echo "<title>" . TITLE . " - Booking List</title>\n";
	echo "<link>" . SYSTEM_URL . "</link>\n";
	echo "<description>" . TITLE . ": booking list</description>\n";

	while ($row = ba_db_fetch_assoc ($result)) {
		echo "<item>\n";
		echo "<title>Booking from " . htmlentities (stripslashes ($row ['plFirstName'])) .
			" " . htmlentities (stripslashes ($row ['plSurname'])) . "</title>\n";
		echo "<link>" . SYSTEM_URL . "</link>\n";
		echo "<guid isPermaLink='false'>" . SYSTEM_URL . "bookings_rss.php?id=" . $row ['plPlayerID'] . "</guid>\n";

		//Build up description
		$sDescription = "<description>";
		$sDescription .= "OOC Name: " .htmlentities (stripslashes ($row ['plFirstName'])) .
			" " . htmlentities (stripslashes ($row ['plSurname'])) . " &lt;br&gt;\n";
		if ($row['chMonsterOnly'] == 0) {
			if (strlen($row ['chPreferredName']) == 0)
				$sDescription .= "IC Name: " . htmlentities (stripslashes ($row ['chName'])) . " &lt;br&gt;\n";
			else
				$sDescription .= "IC Name: " . htmlentities (stripslashes ($row ['chPreferredName'])) . " &lt;br&gt;\n";
			if (LIST_GROUPS_LABEL != '') {
				if ($row ['chGroupText'] == 'Enter name here if not in above list' || $row ['chGroupText'] == '')
					$sDescription .= "Group: " . htmlentities (stripslashes ($row ['chGroupSel']));
				else
					$sDescription .= "Group: Other (" . htmlentities (stripslashes ($row ['chGroupText'])) . ")";
				$sDescription .= " &lt;br&gt;\n";
			}

			$sDescription .= "Faction: ". htmlentities (stripslashes ($row ['chFaction'])) ." &lt;br&gt;\n";
		}
		$sDescription .= "Event: " . htmlentities (stripslashes ($row ['evEventName']));
		$sDescription .= "</description>\n";
		echo "$sDescription</item>\n";
	}

	echo "</channel>\n</rss>\n";
}

//Close link to database
ba_db_close ($link);