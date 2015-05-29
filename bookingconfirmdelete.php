<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File bookingconfirmdelete.php
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

	$bookingid = (int)htmlentities (stripslashes($_GET['BookingID']));

	if ($bookingid == 0)
	{
		$bookingid = (int)htmlentities (stripslashes($_POST['BookingID']));
	}

	$sql = "Select * FROM {$db_prefix}bookings inner join {$db_prefix}events on evEventID = bkEventID where bkPlayerID = $PLAYER_ID and bkID = " . $bookingid;


	$result = ba_db_query ($link, $sql);
	if (ba_db_num_rows($result) == 0)
	{
			$sMsg = "You cannot view this booking";
			$sURL = fnSystemURL () . 'start.php?warn=' . urlencode ($sMsg);
			header ("Location: $sURL");
	}
	$bookinginfo = ba_db_fetch_assoc ($result);

	if ($_POST['cancel'] != null)
	{
		$sURL = fnSystemURL () . 'booking.php?BookingID=' . $bookingid;
		header ("Location: $sURL");
	}
	else if ($_POST['delete'] != null || $_POST['rebook'] != null)
	{
		$sql = "DELETE FROM {$db_prefix}bookingitems WHERE biBookingID = ".$bookingid;
		ba_db_query ($link, $sql);
		$sql = "DELETE FROM {$db_prefix}paymentrequests WHERE prBookingID = ".$bookingid;
		ba_db_query ($link, $sql);
		$sql = "DELETE FROM {$db_prefix}bookings WHERE bkID = ".$bookingid;
		ba_db_query ($link, $sql);
		
		if ($_POST['delete'] != null)
		{
			$sMsg = "Your booking has been cancelled for ".htmlentities (stripslashes ($bookinginfo['evEventName']));
			$sURL = fnSystemURL () . 'start.php?warn=' . urlencode ($sMsg);
			header ("Location: $sURL");
		}
		else
		{
			$sURL = fnSystemURL () . 'eventbooking.php?EventID=' . $bookinginfo['evEventID'];
			header ("Location: $sURL");
		}
	}

	echo "<h2>Delete booking for event - ".htmlentities (stripslashes ($bookinginfo['evEventName']))."</h2>";

?>

<p>Please confirm that you wish to delete your current booking for this event.</p>

<?php
if ($bookinginfo['bkInQueue'] == 1)
{
	echo "<p>You will lose your place in the queue, and may miss out on a place at the event if you continue.</p>";
}
if ($bookinginfo['bkAmountPaid'] > 0)
{
	echo "<p>You will need to request a refund of any payment made, please contact <a href = 'mailto:" .Obfuscate (EVENT_CONTACT_MAIL) . "'>" . EVENT_CONTACT_NAME . "</a>.</p>";
}
?>
	
<form method='POST' action='bookingconfirmdelete.php'>
<table>
<input type="hidden" value="<?php echo $bookingid ?>" name='BookingID'/>
<tr><td><input type="submit" name='rebook' value='Cancel this booking and rebook as a different type'/></td></tr>
<tr><td><input type="submit" name='delete' value='Cancel this booking without rebooking'/></td></tr>
<tr><td><input type="submit" name='cancel' value='Leave this booking'/></td></tr>
</table>
</form>
	
<?php
include ('inc/inc_foot.php');
?>