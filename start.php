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

include ('inc/inc_head_db.php');
$db_prefix = DB_PREFIX;


if ($_POST ['btnSubmit'] != '' && (CheckReferrer ('booking.php') || CheckReferrer ('eventbookingconfirm.php'))) {
$bookingid = (int)$_POST['hBooking'];
$email = htmlentities(stripslashes($_POST['txtEmail']));

	if ($bookingid > 0)
	{
		$sBody = "You have recieved a request to make a payment for an event at " . SYSTEM_NAME . ". " .
			"\n\nIf you have an account then please login to make this payment.\n" .
			"Otherwise you must create an account if you wish to make a payment, using this e-mail address.\n\n" .
			"If you have recieved this request in error, then please ignore it, or contact ".EVENT_CONTACT_NAME." (".EVENT_CONTACT_MAIL.") if you have any questions.";
			"\n\n" . fnSystemURL ();
			mail ($email, SYSTEM_NAME . ' - Payment Request', $sBody, "From:" . SYSTEM_NAME . " <" . EVENT_CONTACT_MAIL . ">");

		$sql = "INSERT INTO {$db_prefix}paymentrequests (prEmail, prBookingID) VALUES ('$email', $bookingid)";
		ba_db_query($link, $sql);
	}

}


if ($_GET ['green'] != '')
	$sGreen .= htmlentities ($_GET ['green']);
if ($_GET ['warn'] != '')
	$sWarn .= htmlentities ($_GET ['warn']);

include ('inc/inc_head_html.php');
?>

<h1><?php echo TITLE?></h1>

<?php
echo ANNOUNCEMENT_MESSAGE;

if ($sGreen != '')
	echo "<p class = 'green'>$sGreen</p>\n";
if ($sWarn != '')
	echo "<p class = 'warn'>$sWarn</p>\n";

//Check if player has entered IC & OOC data
$sql = "SELECT chName FROM {$db_prefix}characters WHERE chPlayerID = $PLAYER_ID";
$result = ba_db_query ($link, $sql);
$iIC = ba_db_num_rows ($result);

//Check for OOC data needs to check for some actual data, as a record will always exist
$sql = "SELECT plFirstName " .
	"FROM {$db_prefix}players " .
	"WHERE plPlayerID = $PLAYER_ID";
$result = ba_db_query ($link, $sql);
$row = ba_db_fetch_assoc ($result);

if ($row ['plFirstName'] != '')
	$bOOC = True;
else
	$bOOC = False;

echo "<p>\n";

echo "You can pay ";
if (USE_PAY_PAL)
	echo "via PayPal or ";
echo "by cheque, cash or postal order. Once your payment has been confirmed (by an admin) you will be listed in the booking list for that event.\n";
echo "</p>\n<p>\n";

echo "<h2>Player Details</h2>";
echo "<p><a href = 'ooc_form.php'>";
if ($bOOC == true) { echo "Edit ";} else {echo "Enter ";}
echo "OOC information</a></p>\n";
?>
</p>

<?php
echo "<p><a href = 'ic_form.php'>";
if ($iIC == 1) { echo "Edit "; } else {echo "Enter ";}
echo "IC information</a><br>\n";

if ($bOOC)
{
	echo "<h2>Events</h2>";
	echo "<p><a href='iCalendar.php'>iCalendar feed of events</a></p>\n";

	$sql = "select bkEventID from {$db_prefix}bookings where bkPlayerID = $PLAYER_ID";
	$result = ba_db_query ($link, $sql);
	$bookedeventids = "";
	while ($row = ba_db_fetch_assoc ($result))
	{
		$bookedeventids .= $row['bkEventID'] . ",";
	}

	$bookedeventids .= "-1";

	$eventlinks = "";

	$sql = "Select * FROM {$db_prefix}events where evBookingsOpen <= '".$today."' and evEventDate >= '".$today."' and evEventID not in ($bookedeventids)";
	$result = ba_db_query ($link, $sql);
	$eventlinks.= "<table>";
	$availableevents = 0;
	while ($row = ba_db_fetch_assoc ($result))
	{
		$availableevents++;
		$eventlinks.= "<tr><td><a href='eventdetails.php?EventID=".$row['evEventID']."'>". htmlentities (stripslashes ($row['evEventName']))."</a></td><td>".$row['evEventDate']."</td><td>";
		if ($row['evBookingsClose'] >= $today) { $eventlinks .= "<a href='eventbooking.php?EventID=".$row['evEventID']."'>Book Now!</a>"; }
		else { $eventlinks .= "Bookings closed"; }
		$eventlinks.="</td></tr></td>";
	}
	$eventlinks.= "</table>";
	if ($availableevents == 0) { $eventlinks = "<p>There are no upcoming events that you have not booked for.</p>"; }

	echo $eventlinks;

	echo "<h2>Your Bookings</h2>";
	$sql = "Select * FROM {$db_prefix}bookings inner join {$db_prefix}events on bkEventID = evEventID where bkPlayerID = $PLAYER_ID and evEventDate >= '".$today."'";
	$result = ba_db_query ($link, $sql);
	if (ba_db_num_rows($result) == 0)
	{
		echo "<p>You have no recorded bookings for upcoming events.</p>";
	}
	else
	{

		echo "<table>";
		while ($row = ba_db_fetch_assoc ($result))
		{
			echo "<tr><td><a href='eventdetails.php?EventID=".$row['evEventID']."'>". htmlentities (stripslashes ($row['evEventName']))."</a></td><td>".$row['evEventDate']."</td><td>".str_replace('Staff', $stafftext, $row['bkBookAs'])."</td><td><a href='booking.php?BookingID=".$row['bkID']."'>View Booking</a></td></tr></td>";
		}
		echo "</table>";

	}
}
else
{
	echo "<h2>Events</h2>";
	echo "You must enter your IC and OOC details before booking any events";
}

$sql = "select bkID, bookingplayer.plFirstName, bookingplayer.plSurname, chPreferredName, evEventName, bkBookAs from {$db_prefix}paymentrequests inner join {$db_prefix}bookings on prBookingID = bkID inner join {$db_prefix}events on bkEventID = evEventID inner join {$db_prefix}players on prEmail =  {$db_prefix}players.plEmail inner join {$db_prefix}players as bookingplayer on bkPlayerID = bookingplayer.plPlayerID inner join {$db_prefix}characters on chPlayerID = bookingplayer.plPlayerID where {$db_prefix}players.plPlayerID = $PLAYER_ID";

$result = ba_db_query ($link, $sql);
if (ba_db_num_rows($result) > 0)
{
	echo "<h2>Payment Requests</h2>";
	echo "<p>You have recieved requests from the following users to make payment for their event</p>";
		echo "<table>";
		while ($row = ba_db_fetch_assoc ($result))
		{
			echo "<tr>";
			echo "<td>".$row['plFirstName']." ".$row['plSurname']."</td>";
			echo "<td>".$row['chPreferredName']."</td>";
			echo "<td>". htmlentities (stripslashes ($row['evEventName']))."</td>";
			echo "<td>".str_replace('Staff', $stafftext, $row['bkBookAs'])."</td>";
			echo "<td><a href='booking.php?BookingID=".$row['bkID']."'>Pay Now!</a></td>";

			echo "</tr>";
		}
		echo "</table>";
}


include ('inc/inc_foot.php');
?>
