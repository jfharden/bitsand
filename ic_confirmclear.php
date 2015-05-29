<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File ic_confirmclear.php
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

include ('inc/inc_head_db.php');
$db_prefix = DB_PREFIX;

if (strtolower ($_POST ['btnSubmit']) == 'yes' && CheckReferrer ('ic_confirmclear.php')) {

	$sql = "DELETE FROM {$db_prefix}characters WHERE chPlayerID = $PLAYER_ID";
	if (! ba_db_query ($link, $sql)) {
		$sWarn = "There was a problem clearing character details";
		LogError ("Error clearing character details (characters) (admin).\nPlayer ID: $PLAYER_ID");
	}

	$sql = "DELETE FROM {$db_prefix}ospstaken WHERE otPlayerID = $PLAYER_ID";
	if (! ba_db_query ($link, $sql)) {
		$sWarn = "There was a problem clearing character details";
		LogError ("Error clearing character details (ospstaken) (admin).\nPlayer ID: $PLAYER_ID");
	}

	$sql = "DELETE FROM {$db_prefix}guildmembers WHERE gmPlayerID = $PLAYER_ID";
	if (! ba_db_query ($link, $sql)) {
		$sWarn = "There was a problem clearing character details";
		LogError ("Error clearing character details (guildmembers) (admin).\nPlayer ID: $PLAYER_ID");
	}

	$sql = "DELETE FROM {$db_prefix}skillstaken WHERE stPlayerID = $PLAYER_ID";
	if (! ba_db_query ($link, $sql)) {
		$sWarn = "There was a problem clearing character details";
		LogError ("Error clearing character details (skillstaken) (admin).\nPlayer ID: $PLAYER_ID");
	}


	$sURL = fnSystemURL () . 'ic_form.php';
	header ("Location: $sURL");
}

if (strtolower ($_POST ['btnSubmit']) == 'no' && CheckReferrer ('ic_confirmclear.php')) {
	$sURL = fnSystemURL () . 'ic_form.php';
	header ("Location: $sURL");
}

include ('inc/inc_head_html.php');
include ('inc/inc_forms.php');
?>
<h1><?php echo TITLE?> - Clear IC Details</h1>
<p>
<strong>Are you sure you want to clear your character information?</strong>
</p>
<p>
Once it's confirmed it cannot be undone, you will need to re-enter your information.
</p>
<p>
<form method='POST' action='ic_confirmclear.php'>
<table><tr><td><input type='submit' name='btnSubmit' value = 'Yes' /></td><td><input type='submit' name='btnSubmit' value = 'No' /></td></tr></table>
</form>
</p>
<?php
include ('inc/inc_foot.php');
?>
