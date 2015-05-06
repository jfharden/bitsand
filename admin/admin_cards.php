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

//Character Skills
$db_prefix = DB_PREFIX;
$sql = "SELECT bkPlayerID, bkDatePaymentConfirmed, stPlayerID, stSkillID " .
	"FROM {$db_prefix}skillstaken, {$db_prefix}bookings " .
	"WHERE stPlayerID = bkPlayerID AND bkDatePaymentConfirmed <> '0000-00-00' AND bkDatePaymentConfirmed <> '' and bkEventID = $eventid";
$result = ba_db_query ($link, $sql);

//Initialise counters
$iCards = 0;
$iSenseMagic = 0;
$iEvaluate = 0;
$iPotionLore = 0;
$iPoisonLore = 0;
$iRecForgery = 0;

while ($row = ba_db_fetch_assoc ($result)) {
	// Level 1 magics
	if ($row ['stSkillID'] == 21 || $row ['stSkillID'] == 25 || $row ['stSkillID'] == 29)
		$iCards = $iCards + 4;
	// Level 2 magics
	if ($row ['stSkillID'] == 23 || $row ['stSkillID'] == 27 || $row ['stSkillID'] == 31)
		$iCards = $iCards + 12;
	if ($record ['skID'] == 10)
		$iCards = $iCards + 4;
	if ($record ['skID'] == 12)
		$iCards = $iCards + 8;
	if ($record ['skID'] == 14)
		$iCards = $iCards + 12;
	if ($record ['skID'] == 16)
		$iCards = $iCards + 16;
	if ($row ['stSkillID'] == 24)
		$iSenseMagic++;
	if ($row ['stSkillID'] == 26)
		$iEvaluate++;
	if ($row ['stSkillID'] == 18)
		$iPotionLore++;
	if ($row ['stSkillID'] == 20)
		$iPoisonLore++;
	if ($row ['stSkillID'] == 28)
		$iRecForgery++;
}

//OSP Skills
$sql = "SELECT bkPlayerID, bkDatePaymentConfirmed, otPlayerID, otOspID " .
	"FROM {$db_prefix}ospstaken, {$db_prefix}bookings " .
	"WHERE otPlayerID = bkPlayerID AND bkDatePaymentConfirmed <> '0000-00-00' AND bkDatePaymentConfirmed <> '' and bkEventID = $eventid";
$result = ba_db_query ($link, $sql);

//Initialise new counters
$iTranslate = 0;
$iHerbLore = 0;
$iNewsmonger = 0;
$iGeneralKnowledge = 0;
$iFarTravelled = 0;
while ($row = ba_db_fetch_assoc ($result)) {
	if ($row ['otOspID'] == 161)
		$iTranslate++;
	if ($row ['otOspID'] == 84)
		$iHerbLore++;
	if ($row ['otOspID'] == 122)
		$iNewsmonger++;
	if ($row ['otOspID'] == 76)
		$iGeneralKnowledge++;
	if ($row ['otOspID'] == 72)
		$iFarTravelled++;
	//Extra spell card OSPs
	if ($row ['otOspID'] == 6)
		$iCards = $iCards + 4;
	if ($row ['otOspID'] == 7)
		$iCards = $iCards + 8;
	if ($row ['otOspID'] == 3)
		$iCards = $iCards + 12;
	if ($row ['otOspID'] == 4)
		$iCards = $iCards + 16;
}

//Get number of meal tickets & bunks
$sql = "SELECT COUNT(*) AS cMealTickets FROM {$db_prefix}bookings " .
	"WHERE bkDatePaymentConfirmed <> '0000-00-00' AND bkDatePaymentConfirmed <> '' AND bkMealTicket = 1 and bkEventID = $eventid";
