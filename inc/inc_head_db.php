<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File inc/inc_head_db.php
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
require_once("inc_common.php");

//Initialise $CSS_PREFIX. Will be changed (in inc_admin.php) if required
//Note that another script may have already set it to '../' - do not change it in that case
if (!isset($CSS_PREFIX) || $CSS_PREFIX != '../')
	$CSS_PREFIX = '';

//Return base URL
function fnSystemURL () {
	$sHost = $_SERVER ['HTTP_HOST'];
	$sURI = rtrim (dirname ($_SERVER ['PHP_SELF']), '/\\');
	return url_scheme() . "://$sHost$sURI/";
}

function account_has_no_email($link, $player_id) {
	$sql = "SELECT plEmail FROM {$db_prefix}players WHERE plPlayerId = $player_id ";
	$result = ba_db_query ($link, $sql);
	$row = ba_db_fetch_assoc ($result);

	$match = preg_match("/^\s*$/", $row["plEmail"]);
	if ($match === FALSE || $match === 1) {
		return true;
	} else {
		return false;
	}
}

/*
 * Ensure that the config file has been correctly created. If not gracefully
 * stop the script from executing anything else
 */
if (!file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'inc_config.php')) {
	die('Bitsand has not been configured correctly, please ensure config file has been created.');
}
require_once('inc_config.php');
require_once('inc_session_handling.php');

//Load error reporting, encrypt/decrypt functions
include ('inc_error.php');
include ('inc_crypt.php');

//Load database functions
require ('inc_ba_db.php');

//Open link to database
$link = ba_db_connect ();

require ('inc_config_fromdb.php');

$stafftext = STAFF_LABEL;

$today = date("Y-m-d");
$db_prefix = DB_PREFIX;

//Check for maintenance mode
if (MAINTENANCE_MODE == True) {
	$sURL = fnSystemURL() . 'maintenance.html';
	//Don't redirect if in install directory
	if (strpos($sURL, "install") === false)
		header ("Location: $sURL");
	header ("Location: $sURL");
}

//Clear cookies and redirect to index.php
function ForceLogin ($sMsg = '') {
	destroy_session();

	//Make up URL
	$sURL = fnSystemURL () . 'index.php?warn=' . urlencode ($sMsg);
	header ("Location: $sURL");
}

//Log access to access_log table. Passwords are *not* logged
$aPost = $_POST;
if ($aPost ['txtPassword'] != '')
	$aPost ['txtPassword'] = '****';
if ($aPost ['txtPassword1'] != '')
	$aPost ['txtPassword1'] = '****';
if ($aPost ['txtPassword2'] != '')
	$aPost ['txtPassword2'] = '****';
$key = CRYPT_KEY;
$sql = "INSERT INTO " . DB_PREFIX . "access_log (alPlayerID, alIP, alPage, alGet, alePost) " .
	"VALUES ($PLAYER_ID, '" .
	ba_db_real_escape_string ($link, $_SERVER ['REMOTE_ADDR']) . "', '" .
	ba_db_real_escape_string ($link, $_SERVER ["PHP_SELF"]) . "', '" .
	ba_db_real_escape_string ($link, print_r ($_GET, True)) . "', " .
	"AES_ENCRYPT('" .ba_db_real_escape_string ($link, print_r ($aPost, True)) . "', '$key'))";
ba_db_query ($link, $sql);

//Check for cookie that shows that user is logged in. If user is not logged in, go to index.php
//Do not check if $bLoginCheck == 'FALSE' - this allows some pages to not require login, but defaults to login being required
if ($bLoginCheck !== False) {
	if (!is_player_logged_in()) {
		//User is not logged in, and must be logged in to access this page.
		ForceLogin ('You must be logged in to access that page');
	}
	elseif (account_has_no_email($link, $PLAYER_ID) === true) {
		// The account that is attempting to be logged into has no email set
		// This can happen where an admin has added an account/booking from a
		// paper booking form (so the player never has a user in the booking system)
		// but it leaves behind an account with an empty email and password
		// This has already been fixed but there are systems that have been running
		// for many years with accounts like this in them.
		ForceLogin("Error logging in, cannot login to that account");
	}
	else {
		//Check player ID and login time against database sessions table
		$sLoginTime = login_time_from_session();
		//Only first two octets of remote IP are stored to avoid issue with dial-up etc (see issue 170)
		$aIP = explode (".", $_SERVER ['REMOTE_ADDR']);
		$sIP = ba_db_real_escape_string ($link, $aIP [0] . "." . $aIP [1]);
		$sql = "SELECT ssPlayerID, ssLastAccess FROM " . DB_PREFIX . "sessions " .
			"WHERE ssPlayerID = $PLAYER_ID AND ssLoginTime = '$sLoginTime' AND " .
			"ssIP = '$sIP'";
		LogWarning ("SQL to check player is logged in:\n$sql");
		$result = ba_db_query ($link, $sql);
		//$result will be False if SQL returned no rows
		if ($result !== False) {
			$row = ba_db_fetch_assoc ($result);
			//User is logged in. Check time difference since ssLastAccess
			$iNow = time ();
			$iDiff = $iNow - $row ['ssLastAccess'];
			//Get time difference in minutes
			$iDiff = (int) $iDiff / 60;
			if ($iDiff > LOGIN_TIMEOUT) {
				//User has been inactive for too long. Delete session and force new login
				$sql = "DELETE FROM " . DB_PREFIX . "sessions WHERE ssPlayerID = $PLAYER_ID";
				ba_db_query ($link, $sql);
				ForceLogin ('Your login has timed out. Please login again');
			}
			else {
				//Update ssLastAccess
				$sql = "UPDATE " . DB_PREFIX . "sessions SET ssLastAccess = $iNow WHERE ssPlayerID = $PLAYER_ID";
				ba_db_query ($link, $sql);
			}
		}
		else {
			//query returned no rows - user is not logged in or is on a different IP address
			//Delete existing session and force new login
			$sql = "DELETE FROM " . DB_PREFIX . "sessions WHERE ssPlayerID = $PLAYER_ID";
			ba_db_query ($link, $sql);
			ForceLogin ();
		}
	}
}

