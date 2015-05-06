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

//Exit if either btnSubmit is empty, encryption key is wrong, or was not referred from "update_db.php"
//if ($_POST ['btnSubmit'] == '' || $_POST ['txtCryptKey'] != CRYPT_KEY || CheckReferrer ('update_db.php'))
//	die ("Wrong encryption key");

include ('../inc/inc_config.php');
//Report all errors except E_NOTICE
error_reporting (E_ALL ^ E_NOTICE);

//Send headers to tell browser that this is a plain text file
header("Content-Type: text/plain");

echo "<?php
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

// Root user ID (Just the number - do not include leading zero's or the prefix defined above)
// This user will have more privileges than anyone else, including admin users
// The default is NULL, which equates to no-one
// Note that the value is not enclosed in quote marks\n";
if (ROOT_USER_ID == NULL)
	echo "define ('ROOT_USER_ID', NULL);\n";
else
	echo 'define (\'ROOT_USER_ID\',' . (int) ROOT_USER_ID . ");\n";

echo "// Prefix for player ID. Player ID will be this prefix followed by a number\n";
echo 'define (\'PID_PREFIX\', \''.PID_PREFIX."');\n";
echo "\n// Type of database. Valid values are 'mysql' (for the PHP MySQL extension) or 'mysqli' (for the PHP Improved MySQL extension)\n";
echo 'define (\'DB_TYPE\',\''.DB_TYPE."');\n";
echo "// MySQL hostname\ndefine ('DB_HOST','".DB_HOST."');\n";
echo "// Name of MySQL database\ndefine ('DB_NAME','".DB_NAME."');\n";
echo "// Table prefix. All tables will start with this, if defined. Can be useful if you have multiple applications sharing a single database\n";
echo 'define (\'DB_PREFIX\',\''.DB_PREFIX."');\n";
echo "// MySQL user name & password. User must have SELECT, INSERT, UPDATE & DELETE privileges\n";
echo 'define (\'DB_USER\',\''.utf8_encode(DB_USER)."');\n";
echo 'define (\'DB_PASS\',\''.utf8_encode(DB_PASS)."');\n";

echo "\n// The following items are used for encryption
// For each one, use a long (a least twice as long as the minimum password length) string of random characters.
// Do not use an intelligible phrase or word. Do not use single or double quote marks (' or \")
// Key for encryption of OOC data\n";
echo 'define (\'CRYPT_KEY\',\''.utf8_encode(CRYPT_KEY)."');\n";
echo "// Password salts\n";
echo 'define (\'PW_SALT\',\''.utf8_encode(PW_SALT)."');\n";
echo 'define (\'OLD_PW_SALT\',\''.utf8_encode(OLD_PW_SALT)."');\n";

echo "\n// Web site address where the system is hosted.
// Include the  'http://' (or 'https://') prefix and trailing '/' but do not include 'index.php'.
// Examples:
// 'http://www.domain.tld/' is valid
// 'http://www.domain.tld' is invalid
// 'http://www.domain.tld/index.php' is invalid
// 'www.domain.tld/' is invalid\n";
echo 'define (\'SYSTEM_URL\',\''.SYSTEM_URL."');\n\n";
echo "// Set to True to enable debug mode (errors & warnings are reported) DO NOT USE EXCEPT WHEN TESTING
// Note that True and False are not enclosed in quote marks\n";
if (DEBUG_MODE)
	echo 'define (\'DEBUG_MODE\',True'.");\n";
else
	echo 'define (\'DEBUG_MODE\',False'.");\n";
echo "// Set to True to enable maintenance mode (ie users will be shown a 'site is down for maintenance' message)\n";
if (MAINTENANCE_MODE)
	echo 'define (\'MAINTENANCE_MODE\',True'.");\n";
else
	echo 'define (\'MAINTENANCE_MODE\',False'.");\n";

echo "\n// Log files. These must be writeable by the web server user
// One file can be used for both if desired - just specify the same file name for both
// If no logs are required (not recommended), set both to ''
// NOTE THAT THIS IS NOT A URL - IT IS A LOCAL FILE. So, for instance,
// on a Windows server, this might be something like 'c:\web\bitsand\log.txt',
// on a Unix/Linux server, it might be something like '/home/web/bitsand.log'\n";
echo 'define (\'WARNING_LOG\',\''.WARNING_LOG."');\n";
echo 'define (\'ERROR_LOG\',\''.ERROR_LOG."');\n";
echo "\n/* Items below here should not need to be edited */\n";
echo 'define (\'NUM_GUILDS\','. (int) NUM_GUILDS.");\n";
echo 'define (\'MAX_CHAR_PTS\','. (int) MAX_CHAR_PTS.");\n";
echo 'define (\'MAX_NPC_PTS\','. (int) MAX_NPC_PTS.");\n";
echo 'define (\'MAX_OSPS\','. (int) MAX_OSPS.");\n";
echo "?>\n";
?>
