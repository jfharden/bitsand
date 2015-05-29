<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File help/help_config_paypal.php
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

include ('inc_help_head.php');
?>

<p><strong>USE_PAY_PAL:</strong> Check this value to allow payments via Paypal.</p>
<p><strong>PAYPAL_EMAIL:</strong> E-mail address for PayPal payments to be sent to.</p>
<p><strong>PAYPAL_PLAYER_1, PAYPAL_PLAYER_2, PAYPAL_PLAYER_3, PAYPAL_PLAYER_4,<br>
PAYPAL_MONSTER_1, PAYPAL_MONSTER_2, PAYPAL_MONSTER_3, PAYPAL_MONSTER_4,<br>
PAYPAL_STAFF_1, PAYPAL_STAFF_2, PAYPAL_STAFF_3, PAYPAL_STAFF_4:</strong><br>
Descriptive name of item to be paid for. Player ID will be automatically appended. This will be included in the e-mail sent to the above address. It will not be displayed on the web page.<br>
In each case:</p>
<p>
Option 1 is for booking with neither meal ticket nor bunk.<br>
Option 2 is for booking with meal ticket and a bunk.<br>
Option 3 is for booking with no meal ticket, and a bunk.<br>
Option 4 is for booking with meal ticket and no bunk.<br>
All are optional, but at least one must be specified.
</p>
<p><strong>PAYPAL_AMOUNT_P1, PAYPAL_AMOUNT_P2, PAYPAL_AMOUNT_P3, PAYPAL_AMOUNT_P4,<br>
PAYPAL_AMOUNT_M1, PAYPAL_AMOUNT_M2, PAYPAL_AMOUNT_M3, PAYPAL_AMOUNT_M4,<br>
PAYPAL_AMOUNT_S1, PAYPAL_AMOUNT_S2, PAYPAL_AMOUNT_S3, PAYPAL_AMOUNT_S4:</strong><br>
Amount to charge. If the booking is not charged, set it to 0.00, if the booking type is not used use a blank value.</p>
<p><strong>PAYPAL_AUTO_MARK_PAID:</strong> If true, PayPal payments are automatically marked as paid, otherwise they must be manually accepted.</p>
<?php
include ('inc_help_foot.php');