<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File index.php
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

$db_prefix = DB_PREFIX;
if ($PLAYER_ID != 0) {
	//Check if player has entered IC & OOC data
	$sql = "SELECT chName FROM {$db_prefix}characters WHERE chPlayerID = $PLAYER_ID";
	$result = ba_db_query ($link, $sql);
	$iIC = ba_db_num_rows ($result);

	//Check for OOC data needs to check for some actual data, as a record will always exist
	$sql = "SELECT plFirstName FROM {$db_prefix}players WHERE plPlayerID = $PLAYER_ID";
	$result = ba_db_query ($link, $sql);
	$row = ba_db_fetch_assoc ($result);
	if ($row ['plFirstName'] != '')
		$bOOC = True;
	else
		$bOOC = False;

	echo "<hr>\n<p>";
	echo "Logged in with Player ID " . PID_PREFIX . sprintf ('%03s', $PLAYER_ID) . "<br>\n";


	echo "<ul>\n";
	echo "<li><a href = '{$CSS_PREFIX}terms.php'>Terms &amp; conditions</a></li>\n";
	echo "<li>Problem? See the <a href = '{$CSS_PREFIX}faq.php'>FAQ</a>. Or e-mail <a href = 'mailto:" .
		Obfuscate (EVENT_CONTACT_MAIL) . "'>" . EVENT_CONTACT_NAME . "</a> with event queries, or <a href = 'mailto:" .
		Obfuscate (TECH_CONTACT_MAIL) . "'>" . TECH_CONTACT_NAME . "</a> with web site problems.</li>\n";
	echo "</ul>\n";
}

/*
 * Use include_once to include the version file so that we have access to the
 * BitsandVersion object.  This is until we implement v9 with an MVC framework
 * with autoloader.
 */
if (file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'version.php')) {
	include_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'version.php');
	$version = ' v' . BitsandVersion::get();
} else {
	$version = '';
}



?>

<hr>
<p class = 'smallprint'>
This online booking system runs on Bitsand, a web-based booking system for LRP events. Bitsand is copyright (c) <a href = "http://github.com/PeteAUK/Bitsand">The Bitsand Project<?php echo $version; ?></a>.<br>
Found a bug? <a href = "https://github.com/PeteAUK/bitsand/issues">Report it</a>.<br>
Bitsand is free software; you can redistribute it and/or modify it under the terms of the <a href = "<?php echo $CSS_PREFIX?>LICENCE.html">GNU General Public License</a> as published by the Free Software Foundation; either version 3 of the License, or (at your option) any later version.<br>
<a href = "<?php echo $CSS_PREFIX?>download.php">Full details, including download links</a>
</p>

<?php
//Close link to database
ba_db_close ($link);

if (ini_get ('error_reporting') != 0)
	echo "<p style = 'border: solid thin orange; background: orange; text-align: center;'><b>DEBUG MODE ENABLED</b></p>\n";
?>
</body>
</html>