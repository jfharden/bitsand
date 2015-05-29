<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File faq.php
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