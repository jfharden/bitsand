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

//Do not need login check for this page
$bLoginCheck = False;

include ('inc/inc_head_db.php');
include ('inc/inc_head_html.php');
?>

<h1><?php echo TITLE?> - FAQ</h1>

<?php
$db_prefix = DB_PREFIX;
$sql = "SELECT faqOrder, faqQuestion, faqAnswer FROM {$db_prefix}faq ORDER BY faqOrder";
$result = ba_db_query ($link, $sql);
while ($row = ba_db_fetch_assoc ($result)) {
	$faqQuestion = htmlentities ($row ['faqQuestion']);
	$faqAnswer = htmlentities ($row ['faqAnswer']);
	$faqQuestion = str_replace ("EVENT_MAIL", "<a href = 'mailto:" . Obfuscate (EVENT_CONTACT_MAIL) . "'>" . EVENT_CONTACT_NAME . "</a>", $faqQuestion);
	$faqQuestion = str_replace ("TECH_MAIL", "<a href = 'mailto:" . Obfuscate (TECH_CONTACT_MAIL) . "'>" . TECH_CONTACT_NAME . "</a>", $faqQuestion);
	$faqQuestion = str_replace ("PLAYER_ID", player_ID (), $faqQuestion);
	$faqAnswer = str_replace ("EVENT_MAIL", "<a href = 'mailto:" . Obfuscate (EVENT_CONTACT_MAIL) . "'>" . EVENT_CONTACT_NAME . "</a>", $faqAnswer);
	$faqAnswer = str_replace ("TECH_MAIL", "<a href = 'mailto:" . Obfuscate (TECH_CONTACT_MAIL) . "'>" . TECH_CONTACT_NAME . "</a>", $faqAnswer);
	$faqAnswer = str_replace ("PLAYER_ID", player_ID (), $faqAnswer);

	echo "<p class = 'question'>" . stripslashes ($faqQuestion) . "</p>\n";
	echo "<p>" . stripslashes ($faqAnswer) . "</p>\n";
}
?>

<?php
include ('inc/inc_foot.php');
?>
