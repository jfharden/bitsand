<?php
/*
Bitsand - a web-based booking system for LRP events
Copyright (C) 2006 - 2014 The Bitsand Project (http://bitsand.googlecode.com/)

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

function generatePaypalButton($itemname, $itemnumber, $amount, $custom, $amountid = 'amount')
{
			echo "<form class='paypalbutton' target='paypal' action='https://www.paypal.com/cgi-bin/webscr' method='post'>\n";
			echo "<input type='hidden' name='cmd' value='_xclick'>\n";
			echo "<input type='hidden' name='business' value='" . PAYPAL_EMAIL . "'>\n";
			echo "<input type='hidden' name='item_name' value='$itemname'>\n";
			echo "<input type='hidden' name='item_number' value='$itemnumber'>\n";
			echo "<input type='hidden' name='custom' value='".$custom."'>\n";
			echo "<input type='hidden' name='cbt' value='Return to " . SYSTEM_NAME . "'>\n";
			echo "<input type='hidden' name='currency_code' value='GBP'>\n";
			echo "<input type='hidden' name='amount' id='$amountid' value='" . $amount . "'>\n";
			echo "<input type='hidden' name='no_shipping' value='1'>\n";
			echo "<input type='hidden' name='return' value='" . SYSTEM_URL . "start.php?green=PayPal%20payment%20completed'>\n";
			echo "<input type='hidden' name='cancel_return' value='" . SYSTEM_URL . "start.php?warn=PayPal%20payment%20cancelled'>\n";
			echo "<input type='hidden' name='notify_url' value='" . SYSTEM_URL . "paypal_receipt.php'>\n";
			echo "<input type='image' src='http://images.paypal.com/images/x-click-but01.gif' name='submit' alt='Make payments with PayPal - fast, free and secure!'>\n";
			echo "</form>\n";
}
?>
