<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File admin/admin_bookings.php
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

error_reporting(E_ALL);
ini_set('display_errors', '1');

include ('../inc/inc_head_db.php');
include ('../inc/inc_admin.php');
include ('../inc/inc_head_html.php');
include ('../inc/inc_commonqueries.php');

$db_prefix = DB_PREFIX;
$key = CRYPT_KEY;

	$newbooking = 0;
	$bookingid = (int)htmlentities (stripslashes($_GET['BookingID']));
	$bookingitemid = (int)htmlentities (stripslashes($_GET['BookingItemID']));
	$playerid = (int)htmlentities (stripslashes($_GET['PlayerID']));
	$eventid = (int)htmlentities (stripslashes($_GET['EventID']));

	if ($bookingitemid > 0 && CheckReferrer ('admin_booking.php'))
	{
		$sql = "delete from {$db_prefix}bookingitems where biBookingItemID = $bookingitemid";
		$result = ba_db_query ($link, $sql);
		resetExpectedAmount($bookingid);
	}

	//Create default event booking record, to be updated further down.
	if ($_POST ['btnSave'] != '' && $playerid > 0 && $eventid > 0 && CheckReferrer ('admin_booking.php'))
	{
		$bookas = htmlentities(stripslashes($_POST['cboBookAs']));
		$expectedvalue = htmlentities(stripslashes($_POST['txtOverriddenExpectedValue']));
		$bookingsql = "insert into {$db_prefix}bookings (bkPlayerID, bkBookAs, bkEventID, bkDateICConfirmed, bkDateOOCConfirmed, bkAmountPaid, bkAmountExpected, bkInQueue) VALUES (".$playerid.", '".$bookas."', ".$eventid.", '".$today."', '".$today."', 0, ".$expectedvalue.", 0)";
		$result = ba_db_query ($link, $bookingsql);
		$bookingid = ba_insert_id($link);
		$playerid = 0;
		$eventid = 0;
		$newbooking = 1;
	}

	//Get event and booking details
	if ($bookingid > 0)
	{
		$bookingsql = "Select * FROM {$db_prefix}bookings inner join {$db_prefix}events on evEventID = bkEventID inner join {$db_prefix}players on plPlayerID = bkPlayerID where bkID = " . $bookingid;
	}
	else
	{
		$bookingsql = "Select * FROM {$db_prefix}events cross join {$db_prefix}players where evEventID = " . $eventid." and plPlayerID = ". $playerid;
	}

	$result = ba_db_query ($link, $bookingsql);
	$bookinginfo = ba_db_fetch_assoc ($result);

	if ($playerid == 0 && ba_db_num_rows($result) == 0)
	{
		$sMsg = "You cannot view this booking";
		$sURL = fnSystemURL () . 'admin.php?warn=' . urlencode ($sMsg);
		header ("Location: $sURL");
	}

	$eventinfo = getEventDetails($bookinginfo['evEventID'], 0);

	//Delete
	if (($_POST ['btnDelete'] != '' || $_POST ['btnDeleteAndRebook'] != '') && CheckReferrer ('admin_booking.php'))
	{
		if ($_POST['txtConfirm'] == 'CONFIRM')
		{
			deleteBooking($bookinginfo['bkID']);
			if ($_POST ['btnDelete'] != '')
			{
				$sURL = fnSystemURL () . 'admin_manageevent.php?EventID='.$bookinginfo['bkEventID'];
			}
			else
			{
				$sURL = fnSystemURL () . 'admin_booking.php?PlayerID='.$bookinginfo['bkPlayerID']."&EventID=".$bookinginfo['bkEventID'];
			}
			header ("Location: $sURL");
		}
	}

	//Update
	if ($_POST ['btnSave'] != '' && CheckReferrer ('admin_booking.php'))
	{
		$bookas = htmlentities(stripslashes($_POST['cboBookAs']));
		$overriddenvalue = htmlentities(stripslashes($_POST['txtOverriddenExpectedValue']));
		$overrideexpected = (int)$_POST['chkOverride'];
		$overriddenpaidvalue = htmlentities(stripslashes($_POST['txtOverriddenAmountPaid']));
		$overridepaid = (int)$_POST['chkOverridePaid'];



		$sql = "update {$db_prefix}bookings set bkID = $bookingid";
		if ($bookas != '') { $sql .= ",  bkBookAs = '$bookas'"; }
		if ($overrideexpected == 1) { $sql .= ", bkAmountExpected = $overriddenvalue "; }
		else { $overrideexpected = 0; }
		if ($overridepaid == 1) {
			$sql .= ", bkAmountPaid = $overriddenpaidvalue ";
			if ($overriddenpaidvalue > 0)
			{
				$sql .= ", bkDatePaymentConfirmed = '".$today."'";
			}
		}
		$sql .=  " where bkID = $bookingid";

		ba_db_query ($link, $sql);

		foreach ($_POST as $key => $value) {
			if (substr ($key, 0, 7) == "chkitem") {
				$itemid = (int)str_replace("chkitem", "", $key);
				if ($itemid > 0)
				{
					$newitemsql = "insert into {$db_prefix}bookingitems (biBookingID, biItemID, biQuantity) VALUES ($bookingid, $itemid, 1)";
					ba_db_query ($link, $newitemsql);
				}
			}
			if (substr ($key, 0, 7) == "txtitem") {
				$itemid = (int)str_replace("txtitem", "", $key);
				if ($itemid > 0)
				{
					$value = (int)$value;
					$newitemsql = "insert into {$db_prefix}bookingitems (biBookingID, biItemID, biQuantity) VALUES ($bookingid, $itemid, $value)";
					ba_db_query ($link, $newitemsql);
				}
			}
		}

		if ($overrideexpected == 0) {
			resetExpectedAmount($bookingid);
		}
	}

	if ($newbooking == 1)
	{
		$sURL = fnSystemURL () . 'admin_booking.php?BookingID=' . $bookingid;
		header ("Location: $sURL");
	}

	$sql = "select * from {$db_prefix}bookingitems inner join {$db_prefix}items where biItemID = itItemID and biBookingID = $bookingid";

	$result = ba_db_query ($link, $sql);
	$itemselected = array();
	$usedidlist = "";
	//Building a list of items for this booking, we'll update bunk and meal status here as well
	$bunkandmealstatussql = "update {$db_prefix}bookings set bkBunkRequested = 0, bkBunkAllocated = 0, bkMealTicket = 0";
	while ($row = ba_db_fetch_assoc ($result))
	{
		array_push($itemselected, $row);
		$usedidlist .= $row['itItemID'] .",";

		if ($row['itBunk'] == 1) { $bunkandmealstatussql .= ", bkBunkRequested = 1, bkBunkAllocated = 1"; }
		if ($row['itMeal'] == 1) { $bunkandmealstatussql .= ", bkMealTicket = 1"; }
	}
	$bunkandmealstatussql .= " where bkID = $bookingid";
	$result = ba_db_query ($link, $bunkandmealstatussql);
	$usedidlist .= "-1";

	$sql = "select * from {$db_prefix}items where (itAvailability = '".$bookinginfo['bkBookAs']."' or itAvailability = 'All') AND itItemID not in ($usedidlist) and itEventID = ".$bookinginfo['evEventID'];
	$result = ba_db_query ($link, $sql);
	$itemnotselected = array();
	while ($row = ba_db_fetch_assoc ($result))
	{
		array_push($itemnotselected, $row);
	}

	//Finished all our updates, make sure the booking info is up current
	$result = ba_db_query ($link, $bookingsql);
	$bookinginfo = ba_db_fetch_assoc ($result);
