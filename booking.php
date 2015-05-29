<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File inc/inc_config_dist.php.php
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

	$bookingid = (int)htmlentities (stripslashes($_GET['BookingID']));
	$sql = "Select * FROM {$db_prefix}bookings inner join {$db_prefix}events on evEventID = bkEventID where bkPlayerID = $PLAYER_ID and bkID = " . $bookingid;

	$otherpaymentsql = "select * from {$db_prefix}paymentrequests inner join {$db_prefix}players on plEmail = prEmail inner join {$db_prefix}bookings on bkID = prBookingID inner join {$db_prefix}events on evEventID = bkEventID where bkID = $bookingid and plPlayerID = $PLAYER_ID";
	$paymentrequest = 0;

	$result = ba_db_query ($link, $sql);
	if (ba_db_num_rows($result) == 0)
	{
		$result = ba_db_query ($link, $otherpaymentsql);
		$paymentrequest = 1;
		if (ba_db_num_rows($result) == 0)
		{
			$sMsg = "You cannot view this booking";
			$sURL = fnSystemURL () . 'start.php?warn=' . urlencode ($sMsg);
			header ("Location: $sURL");
		}
	}
	$bookinginfo = ba_db_fetch_assoc ($result);

?>

<script type='text/javascript'>

function updatePaypalButton()
{
	$('#txtAnotherAmount').attr('value',Number($('#txtAnotherAmount').attr('value')).toFixed(2));
	if ($('#txtAnotherAmount').attr('value') == "NaN") { $('#txtAnotherAmount').attr('value', "0.00");}
	$('#anotheramount').attr('value', $('#txtAnotherAmount').attr('value'));

}

</script>

<?php

	echo "<h2>Your booking details for event - ".htmlentities (stripslashes ($bookinginfo['evEventName']))."</h2>";

	$sql = "select * from {$db_prefix}bookingitems inner join {$db_prefix}items where biItemID = itItemID and biBookingID = " . $bookingid;
	$result = ba_db_query ($link, $sql);
	echo "<table>";
	echo "<tr><th>Description</th><th>Quantity</th><th>Cost</th></tr>";
	while ($row = ba_db_fetch_assoc ($result))
	{
		echo "<tr><td>".$row['itDescription']."</td><td>".$row['biQuantity']."</td><td>&pound;".$row['itItemCost']."</td></tr>";
	}

	echo "<tr class='total'><td colspan=2 class='totallabel'>Total:</td><td>&pound;".$bookinginfo['bkAmountExpected']."</td></tr>";
	echo "</table>";

	if ($bookinginfo['bkDatePaymentConfirmed'] != '0000-00-00')
	{
		echo "<p>Your booking is recorded as paid.</p>";
	}

	if ($bookinginfo['bkInQueue'] == 1)
	{
	 	echo "<p>Your booking has been placed in a queue. You will be able to pay once it is removed from the queue by an admin.</p>";
		echo "<p>If you would like to cancel your queued booking and rebook a different ticket type then <a href='bookingconfirmdelete.php?BookingID=".$bookingid."'>click here</a>.";
	}
	else if ($bookinginfo['bkAmountExpected'] > $bookinginfo['bkAmountPaid'])
	{
		$remaining = number_format($bookinginfo['bkAmountExpected'] - $bookinginfo['bkAmountPaid'],2);
		if (USE_PAY_PAL) {
			echo "<table class='payment'><tr><td>Pay balance of &pound;$remaining via Paypal:</td><td>";
			generatePaypalButton("Event booking - ".$bookinginfo['evEventName'] . " (" . PID_PREFIX . sprintf ('%03s', $bookinginfo['bkPlayerID']).")", $bookinginfo['bkPlayerID'], $remaining, $bookinginfo['bkID']);
			echo "</td></tr>";
			echo "<tr><td>Pay another amount:</td><td><input type='text' value='".$remaining."' onChange='updatePaypalButton()' id='txtAnotherAmount' class='paypalamount'/>";
			generatePaypalButton("Event booking - ".$bookinginfo['evEventName'] . " (" . PID_PREFIX . sprintf ('%03s', $bookinginfo['bkPlayerID']).") - Partial Payment", $bookinginfo['bkPlayerID'], $remaining, $bookinginfo['bkID'], 'anotheramount');
			echo "</td></tr>";
		}
		if ($paymentrequest == 0)
		{
			echo "<tr><td>Request payment by another user:</td><td>";
			echo "<form action='start.php' method='post'><input type=hidden name='hBooking' value='".$bookinginfo['bkID']."' /><input type=text name='txtEmail' /><input type='submit' name='btnSubmit' value='Send Email' /></form>";
			echo "</td></tr>";
			echo "<tr><td>Cancel booking:</td><td><a href='bookingconfirmdelete.php?BookingID=".$bookingid."'>Cancel</a></td>";
		}
		echo "</table>";
	}

	if ($bookinginfo["bkBunkAllocated"]) {
		echo "<p>A bunk has been allocated to you</p>";
	}


?>

<?php
include ('inc/inc_foot.php');
?>
