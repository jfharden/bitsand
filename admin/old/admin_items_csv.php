<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File admin/admin_items_csv.php
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

//Export bookings list to csv
include ('../inc/inc_head_db.php');
include ('../inc/inc_admin.php');
include ('../inc/inc_commonqueries.php');

$db_prefix = DB_PREFIX;
$key = CRYPT_KEY;

$eventinfo = getEventDetails($_GET['EventID'], 0, 'admin.php');
$eventid = $eventinfo['evEventID'];

//Send headers to tell browser that this is a CSV file
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=booking_items.csv;");

//Get list of players marked as paid
$key = CRYPT_KEY;
$db_prefix = DB_PREFIX;
$sql = "SELECT bkPlayerID,
plFirstName,
plSurname,
biQuantity,
itDescription
FROM {$db_prefix}bookings, {$db_prefix}bookingitems, {$db_prefix}items, {$db_prefix}players
WHERE bkEventID = $eventid
AND bkDatePaymentConfirmed <> '0000-00-00'
AND bkID = biBookingID
AND itItemID = biItemID
AND plPlayerID = bkPlayerID";

//echo $sql;
$result = ba_db_query ($link, $sql);

//Header row
echo '"Player ID","Player First Name","Player Surname",';
echo '"Quantity","Item Description"' . "\n";

while ($row = ba_db_fetch_assoc ($result)) {
	echo '"' . PID_PREFIX . sprintf ('%03s', $row ['bkPlayerID']) . '",';
	echo '"' . str_replace('"', '""', stripslashes ($row ['plFirstName'])) . '",';
	echo '"' . str_replace('"', '""', stripslashes ($row ['plSurname'])) . '",';
	echo $row ['biQuantity'] . ',';
	echo '"' . str_replace('"', '""', stripslashes ($row ['itDescription'])) . '",';
	echo "\n";
}

//Close link to database
ba_db_close ($link);