?>

<script type="text/javascript">
	var notselecteditems = new Array();

	<?php
	foreach($itemnotselected as $item)
	{
		echo "notselecteditems[".$item['itItemID']."] = new Array('".$item['itDescription']."',".$item['itItemCost'].",'".$item['itAllowMultiple']."');\n";
	}
	?>
	function addnewitem()
	{
		var newItemRow = "<tr>";
		var itemid = $('#cboItem').val();
		if (itemid > 0)
		{
			newItemRow += "<td>" + notselecteditems[itemid][0] + "</td>";
			newItemRow += "<td>&pound;" + notselecteditems[itemid][1] + "</td>";
			if (notselecteditems[itemid][2] == 1)
			{
				newItemRow += "<td><input class='multipleitem' cost='"+ notselecteditems[itemid][1] +"' type=text value=0 name='txtitem" + itemid + "' /></td>";
			}
			else
			{
				newItemRow += "<td><input class='singleitem' cost='"+ notselecteditems[itemid][1] +"' type=checkbox value=1 name='chkitem" + itemid + "' checked /></td>";
			}
			$("#cboItem option[value='"+itemid+"']").remove();
   			if ($('#cboItem option').size() == 0) { $('#cboItem').hide(); $('#btnAdd').hide(); }
			newItemRow += "</tr>";
			$('#itemtable tr:last').after(newItemRow);
			changetotal();
		}
	}

	function changetotal()
	{
		var bookingtotal = 0;
		$('.singleitem').each(function() {
			var cost = $(this);
			if (cost.attr('checked'))
			{
				bookingtotal += parseFloat(cost.attr('cost'));
			}
			}
		);

		$('.multipleitem').each(function() {
			var cost = $(this);
			bookingtotal += (parseFloat(cost.attr('cost')) * cost.val());
			}
		);

		$('#bookingtotal').text(bookingtotal.toFixed(2));
		$('#txtOverriddenExpectedValue').text(bookingtotal.toFixed(2));
		$('#txtOverriddenExpectedValue').val(bookingtotal.toFixed(2));
	}

	function setvisibility(target)
	{
		if (target == "expected")
		{
			if ($('#chkOverride').attr('checked')) { $('#txtOverriddenExpectedValue').show(); } else {$('#txtOverriddenExpectedValue').hide(); }
		}
		if (target == "paid")
		{
			if ($('#chkOverridePaid').attr('checked')) { $('#txtOverriddenAmountPaid').show(); } else {$('#txtOverriddenAmountPaid').hide(); }
		}
	}

	function showitems()
	{
		var bookingtype = $('#cboBookAs').val();
		$(".bookingitems tr").hide();
		$('.singleitem').attr('checked', false);
		$('.multipleitem').val(0);
		$("#bookingtotal").innerHTML = '0';
		$('.'+bookingtype).fadeTo("slow", 1.0);
		$('.All').fadeTo("slow", 1.0);
		$('.bookingitems tfoot tr').fadeTo("slow", 1.0);

		var defaultSet = false;
		$('.singleitem').each(function() {
			var defaultItem = $(this);

			if (defaultItem.attr('ticket') == 1 && defaultItem.is(":visible") && !defaultSet)
			{
				defaultItem.attr('checked', true);
				defaultSet = true;
				changetotal();
			}

			/*
			if (defaultItem.attr('bunk') == 1)
			{
				var bunkRow = $('#' + defaultItem.attr('id').replace('chk','row'));
				if (bookingtype == 'Player' && !PlayerBunkAvailable) { bunkRow.hide(); }
				if (bookingtype == 'Monster' && !MonsterBunkAvailable) {  bunkRow.hide(); }
				if (bookingtype == 'Staff' && !StaffBunkAvailable) {  bunkRow.hide(); }
			}
			*/
		}

		);
	}
