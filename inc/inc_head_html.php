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

//Function to obfuscate an e-mail address - returns address made up of HTML entities
function Obfuscate ($email) {
	$sReturn = '';
	for ($i = 0; $i < strlen ($email); $i++) {
		$bit = $email [$i];
		$sReturn .= '&#' . ord ($bit) . ';';
	}
	return $sReturn;
}

//Function to write a help link. The link is quite long (with alt/title, etc) so it
//is easier/quicker to implement it as a function
function HelpLink ($helppage) {
	echo "<a href = '" . SYSTEM_URL . "help/$helppage' target = 'help_popup' onClick = 'wopen(\"" . SYSTEM_URL . "help/$helppage\"); " .
		"return false;'><img src = '" . SYSTEM_URL . "img/help.png' style = 'border:none' " .
		"alt = 'Get help on this feature' title = 'Get help on this feature'></a>";
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<script type = 'text/javascript'>
function wopen(url) {
	var win = window.open(url, 'help_popup', 'width=500, height=300, location=no, menubar=no, status=no, toolbar=no, scrollbars=yes, resizable=yes');
	//win.resizeTo(w, h);
	win.focus();
}
</script>

<?php
//Use HTTPS or HTTP link for JQuery, to avoid browser complaining that page contains non-secure items when using HTTPS
if ($_SERVER ["HTTPS"])
{
	echo "<script src='https://ajax.microsoft.com/ajax/jquery/jquery-1.5.1.min.js' type='text/javascript'></script>\n";
	echo "<script src='https://ajax.aspnetcdn.com/ajax/jquery.ui/1.8.10/jquery-ui.min.js' type='text/javascript'></script>\n";
	echo "<link href='https://ajax.aspnetcdn.com/ajax/jquery.ui/1.8.10/themes/flick/jquery-ui.css' rel='stylesheet' type='text/css' />\n";
}
else
{
	echo "<script src='http://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.5.1.min.js' type='text/javascript'></script>\n";
	echo "<script src='http://ajax.aspnetcdn.com/ajax/jquery.ui/1.8.10/jquery-ui.min.js' type='text/javascript'></script>\n";
	echo "<link href='http://ajax.aspnetcdn.com/ajax/jquery.ui/1.8.10/themes/flick/jquery-ui.css' rel='stylesheet' type='text/css' />\n";
}
echo "<link rel = 'shortcut icon' href = '" . SYSTEM_URL . "favicon.ico'>\n";
echo "<link rel = 'alternate' type = 'application/rss+xml'  href = '" . SYSTEM_URL .
	"bookings_rss.php' title = '" . TITLE . " - Booking List'>\n";

//Different style for some pages.
$sPage = basename ($_SERVER ["SCRIPT_FILENAME"]);

echo "<link rel = 'stylesheet' type = 'text/css' href = '{$CSS_PREFIX}inc/main.css' media = 'screen'>\n";
echo "<link rel = 'stylesheet' type = 'text/css' href = '{$CSS_PREFIX}inc/body.css' media = 'screen'>\n";
if (strpos($sPage, "admin") === 0 || strpos($sPage, "root") === 0)
{
	echo "<link rel = 'stylesheet' type = 'text/css' href = '{$CSS_PREFIX}inc/admin.css' media = 'screen'>\n";
	echo "<link rel = 'stylesheet' type = 'text/css' href = '{$CSS_PREFIX}inc/wysiwyg/jquery.wysiwyg.css' media = 'screen'>\n";
	echo "<link rel = 'stylesheet' type = 'text/css' href = '{$CSS_PREFIX}inc/wysiwyg/jquery.wysiwyg.modal.css' media = 'screen'>\n";
}
echo "<link rel = 'stylesheet' type = 'text/css' href = '{$CSS_PREFIX}inc/print.css' media = 'print'>\n";

if ($metadescription ==  '')
{
	$metadescription = "Online event bookings";
}
echo "<meta name=\"description\" content=\"$metadescription\">";
?>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
<title><?php echo TITLE?></title>

</head>
<?php
if ($sPage == 'index.php' || $sPage == 'start.php')
	echo "<body class = 'event'>\n";
elseif ($sPage == 'ic_form.php')
	echo "<body onload = 'fnCalculate ()'>\n";
else
	echo "<body>\n";

echo"<div class='topshadow' id='topleft'></div>";
echo"<div class='topshadow' id='topmiddle'></div>";
echo"<div class='topshadow' id='topright'></div>";
echo "<div id='logo'><a href='start.php'><img src='img/logo.jpg' alt='Logo'/></a></div>";

echo "<div id='management' class='menu'>";
echo "<ul>\n";
if ($PLAYER_ID == 0)
{
	echo "<li><form action = 'index.php' method = 'post'>";
	echo "Login:&nbsp;";
	echo "<input name = 'txtEmail' value=''/>";
	echo "&nbsp;";
	echo "<input name = 'txtPassword' value='' type='password'/>";
	echo "<input type = 'submit' name = 'btnSubmit' value = '->'>";
	echo "</form></li>";
	echo "<li><a href = '{$CSS_PREFIX}register.php'>Register</a></li>";
}
else
{
	echo "<li><a href = '{$CSS_PREFIX}change_password.php'>Manage Account</a></li>\n";
	echo "<li><a href = '{$CSS_PREFIX}index.php?green=" . urlencode ("You have been logged out") . "'>Log out</a></li>\n";
}

	echo "</ul>";
	echo "</div>";


echo "<div id='commonactions' class='menu'>";
echo "<ul>\n";

if ($PLAYER_ID == 0)
	echo "<li><a href = '{$CSS_PREFIX}index.php'>Home page</a></li>\n";
else
	echo "<li><a href = '{$CSS_PREFIX}start.php'>Home page</a></li>\n";


$today = date("Y-m-d");
$sql = "select evEventID, evEventName, evEventDate from " . DB_PREFIX . "events where evBookingsOpen <= '".$today."' and evEventDate >= '".$today."'";
$result = ba_db_query ($link, $sql);

	if (ba_db_num_rows($result) == 1)
	{
		$evrow = ba_db_fetch_assoc($result);
		echo "<li><a href = '{$CSS_PREFIX}eventdetails.php?EventID=".$evrow['evEventID']."'>Event details</a></li>\n";
	}
	else
	{
		echo "<li><a href = '{$CSS_PREFIX}eventlist.php'>Event list</a></li>\n";
	}



if ($PLAYER_ID != 0)
{
	if (($sOOC == '' || $sOOC == '0000-00-00'))
		echo "<li><a href = '{$CSS_PREFIX}ooc_form.php'>OOC information</a></li>\n";
	else
		echo "<li><a href = '{$CSS_PREFIX}ooc_view.php'>OOC information</a></li>\n";
	if (($sDateIC == '' || $sDateIC == '0000-00-00'))
		echo "<li><a href = '{$CSS_PREFIX}ic_form.php'>IC information</a></li>\n";
	else
		echo "<li><a href = '{$CSS_PREFIX}ic_view.php'>IC information</a></li>\n";

	//Show link to admin page if user is an admin or root user
	$sql = "SELECT plAccess FROM " . DB_PREFIX . "players WHERE plPlayerID = $PLAYER_ID";
	$result = ba_db_query ($link, $sql);
	$inc_head_html_row = ba_db_fetch_assoc ($result);
	if ($inc_head_html_row ['plAccess'] == 'admin' || ROOT_USER_ID == $PLAYER_ID) {
		echo "<li><a href = '{$CSS_PREFIX}admin/admin.php'>Admin</a></li>\n";
	}
}

echo "</ul>";
echo "</div>";

if (($inc_head_html_row ['plAccess'] == 'admin' || ROOT_USER_ID == $PLAYER_ID) && $PLAYER_ID != 0) {
	//Check for install & NON_WEB directories
	if (file_exists (dirname ($_SERVER ["SCRIPT_FILENAME"]) . "/install"))
		echo "<span class = 'sans-warn'>The <a href = 'install/'>install</a> directory is present. It should be removed if the system is live</span><br />";
	if (file_exists (dirname ($_SERVER ["SCRIPT_FILENAME"]) . "/NON_WEB"))
		echo "<span class = 'sans-warn'>The NON_WEB directory is present. It should be removed</span><br />";
}

if (ini_get ('error_reporting') != 0)
	echo "<p style = 'border: solid thin orange; background: orange; text-align: center;'><b>DEBUG MODE ENABLED</b></p>\n";
?>
