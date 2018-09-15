<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File paypal_receipt.php
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

// This page is for Paypal to call back to, there is no login for a user on this endpoint
$bLoginCheck = False;

include ('inc/inc_head_db.php');
$db_prefix = DB_PREFIX;

function paypalIPNVerification() {
	// Taken from Paypal example https://developer.paypal.com/docs/classic/ipn/ht_ipn/#do-it
	// and modified just a little for readability

	// STEP 1: read POST data
	// Reading POSTed data directly from $_POST causes serialization issues with array data in the POST.
	// Instead, read raw POST data from the input stream.
	$raw_post_data = file_get_contents('php://input');
	$raw_post_array = explode('&', $raw_post_data);

	$myPost = array();
	foreach ($raw_post_array as $keyval) {
		$keyval = explode ('=', $keyval);
		if (count($keyval) == 2)
			$myPost[$keyval[0]] = urldecode($keyval[1]);
	}

	// read the IPN message sent from PayPal and prepend 'cmd=_notify-validate'
	$req = 'cmd=_notify-validate';

	if (function_exists('get_magic_quotes_gpc')) {
		$get_magic_quotes_exists = true;
	}

	foreach ($myPost as $key => $value) {
		if ($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
			$value = urlencode(stripslashes($value));
		} else {
			$value = urlencode($value);
		}
		$req .= "&$key=$value";
	}

	// Step 2: POST IPN data back to PayPal to validate
	$ch = curl_init('https://ipnpb.paypal.com/cgi-bin/webscr');
	curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

	$res = curl_exec($ch);

	if ($res === false) {
		LogError("CURL ERROR with Paypal " . curl_error($ch) . " when processing IPN data");
		curl_close($ch);
		return false;
	}
	curl_close($ch);

	return $res;
}

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

$paypalResponse = paypalIPNVerification();

if ($paypalResponse === false) {
	LogError ("There was a problem validating a PayPal payment. (HTTP error when trying to POST to https://ipnpb.paypal.com/cgi-bin/webscr)\n $item_name");
	fnMailer ("There was a problem validating a PayPal payment.\n" .
		"Payment will have to be manually processed\n $item_name", True);
}
else {
	if (strcmp ($paypalResponse, "VERIFIED") == 0) {
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
	else if (strcmp ($paypalResponse, "INVALID") == 0) {
		// log for manual investigation
		LogError ("There was a problem with PayPal payment - PayPal returned 'INVALID' when verifying payment.\n" .
			"Item name; '$item_name'");
		fnMailer ("There was a problem with PayPal payment - PayPal returned 'INVALID' when verifying payment." .
			"Payment will have to be manually processed.\n" .
			"Item name; '$item_name'", True);
	}
	else {
		LogError ("There was a problem with PayPal payment - PayPal returned an unknown response when verifying payment.\n" .
			"Item name; '$item_name'\n" .
			"Paypal response was: \n-------------------------------\n" . $paypalResponse
		);
		fnMailer ("There was a problem with PayPal payment - PayPal returned an unknown response when verifying payment." .
			"Payment will have to be manually processed.\n" .
			"Item name; '$item_name'", True);
	}
}

include ('inc/inc_foot.php');