// Function to check for forms being submitted from elsewhere
function CheckReferrer ($Referrer_Check, $Referrer_Check_2 = "") {
	global $PLAYER_ID;
	$bForceLogin = True;
	//Get referrer, minus the query string
	$sReferrer = parse_url ($_SERVER ['HTTP_REFERER'], PHP_URL_SCHEME) . '://' .
		parse_url ($_SERVER ['HTTP_REFERER'], PHP_URL_HOST) .
		parse_url ($_SERVER ['HTTP_REFERER'], PHP_URL_PATH);
	if ($sReferrer == fnSystemURL () . $Referrer_Check)
		$bForceLogin = False;
	if ($sReferrer == fnSystemURL () . $Referrer_Check_2)
		$bForceLogin = False;
	//Special case - start page, with trailing slash but no 'index.php'
	if (fnSystemURL () == $sReferrer && $Referrer_Check == 'index.php')
		$bForceLogin = False;
	//Special case - start page, with no trailing slash
	if (substr (fnSystemURL (), 0, strlen (fnSystemURL ()) - 1) == $Referrer && $Referrer_Check == 'index.php')
		$bForceLogin = False;
	if ($bForceLogin) {
		//Delete any existing session and force new login
		$sql = "DELETE FROM " . DB_PREFIX . "sessions WHERE ssPlayerID = $PLAYER_ID";
		ba_db_query ($link, $sql);
		LogWarning ("Form submitted from $sReferrer (expected " . fnSystemURL () . "$Referrer_Check)\nPlayer ID: $PLAYER_ID");
		ForceLogin ();
	}
	else
		return True;
}

//Return player ID in brackets if logged in, empty string if not
function player_ID () {
	if ($PLAYER_ID == 0)
		return "";
	else
		return "(" . PID_PREFIX . sprintf ('%03s', $PLAYER_ID) . ") ";
}

function sanitiseAmount($amount, $negativeallowed=False)
{
	//Tidy up a value passed as an amount, so it's safe to go the database
	//Means it's a number between 0 and 999, with 2dp
	// If $negativeallowed is True, amount may be less than 0
	if (!is_numeric($amount)) {$amount = 0;}
	if ($negativeallowed === False && $amount < 0)
		$amount = 0;
	if ($amount >= 1000) {$amount = 999.99;}
	$amount= round($amount, 2);
	return $amount;
}

//Get Email Preferences
if ($PLAYER_ID > 0)
{
	$sql = "SELECT plEmailOOCChange, plEmailICChange, plEmailPaymentReceived, plEmailRemovedFromQueue FROM " . DB_PREFIX ."players where plPlayerID = $PLAYER_ID";
		$result = ba_db_query ($link, $sql);
		//$result will be False if SQL returned no rows
		if ($result !== False) {
			$row = ba_db_fetch_assoc ($result);
			$bEmailOOCChange = $row['plEmailOOCChange'];
			$bEmailICChange = $row['plEmailICChange'];
			$bEmailPaymentReceived = $row['plEmailPaymentReceived'];
			$bEmailRemovedFromQueue = $row['plEmailRemovedFromQueue'];
	}
}

//Function to send e-mail to event contact, and tech contact if $bTech is True
function fnMailer ($sBody, $bTech = False) {
	ini_set("sendmail_from", EVENT_CONTACT_MAIL);
	$sBody .= "\n\n" . SYSTEM_URL;
	mail (EVENT_CONTACT_MAIL, SYSTEM_NAME, $sBody, "From:" . SYSTEM_NAME . " <" . EVENT_CONTACT_MAIL . ">", '-f'.EVENT_CONTACT_MAIL);
	if ($bTech)
	{
		mail (TECH_CONTACT_MAIL, SYSTEM_NAME, $sBody, "From:" . SYSTEM_NAME . " <" . EVENT_CONTACT_MAIL . ">", '-f'.EVENT_CONTACT_MAIL);
	}
}
