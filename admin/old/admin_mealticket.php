<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File admin/admin_mealticket.php
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

$key = CRYPT_KEY;


if ($_POST ['btnSubmit'] != '' && CheckReferrer ('admin_mealticket.php'))
foreach ($_POST as $key => $value) {
	if (substr ($key, 0, 8) == "hBooking") {
		$meal = (int) $_POST ["chkPl{$value}"];
		if ($meal > 0) { $meal = 1; }
		else { $meal = 0; }

		$payongate = (int) $_POST ["chkPayOnGate{$value}"];
		if ($payongate > 0) { $payongate = 1; }
		else { $payongate = 0; }

		$iBookingID = (int) $value;
		$amountpaid = sanitiseAmount($_POST ["txtAmountPaid{$value}"]);
		$amountexpected = sanitiseAmount($_POST ["txtAmountExpected{$value}"]);
		$sql_update = "UPDATE {$db_prefix}bookings SET bkMealTicket = $meal, bkAmountPaid = $amountpaid, bkAmountExpected = $amountexpected, bkPayOnGate = $payongate WHERE bkID = " . $iBookingID;
		ba_db_query ($link, $sql_update);

		removeItem($iBookingID, 'meal');
		if ($meal > 0) { addItem($iBookingID, 'meal'); }
	}
}

//Get list of players that have confirmed their booking
$sql = "SELECT plPlayerID, " .
	"plFirstName, " .
	"plSurname, " .
	"bkBookAs, " .
	"bkBunkRequested, " .
	"bkMealTicket, " .
	"chName, " .
	"bkDateOOCConfirmed, " .
	"bkDateICConfirmed, " .
	"bkDatePaymentConfirmed, " .
	"bkAmountPaid, " .
	"bkAmountExpected, " .
	"bkPayOnGate, ".
	"bkID ".
	"FROM {$db_prefix}players, {$db_prefix}characters, {$db_prefix}bookings " .
	"WHERE plPlayerID = chPlayerID AND chPlayerID = bkPlayerID and bkEventID = $eventid";

$result = ba_db_query ($link, $sql);
?>
<script src="../inc/sorttable.js" type="text/javascript"></script>

<?php

$playermeal = getItemCost('meal', 'player', $eventid);
$monstermeal = getItemCost('meal', 'monster', $eventid);
$staffmeal = getItemCost('meal', 'staff', $eventid);

echo '<script type="text/javascript">';
echo "
	function changeAmounts(id, bookingtype, updateexpected)
	{
		var playermeal = $playermeal;
		var monstermeal = $monstermeal;
		var staffmeal = $staffmeal;

		var meal = document.forms[0].elements['chkPl' + id].checked;
		var gate = document.forms[0].elements['chkPayOnGate' + id].checked;
		var amount1 = 0;
		var amount2 = 0;

		if (bookingtype == 'Staff')
		{
			amount1 = staffmeal;
		}
		else if (bookingtype == 'Monster')
		{
			amount1 = monstermeal;
		}
		else
		{
			amount1 = playermeal;
		}

		amount1 = amount1.toFixed(2);

		if (updateexpected)
		{
			if (meal)
			{
				document.forms[0].elements['txtAmountExpected' + id].value = Number(document.forms[0].elements['txtAmountExpected' + id].value) + Number(amount1);
			}
			else
			{
				document.forms[0].elements['txtAmountExpected' + id].value -= Number(amount1);
			}
		}

		if (gate)
		{
			document.forms[0].elements['txtAmountPaid' + id].value = '0.00';
		}
	}
";


echo "</script>";
?>

<h1><?php echo TITLE?> - Meal Tickets</h1>
<p>
<a href = 'admin_manageevent.php?EventID=<?php echo $eventinfo['evEventID'];?>'>Return to event management for - <?php echo htmlentities (stripslashes ($eventinfo['evEventName']));?></a>
</p>

<h2><?php echo htmlentities (stripslashes ($eventinfo['evEventName']));?></h2>

<p>
The following people have confirmed a booking. If the Payment date is blank, they are not marked as paid. Click on a column header to sort by that column. To request or remove a meal ticket, tick or untick the relevant players boxes and click Submit.
</p>

<form action = 'admin_mealticket.php?EventID=<?php echo $eventinfo['evEventID'];?>' method = 'post'>

<table border = '1' class="sortable">
<tr>
<th>Request Meal Ticket</th>
<th>Meal Ticket Requested?</th>
<th>Amount Paid</th>
<th>Amount Expected</th>
<th>Pay on Gate</th>
<th>Player ID</th>
<th>OOC First Name</th>
<th>OOC Surname</th>
<th>IC Name</th>
<th>Booking As</th>
<th>Date Details Confirmed</th>
<th>Date Payment Confirmed</th>
</tr>

<?php
while ($row = ba_db_fetch_assoc ($result)) {
	echo "<tr class = 'highlight'>";
	if ($row ['bkMealTicket'] != 0)
		$sChecked = ' checked';
	else
		$sChecked = '';
	echo "<input type = 'hidden' name = 'hBooking" . $row ['bkID'] . "' value = '" . $row ['bkID'] . "'>\n";
	echo "<td class = 'mid'><input type = 'checkbox' name = 'chkPl{$row ['bkID']}' value = '{$row ['bkID']}'{$sChecked} onClick=\"changeAmounts(". $row ['bkID'] .",'".$row ['bkBookAs']."', 1)\"></td>\n";
	if ($row ['bkMealTicket'] == 0)
		echo "<td>No</td>";
	else
		echo "<td>Yes</td>";
	echo "<td><input size = 5 name='txtAmountPaid" . $row ['bkID'] . "' value='".$row ['bkAmountPaid']."' /></td>\n";
	echo "<td><input size = 5 name='txtAmountExpected" . $row ['bkID'] . "' value='".$row ['bkAmountExpected']."' /></td>\n";

	if ($row ['bkPayOnGate'] == 1)
		$sChecked = ' checked';
	else
		$sChecked = '';

	echo "<td class = 'mid'><input type = 'checkbox' name = 'chkPayOnGate{$row ['bkID']}' value = '{$row ['bkID']}'{$sChecked}  onClick=\"changeAmounts(". $row ['bkID'] .",'".$row ['bkBookAs']."',0)\"></td>\n";

	echo "<td><a href = 'admin_viewdetails.php?pid=" . $row ['plPlayerID'] . "'>";
	echo PID_PREFIX . sprintf ('%03s', $row ['plPlayerID']) . "</a></td>\n";
	echo "<td>" . htmlentities (stripslashes ($row ['plFirstName'])) . "</td>\n";
	echo "<td>" . htmlentities (stripslashes ($row ['plSurname'])) . "</td>\n";
	echo "<td>" . htmlentities (stripslashes ($row ['chName'])) . "</td>\n";
	echo "<td>" . htmlentities (stripslashes ($row ['bkBookAs'])) . "</td>\n";
	echo "<td>" . htmlentities (stripslashes ($row ['bkDateOOCConfirmed'])) . "</td>\n";

	if ($row ['bkDatePaymentConfirmed'] != '0000-00-00')
	{
		$PaymentDate = $row ['bkDatePaymentConfirmed'];
	}
	else
	{
		$PaymentDate = '';
	}
	echo "<td>" . htmlentities (stripslashes ($PaymentDate)) . "</td>\n";
	echo "</tr>\n";
}
?>

</table>

<p>
<input type = 'submit' value = 'Submit' name = 'btnSubmit'>&nbsp;
<input type = 'reset' value = 'Reset'>
</p>
</form>

<?php
include ('../inc/inc_foot.php');