</script>

<h1><?php echo TITLE?> - Edit Individual Booking</h1>
<p>
<a href = 'admin_manageevent.php?EventID=<?php echo $bookinginfo['evEventID'];?>'>Return to event management for - <?php echo htmlentities (stripslashes ($bookinginfo['evEventName']));?></a>
</p>
<p>
<a href = 'admin_bookingstatus.php?EventID=<?php echo $bookinginfo['evEventID'];?>'>Return to booking management for - <?php echo htmlentities (stripslashes ($bookinginfo['evEventName']));?></a>
</p>

<?php
	echo "<h2>".PID_PREFIX . sprintf ('%03s', $bookinginfo ['plPlayerID'])." - ".$bookinginfo['plFirstName']." ".$bookinginfo['plSurname']."</h2>";
	echo "<p class='warn'>Warning - As an admin you can make bookings even when there are no marked spaces, or when tickets are no longer valid. Please be careful.</p>";


	if ($bookingid == 0)
	{
		//New booking
		echo "<form action='admin_booking.php?PlayerID=$playerid&EventID=$eventid' method='POST'>";
		echo "<p>Booking type: \n";
		echo "<select id='cboBookAs' name='cboBookAs' onChange='showitems()'>\n";

		//Not limiting by space, trust admins to be sensible
		echo "<option value='Player'>Player</option>";
		if ($eventinfo['evAllowMonsterBookings']) { echo "<option value='Monster'>Monster</option>";}
		echo "<option value='Staff'>".$stafftext."</option>";
		echo "</select></p>";

		$sql = "SELECT * from {$db_prefix}items where itEventID = ".$eventinfo['evEventID'];
		$result = ba_db_query ($link, $sql);

		echo "\n<input type='hidden' value='".$eventinfo['evEventID']."' name='eventid'/>";
		echo "\n<table class='bookingitems'>\n";
		while ($iteminfo = ba_db_fetch_assoc ($result))
		{
			echo "<tr class='".$iteminfo['itAvailability']."' id='row".$iteminfo['itItemID']."'><td class='description'>".$iteminfo['itDescription']."</td><td>&pound;".$iteminfo['itItemCost']."</td><td>";
			if ($iteminfo['itAllowMultiple']) {
				echo "<select class='multipleitem' name='cbo".$iteminfo['itItemID']."' id='cbo".$iteminfo['itItemID']."' onChange='changetotal()' cost='".$iteminfo['itItemCost']."'>";
				for($i=0; $i<31;$i++)
				{
					echo "<option value='$i'>$i</option>";
				}
				echo "</select>";
			}
			else {
				echo "<input type='checkbox' class='singleitem' name='chkitem".$iteminfo['itItemID']."' id='chk".$iteminfo['itItemID']."' onClick='changetotal()' cost='".$iteminfo['itItemCost']."' ticket='".$iteminfo['itTicket']."' bunk='".$iteminfo['itBunk']."'";
				if ($iteminfo['itMandatory'] == 1) {
					echo " checked='checked' />";
				}
				else
				{
					echo "/>";
				}
			  }
			echo "</td></tr>\n";
		}
		echo "<tfoot>";
		echo "<tr class='total'><td class='totallabel' colspan=2>Total:</td><td>&pound;<span id='bookingtotal'>0.00</span></td></tr>";
		echo "</tfoot>";
		echo "\n</table>";
		echo "\n<script>showitems();</script>";
	}
	else
	{
		//Editing a booking
		echo "<h3>Edit Booking</h3>";
		echo "<p>This is a ".$bookinginfo['bkBookAs']." booking.</p>";
		echo "<form action='admin_booking.php?BookingID=$bookingid' method='POST'>";
		echo "<table id='itemtable'>";
		echo "<tr><th>Description</th><th>Cost</th><th>Quantity</th></tr>";
		echo "<tfoot>";
		echo "<tr><td>Expected Payment:</td><td>&pound;<span id='bookingtotal'>".(is_null($bookinginfo['bkAmountExpected']) ? 0.00 : $bookinginfo['bkAmountExpected'])."</span></td></tr>";
		echo "<tr><td>Amount Recieved:</td><td>&pound;<span id='bookingtotal'>".(is_null($bookinginfo['bkAmountPaid']) ? 0.00 : $bookinginfo['bkAmountPaid'])."</span></td></tr>";
		echo "</tfoot>";

		//while ($row = ba_db_fetch_assoc ($result))
		foreach($itemselected as $row)
		{
			echo "<tr><td>".$row['itDescription']."</td><td>&pound;".$row['itItemCost']."</td><td>";
			echo $row['biQuantity'];
			$cost = $row['itItemCost']*$row['biQuantity'];
			echo "<input class='singleitem' cost='$cost' type=checkbox checked style='display:none;' /></td>";
			echo "<td><a href='admin_booking.php?BookingID=$bookingid&BookingItemID=".$row['biBookingItemID']."'>Remove</a></td>";
			echo "</tr>";
		}
		echo "</table>";
		if (count($itemnotselected) > 0)
		{
			echo "<p>";
			echo "<select name='cboItem' id='cboItem'>";
			foreach($itemnotselected as $item)
			{
				echo "<option value='".$item['itItemID']."'>".$item['itDescription']."</option>";
			}
			echo "</select>";
			echo "<input type='button' name='btnAdd' id='btnAdd' value='Add new item' onClick='addnewitem()'/><br/>";
			echo "</p>";
		}
	}


	?>
	<p>
	Override expected amount:<input type='checkbox' value=1 id='chkOverride' name='chkOverride' onClick='setvisibility("expected")'/><input id='txtOverriddenExpectedValue' type='text' name='txtOverriddenExpectedValue' value=<?php echo (is_null($bookinginfo['bkAmountExpected']) ? 0.00 : $bookinginfo['bkAmountExpected']);?> /><br/>
	Set amount paid:<input type='checkbox' value=1 id='chkOverridePaid' name='chkOverridePaid' onClick='setvisibility("paid")'/><input id='txtOverriddenAmountPaid' type='text' name='txtOverriddenAmountPaid' value=<?php echo (is_null($bookinginfo['bkAmountPaid']) ? 0.00 : $bookinginfo['bkAmountPaid']);?> /><br/>
	<input type='submit' name='btnSave' value='Save Booking' />
	</p>
	</form>

<?php
	if ($bookingid != 0)
	{
		echo "<hr />";
		echo "<h3>Delete Booking</h3>";
		echo "<form action='admin_booking.php?BookingID=$bookingid' method='POST'>";
		echo "<p>Delete booking (enter 'CONFIRM' in the box): ";
		echo "<input type='text' name='txtConfirm' /> ";
		echo "<input type='submit' name='btnDeleteAndRebook' value='Delete and Rebook' />";
		echo "<input type='submit' name='btnDelete' value='Delete' />";
		echo "</p></form>";

	}
?>

<script type="text/javascript">
$('#txtOverriddenExpectedValue').hide();
$('#txtOverriddenAmountPaid').hide();
</script>


<?php
include ('../inc/inc_foot.php');
?>
