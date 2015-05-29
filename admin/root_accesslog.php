<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File admin/root_accesslog.php
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
require ('../inc/inc_admin.php');
require ('../inc/inc_root.php');

//Initialise some useful variables
$key = CRYPT_KEY;
$db_prefix = DB_PREFIX;
$sMsg = '';

//Set defaults
$sDateStart = date ("Y-m-d", strtotime ("-1 week"));
$sDateEnd = date ("Y-m-d", strtotime ("+1 day"));
$iRecStart = 0;
$iRecNum = 50;
$bDomain = True;

if ($_GET ['btnQuery'] != '' && CheckReferrer ('root_accesslog.php')) {
	$sDateStart = ba_db_real_escape_string ($link, $_GET ['txtStart']);
	$sDateEnd = ba_db_real_escape_string ($link, $_GET ['txtEnd']);
	$sIP = ba_db_real_escape_string ($link, $_GET ['txtIP']);
	$iRecStart = (int) $_GET ['txtRecStart'];
	$iRecNum = (int) $_GET ['txtRecNum'];
	$bDomain = (bool) $_GET ['chkDomain'];
	//Make sure LIMIT parameters are never less than 0, 1
	if ($iRecStart < 0)
		$iRecStart = 0;
	if ($iRecNum < 1)
		$iRecNum = 1;

	$sLogSQL = "SELECT alDateTime, alPlayerID, alIP, alPage, alGet, AES_DECRYPT(alePost, '$key') AS dPost " .
		"FROM {$db_prefix}access_log";
	if ($sDateStart != '' || $sDateEnd != '' || $sIP != '')
		$sLogSQL .= " WHERE ";
	if ($sDateStart != '' && $sDateEnd != '')
		$sLogSQL .= "alDateTime >= '$sDateStart' AND alDateTime <= '$sDateEnd'";
	if ($sDateStart != '' && $sDateEnd != '' && $sIP != '')
		$sLogSQL .= " AND ";
	if ($sIP != '')
		$sLogSQL .= "alIP = '$sIP'";
	$sLogSQL .= " ORDER BY " . ba_db_real_escape_string ($link, $_GET ['selOrder']);
	if ($_GET ['chkDesc'] != '')
		$sLogSQL .= " DESC";
	$sLogSQL .= " LIMIT $iRecStart, $iRecNum";
	$result = ba_db_query ($link, $sLogSQL);
}

if ($_GET ['btnQuery'] != '' && $_GET ['rdoExportView'] == 'download' && CheckReferrer ('root_accesslog.php')) {
	//Send headers to tell browser that this is a CSV file
	header("Content-Type: text/csv");
	header("Content-Disposition: attachment; filename=bookings.csv;");
	//Send headers
	echo '"Date - Time","Player ID","IP Address","Page","GET Query","POST Request"' . "\n";
	//Send data
	while ($row = ba_db_fetch_assoc ($result)) {
		echo '"' . $row ['alDateTime'] . '",';
		echo '"' . $row ['alPlayerID'] . '",';
		if ($bDomain)
			echo '"' . $row ['alIP'] . " (" . gethostbyaddr ($row ['alIP']) . ')",';
		else
			echo '"' . $row ['alIP'] . '",';
		echo '"' . $row ['alPage'] . '",';
		echo '"' . $row ['alGet'] . '",';
		echo '"' . $row ['dPost'] . "\"\n";
	}
	exit;
}

include ('../inc/inc_head_html.php');

if ($_POST ['btnDelete'] != '' && CheckReferrer ('root_accesslog.php')) {
	$sDeleteDate = ba_db_real_escape_string ($link, $_POST ['txtDeleteDate']);
	$sDeleteSQL = "DELETE FROM {$db_prefix}access_log WHERE alDateTime <= '$sDeleteDate'";
	$result = ba_db_query ($link, $sDeleteSQL);
	if ($result === False)
		$sMsg = "<span class = 'warn'>Problem deleting records</span>";
	else
		$sMsg = "<span class = 'green'>Records deleted</span>";
}
?>
<script src="../inc/sorttable.js" type="text/javascript"></script>

<h1><?php echo TITLE?> - Access Log</h1>

<?php
if ($sMsg != '')
	echo "<p>$sMsg</p>";
?>

<p>
<a href = 'admin.php'>Admin</a>
</p>

<h3>Dates/IP Address</h3>

<p>
If dates <i>and</i> IP address are specified, then both will be used (ie queries from that IP address, between those dates, will be shown).
</p>