$result = ba_db_query ($link, $sql);
$row = ba_db_fetch_assoc ($result);
$iMeal = (int) $row ['cMealTickets'];
$sql = "SELECT COUNT(*) AS cMealTickets FROM {$db_prefix}bookings " .
	"WHERE bkDatePaymentConfirmed <> '0000-00-00' AND bkDatePaymentConfirmed <> '' AND bkMealTicket = 1 and bkEventID = $eventid";
//Player bunks
$sql = "SELECT COUNT(*) AS cBunks " .
		"FROM {$db_prefix}players, {$db_prefix}bookings " .
		"WHERE plPlayerID = bkPlayerID AND bkBookAs LIKE 'Player' AND " .
		"bkDatePaymentConfirmed <> '0000-00-00' AND bkDatePaymentConfirmed <> '' AND bkBunkAllocated = 1 and bkEventID = $eventid";
$result = ba_db_query ($link, $sql);
$row = ba_db_fetch_assoc ($result);
$iPlayerBunks = (int) $row ['cBunks'];
//Staff bunks
$sql = "SELECT COUNT(*) AS cBunks " .
		"FROM {$db_prefix}players, {$db_prefix}bookings " .
		"WHERE plPlayerID = bkPlayerID AND bkBookAs LIKE 'Staff' AND " .
		"bkDatePaymentConfirmed <> '0000-00-00' AND bkDatePaymentConfirmed <> '' AND bkBunkAllocated = 1 and bkEventID = $eventid";
$result = ba_db_query ($link, $sql);
$row = ba_db_fetch_assoc ($result);
$iStaffBunks = (int) $row ['cBunks'];
//Monster bunks
if (ALLOW_MONSTER_BOOKINGS) {
	$sql = "SELECT COUNT(*) AS cBunks " .
			"FROM {$db_prefix}players, {$db_prefix}bookings " .
			"WHERE plPlayerID = bkPlayerID AND bkBookAs LIKE 'Monster' AND " .
			"bkDatePaymentConfirmed <> '0000-00-00' AND bkDatePaymentConfirmed <> '' AND bkBunkAllocated = 1 and bkEventID = $eventid";
	$result = ba_db_query ($link, $sql);
	$row = ba_db_fetch_assoc ($result);
	$iMonsterBunks = (int) $row ['cBunks'];
}
else
	$iMonsterBunks = 0;
?>

<?php
echo "<h1>" . TITLE . " - Cards, Lore Sheets, Meal Tickets &amp; bunks required</h1>\n";
?>

<p>
<a href = 'admin_manageevent.php?EventID=<?php echo $eventinfo['evEventID'];?>'>Return to event management for - <?php echo htmlentities (stripslashes ($eventinfo['evEventName']));?></a>
</p>

<h2><?php echo htmlentities (stripslashes ($eventinfo['evEventName']));?></h2>

<p>
Required for the pre-booked characters:
</p>

<h3>Booked Item Summary</h3>

<?php

$sql = "select itDescription, itAvailability, ifnull(sum(biQuantity),0) as itBookingCount from {$db_prefix}items left outer join {$db_prefix}bookingitems on itItemID = biItemID inner join {$db_prefix}bookings on bkID = biBookingID where itEventID = $eventid and  bkDatePaymentConfirmed <> '' AND bkDatePaymentConfirmed <> '0000-00-00' group by itItemID";


$result = ba_db_query ($link, $sql);
echo "<table><tr><th>Item name</th><th>Availability</th><th>Booking Count</th></tr>";
while($itembooking = ba_db_fetch_assoc($result))
{
	echo "<tr><td>".$itembooking['itDescription']."</td><td>".$itembooking['itAvailability']."</td><td>".$itembooking['itBookingCount']."</td></tr>";
}
echo "</table>";

echo "<h3>Power Cards</h3>\n";
echo "<p>$iCards Power cards per day\n<br>";
echo $iHerbLore * 5 . " Herb cards\n</p>\n";

