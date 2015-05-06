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
include ('../inc/inc_admin.php');

$sGreen = "";
$db_prefix = DB_PREFIX;
if ($_POST ['btnSubmit'] != '' && CheckReferrer ('admin_adduser.php')) {
	//Insert new user
	$sFirstName = ba_db_real_escape_string ($link, $_POST ['txtFirstName']);
	$sSurname = ba_db_real_escape_string ($link, $_POST ['txtSurname']);
	$sql = "INSERT INTO {$db_prefix}players (plFirstName, plSurname, plPassword) VALUES ('$sFirstName', '$sSurname', 'ACCOUNT DISABLED')";
	ba_db_query ($link, $sql);
	$sGreen = htmlentities ($_POST ['txtFirstName']) . " " . htmlentities ($_POST ['txtSurname']) . " has been added.";
}

include ('../inc/inc_head_html.php');
?>

<h1><?php echo TITLE?> - Create User</h1>

<p>
To create a new user, enter the OOC name below. The user's details can then be edited using the <a href = "admin.php">admin pages</a>. Note that the user's account will be disabled, so they will not be able to log on themselves, unless the account is enabled (to enable the account, simply reset the password).
</p>

<form action = "admin_adduser.php" method = "post">
<table class = 'blockmid'>
<tr>
<td>First Name:</td>
<td><input name = 'txtFirstName' class = 'text'></td>
</tr><tr>
<td>Surname:</td>
<td><input name = 'txtSurname' class = 'text'></td>
</tr><tr>
<td colspan = '2'>&nbsp;</td>
</tr><tr>
<td colspan = '2' class = 'mid'><input type = 'submit' name = 'btnSubmit' value = 'Add User'>&nbsp;
<input type = 'reset' value = "Reset form"></td>
</tr>
</table>
</form>

<?php
if ($sGreen != '') {
	echo "<p class = 'green'>" . htmlentities ($sGreen) . "<br>\n";
	echo "<a href = 'admin_search.php?txtFirstName=" . urlencode ($sFirstName) . "&txtSurname=" . urlencode ($sSurname) .
		"&btnSubmit=Search'>Edit details\n</p>";
}
include ('../inc/inc_foot.php');
?>
