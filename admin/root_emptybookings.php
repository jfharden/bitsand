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

include ('../inc/inc_head_db.php');
require ('../inc/inc_admin.php');
require ('../inc/inc_root.php');
include ('../inc/inc_head_html.php');

$sWarn = '';
if ($_POST ['btnDelete'] != '' && CheckReferrer ('root_emptybookings.php')) {
	if ($_POST ['txtConfirm'] == 'CONFIRM') {
		//Remove all records from bookings table
		$sql = "DELETE FROM " . DB_PREFIX . "bookings";
		ba_db_query ($link, $sql);
		//Set "Bunk Requested" and "Bunk Assigned" to False
		$sql = "UPDATE " . DB_PREFIX . "players SET plBunkRequested = 0, plBunkAssigned = 0";
		ba_db_query ($link, $sql);
		$sWarn = "All bookings deleted";
	}
	else
		$sWarn = "CONFIRM was not entered correctly in the text box. It must be all upper case.";
}
?>

<script type="text/javascript">
<!--
function fnConfirm () {
	return confirm ("Are you sure you want to remove all bookings?")
}
// -->
</script>

<h1><?php echo TITLE?> - Remove All Bookings</h1>

<p>
<a href = 'admin.php'>Admin</a>
</p>

<?php
if ($sWarn != '') {
	echo "<span class = 'warn'>$sWarn</span>";
}
?>

<p>
This page can be used to remove all bookings currently in the system, so that bookings can be made for the next event. <b>This should only be done as part of the preparations for a new event.</b> If it is done by mistake, all bookings will need to be re-booked.
</p>

<form action = 'root_emptybookings.php' method = 'post' onsubmit = 'return fnConfirm ()'>
<p>
To guard against mistakes, enter <b>confirm</b> (in capital letters) in the box below, then click "Delete"<br>
<input name = 'txtConfirm'>&nbsp;
<input type = 'submit' value = 'Delete' name = 'btnDelete'>
</p>
</form>

<?php
include ('../inc/inc_foot.php');
?>