<form action = 'root_accesslog.php' method = 'get'>
<table>
<tr><td>Start date (YYYY-MM-DD):</td>
<td><input name = 'txtStart' value = '<?php echo $sDateStart?>'> (midnight)</td></tr>
<tr><td>End date (YYYY-MM-DD:</td>
<td><input name = 'txtEnd' value = '<?php echo $sDateEnd?>'> (midnight)</td></tr>
<tr><td colspan = '2'>&nbsp;</td></tr>
<tr><td>IP Address:</td>
<td><input name = 'txtIP' value = '<?php echo htmlentities ($_GET ['txtIP'])?>'>
<?php
if ($bDomain)
	echo "<input type = 'checkbox' name = 'chkDomain' checked> Show host names\n";
else
	echo "<input type = 'checkbox' name = 'chkDomain'> Show host names\n";
?>
</td></tr>
<tr><td colspan = '2'>&nbsp;</td></tr>
<tr><td>Start record (0 is first record):</td>
<td><input name = 'txtRecStart' value = '<?php echo $iRecStart?>' size = '5'></td></tr>
<tr><td>Number to show:</td>
<td><input name = 'txtRecNum' value = '<?php echo $iRecNum?>' size = '5'></td></tr>
<tr><td colspan = '2'>&nbsp;</td></tr>
<tr><td>Order by:</td>
<td><select name = 'selOrder'>
<option value = 'alDateTime'>Date</option>
<option value = 'alPlayerID'>Player ID</option>
<option value = 'alIP'>IP Address</option>
<option value = 'alPage'>Page</option>
</select>

<?php
if ($_GET ['chkDesc'] != '')
	echo "<input type = 'checkbox' name = 'chkDesc' value = 'DESC' checked> Descending";
else
	echo "<input type = 'checkbox' name = 'chkDesc' value = 'DESC'> Descending";
?>
</td></tr>
<tr><td colspan = '2'>
<input type = "radio" name = "rdoExportView" value = "download">Download as CSV file<br>
<input type = "radio" name = "rdoExportView" value = "view" checked>View on line
</td></tr>
<tr><td colspan = '2'>&nbsp;</td></tr>
<tr><td colspan = '2'>
<input type = 'submit' name = 'btnQuery' value = 'Submit'>
</td></tr></table>
</form>

<h3>Delete Old Logs</h3>

<form action = 'root_accesslog.php' method = 'post'>
<p>
Delete access logs older than (YYYY-MM-DD):
<input name = 'txtDeleteDate' value = <?php echo date ("Y-m-d", strtotime ("-3 months"))?>> (midnight)<br>
<input type = 'submit' name = 'btnDelete' value = 'Delete'>
</p>
</form>

<?php
if ($_GET ['btnQuery'] != '' && CheckReferrer ('root_accesslog.php')) {
	echo "<p>Showing up to $iRecNum records. Click on a column header to sort by that column.</p>\n";

	echo "<table border = '1' class='sortable'>\n<thead>\n";
	echo "<tr><th>Date &amp; Time</th>\n";
	echo "<th>Player ID</th>\n";
	echo "<th>IP Address</th>\n";
	echo "<th>Page</th>\n";
	echo "<th>GET query</th>\n";
	echo "<th>POST request</th></tr>\n</thead>\n<tbody>\n";

	if ($_GET ['rdoExportView'] == 'view') {
		$result = ba_db_query ($link, $sLogSQL);
		while ($row = ba_db_fetch_assoc ($result)) {
			echo "<tr><td>{$row ['alDateTime']}</td>\n";
			echo "<td>{$row ['alPlayerID']}</td>\n";
			echo "<td>{$row ['alIP']}";
			if ($bDomain)
				echo "<br>" . gethostbyaddr ($row ['alIP']);
			echo "</td>\n";
			echo "<td>{$row ['alPage']}</td>\n";
			echo "<td>{$row ['alGet']}</td>\n";
			echo "<td>{$row ['dPost']}</td></tr>\n";
		}
	}

	echo "</tbody>\n<tfoot><tr>\n";
	echo "<td><a href = 'root_accesslog.php?txtStart=" . urlencode ($sDateStart) . "&amp;" .
		"txtEnd=" . urlencode ($sDateEnd) . "&amp;" .
		"txtIP=" . urlencode ($_GET ['txtIP']) . "&amp;" .
		"txtRecStart=" . urlencode ($iRecStart - $iRecNum) . "&amp;" .
		"txtRecNum=" . urlencode ($iRecNum) . "&amp;" .
		"selOrder=" . urlencode ($_GET ['selOrder']) . "&amp;" .
		"btnQuery=Submit" .
		"'>Previous $iRecNum</a></td>\n";
	echo "<td colspan = '4'>&nbsp;</td>\n";
	echo "<td><a href = 'root_accesslog.php?txtStart=" . urlencode ($sDateStart) . "&amp;" .
		"txtEnd=" . urlencode ($sDateEnd) . "&amp;" .
		"txtIP=" . urlencode ($_GET ['txtIP']) . "&amp;" .
		"txtRecStart=" . urlencode ($iRecStart + $iRecNum) . "&amp;" .
		"txtRecNum=" . urlencode ($iRecNum) . "&amp;" .
		"selOrder=" . urlencode ($_GET ['selOrder']) . "&amp;" .
		"btnQuery=Submit" .
		"'>Next $iRecNum</a></td>\n";
	echo "</tr>\n</tfoot>\n";
	echo "</table>\n";
}

include ('../inc/inc_foot.php');
?>
