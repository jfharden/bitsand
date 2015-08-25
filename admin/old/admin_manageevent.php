<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File admin/manageevent.php
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

$eventinfo = getEventDetails($_GET["EventID"], 0, 'admin.php');
?>

<h1><?php echo TITLE?> - Manage Event</h1>
<p>
<a href = 'admin.php'>Admin</a>
</p>
<h2><?php echo htmlentities (stripslashes ($eventinfo['evEventName']));?></h2>

<p>
<?php
echo "<a href = '../eventdetails.php?EventID=".$eventinfo['evEventID']."'>View event details</a> as a player will see them";
?>
</p>

<p>
The reports below are based on the people that have booked for the upcoming event <i>only</i>. People that have confirmed IC &amp; OOC details, and been marked as paid are considered to have booked. The &quot;Mark payments received&quot; report obviously only includes people that haven't been marked as paid.
</p>

<p>
<?php
echo "<a href = 'admin_editeventdetails.php?EventID=".$eventinfo['evEventID']."'>Edit event details</a><br />\n";
HelpLink ('help_payments_received.php');
echo " <a href = 'admin_markpaid.php?EventID=".$eventinfo['evEventID']."'>Mark payments received</a>, or to pay on the gate<br />\n";
HelpLink ('help_assign_bunks.php');
echo " <a href = 'admin_bunks.php?EventID=".$eventinfo['evEventID']."'>Assign bunks</a><br />\n";
HelpLink ('help_request_meal_ticket.php');
echo " <a href = 'admin_mealticket.php?EventID=".$eventinfo['evEventID']."'>Manage meal tickets</a><br />\n";
echo "<a href = 'admin_amount.php?EventID=".$eventinfo['evEventID']."'>Manage amounts paid</a><br />\n";
echo "<a href = 'admin_cards.php?EventID=".$eventinfo['evEventID']."'>Cards, Lore Sheets, Meal Tickets &amp; bunks required</a><br />\n";
echo "<a href = 'admin_marshal.php?EventID=".$eventinfo['evEventID']."'>Manage marshal status</a><br />\n";
?>

<a href = 'admin_booking_queue.php?EventID=<?=$eventinfo['evEventID'];?>'>Manage booking queue</a><br />
<a href = 'admin_bookings_csv.php?EventID=<?=$eventinfo['evEventID'];?>'>Save bookings to csv</a> (use as a mail merge source for creating character cards, etc)<br />
<a href = 'admin_items_csv.php?EventID=<?=$eventinfo['evEventID'];?>'>Save item bookings to csv</a> - this CSV lists all items (including tickets) bought<br />
<a href = 'admin_bookingstatus.php?EventID=<?=$eventinfo['evEventID'];?>'>Bookings Status</a>. This page can be used to delete bookings.<br />
<a href = 'admin_signsheet.php?EventID=<?=$eventinfo['evEventID'];?>'>Signature &amp; car registration sheet</a> - print this, then ask players to sign it as they arrive<br />
<a href = 'admin_medical.php?EventID=<?=$eventinfo['evEventID'];?>'>Medical details sheet</a> - print this, then give it to the head first-aider<br />
<a href = 'admin_diet.php?EventID=<?=$eventinfo['evEventID'];?>'>Dietary requirements sheet</a> - print this, then give it to the caterers<br />
<a href = 'admin_booked.php?EventID=<?=$eventinfo['evEventID'];?>'>Players booked</a> - includes car registrations, and has links to full data of any player<br />
<a href = 'admin_addbooking.php?EventID=<?=$eventinfo['evEventID'];?>'>Add booking</a> - add booking manually for players that can't book online.<br />
<a href = 'admin_deleteevent.php?EventID=<?=$eventinfo['evEventID'];?>'>Delete event</a> - remove this event from the system, including any linked bookings.<br />

<?php
if (ALLOW_EVENT_PACK_BY_POST)
{
	echo "<a href = 'admin_eventpackbypost.php?EventID=".$eventinfo['evEventID']."'>Event pack by post</a> - a list of booked players requesting a event pack to be sent by post<br/>";
}
echo "<a href = 'admin_finalconfirmation.php?EventID=".$eventinfo['evEventID']."'>Final confirmation</a> - send an e-mail to all booked players as a final confirmation of their booking.<br />\n";
echo "</p>";
?>

<?php
include ('../inc/inc_foot.php');