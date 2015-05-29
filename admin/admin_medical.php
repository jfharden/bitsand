<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File admin/admin_medical.php
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

$eventinfo = getEventDetails($_GET['EventID'], 0, 'admin.php');
$eventid = $eventinfo['evEventID'];

//Get list of players with medical issues
$db_prefix  = DB_PREFIX;
$sql = "SELECT plFirstName, " .
	"plSurname, " .
	"AES_DECRYPT(pleMedicalInfo, '$key') AS dMedicalInfo " .
	"FROM {$db_prefix}players, {$db_prefix}bookings " .
	"WHERE AES_DECRYPT(pleMedicalInfo, '$key') <> '' " .
	"AND plPlayerID = bkPlayerID " .
	"AND bkDatePaymentConfirmed <> '0000-00-00' " .
	"AND bkDatePaymentConfirmed <> '' " .
	"AND bkEventID = $eventid " .
	"ORDER BY plSurname";
$result = ba_db_query ($link, $sql);
?>

<div class='noprint'>
<h1><?php echo TITLE?> - Medical Details</h1>
<p>
<a href = 'admin_manageevent.php?EventID=<?php echo $eventinfo['evEventID'];?>'>Return to event management for - <?php echo htmlentities (stripslashes ($eventinfo['evEventName']));?></a>
</p>

<h2><?php echo htmlentities (stripslashes ($eventinfo['evEventName']));?></h2>

<i>Note that this page is designed to be printed. It will look different when printed. Use Print Preview to see the difference.</i>
</div>


<p style = "text-align: center; font-weight: bold; color: red;">MEDICAL DETAILS: HIGHLY CONFIDENTIAL</p>
<table width = "100%">
<thead>
<tr><th width = '20%'>Name</th><th>Medical Information</th></tr>
</thead>

<tbody>
<?php
$iRowCount = 0;
while ($row = ba_db_fetch_assoc ($result)) {
	echo "<tr><td width = '20%'>" .
		htmlentities (stripslashes ($row ['plFirstName'])) . " " .
		htmlentities (stripslashes ($row ['plSurname'])) . "</td>";
	$sMedicalInfo = str_replace ('\r\n', "\n", $row ['dMedicalInfo']);
	echo "<td>" .
		nl2br (htmlentities (stripslashes ($sMedicalInfo))) .
		"</td></tr>\n";
	if ($iRowCount++ > 12) {
		//Start a new table and force a page break
		echo "</table>\n";
		echo "<p style = 'page-break-before:always; text-align: center; font-weight: bold; color: red;'>MEDICAL DETAILS: HIGHLY CONFIDENTIAL</p>\n<table width = '100%'>\n";
		echo "<thead><tr><th width = '20%'>Name</th><th>Medical Information</th></tr></thead>\n";
		$iRowCount = 0;
	}
}

//Close link to database
ba_db_close ($link);
?>

</tbody>
</table>

</body>
</html>
