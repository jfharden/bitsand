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

//Do not check that user is logged in
$bLoginCheck = False;

//Initialise $CSS_PREFIX
$CSS_PREFIX = '../';

include ($CSS_PREFIX . 'inc/inc_head_db.php');
include ($CSS_PREFIX . 'inc/inc_head_html.php');
//Report all errors except E_NOTICE
error_reporting (E_ALL ^ E_NOTICE);
?>

<h1>Create Tables with Prefixes</h1>

<p>
This page will create a set of tables, with prefixes, as defined in the configuration file. It will report progress as it goes. Note that, in order for this to work, the user defined in the configuration file must have permission to create and drop tables in the specified database. To use it, enter the value of CRYPT_KEY from the configuration file and click &quot;Create Tables&quot;.
</p>

<?php
if (! isset ($_POST ['btnSubmit'])) {
	echo "<form action = 'create_tables.php' method = 'post'>\n<p>";
	echo "Value of CRYPT_KEY in configuration file: <input type = 'password' name = 'txtKey'><br>\n";
	echo "<input type = 'submit' value = 'Create Tables' name = 'btnSubmit'></p></form><p>\n";
}

if ($_POST ['btnSubmit'] != '' && $_POST ['txtKey'] == CRYPT_KEY && CheckReferrer ('create_tables.php')) {
	$db_prefix = DB_PREFIX;

	echo "Creating table: {$db_prefix}access_log<br>\n";
	ba_db_query ($link, "DROP TABLE IF EXISTS `{$db_prefix}access_log`");
	ba_db_query ($link, "CREATE TABLE IF NOT EXISTS `{$db_prefix}access_log` (`alID` int(11) NOT NULL auto_increment, `alDateTime` timestamp NOT NULL default CURRENT_TIMESTAMP, `alPlayerID` int(11) NOT NULL COMMENT 'This will be zero if the user was not logged in', `alIP` tinytext NOT NULL, `alPage` text NOT NULL, `alGet` text NOT NULL, `alePost` blob NOT NULL COMMENT 'Encrypted', PRIMARY KEY  (`alID`)) ENGINE=MyISAM AUTO_INCREMENT=1");
	echo "Table created: {$db_prefix}access_log<br>\n";

	echo "Creating table: {$db_prefix}bookings<br>\n";
	ba_db_query ($link, "DROP TABLE IF EXISTS `{$db_prefix}bookings`");
	ba_db_query ($link, "CREATE TABLE IF NOT EXISTS `{$db_prefix}bookings` (`bkID` int(11) NOT NULL auto_increment, `bkPlayerID` int(11) NOT NULL, `bkDateOOCConfirmed` date NOT NULL, `bkDateICConfirmed` date NOT NULL, `bkDatePaymentConfirmed` date NOT NULL, `bkMealTicket` tinyint(1) NOT NULL default '0', `bkPayOnGate` tinyint(1) NOT NULL default '0', `bkAmountPaid` decimal(5,2), `bkAmountExpected` decimal(5,2), `bkInQueue` SMALLINT, `bkBunkRequested` tinyint(1) NOT NULL default '0', `bkBunkAllocated` tinyint(1) NOT NULL default '0', `bkBookAs` enum('Monster','Player','Staff') default NULL, `bkEventID` int(11) not null, PRIMARY KEY  (`bkID`)) ENGINE=MyISAM AUTO_INCREMENT=1");
	echo "Table created: {$db_prefix}bookings<br>\n";

	echo "Creating table: {$db_prefix}characters<br>\n";
	ba_db_query ($link, "DROP TABLE IF EXISTS `{$db_prefix}characters`");
	ba_db_query ($link, "CREATE TABLE IF NOT EXISTS `{$db_prefix}characters` (`chCharacterID` int(11) NOT NULL auto_increment, `chPlayerID` int(11) NOT NULL default '0', `chName` text NOT NULL, `chPreferredName` text, `chRace` text NOT NULL, `chGender` enum('Female','Male') NOT NULL default 'Male', `chGroupSel` text NOT NULL, `chGroupText` text NOT NULL, `chFaction` text NOT NULL, `chAncestor` text NOT NULL, `chAncestorSel` text, `chLocation` text NOT NULL, `chNPC` tinyint(1) NOT NULL default '0', `chNotes` text NOT NULL, `chOSP` text NOT NULL, `chMonsterOnly` tinyint(1), PRIMARY KEY  (`chCharacterID`)) ENGINE=MyISAM AUTO_INCREMENT=1");
	echo "Table created: {$db_prefix}characters<br>\n";

	echo "Creating table: {$db_prefix}players<br>\n";
	ba_db_query ($link, "DROP TABLE IF EXISTS `{$db_prefix}players`");
	ba_db_query ($link, "CREATE TABLE IF NOT EXISTS `{$db_prefix}players` (`plPlayerID` int(11) NOT NULL auto_increment, `plPassword` text NOT NULL, `pleAddress1` blob NOT NULL, `pleAddress2` blob NOT NULL, `pleAddress3` blob NOT NULL, `pleAddress4` blob NOT NULL, `plePostcode` blob NOT NULL, `pleTelephone` blob NOT NULL, `pleMobile` blob NOT NULL, `pleMedicalInfo` blob NOT NULL, `pleEmergencyNumber` blob NOT NULL, `plLoginCounter` tinyint(4) NOT NULL default '0' COMMENT 'Number of times user has tried to log in. Reset to 0 on successful login', `plAccess` text NOT NULL, `plFirstName` text NOT NULL, `plSurname` text NOT NULL, `plEmail` text NOT NULL, `plDOB` text NOT NULL, `plEmergencyName` text NOT NULL, `plEmergencyRelationship` text NOT NULL, `plCarRegistration` text NOT NULL, `plDietary` enum('Omnivore','Vegetarian','Vegan','Other/allergy (details in Medical Information box)') default NULL, `plNewMail` text COMMENT 'New e-mail address', `plNewMailCode` tinytext COMMENT 'Code to confirm new e-mail address', `plNotes` TEXT NULL COMMENT 'General notes', `plAdminNotes` TEXT, `plOldSalt` tinyint(1) NOT NULL default '0' COMMENT 'Use PW_OLD_SALT if set to true', `plLastLogin` DATE NULL, `plEmailICChange` tinyint(1) default '1', `plEmailOOCChange` tinyint(1) default '1', `plEmailPaymentReceived` tinyint(1) default '1', `plEmailRemovedFromQueue` tinyint(1) default '1', `plEventPackByPost` tinyint(1), `plMarshal` enum('No', 'Marshal', 'Referee', 'Senior Referee') default 'No', `plRefNumber` int(11) default '0', PRIMARY KEY  (`plPlayerID`)) ENGINE=MyISAM AUTO_INCREMENT=1");
	echo "Table created: {$db_prefix}players<br>\n";

	echo "Creating table: {$db_prefix}sessions<br>\n";
	ba_db_query ($link, "DROP TABLE IF EXISTS `{$db_prefix}sessions`");
	ba_db_query ($link, "CREATE TABLE IF NOT EXISTS `{$db_prefix}sessions` (`ssID` int(11) NOT NULL auto_increment, `ssPlayerID` int(11) NOT NULL, `ssLoginTime` text NOT NULL, `ssIP` tinytext NOT NULL, `ssLastAccess` int(11) NOT NULL, PRIMARY KEY  (`ssID`)) ENGINE=MyISAM AUTO_INCREMENT=1");
	echo "Table created: {$db_prefix}sessions<br>\n";

	echo "Creating table: {$db_prefix}items<br>\n";
	ba_db_query ($link, "DROP TABLE IF EXISTS `{$db_prefix}items`");
	ba_db_query ($link, "CREATE TABLE IF NOT EXISTS `{$db_prefix}items` (`itItemID` int(11) NOT NULL auto_increment, `itDescription` text NOT NULL, `itTicket` tinyint(1) NOT NULL DEFAULT '0', `itMeal` int(1) NOT NULL DEFAULT '0', `itBunk` int(1) NOT NULL DEFAULT '0', `itAvailableFrom` date NOT NULL,`itAvailableTo` date NOT NULL,`itAvailability` enum('Player','Monster','Staff','All') default 'All', `itItemCost` decimal(5,2) NOT NULL default '0', `itAllowMultiple` tinyint(1) not null default '0', `itMandatory` tinyint(1) not null default '0', `itEventID` int(11) NOT NULL, PRIMARY KEY  (`itItemID`)) ENGINE=MyISAM AUTO_INCREMENT=1");
	echo "Table created: {$db_prefix}items<br>\n";

	echo "Creating table: {$db_prefix}bookingitems<br>\n";
	ba_db_query ($link, "DROP TABLE IF EXISTS `{$db_prefix}bookingitems`");
	ba_db_query ($link, "CREATE TABLE IF NOT EXISTS `{$db_prefix}bookingitems` (`biBookingItemID` int(11) NOT NULL auto_increment, `biBookingID` int(11) NOT NULL, `biItemID` int(11) not null, `biQuantity` int(11) not null default '0', PRIMARY KEY  (`biBookingItemID`)) ENGINE=MyISAM AUTO_INCREMENT=1");
	echo "Table created: {$db_prefix}bookingitems<br>\n";

	echo "Creating table: {$db_prefix}events<br>\n";
	ba_db_query ($link, "DROP TABLE IF EXISTS `{$db_prefix}events`");
	ba_db_query ($link, "CREATE TABLE IF NOT EXISTS `{$db_prefix}events` (`evEventID` int(11) NOT NULL auto_increment, `evEventName` text NOT NULL,  `evEventDescription` text NOT NULL, `evEventDetails` text NOT NULL,  `evEventDate` date NOT NULL, `evBookingsOpen` date NOT NULL,`evBookingsClose` date NOT NULL, evPlayerSpaces int(11) NOT NULL DEFAULT '0', evMonsterSpaces int(11) NOT NULL DEFAULT '0', evStaffSpaces int(11) NOT NULL DEFAULT '0', evTotalSpaces int(11) NOT NULL DEFAULT '0', evPlayerBunks int(11) NOT NULL DEFAULT '0', evMonsterBunks int(11) NOT NULL DEFAULT '0', evStaffBunks int(11) NOT NULL DEFAULT '0', evTotalBunks int(11) NOT NULL DEFAULT '0', evAllowMonsterBookings tinyint(1) NOT NULL DEFAULT '1', evUseQueue tinyint(1) NOT NULL default '0', PRIMARY KEY  (`evEventID`)) ENGINE=MyISAM AUTO_INCREMENT=1");
	echo "Table created: {$db_prefix}events<br>\n";

	echo "Creating table: {$db_prefix}paymentrequests<br>\n";
	ba_db_query ($link, "DROP TABLE IF EXISTS `{$db_prefix}paymentrequests`");
	ba_db_query ($link, "CREATE TABLE IF NOT EXISTS `{$db_prefix}paymentrequests` (`prPaymentRequestID` int(11) NOT NULL auto_increment, `prEmail` text NOT NULL, `prBookingID` int(11) NOT NULL DEFAULT '0', PRIMARY KEY  (`prPaymentRequestID`)) ENGINE=MyISAM AUTO_INCREMENT=1");
	echo "Table created: {$db_prefix}paymentrequests<br>\n";

	echo "Creating table: {$db_prefix}factions<br>\n";
	ba_db_query ($link, "DROP TABLE IF EXISTS `{$db_prefix}factions`");
	ba_db_query ($link, "CREATE TABLE IF NOT EXISTS `{$db_prefix}factions` (`faID` tinyint(4) NOT NULL default '0', `faName` text NOT NULL, PRIMARY KEY  (`faID`)) ENGINE=MyISAM");
	echo "Populating table: {$db_prefix}factions<br>\n";
	ba_db_query ($link, "INSERT INTO `{$db_prefix}factions` (`faID`, `faName`) VALUES (1, 'Bears'),(2, 'Dragons'),(3, 'Gryphons'),(4, 'Harts'),(5, 'Jackals'),(6, 'Lions'),(7, 'Tarantulas'),(8, 'Unicorns'),(9, 'Vipers'),(10, 'Wolves'),(11, 'Non-Faction'),(12, 'Staff')");
	echo "Table created: {$db_prefix}factions<br>\n";

	echo "Creating table: {$db_prefix}groups<br>\n";
	ba_db_query ($link, "DROP TABLE IF EXISTS `{$db_prefix}groups`");
	ba_db_query ($link, "CREATE TABLE IF NOT EXISTS `{$db_prefix}groups` (`grID` int(11) NOT NULL auto_increment, `grName` text NOT NULL, PRIMARY KEY  (`grID`)) ENGINE=MyISAM AUTO_INCREMENT=1");
	echo "Table created: {$db_prefix}groups<br>\n";

	echo "Inserting default record: {$dbprefix}groups<br>\n";
	ba_db_query ($link, "insert into `{$db_prefix}groups` (`grName`) VALUES ('Other (enter name below)')");
	echo "Record inserted: {$db_prefix}groups<br>\n";

	echo "Creating table: {$db_prefix}guildmembers<br>\n";
	ba_db_query ($link, "DROP TABLE IF EXISTS `{$db_prefix}guildmembers`");
	ba_db_query ($link, "CREATE TABLE IF NOT EXISTS `{$db_prefix}guildmembers` (`gmID` int(11) NOT NULL auto_increment, `gmPlayerID` int(11) NOT NULL, `gmName` text NOT NULL, PRIMARY KEY  (`gmID`)) ENGINE=MyISAM AUTO_INCREMENT=1");
	echo "Table created: {$db_prefix}guildmembers<br>\n";

	echo "Creating table: {$db_prefix}guilds<br>\n";
	ba_db_query ($link, "DROP TABLE IF EXISTS `{$db_prefix}guilds`");
	ba_db_query ($link, "CREATE TABLE IF NOT EXISTS `{$db_prefix}guilds` (`guID` int(11) NOT NULL, `guName` text NOT NULL, PRIMARY KEY  (`guID`)) ENGINE=MyISAM");
	echo "Populating table: {$db_prefix}guilds<br>\n";
	ba_db_query ($link, "INSERT INTO `{$db_prefix}guilds` (`guID`, `guName`) VALUES (5, 'Bards'),(8, 'Mages'),(6, 'Healers'),(7, 'Incantors'),(2, 'Alchemists'),(3, 'Archers'),(10, 'Scouts'),(9, 'Militia'),(4, 'Bank'),(1, ' None'),(11, ' Armourers')");
	echo "Table created: {$db_prefix}guilds<br>\n";

	echo "Creating table: {$db_prefix}locations<br>\n";
	ba_db_query ($link, "DROP TABLE IF EXISTS `{$db_prefix}locations`");
	ba_db_query ($link, "CREATE TABLE IF NOT EXISTS `{$db_prefix}locations` (`lnID` tinyint(4) NOT NULL auto_increment, `lnName` text NOT NULL, PRIMARY KEY  (`lnID`)) ENGINE=MyISAM");
	echo "Table created: {$db_prefix}locations<br>\n";

	echo "Creating table: {$db_prefix}skills<br>\n";
	ba_db_query ($link, "DROP TABLE IF EXISTS `{$db_prefix}skills`");
	ba_db_query ($link, "CREATE TABLE IF NOT EXISTS `{$db_prefix}skills` (`skID` int(11) NOT NULL default '0',`skName` text NOT NULL, `skShortName` text NOT NULL COMMENT 'Short version of the skill name for character cards',`skCost` tinyint(4) NOT NULL default '0', PRIMARY KEY  (`skID`)) ENGINE=MyISAM");
	echo "Populating table: {$db_prefix}skills<br>\n";
	ba_db_query ($link, "INSERT INTO `{$db_prefix}skills` (`skID`, `skName`, `skShortName`, `skCost`) VALUES (1, 'Ambidexterity', 'Ambidex', 2), (2, 'Ritual Magic 2', 'Ritual 2', 4), (3, 'Large Melee Weapon Use', 'Lg Melee Wpn', 2), (4, 'Ritual Magic 3', 'Ritual 3', 6), (5, 'Projectile Weapon Use', 'Proj Weapon', 4), (6, 'Contribute To Ritualist', 'Contribute', 1), (7, 'Shield Use', 'Shield Use', 2), (8, 'Invocation', 'Invocation', 3), (9, 'Thrown Weapon', 'Thrown', 1), (10, 'Power 1', 'Power 1', 2), (11, 'Body Development 1', 'Body Dev 1', 4), (12, 'Power 2', 'Power 2', 4), (13, 'Body Development 2', 'Body Dev 2', 8), (14, 'Power 3', 'Power 3', 6), (15, 'Light Armour Use', 'Light Armour', 2), (16, 'Power 4', 'Power 4', 8), (17, 'Medium Armour Use', 'Med Armour', 3), (18, 'Potion Lore', 'Potion Lore', 3), (19, 'Heavy Armour Use', 'Heavy Armour', 4), (20, 'Poison Lore', 'Poison Lore', 4), (21, 'Healing 1', 'Healing 1', 4), (22, 'Cartography', 'Cartography', 1), (23, 'Healing 2', 'Healing 2', 8), (24, 'Sense Magic', 'Sense Magic', 1), (25, 'Spellcasting 1', 'Spell 1', 4), (26, 'Evaluate', 'Evaluate', 1), (27, 'Spellcasting 2', 'Spell 2', 8), (28, 'Recognise Forgery', 'Rec Forgery', 1), (29, 'Incantations 1', 'Incant 1', 4), (30, 'Physician', 'Physician', 2), (31, 'Incantations 2', 'Incant 2', 8), (32, 'Bind Wounds', 'Bind Wounds', 1), (33, 'Ritual Magic 1', 'Ritual 1', 2)");
	echo "Table created: {$db_prefix}skills<br>\n";

	echo "Creating table: {$db_prefix}skillstaken<br>\n";
	ba_db_query ($link, "DROP TABLE IF EXISTS `{$db_prefix}skillstaken`");
	ba_db_query ($link, "CREATE TABLE IF NOT EXISTS `{$db_prefix}skillstaken` (`stID` int(11) NOT NULL auto_increment, `stPlayerID` int(11) NOT NULL default '0', `stSkillID` tinyint(4) NOT NULL default '0', PRIMARY KEY  (`stID`)) ENGINE=MyISAM AUTO_INCREMENT=1");
	echo "Table created: {$db_prefix}skillstaken<br>\n";

	echo "Creating table: {$db_prefix}osps<br>\n";
	ba_db_query ($link, "DROP TABLE IF EXISTS `{$db_prefix}osps`");
	ba_db_query ($link, "CREATE TABLE `{$db_prefix}osps` (`ospID` int(11) NOT NULL auto_increment, `ospName` tinytext NOT NULL, `ospShortName` tinytext NOT NULL COMMENT 'Short version of the OSP name for character cards', `ospAllowAdditionalText` tinyint(1) default 0, PRIMARY KEY  (`ospID`))");
	echo "Populating table: {$db_prefix}osps<br>\n";
	ba_db_query ($link,
	"INSERT INTO `{$db_prefix}osps` (`ospID`, `ospName`, `ospShortName`, `ospAllowAdditionalText`) VALUES
		(0, ' None', '', 0),
		(1, '+1 Base LHV', '+1 Base LHV', 0),
		(2, '+1 LHV', '+1 LHV', 0),
		(3, '+12 Spell Cards', '+12 Spell Cards', 0),
		(4, '+16 Spell Cards', '+16 Spell Cards', 0),
		(5, '+2 LHV', '+2 LHV', 0),
		(6, '+4 Spell Cards', '+4 Spell Cards', 0),
		(7, '+8 Spell Cards', '+8 Spell Cards', 0),
		(8, 'Activate Item', 'Activate Item', 0),
		(9, 'Additonal Reforging', 'Additonl Reforg', 0),
		(10, 'Advanced Healing', 'Adv Healing', 0),
		(11, 'Advanced Pattern Scan', 'Adv Pattrn Scan', 0),
		(12, 'Apprentice', 'Apprentice', 1),
		(13, 'Armour Mastery', 'Armour Mastery', 0),
		(14, 'Armour Mastery (Advanced)', 'Armr Mastry Adv', 0),
		(15, 'Armour Mastery (Expert)', 'Exp Armr Mastry', 0),
		(16, 'Armoursmith (Apprentice)', 'Armoursmith App', 0),
		(17, 'Armoursmith (Artisan)', 'Armrsmith Artsn', 0),
		(18, 'Armoursmith (Master)', 'Armrsmith Mstr', 0),
		(19, 'Beast-form Casting', 'Beast-form Cast', 0),
		(20, 'Beast-form Changes +2', 'Beast Change +2', 0),
		(21, 'Beast-form Intelligence', 'Beast-form Int', 0),
		(22, 'Beast-form Skill Use', 'Beast Skill Use', 0),
		(23, 'Beguile', 'Beguile', 0),
		(24, 'Bowyer (Apprentice)', 'Bowyer App', 0),
		(25, 'Bowyer (Artisan)', 'Bowyer Artisan', 0),
		(26, 'Cast Additional Incantation', 'Cast Add Incant', 0),
		(27, 'Cast Additional Magecraft', 'Cast Add Magecr', 0),
		(28, 'Cast High Countermagic', 'Cst High Countr', 0),
		(29, 'Cast Mass Charms', 'Cast Mass Charm', 0),
		(30, 'Champion', 'Champion', 0),
		(31, 'Command', 'Command', 1),
		(32, 'Conceal Item', 'Conceal Item', 0),
		(33, 'Contribute to 2nd Ritual', 'Contribute 2nd', 0),
		(34, 'Corruption', 'Corruption', 0),
		(35, 'Create Antidotes', 'Create Antidote', 0),
		(36, 'Create Magical Poison', 'Cr Magic Poison', 0),
		(37, 'Create Magical Potion', 'Cr Magic Potion', 0),
		(38, 'Create Poison 1', 'Create Poison 1', 0),
		(39, 'Create Poison 2', 'Create Poison 2', 0),
		(40, 'Create Poison 3', 'Create Poison 3', 0),
		(41, 'Create Potion 1', 'Create Potion 1', 0),
		(42, 'Create Potion 2', 'Create Potion 2', 0),
		(43, 'Create Potion 3', 'Create Potion 3', 0),
		(44, 'Create Reagents', 'Create Reagents', 0),
		(45, 'Create Reagents (Improved)', 'Imp Cr Reagent', 0),
		(46, 'Crushing Blow', 'Crushing Blow', 0),
		(47, 'Daemonology', 'Daemonology', 0),
		(48, 'Damage Reduction (Fatal)', 'Dmg Reduc Fatal', 0),
		(49, 'Damage Reduction (Magic)', 'Dmg Reduc Magic', 0),
		(50, 'Dark Incantation', 'Dark Incant', 0),
		(51, 'Dedicated Follower', 'Dedicated Fllwr', 0),
		(52, 'Detect and Remove Beguile', 'Det Rem Beguile', 0),
		(53, 'Diagnose Powers', 'Diagnose Powers', 0),
		(54, 'Discern Ancestral Being', 'Disc Ancestral', 0),
		(55, 'Discern Daemonic Being', 'Disc Daemonic', 0),
		(56, 'Discern Elemental Being', 'Disc Elemental', 0),
		(57, 'Discern Pattern Type', 'Disc Pttrn Type', 0),
		(58, 'Discern Race', 'Discern Race', 0),
		(59, 'Discern Race and Pattern', 'Disc Race Patrn', 0),
		(60, 'Discern Unliving', 'Disc Unliving', 0),
		(61, 'Dismiss Rank +10', 'Dismiss Rnk +10', 0),
		(62, 'Dismiss Rank +5', 'Dismiss Rank +5', 0),
		(63, 'Dismiss/Control +4', 'Dmiss/Cntrl +4', 0),
		(64, 'Dismiss/Control +8', 'Dmiss/Cntrl +8', 0),
		(65, 'Elementalism', 'Elementalism', 0),
		(66, 'Enchant Projectile Weapon', 'Ench Proj Wpn', 0),
		(67, 'Enchanted Claws', 'Enchanted Claws', 0),
		(68, 'Enchanted Strikedown Claws', 'Ench Sdown Claw', 0),
		(69, 'Enchanting', 'Enchanting', 0),
		(70, 'Escape Bonds', 'Escape Bonds', 0),
		(71, 'Expert Physician', 'Expert Physcian', 0),
		(72, 'Far Travelled', 'Far Travelled', 0),
		(73, 'Fearsome Aspect', 'Fearsome Aspect', 0),
		(74, 'Forensic Analysis', 'Forensic Analys', 0),
		(75, 'Forgery', 'Forgery', 0),
		(76, 'General Knowledge', 'Genrl Knowledge', 1),
		(77, 'Global Blast Wedge', 'Globl Blast Wdg', 0),
		(78, 'Guarded Channeling', 'Guarded Channel', 0),
		(79, 'Halt Shot', 'Halt Shot', 0),
		(80, 'Hand of Nature', 'Hand of Nature', 0),
		(81, 'Harden Body', 'Harden Body', 0),
		(82, 'Heal Alien or Aberrant Pattern', 'Heal Alien Aber', 0),
		(83, 'Heal Magical Pattern', 'Heal Magic Ptrn', 0),
		(84, 'Herb Lore', 'Herb Lore', 0),
		(85, 'High Magic', 'High Magic', 0),
		(86, 'Identify', 'Identify', 0),
		(87, 'Immune to Charms', 'Immune Charms', 0),
		(88, 'Immune to Disease', 'Immune Disease', 0),
		(89, 'Immune to Distract and Confusion', 'Imm Dist & Conf', 0),
		(90, 'Immune to Fear', 'Immune to Fear', 0),
		(91, 'Immune to Fumble', 'Immune Fumble', 0),
		(92, 'Immune to Fumble and Shatter', 'Imm Fumbl Shatr', 0),
		(93, 'Immune to Immobilisation', 'Imm to Immobil', 0),
		(94, 'Immune to Lethal Alchemical Poisons', 'Imm Alch Poison', 0),
		(95, 'Immune to Mind Effects', 'Imm Mind Effect', 0),
		(96, 'Immune to Mute', 'Immune to Mute', 0),
		(97, 'Immune to Repel', 'Immune Repel', 0),
		(98, 'Immune to Repel and Strikedown', 'Imm Repel Sdown', 0),
		(99, 'Immune to Sleep', 'Immune to Sleep', 0),
		(100, 'Immune to Through', 'Immune Through', 0),
		(101, 'Improved Regeneration', 'Impr Regenerat', 0),
		(102, 'Improved Research Ability', 'Imp Rsrch Ablty', 0),
		(103, 'Increased Alchemical Production', 'Inc Alchem Prod', 0),
		(104, 'Journeyman', 'Journeyman', 1),
		(105, 'Last Rites', 'Last Rites', 0),
		(106, 'Last Rites (Improved)', 'Impr Last Rites', 0),
		(107, 'Level 2 Spell Reduction (1)', 'L2 Spell Red 1', 0),
		(108, 'Light Incantation', 'Light Incantat', 0),
		(109, 'Locate', 'Locate', 0),
		(110, 'Magebolt Wedge', 'Magebolt Wedge', 0),
		(111, 'Magic Resistance', 'Magic Resistnce', 0),
		(112, 'Magical Armour Mastery', 'Magc Armr Mstry', 0),
		(113, 'Mass Blast Wedge', 'Mass Blast Wdg', 0),
		(114, 'Master', 'Master', 1),
		(115, 'Master Countermagic', 'Master Counter', 0),
		(116, 'Master Poisoner', 'Master Poisoner', 0),
		(117, 'Mighty Blow', 'Mighty Blow', 0),
		(118, 'Mind Healing', 'Mind Healing', 0),
		(119, 'Mortician', 'Mortician', 0),
		(120, 'Mortician (Expert)', 'Mortcian Expert', 0),
		(121, 'Necromancy', 'Necromancy', 0),
		(122, 'Newsmonger', 'Newsmonger', 0),
		(123, 'Oath Sworn', 'Oath Sworn', 1),
		(124, 'Oiled Arrows', 'Oiled Arrows', 0),
		(125, 'Oiled Weapons', 'Oiled Weapons', 0),
		(126, 'Paladin (Tier 1)', 'Paladin 1', 1),
		(127, 'Patch Unliving', 'Patch Unliving', 0),
		(128, 'Perform Transport Rite', 'Transport Rite', 0),
		(129, 'Quick Armour Repair', 'Quick Armr Rpr', 0),
		(130, 'Rally', 'Rally', 0),
		(131, 'Repair Destroyed Items', 'Repr Dest Item', 0),
		(132, 'Repair Enchanted Items', 'Repr Ench Item', 0),
		(133, 'Repair Unliving (Advanced)', 'Repr Unliv Adv', 0),
		(134, 'Revitalise Unliving', 'Revitlse Unlvng', 0),
		(135, 'Revive', 'Revive', 0),
		(136, 'Rite Master', 'Rite Master', 0),
		(137, 'Ritual Magic (Improved)', 'Impr Ritual Mag', 0),
		(138, 'Ritualist (Expert)', 'Expert Ritualst', 0),
		(139, 'Ritualist (Master)', 'Master Ritualst', 0),
		(140, 'Sage', 'Sage', 1),
		(141, 'Scholar', 'Scholar', 1),
		(142, 'Scribe Scroll', 'Scribe Scroll', 0),
		(143, 'Self Cure', 'Self Cure', 0),
		(144, 'Shadow Magic', 'Shadow Magic', 0),
		(145, 'Shield Dismiss Level', 'Shield Dmis Lvl', 0),
		(146, 'Shield Mastery', 'Shield Mastery', 0),
		(147, 'Sigil Spell Reduction (1)', 'Sigil Spll Rd 1', 0),
		(148, 'Sleepless Chanting', 'Sleepless Chant', 0),
		(149, 'Source of Life', 'Source of Life', 0),
		(150, 'Source of Unlife', 'Srce of Unlife', 0),
		(151, 'Spell Tempering', 'Spell Tempering', 0),
		(152, 'Strikedown Shot', 'Strikedown Shot', 0),
		(153, 'Surgeon', 'Surgeon', 0),
		(154, 'Theology', 'Theology', 0),
		(155, 'Through', 'Through', 0),
		(156, 'Through from Behind', 'Through Behind', 0),
		(157, 'Through Thrown', 'Through Thrown', 0),
		(158, 'Toughen Body', 'Toughen Body', 0),
		(159, 'Tracking', 'Tracking', 0),
		(160, 'Transcend Armour', 'Transcnd Armour', 0),
		(161, 'Translate Named Script', 'Tran Nmed Scrpt', 0),
		(162, 'Trap Lore', 'Trap Lore', 0),
		(163, 'Traverse Faction Wards', 'Trav Factn Ward', 0),
		(164, 'Tutor', 'Tutor', 0),
		(165, 'Unending Voice', 'Unending Voice', 0),
		(166, 'Vampire (Tier 1)', 'Vampire 1', 1),
		(167, 'Warlock (Tier 1)', 'Warlock 1', 1),
		(168, 'Weaponsmith (Apprentice)', 'Weaponsmith App', 0),
		(169, 'Weaponsmith (Artisan)', 'Wpnsmith Artsn', 0),
		(170, 'Weaponsmith (Master)', 'Wpnsmith Mstr', 0),
		(171, 'Wedge Mastery', 'Wedge Mastery', 0),
		(172, 'Wedge Mastery (Improved)', 'Imp Wedge Mastr', 0),
		(173, 'Werecreature (Tier 1)', 'Were 1', 1),
		(174, 'Written Forgery', 'Written Forgery', 0),
		(201, 'Beguile (2)', 'Beguile 2', 0),
		(200, 'Beguile (1)', 'Beguile 1', 0),
		(199, 'Damage Reduction (Crush)', 'Dmg Reduc Crush', 0),
		(202, 'Beguile (3)', 'Beguile 3', 0),
		(203, 'Beguile (4)', 'Beguile 4', 0),
		(204, 'Vampire (Tier 2)', 'Vampire 2', 1),
		(205, 'Vampire (Tier 3)', 'Vampire 3', 1),
		(206, 'Vampire (Tier 4)', 'Vampire 4', 1),
		(207, 'Warlock (Tier 2)', 'Warlock 2', 1),
		(208, 'Warlock (Tier 3)', 'Warlock 3', 1),
		(209, 'Warlock (Tier 4)', 'Warlock 4', 1),
		(210, 'Paladin (Tier 2)', 'Paladin 2', 1),
		(211, 'Paladin (Tier 3)', 'Paladin 3', 1),
		(212, 'Paladin (Tier 4)', 'Paladin 4', 1),
		(213, 'Werecreature (Tier 2)', 'Were 2', 1),
		(214, 'Werecreature (Tier 3)', 'Were 3', 1),
		(215, 'Werecreature (Tier 4)', 'Were 4', 1),
		(216, 'Strike for Enchanted', 'Strike Enchanted', 0),
		(217, 'Immune to Paralysis', 'Imm Paralysis', 0),
		(218, 'Strike for Enchanted', 'Strike Enchanted', 0),
		(219, 'Immune to Paralysis', 'Immune Paralysis', 0),
		(220, 'Immune to Disease and Decay', 'Imm Disease+Decay', 0),
		(221, 'Damage Reduction (All)', 'Dmg Reduc All', 0),
		(222, 'Paladin (Tier 0)', 'Paladin 0', 0),
		(223, 'Warlock (Tier 0)', 'Warlock 0', 0),
		(224, 'Werecreature (Tier 0)', 'Were 0', 0),
		(225, 'Vampire (Tier 0)', 'Vampire 0', 0),
		(226, 'Immune to Fatal', 'Imm Fatal', 0),
		(227, '+1 Bonus PR', '+1 Bonus PR', 0),
		(228, '+1 Magical Armour', '+1 Mag Armr', 0),
		(229, '+1 Natural Armour', '+1 Nat Armr', 0),
		(230, '+2 Bonus PR', '+2 Bonus PR', 0),
		(231, '+2 Magical Armour', '+2 Mag Armr', 0),
		(232, '+2 Natural Armour', '+2 Nat Armr', 0),
		(233, 'Advanced Armour Repair', 'Adv Armr Rep', 0),
		(234, 'Brutish Strike', 'Brute strike', 0),
		(235, 'Focused Strike', 'Focus strike', 0),
		(236, 'Goblin Resilience', 'Goblin res', 0),
		(237, 'Hard Worker', 'Hard Worker', 0),
		(238, 'Herb Lore Improved', 'Herb Lore Imp', 0),
		(239, 'Immune to Ingested Poisons', 'Im Ing Poisn', 0),
		(240, 'Improved RoP', 'Improved RoP', 0),
		(241, 'Jack of all Trades', 'Jack Trades', 0),
		(242, 'Magical Armour Repair', 'Mag Arm Rep', 0),
		(243, 'Master Armour Repair', 'Mas Arm Rep', 0),
		(244, 'Master Brewer', 'Master Brewer', 0),
		(245, 'Mystic Claws', 'Mystic Claws', 0),
		(246, 'Natural Claws', 'Natural Claws', 0),
		(247, 'Oathbreaker', 'Oathbreaker', 0),
		(248, 'Regeneration (10m) [Damage type]', 'Regen 10m', 1),
		(249, 'Retractable Claws', 'Retract Claw', 0),
		(250, 'Ritual Crafter', 'Ritual Crafr', 0),
		(251, 'Self Repairing Armour', 'Slf Rep Armr', 0),
		(252, 'Spell Tempering (Master)', 'Spl Temp Mstr', 0)
	");
	echo "Table created: {$db_prefix}osps<br>\n";

	echo "Creating table: {$db_prefix}ospstaken<br>\n";
	ba_db_query ($link, "DROP TABLE IF EXISTS `{$db_prefix}ospstaken`");
	ba_db_query ($link, "CREATE TABLE IF NOT EXISTS `{$db_prefix}ospstaken`(`otID` int(11) NOT NULL auto_increment,`otPlayerID` int(11) NOT NULL,`otOspID` int(11) NOT NULL, `otAdditionalText` text, PRIMARY KEY  (`otID`)) ENGINE=MyISAM AUTO_INCREMENT=1");
	echo "Table created: {$db_prefix}ospstaken<br>\n";

	echo "Creating table: {$dbprefix}ancestors<br>\n";
	ba_db_query ($link, "DROP TABLE IF EXISTS `{$db_prefix}ancestors`");
	ba_db_query ($link, "create table IF NOT EXISTS `{$db_prefix}ancestors` (`anID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, `anName` TEXT)");
	echo "Table created: {$db_prefix}ancestors<br>\n";

	echo "Inserting default record: {$dbprefix}ancestors<br>\n";
	ba_db_query ($link, "insert into `{$db_prefix}ancestors` (`anName`) VALUES ('Other (enter name below)')");
	echo "Record inserted: {$db_prefix}ancestors<br>\n";

	echo "Creating table: {$db_prefix}faq<br>\n";
	ba_db_query ($link, "DROP TABLE IF EXISTS `{$db_prefix}faq`");
	ba_db_query ($link, "CREATE TABLE IF NOT EXISTS `{$db_prefix}faq` (`faqOrder` int(11) NOT NULL, `faqQuestion` text NOT NULL,`faqAnswer` text NOT NULL, PRIMARY KEY  (`faqOrder`)) ENGINE=MyISAM AUTO_INCREMENT=1");
	echo "Table created: {$db_prefix}faq<br>\n";

	echo "Populating table: {$db_prefix}faq<br>\n";
	ba_db_query ($link, "INSERT INTO `{$db_prefix}faq` (`faqOrder`, `faqQuestion`, `faqAnswer`) VALUES (10, 'I''m not in a Faction', 'Included in the list of factions are two options (\"Non-Faction\" and \"Staff\") for people that aren''t in a faction. Just select the one that is most appropriate.'),(20, 'I made a mistake, and now I''ve confirmed it. What do I do?', 'E-mail EVENT_MAIL or TECH_MAIL with the details, including your player ID (PLAYER_ID)'),(30, 'I don''t need to pay. What do I do when it asks me to pay?', 'If there is a \"Finalise booking\" link, just click on that, and it will mark you as paid. If not, e-mail EVENT_MAIL with your Player ID (PLAYER_ID) and you''ll be marked as paid (even though you haven''t actually handed over any cash)'),(40, 'I have lammies. Can I just show them when I arrive, or do you need me to send a scan?', 'List the lammie number eg SC1234, SI1234, and the item name in the Notes box. If possible add a brief precis of what the item is. Show it to game control when you check in. e.g. SI1234: Sword of the Forsaken: Enchanted Through'),(50, 'I didn''t get the e-mail with my password. What do I do?', 'It may have been marked as spam. Check your \"spam\" or \"junk\" folder. If it''s not there, e-mail TECH_MAIL')");
	echo "Table populated: {$db_prefix}faq<br>\n";



	echo "Creating table: {$db_prefix}config<br>\n";
	$createConfigTableScript =	"CREATE TABLE {$db_prefix}config (`cnID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,`cnName` TEXT, `cnANNOUNCEMENT_MESSAGE` TEXT,`cnDISCLAIMER_TEXT` TEXT,`cnEVENT_CONTACT_NAME` TEXT,`cnEVENT_CONTACT_MAIL` TEXT,`cnTECH_CONTACT_NAME` TEXT,`cnTECH_CONTACT_MAIL` TEXT,`cnTITLE` TEXT,`cnSYSTEM_NAME` TEXT,`cnBOOKING_FORM_FILE_NAME` TEXT,`cnBOOKING_LIST_IF_LOGGED_IN` BOOL,`cnLOCATIONS_LABEL` TEXT,`cnLIST_GROUPS_LABEL` TEXT,`cnANCESTOR_DROPDOWN` BOOL,`cnDEFAULT_FACTION` TEXT,`cnNON_DEFAULT_FACTION_NOTES` BOOL,`cnIC_NOTES_TEXT` TEXT,`cnLOGIN_TIMEOUT` SMALLINT UNSIGNED,`cnLOGIN_TRIES` SMALLINT UNSIGNED,`cnMIN_PASS_LEN` SMALLINT UNSIGNED,`cnSEND_PASSWORD` BOOL,`cnUSE_PAY_PAL` BOOL,`cnPAYPAL_EMAIL` TEXT, `cnNPC_LABEL` TEXT, `cnPAYPAL_AUTO_MARK_PAID` BOOL, `cnAUTO_ASSIGN_BUNKS` BOOL, `cnUSE_SHORT_OS_NAMES` tinyint(1), `cnUSE_QUEUE` smallint, `cnALLOW_EVENT_PACK_BY_POST` tinyint(1), `cnSTAFF_LABEL` TEXT, `cnQUEUE_OVER_LIMIT` BOOL)";
	ba_db_query ($link, "DROP TABLE IF EXISTS `{$db_prefix}config`");
	ba_db_query ($link, $createConfigTableScript);
	echo "Table created: {$db_prefix}config<br>\n";


	echo "Populating table: {$db_prefix}config<br>\n";
	ba_db_query ($link, "INSERT INTO `{$db_prefix}config` (cnName) VALUES ('Default')");

	//Need to deal with quotes in strings
	$updateQuery = "UPDATE `{$db_prefix}config` SET ";
	$updateQuery.= "cnANNOUNCEMENT_MESSAGE = '', ";
	$updateQuery.= "cnDISCLAIMER_TEXT = '', ";
	$updateQuery.= "cnEVENT_CONTACT_NAME = '', ";
	$updateQuery.= "cnEVENT_CONTACT_MAIL = '', ";
	$updateQuery.= "cnTECH_CONTACT_NAME = '', ";
	$updateQuery.= "cnTECH_CONTACT_MAIL = '', ";
	$updateQuery.= "cnTITLE = '', ";
	$updateQuery.= "cnSYSTEM_NAME = 'Bitsand', ";
	$updateQuery.= "cnBOOKING_FORM_FILE_NAME = 'bookingform.pdf', ";
	$updateQuery.= "cnBOOKING_LIST_IF_LOGGED_IN = 0, ";
	$updateQuery.= "cnLOCATIONS_LABEL = '', ";
	$updateQuery.= "cnLIST_GROUPS_LABEL = '', ";
	$updateQuery.= "cnANCESTOR_DROPDOWN = 0, ";
	$updateQuery.= "cnDEFAULT_FACTION = '', ";
	$updateQuery.= "cnNON_DEFAULT_FACTION_NOTES = 1, ";
	$updateQuery.= "cnIC_NOTES_TEXT = 'Reason for attending and any other IC notes (eg bloodline)', ";
	$updateQuery.= "cnLOGIN_TIMEOUT = 20, ";
	$updateQuery.= "cnLOGIN_TRIES = 3, ";
	$updateQuery.= "cnMIN_PASS_LEN = 8, ";
	$updateQuery.= "cnSEND_PASSWORD = 1, ";
	$updateQuery.= "cnUSE_PAY_PAL = 1, ";
	$updateQuery.= "cnPAYPAL_EMAIL = '', ";
	$updateQuery.= "cnNPC_LABEL = 'Are you an NPC?:', ";
	$updateQuery.= "cnPAYPAL_AUTO_MARK_PAID = 1, ";
	$updateQuery.= "cnAUTO_ASSIGN_BUNKS = 0, ";
	$updateQuery.= "cnALLOW_EVENT_PACK_BY_POST = 0, ";
	$updateQuery.= "cnSTAFF_LABEL = 'Staff', ";
	$updateQuery.= "cnQUEUE_OVER_LIMIT = 1 ";

	ba_db_query ($link, $updateQuery);
	echo "Table populated: {$db_prefix}config<br>\n";

	echo "<b>Database tables created</b><br>\n";
}
elseif ($_POST ['btnSubmit'] != '' && $_POST ['txtKey'] != CRYPT_KEY)
	echo "<span class = 'sans-warn'>Wrong value entered for CRYPT_KEY</span>";
?>
</p>

<p><a href = "./">Installation Tests &amp; Tools</a></p>
<?php
include ('../inc/inc_foot.php');
?>
