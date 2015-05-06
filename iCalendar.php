<?php
/*
Bitsand - a web-based booking system for LRP events
Copyright (C) 2006 - 2012 The Bitsand Project (http://bitsand.googlecode.com/)

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
include ('inc/inc_commonqueries.php');
$db_prefix = DB_PREFIX;

$sql = "SELECT evEventID, evEventName, evBookingsOpen, evBookingsClose, evEventDate, evEventDetails FROM {$db_prefix}events ORDER BY evEventDate DESC";
$result = ba_db_query ($link, $sql);

echo "BEGIN:VCALENDAR\r\n";
echo "VERSION:2.0\r\n";
echo "PRODID:Bitsand http://bitsand.googlecode.com/\r\n";
echo "X-WR-CALDESC:" . SYSTEM_NAME . "\r\n";

while($row = ba_db_fetch_assoc($result)) {

	// Common elements
	$common = "";
	$common .= "ORGANIZER;CN=" . stripslashes(EVENT_CONTACT_NAME) . ":MAILTO:" . stripslashes(EVENT_CONTACT_MAIL) . "\r\n";
	$common .= "DESCRIPTION:" . SYSTEM_URL . "eventdetails.php?EventID=" . $row["evEventID"] . "\r\n";
	$common .= "END:VEVENT\r\n";

	// iCal event for bookings opening
	$ts = strtotime ($row["evBookingsOpen"]);
	echo "BEGIN:VEVENT\r\n";
	echo "UID:" . SYSTEM_URL . "eventdetails.php?EventID=" . $row["evEventID"] . "&bookings=open\r\n";
	echo "DTSTAMP:" . date("Ymd", $ts) . "T000001\r\n";
	echo "DTSTART;VALUE=DATE:" . date("Ymd", $ts) . "\r\n";
	echo "DTEND;VALUE=DATE:" . date("Ymd", $ts) . "\r\n";
	echo "SUMMARY:" . stripslashes($row["evEventName"]) . " - bookings open\r\n";
	echo $common;

	// iCal event for bookings closing
	$ts = strtotime ($row["evBookingsClose"]);
	echo "BEGIN:VEVENT\r\n";
	echo "UID:" . SYSTEM_URL . "eventdetails.php?EventID=" . $row["evEventID"] . "&bookings=close\r\n";
	echo "DTSTAMP:" . date("Ymd", $ts) . "T000001\r\n";
	echo "DTSTART;VALUE=DATE:" . date("Ymd", $ts) . "\r\n";
	echo "DTEND;VALUE=DATE:" . date("Ymd", $ts) . "\r\n";
	echo "SUMMARY:" . stripslashes($row["evEventName"]) . " - bookings close\r\n";
	echo $common;

	// iCal event for the actual event
	$ts = strtotime ($row["evEventDate"]);
	echo "BEGIN:VEVENT\r\n";
	echo "UID:" . SYSTEM_URL . "eventdetails.php?EventID=" . $row["evEventID"] . "\r\n";
	echo "DTSTAMP:" . date("Ymd", $ts) . "T000001\r\n";
	echo "DTSTART;VALUE=DATE:" . date("Ymd", $ts) . "\r\n";
	echo "DTEND;VALUE=DATE:" . date("Ymd", $ts) . "\r\n";
	echo "SUMMARY:" . stripslashes($row["evEventName"]) . "\r\n";
	echo $common;
}

echo "END:VCALENDAR\r\n";
?>
