<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File admin/admin_faq.php
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

$db_prefix = DB_PREFIX;
$sGreen = '';
$sWarn = '';

if ($_GET ['action'] == 'delete' && CheckReferrer ('admin_faq.php')) {
	$sql = "DELETE FROM {$db_prefix}faq WHERE faqOrder = " . (int) $_GET ['id'];
	if (ba_db_query ($link, $sql) === False) {
		$sWarn = "Error deleting FAQ entry";
		LogError ($sWarn);
	}
	else
		$sGreen = "FAQ entry number {$_GET ['id']} deleted";
}
elseif ($_POST ['btnEdit'] != '' && CheckReferrer ('admin_faq.php')) {
	$iNewOrder = (int) $_POST ['txtOrder'];
	$iOldOrder = (int) $_POST ['hOrder'];
	$sql = "UPDATE {$db_prefix}faq SET faqOrder = $iNewOrder, " .
		"faqQuestion = '" . ba_db_real_escape_string ($link, $_POST ['txtQuestion']) . "', " .
		"faqAnswer = '" . ba_db_real_escape_string ($link, $_POST ['txtAnswer']) . "' " .
		"WHERE faqOrder = $iOldOrder";
	if (ba_db_query ($link, $sql) === False) {
		$sWarn = "Error updating FAQ entry. Check that the Order number is unique.";
		LogError ($sWarn);
	}
	else
		$sGreen = "FAQ entry number $iOldOrder updated";
}

if ($_POST ['btnSubmit'] == 'Add' && CheckReferrer ('admin_faq.php')) {
	$sql = "INSERT INTO {$db_prefix}faq (faqOrder, faqQuestion, faqAnswer) VALUES (" .
		(int) $_POST ['txtOrder'] . ", '" . ba_db_real_escape_string ($link, $_POST ['txtQuestion']) . "', '" .
		ba_db_real_escape_string ($link, $_POST ['txtAnswer']) . "')";
	if (ba_db_query ($link, $sql) === False) {
		$sWarn = "There was an error adding the FAQ item. Check that the Order number is unique.";
		$iOrder = (int) $_POST ['txtOrder'];
		$sQuestion = htmlentities (stripslashes ($_POST ['txtQuestion']));
		$sAnswer = htmlentities (stripslashes ($_POST ['txtAnswer']));
	}
	else
		$sGreen = "The FAQ item was added successfully.";
}

include ('../inc/inc_head_html.php');
?>

<h1><?php echo TITLE?> - Edit FAQ</h1>

<?php
if ($sGreen != '')
	echo "<p class = 'green'>$sGreen</p>";
elseif ($sWarn != '')
	echo "<p class = 'warn'>$sWarn</p>";
?>

<table>
<tr><th>Order</th>
<th>Question &amp; Answer</th>
</tr>
<?php
$sql = "SELECT faqOrder, faqQuestion, faqAnswer FROM {$db_prefix}faq ORDER BY faqOrder";
$result = ba_db_query ($link, $sql);
while ($row = ba_db_fetch_assoc ($result)) {
	$faqQuestion = htmlentities (stripslashes ($row ['faqQuestion']));
	$faqAnswer = htmlentities (stripslashes ($row ['faqAnswer']));

	echo "<form action = 'admin_faq.php' method = 'post'><tr>\n";
	echo "<td><input type = 'hidden' name = 'hOrder' value = '{$row ['faqOrder']}'>";
	echo "<input name = 'txtOrder' value = '{$row ['faqOrder']}' size = '3'></td>";
	echo "<td><input name = 'txtQuestion' value = \"$faqQuestion\" size = '75'></td></tr>\n";
	echo "<tr><td><a href = 'admin_faq.php?action=delete&amp;id={$row ['faqOrder']}'>Delete</a><br>\n";
	echo "<input type = 'submit' value = 'Save Changes' name = 'btnEdit'></td>";
	echo "<td><textarea rows = '4' cols = '70' name = 'txtAnswer' style = 'width: 80ex'>$faqAnswer</textarea></td></tr>\n";
	echo "<tr><td colspan = '2'><hr></td></tr>\n";
	echo "</form>";
}
?>
</table>

<h2><a name = 'add'>Add a New FAQ Item</a></h2>

<p>
<ul>
<li>To add a new item, fill in the details below and click Add.
<li><b>Order</b> must be an integer number, and must be unique.<br>
<li>HTML is not allowed.
<li>To include an e-mail link, enter either EVENT_MAIL or TECH_MAIL (in upper case) and an e-mail link to the relevent contact will be inserted at that point.
<li>To include a logged-on user's player ID, enter PLAYER_ID (in upper case) and it will be inserted at that point (in brackets)
</ul>
</p>

<form action = 'admin_faq.php' method = 'post'>
<table>
<tr><td>Order:</td>
<td><input name = 'txtOrder' size = '3' value = "<?php echo $iOrder ?>"></td></tr>
<tr><td>Question:</td>
<td><input name = 'txtQuestion' size = '75' value = "<?php echo $sQuestion ?>"></td></tr>
<tr><td>Answer:</td>
<td><textarea rows = "4" cols = "60" name = 'txtAnswer' style = 'width: 80ex'><?php echo $sAnswer ?></textarea></td></tr>
<tr><td colspan = "2" class = "mid"><input type = 'submit' value = 'Add' name = 'btnSubmit'>
<input type = 'reset' value = "Reset form"></td></tr>
</table>
</form>

<?php
include ('../inc/inc_foot.php');
?>
