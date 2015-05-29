<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File eventbookingconfirm.php
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

include ('inc/inc_paypalbutton.php');
?>

<h1><?php echo TITLE?></h1>

<script type='text/javascript'>

function updatePaypalButton()
{
	$('#txtAnotherAmount').attr('value',Number($('#txtAnotherAmount').attr('value')).toFixed(2));
	if ($('#txtAnotherAmount').attr('value') == "NaN") { $('#txtAnotherAmount').attr('value', "0.00");}
	$('#anotheramount').attr('value', $('#txtAnotherAmount').attr('value'));

}

</script>
<?php

	if ((int)$_POST['eventid'] != 0)
	{
		$eventid = htmlentities(stripslashes($_POST['eventid']));
		$sql = "select * from {$db_prefix}events where evEventID = $eventid";
		$result = ba_db_query ($link, $sql);
		$eventinfo = ba_db_fetch_assoc ($result);
		echo "<h2>Your booking has been recorded - ".htmlentities (stripslashes ($eventinfo['evEventName']))."</h2>";
	}
	else
	{
		$sMsg = "The selected event is not currently open for bookings";
		$sURL = fnSystemURL () . 'start.php?warn=' . urlencode ($sMsg);
		header ("Location: $sURL");
	}

	$bookas = htmlentities(stripslashes($_POST['cboBookAs']));
	$sql = "SELECT * from {$db_prefix}bookings where bkPlayerID = $PLAYER_ID and bkEventID = $eventid";

	$result = ba_db_query ($link, $sql);

	if (ba_db_num_rows($result) == 0)
	{
		$sql = "INSERT INTO {$db_prefix}bookings (bkPlayerID, bkEventID, bkBookAs, bkDateICConfirmed, bkDateOOCConfirmed) VALUES ($PLAYER_ID, $eventid, '$bookas', '$today', '$today')";
		ba_db_query ($link, $sql);

		$sql = "SELECT * from {$db_prefix}bookings where bkPlayerID = $PLAYER_ID and bkEventID = $eventid";
		$result = ba_db_query ($link, $sql);
		$bookinginfo = ba_db_fetch_assoc ($result);
	}
	else
	{
		$bookinginfo = ba_db_fetch_assoc ($result);

	}

	$sql = "delete from {$db_prefix}bookingitems where biBookingID = ".$bookinginfo['bkID'];
	ba_db_query ($link, $sql);


	echo "<table>";
	echo "<tr><th>Description</th><th>Quantity</th><th>Cost</th></tr>";
	$bookingtotal = 0;

	$playerbooking = 0;

