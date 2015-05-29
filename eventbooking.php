<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File eventbooking.php
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

include ('inc/inc_head_db.php');

include ('inc/inc_head_html.php');
include ('inc/inc_commonqueries.php');

function GetBookingTypeAvailability($eventid, $bookingtype, $spaces)
{
	//Returns -1 if no booking allowed
	//Returns 1 if booking allowed
	//Returns 0 if booking will be placed in queue as speaces are full.
	global $link, $today, $db_prefix;

	$sql = "select count(itItemID) from {$db_prefix}items where itTicket = 1 and itAvailableFrom <= '$today' and itAvailableTo >= '$today' and itAvailability in ('All', '$bookingtype') and itEventID = $eventid";
	$result = ba_db_query ($link, $sql);
	$TicketTypeAvailable = ba_db_fetch_row($result);
	$TicketTypeAvailableCount = $TicketTypeAvailable[0];
	if ($TicketTypeAvailableCount == 0 && $bookingtype != "All") { return -1; }

	$sql = "select count(bkID) as BookingCount from {$db_prefix}bookings where bkInQueue = 0 and bkEventID = $eventid ";
	if ($bookingtype != "All") { $sql .= " and bkBookAs = '$bookingtype'"; }
	$result = ba_db_query ($link, $sql);
	$BookingCount = ba_db_fetch_assoc ($result);
	$BookingCount = $BookingCount['BookingCount'];
	if ($BookingCount >= $spaces) {
		if (QUEUE_OVER_LIMIT) {	return 0;}
		else { return -1; }
	}
	return 1;

}

function GetBunkAvailability($eventid, $bookingtype, $bunks)
{
	global $link, $today, $db_prefix;


	$sql = "select ifnull(sum(bkBunkAllocated), 0) as BunksAllocated from {$db_prefix}bookings where bkEventID = $eventid ";
	if ($bookingtype != "All") { $sql .= " and bkBookAs = '$bookingtype' "; }

	$result = ba_db_query ($link, $sql);
	$BookingTypeAvailable = ba_db_fetch_row($result);
	$BookingTypeCount = $BookingTypeAvailable[0];
	if ($BookingTypeCount >= $bunks ) { return false;}

	return true;

}
	$eventinfo = getEventDetails($_GET['EventID'], 1);

?>

<script type = 'text/javascript'>

<?php
if (GetBunkAvailability($eventinfo['evEventID'], "All", $eventinfo['evTotalBunks']))
{
	if (GetBunkAvailability($eventinfo['evEventID'], "Player", $eventinfo['evPlayerBunks'])) {$playerbunks = 1;} else {$playerbunks = 0;}
	if (GetBunkAvailability($eventinfo['evEventID'], "Monster", $eventinfo['evMonsterBunks'])) {$monsterbunks = 1;} else {$monsterbunks = 0;}
	if (GetBunkAvailability($eventinfo['evEventID'], "Staff", $eventinfo['evStaffBunks'])) {$staffbunks = 1;} else {$staffbunks = 0;}
}
else
{
	$playerbunks = $monsterbunks = $staffbunks = 0;
}
echo "var PlayerBunkAvailable = $playerbunks;";
echo "var MonsterBunkAvailable = $monsterbunks;";
echo "var StaffBunkAvailable = $staffbunks;";

?>

	function changetotal()
	{
		var bookingtotal = 0;
		$('.singleitem').each(function() {
			var cost = $(this);
			if (cost.attr('checked'))
			{
				bookingtotal += parseFloat(cost.attr('cost'));
				if ($('#h' + cost.attr('id')).length > 0) { $('#h' + cost.attr('id')).val('on'); }
			}
			else
			{
				if ($('#h' + cost.attr('id')).length > 0) { $('#h' + cost.attr('id')).val('off'); }
			}

			}
		);

		$('.multipleitem').each(function() {
			var cost = $(this);
			bookingtotal += (parseFloat(cost.attr('cost')) * cost.val());
			}
		);

		$('#bookingtotal').text(bookingtotal.toFixed(2));

	}

	function showitems()
	{
		var bookingtype = $('#cboBookAs').val();
		$(".bookingitems tr").hide();
		$('.singleitem').attr('checked', false);
		$('.multipleitem').val(0);
		$("#bookingtotal").innerHTML = '0';
		$('.'+bookingtype).fadeTo("slow", 1.0);
		$('.All').fadeTo("slow", 1.0);
		$('.bookingitems tfoot tr').fadeTo("slow", 1.0);

		var defaultSet = false;
		$('.singleitem').each(function() {
			var defaultItem = $(this);

			if (defaultItem.attr('ticket') == 1 && defaultItem.is(":visible") && !defaultSet)
			{
				defaultItem.attr('checked', true);
				defaultSet = true;
				changetotal();
			}

			if (defaultItem.attr('bunk') == 1)
			{
				var bunkRow = $('#' + defaultItem.attr('id').replace('chk','row'));
				if (bookingtype == 'Player' && !PlayerBunkAvailable) { bunkRow.hide(); }
				if (bookingtype == 'Monster' && !MonsterBunkAvailable) {  bunkRow.hide(); }
				if (bookingtype == 'Staff' && !StaffBunkAvailable) {  bunkRow.hide(); }
			}
		}

		);
	}
</script>

