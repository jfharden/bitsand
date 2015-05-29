<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File install/update_db.php
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

//Do not check that user is logged in
$bLoginCheck = False;
//Initialise $CSS_PREFIX
$CSS_PREFIX = '../';

include ($CSS_PREFIX . 'inc/inc_head_db.php');
include ($CSS_PREFIX . 'inc/inc_head_html.php');
//Report all errors except E_NOTICE
error_reporting (E_ALL ^ E_NOTICE);
?>

<h1>Update Database</h1>

<p>
This page will update an existing Bitsand database (from Bitsand v7.x) to work with the latest version of Bitsand (version 8.0). It will report progress as it goes. Note that, in order for this to work, the user defined in the configuration file must have permission to CREATE, ALTER and DROP tables in the specified database. To use it, enter the value of CRYPT_KEY from the configuration file and click &quot;Update&quot;.
</p>
<p>
<strong>This should NOT be run if you are in the middle of an event, it will do strange things to existing bookings, and possibly break everything</strong>
</p>

<form action = "update_db.php" method = "post">
<p>
Value of CRYPT_KEY in configuration file: <input name = "txtKey" type = "password"><br>
<input type = "submit" value = "Update" name = "btnSubmit">
</p>
</form>

<p>
<?php
if ($_POST ['btnSubmit'] != '' && $_POST ['txtKey'] == CRYPT_KEY && CheckReferrer ('update_db.php')) {
	$db_prefix = DB_PREFIX;
	echo "Updating General Knowledge OSP (bug fix for issue 217)<br />\n";
	if (ba_db_query ($link, "UPDATE {$dbprefix}osps SET ospAllowAdditionalText = 1 WHERE ospID = 76") === False)
		echo "<span class = 'sans-warn'>Error updating General Knowledge OSP<br />\n";

	echo "Adding new OSPs (see issue 234 for list)<br />\n";
	if (ba_db_query ($link, "INSERT INTO {$dbprefix}osps (ospName, ospShortName) VALUES ('+1 Bonus PR', '+1 Bonus PR')") === False)
		echo "<span class = 'sans-warn'>Error adding OSP<br />\n";
	if (ba_db_query ($link, "INSERT INTO {$dbprefix}osps (ospName, ospShortName) VALUES ('+1 Magical Armour', '+1 Mag Armr')") === False)
		echo "<span class = 'sans-warn'>Error adding OSP<br />\n";
	if (ba_db_query ($link, "INSERT INTO {$dbprefix}osps (ospName, ospShortName) VALUES ('+1 Natural Armour', '+1 Nat Armr')") === False)
		echo "<span class = 'sans-warn'>Error adding OSP<br />\n";
	if (ba_db_query ($link, "INSERT INTO {$dbprefix}osps (ospName, ospShortName) VALUES ('+2 Bonus PR', '+2 Bonus PR')") === False)
		echo "<span class = 'sans-warn'>Error adding OSP<br />\n";
	if (ba_db_query ($link, "INSERT INTO {$dbprefix}osps (ospName, ospShortName) VALUES ('+2 Magical Armour', '+2 Mag Armr')") === False)
		echo "<span class = 'sans-warn'>Error adding OSP<br />\n";
	if (ba_db_query ($link, "INSERT INTO {$dbprefix}osps (ospName, ospShortName) VALUES ('+2 Natural Armour', '+2 Nat Armr')") === False)
		echo "<span class = 'sans-warn'>Error adding OSP<br />\n";
	if (ba_db_query ($link, "INSERT INTO {$dbprefix}osps (ospName, ospShortName) VALUES ('Advanced Armour Repair', 'Adv Armr Rep')") === False)
		echo "<span class = 'sans-warn'>Error adding OSP<br />\n";
	if (ba_db_query ($link, "INSERT INTO {$dbprefix}osps (ospName, ospShortName) VALUES ('Brutish Strike', 'Brute strike')") === False)
		echo "<span class = 'sans-warn'>Error adding OSP<br />\n";
	if (ba_db_query ($link, "INSERT INTO {$dbprefix}osps (ospName, ospShortName) VALUES ('Focused Strike', 'Focus strike')") === False)
		echo "<span class = 'sans-warn'>Error adding OSP<br />\n";
	if (ba_db_query ($link, "INSERT INTO {$dbprefix}osps (ospName, ospShortName) VALUES ('Goblin Resilience', 'Goblin res')") === False)
		echo "<span class = 'sans-warn'>Error adding OSP<br />\n";
	if (ba_db_query ($link, "INSERT INTO {$dbprefix}osps (ospName, ospShortName) VALUES ('Hard Worker', 'Hard Worker')") === False)
		echo "<span class = 'sans-warn'>Error adding OSP<br />\n";
	if (ba_db_query ($link, "INSERT INTO {$dbprefix}osps (ospName, ospShortName) VALUES ('Herb Lore Improved', 'Herb Lore Imp')") === False)
		echo "<span class = 'sans-warn'>Error adding OSP<br />\n";
	if (ba_db_query ($link, "INSERT INTO {$dbprefix}osps (ospName, ospShortName) VALUES ('Immune to Ingested Poisons', 'Im Ing Poisn')") === False)
		echo "<span class = 'sans-warn'>Error adding OSP<br />\n";
	if (ba_db_query ($link, "INSERT INTO {$dbprefix}osps (ospName, ospShortName) VALUES ('Improved RoP', 'Improved RoP')") === False)
		echo "<span class = 'sans-warn'>Error adding OSP<br />\n";
	if (ba_db_query ($link, "INSERT INTO {$dbprefix}osps (ospName, ospShortName) VALUES ('Jack of all Trades', 'Jack Trades')") === False)
		echo "<span class = 'sans-warn'>Error adding OSP<br />\n";
	if (ba_db_query ($link, "INSERT INTO {$dbprefix}osps (ospName, ospShortName) VALUES ('Magical Armour Repair', 'Mag Arm Rep')") === False)
		echo "<span class = 'sans-warn'>Error adding OSP<br />\n";
	if (ba_db_query ($link, "INSERT INTO {$dbprefix}osps (ospName, ospShortName) VALUES ('Master Armour Repair', 'Mas Arm Rep')") === False)
		echo "<span class = 'sans-warn'>Error adding OSP<br />\n";
	if (ba_db_query ($link, "INSERT INTO {$dbprefix}osps (ospName, ospShortName) VALUES ('Master Brewer', 'Master Brewer')") === False)
		echo "<span class = 'sans-warn'>Error adding OSP<br />\n";
	if (ba_db_query ($link, "INSERT INTO {$dbprefix}osps (ospName, ospShortName) VALUES ('Mystic Claws', 'Mystic Claws')") === False)
		echo "<span class = 'sans-warn'>Error adding OSP<br />\n";
	if (ba_db_query ($link, "INSERT INTO {$dbprefix}osps (ospName, ospShortName) VALUES ('Natural Claws', 'Natural Claws')") === False)
		echo "<span class = 'sans-warn'>Error adding OSP<br />\n";
	if (ba_db_query ($link, "INSERT INTO {$dbprefix}osps (ospName, ospShortName) VALUES ('Oathbreaker', 'Oathbreaker')") === False)
		echo "<span class = 'sans-warn'>Error adding OSP<br />\n";
	if (ba_db_query ($link, "INSERT INTO {$dbprefix}osps (ospName, ospShortName, ospAllowAdditionalText) VALUES ('Regeneration (10m) [Damage type]', 'Regen 10m', 1)") === False)
		echo "<span class = 'sans-warn'>Error adding OSP<br />\n";
	if (ba_db_query ($link, "INSERT INTO {$dbprefix}osps (ospName, ospShortName) VALUES ('Retractable Claws', 'Retract Claw')") === False)
		echo "<span class = 'sans-warn'>Error adding OSP<br />\n";
	if (ba_db_query ($link, "INSERT INTO {$dbprefix}osps (ospName, ospShortName) VALUES ('Ritual Crafter', 'Ritual Crafr')") === False)
		echo "<span class = 'sans-warn'>Error adding OSP<br />\n";
	if (ba_db_query ($link, "INSERT INTO {$dbprefix}osps (ospName, ospShortName) VALUES ('Self Repairing Armour', 'Slf Rep Armr')") === False)
		echo "<span class = 'sans-warn'>Error adding OSP<br />\n";
	if (ba_db_query ($link, "INSERT INTO {$dbprefix}osps (ospName, ospShortName) VALUES ('Spell Tempering (Master)', 'Spl Temp Mstr')") === False)
		echo "<span class = 'sans-warn'>Error adding OSP<br />\n";

}

elseif ($_POST ['btnSubmit'] != '' && $_POST ['txtKey'] != CRYPT_KEY)
	echo "<span class = 'sans-warn'>Wrong value entered for CRYPT_KEY</span>";
?>
</p>

<p><a href = "./">Installation Tests &amp; Tools</a></p>

<?php
include ('../inc/inc_foot.php');
?>
