<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File admin/root_keychange.php
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
require ('../inc/inc_admin.php');
require ('../inc/inc_root.php');
include ('../inc/inc_head_html.php');

$db_prefix = DB_PREFIX;

if ($_POST ['btnChangeKey'] != '' && CheckReferrer ('root_keychange.php')) {
	$sOldKey = $_POST ['txtOldKey'];
	$sNewKey = CRYPT_KEY;
	$sMsg = '';
	$sPrefix = DB_PREFIX;
	echo "UPDATE {$sPrefix}players SET pleAddress1 = AES_ENCRYPT(AES_DECRYPT(pleAddress1, '$sOldKey'), '$sNewKey')<p>";
	if (ba_db_query ($link, "UPDATE {$sPrefix}players SET pleAddress1 = AES_ENCRYPT(AES_DECRYPT(pleAddress1, '$sOldKey'), '$sNewKey')") === False) {
		LogError ('Error updating field pleAddress1 whilst changing encryption key');
		$sMsg = 'There was an error updating your encryption key';
	}
	if (ba_db_query ($link, "UPDATE {$sPrefix}players SET pleAddress2 = AES_ENCRYPT(AES_DECRYPT(pleAddress2, '$sOldKey'), '$sNewKey')") === False) {
		LogError ('Error updating field pleAddress2 whilst changing encryption key');
		$sMsg = 'There was an error updating your encryption key';
	}
	if (ba_db_query ($link, "UPDATE {$sPrefix}players SET pleAddress3 = AES_ENCRYPT(AES_DECRYPT(pleAddress3, '$sOldKey'), '$sNewKey')") === False) {
		LogError ('Error updating field pleAddress3 whilst changing encryption key');
		$sMsg = 'There was an error updating your encryption key';
	}
	if (ba_db_query ($link, "UPDATE {$sPrefix}players SET pleAddress4 = AES_ENCRYPT(AES_DECRYPT(pleAddress4, '$sOldKey'), '$sNewKey')") === False) {
		LogError ('Error updating field pleAddress4 whilst changing encryption key');
		$sMsg = 'There was an error updating your encryption key';
	}
	if (ba_db_query ($link, "UPDATE {$sPrefix}players SET plePostcode = AES_ENCRYPT(AES_DECRYPT(plePostcode, '$sOldKey'), '$sNewKey')") === False) {
		LogError ('Error updating field plePostcode whilst changing encryption key');
		$sMsg = 'There was an error updating your encryption key';
	}
	if (ba_db_query ($link, "UPDATE {$sPrefix}players SET pleTelephone = AES_ENCRYPT(AES_DECRYPT(pleTelephone, '$sOldKey'), '$sNewKey')") === False) {
		LogError ('Error updating field pleTelephone whilst changing encryption key');
		$sMsg = 'There was an error updating your encryption key';
	}
	if (ba_db_query ($link, "UPDATE {$sPrefix}players SET pleMobile = AES_ENCRYPT(AES_DECRYPT(pleMobile, '$sOldKey'), '$sNewKey')") === False) {
		LogError ('Error updating field pleMobile whilst changing encryption key');
		$sMsg = 'There was an error updating your encryption key';
	}
	if (ba_db_query ($link, "UPDATE {$sPrefix}players SET pleMedicalInfo = AES_ENCRYPT(AES_DECRYPT(pleMedicalInfo, '$sOldKey'), '$sNewKey')") === False) {
		LogError ('Error updating field pleMedicalInfo whilst changing encryption key');
		$sMsg = 'There was an error updating your encryption key';
	}
	if (ba_db_query ($link, "UPDATE {$sPrefix}players SET pleEmergencyNumber = AES_ENCRYPT(AES_DECRYPT(pleEmergencyNumber, '$sOldKey'), '$sNewKey')") === False) {
		LogError ('Error updating field pleEmergencyNumber whilst changing encryption key');
		$sMsg = 'There was an error updating your encryption key';
	}
	if ($sMsg == '')
		$sMsg = 'Encryption Key Changed';
}

if ($_POST ['btnChangeSalt'] != '' && CheckReferrer ('root_keychange.php')) {
	$sql = "UPDATE {$sPrefix}players SET plOldSalt = 1";
	if (ba_db_query ($link, $sql) === False) {
		LogError ('Error updating field plOldSalt whilst changing password salt');
		$sMsg = 'There was an error updating your password salt';
	}
	else
		$sMsg = 'Password salt changed';
}
?>

<h1><?php echo TITLE?> - Encryption Key</h1>

<?php
if ($sMsg != '')
	echo "<p class = 'warn'>$sMsg</p>";
?>

<p>
<a href = 'admin.php'>Admin</a>
</p>

<h2>Database Encryption Key</h2>

<p>
This form can be used to change your database encryption key.<br>
<b>Make sure you read the following instructions CAREFULLY. Failure to do so could render your data inaccessible.</b><br>
</p>

<ol>
<li>Put the system into maintenance mode (see the configuration file).</li>
<li>Change the inc_config.php file so that <tt>CRYPT_KEY</tt> is set to the <b>new</b> encryption key.</li>
<li>Enter the <b>old</b> encryption key in the box below and click <b>Change Encryption Key</b>.</li>
</ol>

<form action = 'root_keychange.php' method = 'post'>
Old key: <input name = "txtOldKey"><br>
<input type = "submit" value = "Change Encryption Key" name = "btnChangeKey">
</form>

<h2>Password Salt</h2>

<p>
This form can be used to change your password salt.<br>
<b>Make sure you read the following instructions CAREFULLY. Failure to do so could render users unable to log in.</b><br>
</p>

<ol>
<li>Put the system into maintenance mode (see the configuration file).</li>
<li>Change the inc_config.php file so that <tt>OLD_PW_SALT</tt> is set to the existing salt (ie the current setting of <tt>PW_SALT</tt>.</li>
<li>Change the inc_config.php file so that <tt>PW_SALT</tt> is set to the new salt.</li>
<li>Click <b>Change Salt</b>.</li>
</ol>

<form action = 'root_keychange.php' method = 'post'>
<input type = "submit" value = "Change Salt" name = "btnChangeSalt">
</form>

<?php
include ('../inc/inc_foot.php');
?>