<h1><?php echo TITLE?></h1>

<h2>Book for event - <?php echo htmlentities (stripslashes ($eventinfo['evEventName'])); ?></h2>

<?php
//Check if player has entered IC data
$sql = "SELECT chName FROM {$db_prefix}characters WHERE chPlayerID = $PLAYER_ID";
$result = ba_db_query ($link, $sql);
$iIC = ba_db_num_rows ($result);

if ($iIC == 0)
{
	echo "<p>Note that you cannot book as a player unless you enter your IC details</p>\n";
}
else
{
	//Check skills cost is valid, expand in future to include a better check
	$sql = "select sum(skCost) as pointsspent from {$db_prefix}skillstaken inner join {$db_prefix}skills on stSkillID = skID where stPlayerID = $PLAYER_ID";
	$result = ba_db_query ($link, $sql);
	$pointsspent = ba_db_fetch_assoc ($result);
	if ($pointsspent['pointsspent'] > MAX_CHAR_PTS)
	{
		echo "<p>You must select fewer skills before you can book as a player</p>\n";
		$iIC = 0;
	}
}

if (GetBookingTypeAvailability($eventinfo['evEventID'], "All", $eventinfo['evTotalSpaces']) == 1)
{
	$playerspaces = GetBookingTypeAvailability($eventinfo['evEventID'], "Player", $eventinfo['evPlayerSpaces']);
	$monsterspaces = GetBookingTypeAvailability($eventinfo['evEventID'], "Monster", $eventinfo['evMonsterSpaces']);
	$staffspaces = GetBookingTypeAvailability($eventinfo['evEventID'], "Staff", $eventinfo['evStaffSpaces']);

	if ($playerspaces == 0 && $iIC) { echo "<p class='warning'>Player spaces are full, you may still book but your booking will be placed in a queue, and may not be accepted.</p>"; }
	if ($monsterspaces == 0 && $eventinfo['evAllowMonsterBookings']) { echo "<p class='warning'>Monster spaces are full, you may still book but your booking will be placed in a queue, and may not be accepted.</p>"; }
	if ($staffspaces == 0) { echo "<p class='warning'>$stafftext spaces are full, you may still book but your booking will be placed in a queue, and may not be accepted.</p>"; }

	echo "\n<form action='eventbookingconfirm.php' method='post'>";
	echo "<p>Please select your booking type: \n";
	echo "<select id='cboBookAs' name='cboBookAs' onChange='showitems()'></p>\n";

	if ($playerspaces >= 0 && $iIC) { echo "<option value='Player'>Player</option>";}
	if ($monsterspaces >= 0 && $eventinfo['evAllowMonsterBookings']) { echo "<option value='Monster'>Monster</option>";}
	if ($staffspaces >= 0) { echo "<option value='Staff'>".$stafftext."</option>";}
	echo "</select>";

	$sql = "SELECT * from {$db_prefix}items where itEventID = ".$eventinfo['evEventID']." and itAvailableFrom <= '$today' and itAvailableTo >= '$today' ";
	$result = ba_db_query ($link, $sql);

	echo "\n<input type='hidden' value='".$eventinfo['evEventID']."' name='eventid'/>";
	echo "\n<table class='bookingitems'>\n";
	while ($iteminfo = ba_db_fetch_assoc ($result))
	{
		echo "<tr class='".$iteminfo['itAvailability']."' id='row".$iteminfo['itItemID']."'><td class='description'>".$iteminfo['itDescription']."</td><td>&pound;".$iteminfo['itItemCost']."</td><td>";
		if ($iteminfo['itAllowMultiple']) {
			echo "<select class='multipleitem' name='cbo".$iteminfo['itItemID']."' id='cbo".$iteminfo['itItemID']."' onChange='changetotal()' cost='".$iteminfo['itItemCost']."'>";
			for($i=0; $i<31;$i++)
			{
				echo "<option value='$i'>$i</option>";
			}
			echo "</select>";
		}
		else {
			echo "<input type='checkbox' class='singleitem' name='chk".$iteminfo['itItemID']."' id='chk".$iteminfo['itItemID']."' onClick='changetotal()' cost='".$iteminfo['itItemCost']."' ticket='".$iteminfo['itTicket']."' bunk='".$iteminfo['itBunk']."'";
			if ($iteminfo['itMandatory'] == 1) {
				echo " checked disabled='true' />";
				echo "<input type=hidden id='hchk".$iteminfo['itItemID']."' name='chk".$iteminfo['itItemID']."' cost='".$iteminfo['itItemCost']."' ticket='".$iteminfo['itTicket']."' bunk='".$iteminfo['itBunk']."' value='on' />";
			}
			else
			{
		 		echo "/>";
			}
		  }
		echo "</td></tr>\n";
	}
	echo "<tfoot>";
	echo "<tr class='total'><td class='totallabel' colspan=2>Total:</td><td>&pound;<span id='bookingtotal'>0.00</span></td></tr>";
	echo "<tr><td colspan=2></td><td><input type='submit' value='Make Booking' /></td></tr>";
	echo "</tfoot>";
	echo "\n</table>";
	echo "\n</form>";
	echo "\n<script>showitems();</script>";
}
else
{
echo "<p>There are no spaces left for this event</p>";
}
include ('inc/inc_foot.php');