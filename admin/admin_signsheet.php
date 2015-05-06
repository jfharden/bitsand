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

$eventinfo = getEventDetails($_GET['EventID'], 0, 'admin.php');
$eventid = $eventinfo['evEventID'];

//Get list of players & car registration numbers
$db_prefix  = DB_PREFIX;
$sql = "SELECT plFirstName, " .
	"plSurname, " .
	"plCarRegistration, " .
	"bkDatePaymentConfirmed " .
	"FROM {$db_prefix}players, {$db_prefix}bookings " .
	"WHERE plPlayerID = bkPlayerID AND bkDatePaymentConfirmed <> '0000-00-00' AND bkDatePaymentConfirmed <> '' and bkEventID = $eventid " .
	"ORDER BY plSurname";
$result = ba_db_query ($link, $sql);
?>

<div class='noprint'>
<h1><?php echo TITLE?> - Sign in Sheet</h1>
<p>
<a href = 'admin_manageevent.php?EventID=<?php echo $eventinfo['evEventID'];?>'>Return to event management for - <?php echo htmlentities (stripslashes ($eventinfo['evEventName']));?></a>
</p>

<h2><?php echo htmlentities (stripslashes ($eventinfo['evEventName']));?></h2>

<i>Note that this page is designed to be printed. It will look different when printed. Use Print Preview to see the difference.</i>
</div>


<p>
<?php echo DISCLAIMER_TEXT?>
</p>
<table width = "100%">
<thead>
<tr><th width = '10%'>Name</th><th width = '10%'>Car Reg</th><th>Signature</th></tr>
</thead>

<tbody>
<?php
$iRowCount = 0;
while ($row = ba_db_fetch_assoc ($result)) {
	echo "<tr><td width = '10%'>" .
		htmlentities (stripslashes ($row ['plFirstName'])) . " " .
		htmlentities (stripslashes ($row ['plSurname'])) . "</td>";
	echo "<td width = '10%'>" .
		htmlentities (stripslashes ($row ['plCarRegistration'])) .
		"</td><td>&nbsp;</td></tr>\n";
	if ($iRowCount++ > 12) {
		//Start a new table and force a page break
		echo "</table>\n<p style = 'page-break-before:always'>" . DISCLAIMER_TEXT . "</p>\n<table width = '100%'>\n";
		echo "<thead><tr><th width = '10%'>Name</th><th width = '10%'>Car Reg</th><th>Signature</th></tr></thead>\n";
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
