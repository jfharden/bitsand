<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File admin/admin_config_db_test.php
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
include ($CSS_PREFIX . 'inc/inc_head_db.php');
include ($CSS_PREFIX . 'inc/inc_admin.php');
include ($CSS_PREFIX . 'inc/inc_forms.php');
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
?>
<h1>Configuration Tests</h1>

<p>
This page performs some basic tests on the system configuration. It does not check absolutely everything, but is at least a quick check of the most important things. Anything that may require attention is in <span class = 'sans-warn'>bold red</span> text.
</p>

<p>
<?php
string_check (EVENT_CONTACT_NAME, 'EVENT_CONTACT_NAME', '');
email_check (EVENT_CONTACT_MAIL, 'EVENT_CONTACT_MAIL');
string_check (TECH_CONTACT_NAME, 'TECH_CONTACT_NAME', '');
email_check (TECH_CONTACT_MAIL, 'TECH_CONTACT_MAIL');
echo "</p>\n<p>\n";

string_check (TITLE, 'TITLE', '');
string_check (SYSTEM_NAME, 'SYSTEM_NAME', 'Bitsand');
string_check (DEFAULT_FACTION, 'DEFAULT_FACTION', 'Lions');
echo "<p>\n";

if (MIN_PASS_LEN < 8)
	echo "<span class = 'sans-warn'>Minimum password length is short (" . MIN_PASS_LEN . ")</span><br>";
else
	echo "MIN_PASS_LEN: " . MIN_PASS_LEN . "<br>";
echo "<p>\n";

if (USE_PAY_PAL == False)
	echo "USE_PAY_PAL: False<br>";
else {
	echo "USE_PAY_PAL: True<br>";
	email_check (PAYPAL_EMAIL, 'PAYPAL_EMAIL');
	if (PAYPAL_AUTO_MARK_PAID)
		echo "People paying with PayPal <b>will</b> be automatically marked as paid";
	else
		echo "People paying with PayPal will <b>not</b> be automatically marked as paid";
}

include ('../inc/inc_foot.php');
?>