echo "<h3>Lore Sheets</h3>\n";
echo "<p>$iSenseMagic Sense Magic lore sheets<br>\n";
echo "$iEvaluate Evaluate lore sheets<br>\n";
echo "$iPotionLore Potion Lore lore sheets<br>\n";
echo "$iPoisonLore Poison Lore lore sheets<br>\n";
echo "$iRecForgery Recognise Forgery lore sheets<br>\n";
echo "$iHerbLore Herb Lore lore sheets<br>\n</p>\n";

echo "<h3>Other</h3>\n";
echo "<p>$iTranslate characters have the Translate Named Script OSP<br>\n";
echo "$iNewsmonger characters have the Newsmonger OSP<br>\n";
echo "$iGeneralKnowledge characters have the General Knowledge OSP<br>\n";
echo "$iFarTravelled characters have the Far Travelled OSP<br>\n";
?>

<h3>Meal Tickets &amp; bunks</h3>

<p>
<?php echo $iMeal?> meal tickets<br>
<?php echo $iPlayerBunks?> player bunks<br>
<?php echo $iStaffBunks?> staff bunks<br>
<?php
if (ALLOW_MONSTER_BOOKINGS)
	echo "$iMonsterBunks monster bunks<br>\n";
echo $iPlayerBunks + $iStaffBunks + $iMonsterBunks . " total bunks<br>\n";
?>
</p>

<h3>Bookings</h3>

<p>
<?php
if (ALLOW_MONSTER_BOOKINGS) {
	$sql = "SELECT plPlayerID, " .
		"bkBookAs, " .
		"bkDatePaymentConfirmed " .
		"FROM {$db_prefix}players, {$db_prefix}bookings " .
		"WHERE bkBookAs LIKE 'Monster' AND plPlayerID = bkPlayerID AND bkDatePaymentConfirmed <> '' AND bkDatePaymentConfirmed <> '0000-00-00' and bkEventID = $eventid";
	$result = ba_db_query ($link, $sql);
	$iMonsters = ba_db_num_rows ($result);
}
else
	$iMonsters = 0;
$sql = "SELECT plPlayerID, " .
	"bkBookAs, " .
	"bkDatePaymentConfirmed " .
	"FROM {$db_prefix}players, {$db_prefix}bookings " .
	"WHERE bkBookAs LIKE 'Player' AND plPlayerID = bkPlayerID AND bkDatePaymentConfirmed <> '' AND bkDatePaymentConfirmed <> '0000-00-00' and bkEventID = $eventid";
$result = ba_db_query ($link, $sql);
$iPlayers = ba_db_num_rows ($result);
$sql = "SELECT plPlayerID, " .
	"bkBookAs, " .
	"bkDatePaymentConfirmed " .
	"FROM {$db_prefix}players, {$db_prefix}bookings " .
	"WHERE bkBookAs LIKE 'Staff' AND plPlayerID = bkPlayerID AND bkDatePaymentConfirmed <> '' AND bkDatePaymentConfirmed <> '0000-00-00' and bkEventID = $eventid";
$result = ba_db_query ($link, $sql);
$iStaff = ba_db_num_rows ($result);
$iTotal = $iMonsters + $iPlayers + $iStaff;

$sql = "SELECT bkDatePaymentConfirmed " .
	"FROM {$db_prefix}bookings " .
	"WHERE bkDatePaymentConfirmed <> '' AND bkDatePaymentConfirmed <> '0000-00-00' AND bkPayOnGate = 1 and bkEventID = $eventid";
$result = ba_db_query ($link, $sql);
$iPayOnGate = ba_db_num_rows ($result);
if (ALLOW_MONSTER_BOOKINGS)
	echo "$iMonsters monsters, ";
echo "$iPlayers players, $iStaff staff. ($iTotal total)<br>\n";
echo "$iPayOnGate will be paying on the gate\n";
?>
</p>

<p>
<b>Notes:</b><br>
This is only for those people booked on this system. If you are expecting bookings on the gate, you may need more. Also, if characters die, you may need more cards and/or lore sheets for the new characters.
</p>

<?php
include ('../inc/inc_foot.php');
?>
