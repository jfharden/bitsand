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
$bLoginCheck = False;


include ('inc/inc_head_db.php');
include ('inc/inc_head_html.php');
include ('inc/inc_commonqueries.php');
$db_prefix = DB_PREFIX;

$sql = "SELECT evEventID, evEventName, evEventDate FROM {$db_prefix}events WHERE evBookingsOpen <= '".$today."' AND evBookingsClose >= '".$today."' ORDER BY evEventDate DESC";
$result = ba_db_query ($link, $sql);

echo "<h2>Events open for booking</h2>";

	if (ba_db_num_rows($result) == 0)
	{
		echo "<p>There are no events open for booking</p>";
	}
	else
	{
		echo "<table>";

		while($row = ba_db_fetch_assoc($result))
		{
			echo "<tr><td><a href='eventdetails.php?EventID=".$row['evEventID']."'>". htmlentities (stripslashes ($row['evEventName'])) ."</a></td><td>".$row['evEventDate']."</td></tr>";
		}
		echo "</table>";
	}


$sql = "SELECT evEventID, evEventName, evEventDate FROM {$db_prefix}events WHERE evBookingsClose < '".$today."' ORDER BY evEventDate DESC";
$result = ba_db_query ($link, $sql);

echo "<h2>Events where booking has closed</h2>";

	if (ba_db_num_rows($result) == 0)
	{
		echo "<p>There are no events closed to bookings</p>";
	}
	else
	{
		echo "<table>";

		while($row = ba_db_fetch_assoc($result))
		{
			echo "<tr><td><a href='eventdetails.php?EventID=".$row['evEventID']."'>". htmlentities (stripslashes ($row['evEventName'])) ."</a></td><td>".$row['evEventDate']."</td></tr>";
		}
		echo "</table>";
	}

include ('inc/inc_foot.php');
?>
