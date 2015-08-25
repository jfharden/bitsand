<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File admin/admin_amount.php
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

if ($_POST ['btnSubmit'] != '' && CheckReferrer ('admin_amount.php'))
foreach ($_POST as $key => $value) {
	if (substr ($key, 0, 7) == "hPlayer") {
		$iBookingID = (int) $value;
		$amountpaid = sanitiseAmount($_POST ["txtAmountPaid{$value}"]);
		$amountexpected = sanitiseAmount($_POST ["txtAmountExpected{$value}"]);
		$sql_update = "UPDATE {$db_prefix}bookings SET bkAmountPaid = $amountpaid, bkAmountExpected = $amountexpected WHERE bkID = " . $iBookingID;
		ba_db_query ($link, $sql_update);
	}
}

//Get list of players that have confirmed their booking
$sql = "SELECT bkID, " .
	"plPlayerID, " .
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
	"bkAmountExpected " .
	"FROM {$db_prefix}players, {$db_prefix}characters, {$db_prefix}bookings " .
	"WHERE plPlayerID = chPlayerID AND chPlayerID = bkPlayerID and bkEventID = $eventid";

$result = ba_db_query ($link, $sql);
?>
<script src="../inc/sorttable.js" type="text/javascript"></script>

<h1><?php echo TITLE?> - Manage Amounts Paid</h1>
<p>
<a href = 'admin_manageevent.php?EventID=<?php echo $eventinfo['evEventID'];?>'>Return to event management for - <?php echo htmlentities (stripslashes ($eventinfo['evEventName']));?></a>
</p>

<h2><?php echo htmlentities (stripslashes ($eventinfo['evEventName']));?></h2>


<p>
The following people have confirmed a booking. If the Payment date is blank, they are not marked as paid. Click on a column header to sort by that column.
</p>
<p>
<!--The buttons in the form can be used to quickly set values. <strong>E</strong> sets the paid value to the current expected value. <strong>P</strong> sets the expected value to the current paid value. <strong>C</strong> sets the value to the current amount for the type of the booking. Changes are saved when the form is submitted.-->
</p>
<form action = 'admin_amount.php?EventID=<?php echo $eventinfo['evEventID'];?>' method = 'post'>

<table border = '1' class="sortable">
<tr>
<th>Player ID</th>
<th>OOC First Name</th>
<th>OOC Surname</th>
<th>Amount Paid</th>
<th>Amount Expected</th>
<th>Booking As</th>
<th>Date Payment Confirmed</th>
<th>Meal ticket?</th>
</tr>

<?php
while ($row = ba_db_fetch_assoc ($result)) {
	echo "<tr class = 'highlight'>";
	if ($row ['bkMealTicket'] == 1)
		$sChecked = ' checked';
	else
		$sChecked = '';
	echo "<input type = 'hidden' name = 'hPlayer" . $row ['bkID'] . "' value = '" . $row ['bkID'] . "'>";
	echo "<input type = 'hidden' name = 'hMeal" . $row ['bkID'] . "' value = '" . $row ['bkMealTicket'] . "'>";

	echo "<td><a href = 'admin_viewdetails.php?pid=" . $row ['plPlayerID'] . "'>";
	echo PID_PREFIX . sprintf ('%03s', $row ['plPlayerID']) . "</a></td>\n";
	echo "<td>" . htmlentities (stripslashes ($row ['plFirstName'])) . "</td>\n";
	echo "<td>" . htmlentities (stripslashes ($row ['plSurname'])) . "</td>\n";

	echo "<td><input type = 'textbox' size = 5 name='txtAmountPaid" . $row ['bkID'] . "' value='".$row ['bkAmountPaid']."' />";
	//echo "<input type='button' value='E' onClick=\"changeAmounts(". $row ['bkID'] .",'".$row ['bkBookAs']."', 'P', 'E')\" />";
	//echo "<input type='button' value='C' onClick=\"changeAmounts(". $row ['bkID'] .",'".$row ['bkBookAs']."', 'P', 'C')\"/>";
	echo "</td>\n";
	echo "<td><input type = 'textbox' size = 5 name='txtAmountExpected" . $row ['bkID'] . "' value='".$row ['bkAmountExpected']."' />";
	//echo "<input type='button' value='P' onClick=\"changeAmounts(". $row ['bkID'] .",'".$row ['bkBookAs']."', 'E', 'P')\" />";
	//echo "<input type='button' value='C' onClick=\"changeAmounts(". $row ['bkID'] .",'".$row ['bkBookAs']."', 'E', 'C')\" />";
	echo"</td>\n";

	echo "<td>" . htmlentities (stripslashes ($row ['bkBookAs'])) . "</td>\n";

	if ($row ['bkDatePaymentConfirmed'] != '0000-00-00')
	{
		$PaymentDate = $row ['bkDatePaymentConfirmed'];
	}
	else
	{
		$PaymentDate = '';
	}
	echo "<td>" . htmlentities (stripslashes ($PaymentDate)) . "</td>\n";
	if ($row ['bkMealTicket'] == 0)
		echo "<td>No</td>";
	else
		echo "<td>Yes</td>";

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