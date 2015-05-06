<?php
/*
Bitsand - a web-based booking system for LRP events
Copyright (C) 2006 - 2014 The Bitsand Project (http://bitsand.googlecode.com/)

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

include ('../inc/inc_head_db.php');
include ('../inc/inc_admin.php');
include ('../inc/inc_head_html.php');
include ('../inc/inc_commonqueries.php');

$db_prefix = DB_PREFIX;
$key = CRYPT_KEY;

$eventinfo = getEventDetails($_GET['EventID'], 0, 'admin.php');
$eventid = $eventinfo['evEventID'];


$sql = "SELECT plPlayerID, plFirstName, " .
	"plSurname, " .
	"AES_DECRYPT(pleAddress1, '$key') AS dAddress1, " .
	"AES_DECRYPT(pleAddress2, '$key') AS dAddress2, " .
	"AES_DECRYPT(pleAddress3, '$key') AS dAddress3, " .
	"AES_DECRYPT(pleAddress4, '$key') AS dAddress4, " .
	"AES_DECRYPT(plePostcode, '$key') AS dPostcode, " .
	"chName, bkBookAs " .
	"FROM {$db_prefix}players, {$db_prefix}characters, {$db_prefix}bookings " .
	"WHERE plPlayerID = chPlayerID AND chPlayerID = bkPlayerID AND bkDatePaymentConfirmed <> '0000-00-00' AND plEventPackByPost = 1 and bkEventID = $eventid";
$result = ba_db_query ($link, $sql);
?>
<script src="../inc/sorttable.js" type="text/javascript"></script>

<h1><?php echo TITLE?> - Players Booked and Requesting event pack by post</h1>

<p>
<a href = 'admin.php'>Admin</a>
</p>

<p>
Click on a column header to sort by that column. Click on a player's ID to see that player's details.
</p>

<table border = '1' class="sortable">
<tr>
<th>Player ID</th>
<th>OOC First Name</th>
<th>OOC Surname</th>
<th>IC Name</th>
<th>Booking As</th>
<th>Address1</th>
<th>Address2</th>
<th>Address3</th>
<th>Address4</th>
<th>Postcode</th>
</tr>

<?php
while ($row = ba_db_fetch_assoc ($result)) {
	echo "<tr class = 'highlight'>\n<td>";
	echo "<a href = 'admin_viewdetails.php?pid=" . $row ['plPlayerID'] . "'>";
	echo PID_PREFIX . sprintf ('%03s', $row ['plPlayerID']) . "</a></td>\n<td>";
	echo htmlentities (stripslashes ($row ['plFirstName']));
	echo "</td>\n<td>";
	echo htmlentities (stripslashes ($row ['plSurname']));
	echo "</td>\n<td>";
	echo htmlentities (stripslashes ($row ['chName']));
	echo "</td>\n<td>";
	echo htmlentities (stripslashes ($row ['bkBookAs']));
	echo "</td>\n<td>";
	echo htmlentities (stripslashes ($row ['dAddress1']));
	echo "</td>\n<td>";
	echo htmlentities (stripslashes ($row ['dAddress2']));
	echo "</td>\n<td>";
	echo htmlentities (stripslashes ($row ['dAddress3']));
	echo "</td>\n<td>";
	echo htmlentities (stripslashes ($row ['dAddress4']));
	echo "</td>\n<td>";
	echo htmlentities (stripslashes ($row ['dPostcode']));
	echo "</td>\n</tr>";
}
?>

</table>

<?php
include ('../inc/inc_foot.php');
?>
