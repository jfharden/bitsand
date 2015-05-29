<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File install/config_file_test.php
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

//Initialise $CSS_PREFIX
$CSS_PREFIX = '../';

//Load required inc files
$bLoginCheck = False;
require ($CSS_PREFIX . 'inc/inc_head_db.php');
include ($CSS_PREFIX . 'inc/inc_head_html.php');

function email_check ($sEmail, $sSetting) {
	if ($sEmail == '')
		echo "<span class = 'sans-warn'>$sSetting is not set</span><br>";
	elseif (!eregi ("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]*)$", $sEmail))
		echo "<span class = 'sans-warn'>$sSetting: " . htmlentities ($sEmail) . " is not a valid e-mail address</span><br>\n";
	else
		echo "$sSetting: $sEmail<br>";
}

function string_check ($value, $setting, $default) {
	if ($value == $default)
		echo "<span class = 'sans-warn'>$setting is set to the default value ($default)</span><br>";
	else
		echo "$setting: $value<br>";
}
//Report all errors except E_NOTICE
error_reporting (E_ALL ^ E_NOTICE);
?>
<h1>Configuration and Database Tests</h1>

<p>
This page performs some basic tests on the configuration file. It does not check absolutely everything, but is at least a quick check of the most important things. Anything that may require attention is in <span class = 'sans-warn'>bold red</span> text.
</p>

<p>
<?php
//Find out what SYSTEM_URL probably should be
//$sProtocol (http or https) is based on what protocol was used by referrer
//More robust than using $_SERVER ['HTTPS']
$sProtocol = parse_url ($_SERVER ['HTTP_REFERER'], PHP_URL_SCHEME) . '://';
if ($sProtocol == '://')
	$sProtocol = 'http://';
$sHost = $_SERVER ['HTTP_HOST'];
$sURI = rtrim (dirname ($_SERVER ['PHP_SELF']), '/\\');
$sSysUrl = "$sProtocol$sHost$sURI/";
// Remove the right-most 8 characters ("install/")
$sSysUrl = substr_replace  ($sSysUrl, '', -8);
//Run checks against SYSTEM_URL
if (SYSTEM_URL == 'http://domain.tld/')
	echo "<span class = 'sans-warn'>SYSTEM_URL is set to the default value</span><br>";
elseif (substr (SYSTEM_URL, 0, 7) != 'http://' && substr (SYSTEM_URL, 0, 8) != 'https://')
	echo "<span class = 'sans-warn'>SYSTEM_URL does not have 'http://' or 'https://' prefix</span><br>";
elseif (substr (SYSTEM_URL, -1, 1) != '/')
	echo "<span class = 'sans-warn'>SYSTEM_URL does not end with a /</span><br>";
elseif (SYSTEM_URL != $sSysUrl)
	echo "<span class = 'sans-warn'>SYSTEM_URL appears to be wrong. SYSTEM_URL is set to " . SYSTEM_URL . " but should probably be $sSysUrl</span><br>";
else
	echo "SYSTEM_URL: " . SYSTEM_URL . "<br>";
echo "<p>\n";

if (ROOT_USER_ID == NULL)
	echo "No root user has been defined<br>";
else
	echo "ROOT_USER_ID: " . ROOT_USER_ID . "<br>\n";
if (strlen (CRYPT_KEY) < (MIN_PASS_LEN * 2))
	echo "<span class = 'sans-warn'>CRYPT_KEY length is short (" . strlen (CRYPT_KEY) . ")</span><br>";
else
	echo "CRYPT_KEY is " . strlen (CRYPT_KEY) . " characters long. This is at least twice as long as MIN_PASS_LEN<br>";
if (strlen (PW_SALT) < (MIN_PASS_LEN * 2))
	echo "<span class = 'sans-warn'>PW_SALT length is short (" . strlen (PW_SALT) . ")</span><br>";
else
	echo "PW_SALT is " . strlen (PW_SALT) . " characters long. This is at least twice as long as MIN_PASS_LEN";
echo "<p>\n";
?>

<h2>Testing Database Connectivity</h2>

<p>
<b>Note that for these tests to work, the database must exist, and the tables must have been created.</b><br>
<i>I will now try to connect to the database, using the settings in the configuration file.</i>
</p>

<p>
<?php
$link = ba_db_connect ();
if ($link === False)
	echo "<span class = 'sans-warn'>Unable to connect to the database. Check values of DB_TYPE, DB_HOST, DB_USER, DB_PASS &amp DB_NAME</span>";
else
	echo "<span class = 'sans-green'>Successfully connected to the database</span>";
?>
</p>

<p>
<i>I will now try to insert, query, and delete data from the database. If none of these work, check your database user has permission to access the database, and also check that the DB_PREFIX value set in the configuration file matches the names of your database tables.</i>
</p>

<p>
<?php
//Run test queries - insert record into access_log, select it, delete it
$key = CRYPT_KEY;
$pid = 1;
$ip = '1.2.3.4';
$post = 'test query';
//INSERT
$sql = "INSERT INTO " . DB_PREFIX . "access_log (alPlayerID, alIP, alePost) VALUES ($pid, '$ip', AES_ENCRYPT('$post', '$key'))";
$result = ba_db_query ($link, $sql);
if ($result !== False)
	echo "<span class = 'sans-green'>Successfully inserted a row into the database</span>";
else
	echo "<span class = 'sans-warn'>Unable to insert a row into the database. Check that your database user has INSERT permissions</span>";
echo "<br>\n";
//SELECT
$sql = "SELECT alPlayerID, alIP, AES_DECRYPT(alePost, '$key') AS dPost FROM " . DB_PREFIX . "access_log WHERE alIP = '1.2.3.4'";
$result = ba_db_query ($link, $sql);
$row = ba_db_fetch_assoc ($result);
if ($row ['dPost'] == 'test query')
	echo "<span class = 'sans-green'>Successfuly queried the database</span>";
else
	echo "<span class = 'sans-warn'>Unable to select a row from the database. Check that your database user has SELECT permissions, and also check that CRYPT_KEY is set.</span>";
echo "<br>\n";
//DELETE
$sql = "DELETE FROM " . DB_PREFIX . "access_log WHERE alIP = '1.2.3.4'";
$result = ba_db_query ($link, $sql);
if ($result === False)
	echo "<span class = 'sans-warn'>Unable to delete a row from the database. Check that your database user has DELETE permissions</span>";
else
	echo "<span class = 'sans-green'>Successfuly deleted a row from the database</span>";
echo "<br>\n";
?>
</p>

<p><a href = "./">Installation Tests &amp; Tools</a></p>
<?php
include ('../inc/inc_foot.php');