foreach($_POST as $itemname => $value)
{
	$itemname = htmlentities(stripslashes($itemname));
	$value = htmlentities(stripslashes($value));

	if (strpos($itemname, 'chk') !== false || strpos($itemname, 'cbo') !== false)
	{
		$itemid = str_replace('chk', '', $itemname);
		$itemid = str_replace('cbo', '', $itemid);
		$itemid = (int)$itemid;
		$value = str_replace('on', '1', $value);

		if ($itemid > 0)
		{
			$sql = "select * from {$db_prefix}items where itItemID = $itemid";
			$result = ba_db_query ($link, $sql);
			$iteminfo = ba_db_fetch_assoc ($result);

			if ($value > 0)
			{
				echo "<tr><td>".$iteminfo['itDescription']."</td><td>$value</td><td>&pound;".$iteminfo['itItemCost']."</td></tr>";
				$bookingtotal += ($iteminfo['itItemCost'] * $value);

				//Create item record
				$sql = "INSERT INTO {$db_prefix}bookingitems (biBookingID, biItemID, biQuantity) VALUES (".$bookinginfo['bkID'].", $itemid, $value)";

				ba_db_query ($link, $sql);
				//Update bookinginfo
				if ($iteminfo['itBunk']) { $bookinginfo['bkBunkRequested'] = 1; }
				if ($iteminfo['itMeal']) { $bookinginfo['bkMealTicket'] = 1; }

				if ($iteminfo['itAvailability'] == "Player") { $playerbooking = 1; }
			}
		}

	}

}
	$bookingtotal = number_format($bookingtotal, 2);
	$bookinginfo['bkAmountExpected'] = $bookingtotal;

	echo "<tr class='total'><td colspan=2 class='totallabel'>Total:</td><td>&pound;$bookingtotal</span></td></tr>";
	echo "</table>";

	//Deal with booking queue people
	$bookinginfo['bkInQueue'] = 0;
	if ($eventinfo['evUseQueue'])
	{
		$sql = "select chFaction from {$db_prefix}characters where chPlayerID = $PLAYER_ID";
		$result = ba_db_query ($link, $sql);
		$characterinfo = ba_db_fetch_assoc ($result);
		if ($playerbooking == 1 && $characterinfo['chFaction'] != DEFAULT_FACTION)
		{
			$bookinginfo['bkInQueue'] = 1;
			$queuereason = "your character is not a member of the default faction.";
		}

		//Deal with being over the limit
		if (QUEUE_OVER_LIMIT)
		{
			if ($bookinginfo['bkBookAs'] == "Player") { $spaces = $eventinfo['evPlayerSpaces']; }
			if ($bookinginfo['bkBookAs'] == "Monster") { $spaces = $eventinfo['evMonsterSpaces']; }
			if ($bookinginfo['bkBookAs'] == "Staff") { $spaces = $eventinfo['evStaffSpaces']; }
			$limitsql = "select count(bkID) as BookingCount from {$db_prefix}bookings where bkInQueue = 0 and bkBookAs ='".$bookinginfo['bkBookAs']."' and bkEventID = $eventid ";
			$limitresult = ba_db_query ($link, $limitsql);
			$BookingCount = ba_db_fetch_assoc ($limitresult);
			$BookingCount = $BookingCount['BookingCount'];
			if ($BookingCount > $spaces)
			{
				$bookinginfo['bkInQueue'] = 1;
				$queuereason = "there are no spaces remaining of your booking type.";
			}
		}
	}

	if ($bookinginfo['bkInQueue'] == 0)
	{
		if ($bookingtotal > 0)
		{
			echo "<table class='payment'>";
			echo "<tr><td>Pay Later</td><td><a href='start.php'>Pay later</a></td></tr>";
			if (USE_PAY_PAL) {
				echo "<tr><td>Pay balance of &pound;$bookingtotal via Paypal:</td><td>";
				generatePaypalButton("Event booking - ".$bookinginfo['evEventName'] . " (" . PID_PREFIX . sprintf ('%03s', $PLAYER_ID).")", $PLAYER_ID, $bookingtotal, $bookinginfo['bkID']);
				echo "</td></tr>";
				echo "<tr><td>Pay another amount:</td><td><input type='text' value='".$bookingtotal."' onChange='updatePaypalButton()' id='txtAnotherAmount' class='paypalamount'/>";
				generatePaypalButton("Event booking - ".$bookinginfo['evEventName'] . " (" . PID_PREFIX . sprintf ('%03s', $PLAYER_ID).") - Partial Payment", $PLAYER_ID, $bookingtotal, $bookinginfo['bkID'], 'anotheramount');
				echo "</td></tr>";
			}
			echo "<tr><td>Request payment by another user:</td><td>";
			echo "<form action='start.php' method='post'><input type=hidden name='hBooking' value='".$bookinginfo['bkID']."' /><input type=text name='txtEmail'/><input type='submit' name='btnSubmit' value='Send Email' /></form>";
			echo "</td></tr>";
			echo "</table>";

		}
		else
		{
			echo "<p>Your booking is now complete, as it does not require any payment</p>";
			$bookinginfo['bkDatePaymentConfirmed'] = $today;
		}

	}
	else
	{
		echo "<p>Your booking has been placed in a queue because $queuereason</p>";
		echo "<p>You will be able to pay once it is removed from the queue by an admin.</p>";
		fnMailer ("Event booking - ".$eventinfo['evEventName'] . " (" . PID_PREFIX . sprintf ('%03s', $PLAYER_ID).")\nA booking has been placed in the queue", false);
	}

	//Save booking info
	$sql = "UPDATE {$db_prefix}bookings SET ";
	foreach ($bookinginfo as $key => $value)
	{
		if (!is_numeric($value)) {$value = "'".$value."'"; }
		$sql.= "$key = $value,";
	}
	$sql = substr($sql,0,-1);
	$sql.= " WHERE bkID = ".$bookinginfo['bkID'];

	$result = ba_db_query ($link, $sql);

include ('inc/inc_foot.php');
?>
