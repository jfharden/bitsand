<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File inc/inc_commonqueries.php
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


function getEventDetails($eventid, $requirebookingsopen, $failureURL)
{
	global $today, $db_prefix, $link;
	if ($failureURL == '') { $failureURL = 'start.php'; }
	$eventid = htmlentities(stripslashes($eventid));
	$eventid = (int)$eventid;
	$sql = "Select * FROM {$db_prefix}events where ";
	if ($requirebookingsopen) {$sql .= "evBookingsOpen <= '".$today."' and evBookingsClose >= '".$today."' and ";}
	$sql .= " evEventID = " . $eventid;
	$result = ba_db_query ($link, $sql);
	
	if (ba_db_num_rows($result) == 0)
	{
		$sMsg = "The selected event is not currently open for bookings";
		$sURL = fnSystemURL () . $failureURL . '?warn=' . urlencode ($sMsg);
		header ("Location: $sURL");
	}
	
	return ba_db_fetch_assoc ($result);
}

function removeItem($bookingid, $itemtype)
{
	global $db_prefix, $link;
	$itemfilter = ' and 1 = 0';
	if (strtolower($itemtype) == "meal") { $itemfilter = ' and itMeal = 1';}
	if (strtolower($itemtype) == "bunk") { $itemfilter = ' and itBunk = 1';}
	$sql = "select biBookingItemID from {$db_prefix}bookingitems, {$db_prefix}items where biItemID = itItemID $itemfilter and biBookingID = " . $bookingid;
	$result = ba_db_query ($link, $sql);
	$itemtodeletelist = array();
	while ($itemtodelete = ba_db_fetch_assoc($result))
	{
		array_push($itemtodeletelist, $itemtodelete['biBookingItemID']);
	}
	foreach($itemtodeletelist as $itemtodelete)
	{
		$sql = "delete from {$db_prefix}bookingitems where biBookingItemID = " . $itemtodelete;
		$result = ba_db_query ($link, $sql);
	}
}

function addItem($bookingid, $itemtype)
{
	global $today,$db_prefix, $link;
	$itemfilter = ' 1 = 0';
	if (strtolower($itemtype) == "meal") { $itemfilter = ' itMeal = 1';}
	if (strtolower($itemtype) == "bunk") { $itemfilter = ' itBunk = 1';}
	$sql = "Select bkBookAs, bkEventID from {$db_prefix}bookings where bkID = ". $bookingid;

	$result = ba_db_query ($link, $sql);
	$bookas = ba_db_fetch_assoc($result);
	$eventid = $bookas['bkEventID'];
	$bookas = $bookas['bkBookAs'];

	//Run another query here to add an item of meal ticket
	$sql = "select itItemID from {$db_prefix}items where $itemfilter and itEventID = $eventid and itAvailability in ('All','$bookas') ";
	$sql.= "and itAvailableFrom <= '".$today."' and itAvailableTo >= '".$today."'";
	$sql.= " order by itAvailability desc limit 1";
	$result = ba_db_query ($link, $sql);
	
	if (ba_db_num_rows($result) > 0)
	{
		$itemid = ba_db_fetch_assoc($result);
		$sql = "insert into {$db_prefix}bookingitems (biBookingID, biItemID, biQuantity) VALUES ($bookingid, ".$itemid['itItemID'].", 1)";
		$result = ba_db_query ($link, $sql);
	}
}

function getItemCost($itemtype, $availability, $eventid)
{
	global $today,$db_prefix, $link;
	$itemfilter = ' and 1 = 0';
	if (strtolower($itemtype) == "meal") { $itemfilter = ' and itMeal = 1';}
	if (strtolower($itemtype) == "bunk") { $itemfilter = ' and itBunk = 1';}
	if (strtolower($itemtype) == "ticket") { $itemfilter = ' and itTicket = 1';}
	
	$sql = "select ifnull(itItemCost, 0) as itItemCost from {$db_prefix}items where itEventID = $eventid $itemfilter and itAvailability in ('All','$availability') ";
	$sql.= "and itAvailableFrom <= '".$today."' and itAvailableTo >= '".$today."'";
	$sql.= " order by itAvailability desc limit 1";

	$result = ba_db_query ($link, $sql);
	$price = ba_db_fetch_assoc ($result);
	$price = $price['itItemCost'];
	return $price;
}

function resetExpectedAmount($bookingid)
{
	global $today,$db_prefix, $link;
	
	$sql = "select sum(biQuantity * itItemCost) as Expected from {$db_prefix}bookingitems inner join {$db_prefix}items on biItemID = itItemID where biBookingID = $bookingid";
	$result = ba_db_query ($link, $sql);
	$expected = ba_db_fetch_assoc ($result);
	$expected = $expected['Expected'];
	$sql = "update {$db_prefix}bookings set bkAmountExpected = $expected where bkID = $bookingid";
	$result = ba_db_query ($link, $sql);
}

function deleteBooking($bookingid)
{
	global $today,$db_prefix, $link;
	
	$sql = "DELETE FROM {$db_prefix}bookingitems WHERE biBookingID = ".$bookingid;
	ba_db_query ($link, $sql);
	$sql = "DELETE FROM {$db_prefix}paymentrequests WHERE prBookingID = ".$bookingid;
	ba_db_query ($link, $sql);
	$sql = "DELETE FROM {$db_prefix}bookings WHERE bkID = ".$bookingid;
	ba_db_query ($link, $sql);

}
?>