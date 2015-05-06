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

include ('inc/inc_head_db.php');
$db_prefix = DB_PREFIX;

// read the post from PayPal system and add 'cmd'
$req = 'cmd=_notify-validate';

foreach ($_POST as $key => $value) {
	$value = urlencode (stripslashes ($value));
	$req .= "&$key=$value";
}

// post back to PayPal system to validate
$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen ($req) . "\r\n\r\n";
$fp = fsockopen ('www.paypal.com', 80, $errno, $errstr, 30);

// assign posted variables to local variables
$item_name = $_POST ['item_name'];
$item_number = $_POST ['item_number'];
$payment_status = $_POST ['payment_status'];
$payment_amount = $_POST ['mc_gross'];
$payment_currency = $_POST ['mc_currency'];
$txn_id = $_POST ['txn_id'];
$receiver_email = $_POST ['receiver_email'];
$payer_email = $_POST ['payer_email'];
$custom = $_POST['custom'];

if (!$fp) {
	// HTTP ERROR
	LogError ("There was a problem validating a PayPal payment. (HTTP error when trying to POST to www.paypal.com)\n $item_name");
	fnMailer ("There was a problem validating a PayPal payment.\n" .
		"Payment will have to be manually processed\n $item_name", True);
}
else {
	fputs ($fp, $header . $req);
	while (!feof ($fp)) {
		$res = fgets ($fp, 1024);
		if (strcmp ($res, "VERIFIED") == 0) {
			if (strtolower ($payment_status) == 'completed' && $receiver_email == PAYPAL_EMAIL) {
				//Payment received. Send e-mail to event contact
				fnMailer ("Payment received:\n$item_name\n$payment_amount $payment_currency");

				//Only mark as paid if configured to do so.
				if (PAYPAL_AUTO_MARK_PAID)
				{
					//Mark as paid. 
					//Custom value is the bookingid
					//Don't set bkAmountExpected, and we add to amount paid, allowing potential for partial payments in future.
					$custom = (int)$custom;
					$sql = "UPDATE {$db_prefix}bookings SET bkDatePaymentConfirmed = '" . date ('Y-m-d') . "', bkAmountPaid = bkAmountPaid + ".$payment_amount." WHERE bkID = " . $custom;
					//Run UPDATE query to set paid date
					ba_db_query ($link, $sql);
										
					//Mark bunk as allocated if one was requested
					$sql = "UPDATE {$db_prefix}bookings SET bkBunkAllocated = 1 WHERE bkBunkRequested = 1 and bkID = " . $custom;
					//Run UPDATE query to set assign bunk
					ba_db_query ($link, $sql);
				}

				//Get details for e-mail
				$sql_select = "SELECT plFirstName, plSurname, plEmail FROM {$db_prefix}players WHERE plPlayerID = " . $item_number;
				$result = ba_db_query ($link, $sql_select);
				$row = ba_db_fetch_assoc ($result);
				//Send e-mail
				$sBody = "Your payment for the upcoming event has been received.\n";
				if (PAYPAL_AUTO_MARK_PAID)
				{
					$sBody .= "You are now fully booked.\n\n";
				}
				else
				{
					$sBody .= "You will be fully booked once your booking has been confirmed by a system administrator.\n\n";
				}
				$sBody .= "Thank you.\n\n";
				$sBody .= "Player ID: " . PID_PREFIX . sprintf ('%03s', $iPlayerID) . "\n";
				$sBody .= "OOC Name: " . $row ['plFirstName'] . " " . $row ['plSurname'];
				if ($bEmailPaymentReceived)
					mail ($row ['plEmail'], SYSTEM_NAME . ' - payment received', $sBody, "From:" . SYSTEM_NAME . " <" . EVENT_CONTACT_MAIL . ">");
				
				//Clear any payment requests for this booking
				$sql = "delete from {$db_prefix}paymentrequests where prBookingID = ".$custom;
				$result = ba_db_query($link, $sql);
			}
		}
		else if (strcmp ($res, "INVALID") == 0) {
			// log for manual investigation
			LogError ("There was a problem with PayPal payment - PayPal returned 'INVALID' when verifying payment.\n" .
				"Item name; '$item_name'");
			fnMailer ("There was a problem with PayPal payment - PayPal returned 'INVALID' when verifying payment." .
				"Payment will have to be manually processed.\n" .
				"Item name; '$item_name'", True);
		}
	}
	fclose ($fp);
}

include ('inc/inc_foot.php');
?>
