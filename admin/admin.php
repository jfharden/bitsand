<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File admin/admin.php
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

if ($_GET ['warn'] != '')
	$sWarn .= htmlentities ($_GET ['warn']);
?>

<h1><?php echo TITLE?> - Admin</h1>

<?php
if ($sWarn != '')
	echo "<p class = 'warn'>$sWarn</p>\n";
?>

<h3>Event Admin</h3>
<a href = "admin_editeventdetails.php">Add a new event</a>
<?php

$sql = "SELECT evEventID, evEventName, evEventDate FROM {$db_prefix}events WHERE evBookingsOpen <= '".$today."' AND evBookingsClose >= '".$today."' ORDER BY evEventDate DESC";
$result = ba_db_query ($link, $sql);

echo "<h4>Events open for booking</h4>";

	if (ba_db_num_rows($result) == 0)
	{
		echo "<p>There are no events open for booking</p>";
	}
	else
	{
		echo "<table>";

		while($row = ba_db_fetch_assoc($result))
		{
			echo "<tr><td><a href='admin_manageevent.php?EventID=".$row['evEventID']."'>".htmlentities (stripslashes ($row['evEventName']))."</a></td><td>".$row['evEventDate']."</td></tr>";
		}
		echo "</table>";
	}


$sql = "SELECT evEventID, evEventName, evEventDate FROM {$db_prefix}events WHERE evBookingsClose < '".$today."' ORDER BY evEventDate DESC";
$result = ba_db_query ($link, $sql);

echo "<h4>Events where booking has closed</h4>";

	if (ba_db_num_rows($result) == 0)
	{
		echo "<p>There are no events closed to bookings</p>";
	}
	else
	{
		echo "<table>";

		while($row = ba_db_fetch_assoc($result))
		{
			echo "<tr><td><a href='admin_manageevent.php?EventID=".$row['evEventID']."'>".htmlentities (stripslashes ($row['evEventName']))."</a></td><td>".$row['evEventDate']."</td></tr>";
		}
		echo "</table>";
	}

$sql = "SELECT evEventID, evEventName, evEventDate FROM {$db_prefix}events WHERE evBookingsOpen > '".$today."' ORDER BY evEventDate DESC";
$result = ba_db_query ($link, $sql);

echo "<h4>Events where booking has not yet opened</h4>";

	if (ba_db_num_rows($result) == 0)
	{
		echo "<p>There are no events where booking has not yet opened</p>";
	}
	else
	{
		echo "<table>";

		while($row = ba_db_fetch_assoc($result))
		{
			echo "<tr><td><a href='admin_manageevent.php?EventID=".$row['evEventID']."'>".htmlentities (stripslashes ($row['evEventName']))."</a></td><td>".$row['evEventDate']."</td></tr>";
		}
		echo "</table>";
	}
?>

<h3>Player Admin</h3>

<p>
The reports below are based on <i>all</i> the data in the database, not just those people that have booked for the upcoming event.
</p>

<p>
<?php
HelpLink ('help_add_new_user.php');
echo " <a href = 'admin_adduser.php'>Add a new user</a> (use when players book with paper form)<br />\n";?>
<a href = "admin_search.php">Search</a> - includes links to edit player's IC and/or OOC details or change their password<br>
<a href = 'admin_disabled.php'>Disabled accounts</a> - includes links to reset the password and re-enable the account<br>
Characters: <a href = "admin_chars_export.php?action=save">Save to csv</a> : <a href = "admin_chars_export.php?action=view">View online</a><br>
Players: <a href = "admin_players_export.php?action=save">Save to csv</a> : <a href = "admin_players_export.php?action=view">View online</a><br>
<a href = 'admin_notes.php'>Players with admin notes or illegal combinations of skills</a>
</p>

<h3>Site Admin etc</h3>

<p>
<a href = "admin_faq.php">Add/edit/delete FAQ items</a><br>
<a href = 'admin_ancestors.php'>Add/edit/delete ancestors</a><br>
<a href = 'admin_groups.php'>Add/edit/delete groups</a><br>
<a href = 'admin_locations.php'>Add/edit/delete locations</a><br>
</p>

<p>
<a href = 'admin_changeconfig.php'>Change system configuration settings</a><br>
<a href = 'admin_config_db_test.php'>Check system configuration settings</a>
</p>

<?php
if (ROOT_USER_ID == $PLAYER_ID) {
	echo "<p>\n<b>root user pages</b><br>\n";
	echo "<a href = 'root_accesslog.php'>Query access log</a><br>\n";
	echo "<a href = 'root_admins.php'>List and create admin users</a><br>\n";
	echo "<a href = 'root_oldlogins.php'>View (and optionally delete) users that have not logged in recently</a><br>\n";
	echo "<a href = 'root_emptybookings.php'>Remove all bookings</a><br>\n";
	echo "<a href = 'root_keychange.php'>Change database encryption key or password salt</a><br>\n";
	echo "</p>\n";
}

include ('../inc/inc_foot.php');