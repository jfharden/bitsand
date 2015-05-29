<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File inc/inc_config_dist.php.php
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
include ('../inc/inc_admin.php');
include ('../inc/inc_head_html.php');
include ('../inc/inc_forms.php');
include ('../inc/inc_commonqueries.php');

$eventid = (int)htmlentities(stripslashes($_GET['EventID']));

if ($_POST ['btnSubmit'] != '' && CheckReferrer ('admin_editeventdetails.php')) {
//print_r($_POST);
echo "<br/>";
	if ($eventid > 0)
	{
		//Update
		$updatequery = "UPDATE {$db_prefix}events set ";
		$updatequery.= "evEventName = '".ba_db_real_escape_string($link, $_POST ['txtEventName'])."', ";
		$updatequery.= "evEventDetails = '".ba_db_real_escape_string($link, $_POST ['txtEventDetails'])."', ";
		$updatequery.= "evEventDescription = '".ba_db_real_escape_string($link, $_POST ['txtEventDescription'])."', ";
		$updatequery.= "evPlayerSpaces = '".(int)ba_db_real_escape_string($link, $_POST ['txtPlayerSpaces'])."', ";
		$updatequery.= "evMonsterSpaces = '".(int)ba_db_real_escape_string($link, $_POST ['txtMonsterSpaces'])."', ";
		$updatequery.= "evStaffSpaces = '".(int)ba_db_real_escape_string($link, $_POST ['txtStaffSpaces'])."', ";
		$updatequery.= "evTotalSpaces = '".(int)ba_db_real_escape_string($link, $_POST ['txtTotalSpaces'])."', ";
		$updatequery.= "evPlayerBunks = '".(int)ba_db_real_escape_string($link, $_POST ['txtPlayerBunks'])."', ";
		$updatequery.= "evMonsterBunks = '".(int)ba_db_real_escape_string($link, $_POST ['txtMonsterBunks'])."', ";
		$updatequery.= "evStaffBunks = '".(int)ba_db_real_escape_string($link, $_POST ['txtStaffBunks'])."', ";
		$updatequery.= "evTotalBunks = '".(int)ba_db_real_escape_string($link, $_POST ['txtTotalBunks'])."', ";
		$updatequery.= "evAllowMonsterBookings = ".setBoolValue($_POST ['chkAllowMonsterBookings']).", ";
		$updatequery.= "evUseQueue = ".setBoolValue($_POST ['chkUseQueue']).", ";
		$updatequery.= "evEventDate = '".(int)$_POST ['selEventDateYear']."-".(int)$_POST ['selEventDateMonth']."-".(int)$_POST ['selEventDateDate']."', ";
		$updatequery.= "evBookingsOpen = '".(int)$_POST ['selBookingsOpenYear']."-".(int)$_POST ['selBookingsOpenMonth']."-".(int)$_POST ['selBookingsOpenDate']."', ";
		$updatequery.= "evBookingsClose = '".$_POST ['selBookingsCloseYear']."-".$_POST ['selBookingsCloseMonth']."-".$_POST ['selBookingsCloseDate']."' ";
		$updatequery .= "WHERE evEventID = $eventid";
		ba_db_query ($link, $updatequery);
	}
	else
	{
		//Insert
		$insertquery = "INSERT INTO {$db_prefix}events (";
		$insertquery.= "evEventName, evEventDetails, evEventDescription, evPlayerSpaces, evMonsterSpaces, evStaffSpaces, evTotalSpaces, ";
		$insertquery.= "evPlayerBunks, evMonsterBunks, evStaffBunks, evTotalBunks, evAllowMonsterBookings, evUseQueue, evEventDate, evBookingsOpen, evBookingsClose)";
		$insertquery.= "VALUES (";
		$insertquery.= "'".ba_db_real_escape_string($link, $_POST ['txtEventName'])."', ";
		$insertquery.= "'".ba_db_real_escape_string($link, $_POST ['txtEventDetails'])."', ";
		$insertquery.= "'".ba_db_real_escape_string($link, $_POST ['txtEventDescription'])."', ";
		$insertquery.= "'".(int)ba_db_real_escape_string($link, $_POST ['txtPlayerSpaces'])."', ";
		$insertquery.= "'".(int)ba_db_real_escape_string($link, $_POST ['txtMonsterSpaces'])."', ";
		$insertquery.= "'".(int)ba_db_real_escape_string($link, $_POST ['txtStaffSpaces'])."', ";
		$insertquery.= "'".(int)ba_db_real_escape_string($link, $_POST ['txtTotalSpaces'])."', ";
		$insertquery.= "'".(int)ba_db_real_escape_string($link, $_POST ['txtPlayerBunks'])."', ";
		$insertquery.= "'".(int)ba_db_real_escape_string($link, $_POST ['txtMonsterBunks'])."', ";
		$insertquery.= "'".(int)ba_db_real_escape_string($link, $_POST ['txtStaffBunks'])."', ";
		$insertquery.= "'".(int)ba_db_real_escape_string($link, $_POST ['txtTotalBunks'])."', ";
		$insertquery.= setBoolValue($_POST ['chkAllowMonsterBookings']).", ";
		$insertquery.= setBoolValue($_POST ['chkUseQueue']).", ";
		$insertquery.= "'".(int)$_POST ['selEventDateYear']."-".(int)$_POST ['selEventDateMonth']."-".(int)$_POST ['selEventDateDate']."', ";
		$insertquery.= "'".(int)$_POST ['selBookingsOpenYear']."-".(int)$_POST ['selBookingsOpenMonth']."-".(int)$_POST ['selBookingsOpenDate']."', ";
		$insertquery.= "'".(int)$_POST ['selBookingsCloseYear']."-".(int)$_POST ['selBookingsCloseMonth']."-".(int)$_POST ['selBookingsCloseDate']."' ";
		$insertquery .= ")";
		ba_db_query ($link, $insertquery);
		$eventidsql = "select max(evEventID) as newID from {$db_prefix}events";
		$result = ba_db_query ($link, $eventidsql);
		$eventidarray = ba_db_fetch_assoc($result);
		$eventid = $eventidarray['newID'];

	}

	//Deal with items
	$deletesql = "delete from {$db_prefix}items where itEventID = $eventid and itItemID in(".ba_db_real_escape_string($link, $_POST['hRemovedItemIDs']).")";
	ba_db_query ($link, $deletesql);

	foreach ($_POST as $key => $value) {
		if (substr ($key, 0, 7) == "hItemID") {
			$iItemID = (int)$value;
			$ticket = setBoolValue($_POST ["chkTicket{$value}"]);
			$meal = setBoolValue($_POST ["chkMeal{$value}"]);
			$bunk = setBoolValue($_POST ["chkBunk{$value}"]);
			$allowmultiple = setBoolValue($_POST ["chkAllowMultiple{$value}"]);
			$mandatory = setBoolValue($_POST ["chkMandatory{$value}"]);
			$itemdescription = ba_db_real_escape_string($link, $_POST ["txtItemDescription{$value}"]);
			$availability = ba_db_real_escape_string($link, $_POST ["cboAvailability{$value}"]);
			$availablefrom = ba_db_real_escape_string($link, $_POST ["txtAvailableFrom{$value}"]);
			$availableto = ba_db_real_escape_string($link, $_POST ["txtAvailableTo{$value}"]);
			$itemcost = sanitiseAmount($_POST ["txtItemCost{$value}"], True);

			if ($iItemID > 0)
			{
				$updatequery = "UPDATE {$db_prefix}items set ";
				$updatequery .=  "itTicket = $ticket, ";
				$updatequery .=  "itMeal = $meal, ";
				$updatequery .=  "itBunk = $bunk, ";
				$updatequery .=  "itAllowMultiple = $allowmultiple, ";
				$updatequery .=  "itMandatory = $mandatory, ";
				$updatequery .=  "itDescription = '$itemdescription', ";
				$updatequery .=  "itAvailability = '$availability', ";
				$updatequery .=  "itAvailableFrom = '$availablefrom', ";
				$updatequery .=  "itAvailableTo = '$availableto', ";
				$updatequery .=  "itItemCost = $itemcost ";
				$updatequery .= "WHERE itItemID = $iItemID";
				ba_db_query ($link, $updatequery);

				$itemidlist .= ",".$iItemID;
			}
			else
			{
				$insertquery = "insert into {$db_prefix}items ";
				$insertquery .= "(itTicket, itMeal, itBunk, itMandatory, itAllowMultiple, itDescription, itAvailability, itAvailableFrom, itAvailableTo, itItemCost, itEventID)";
				$insertquery .= " VALUES ";
				$insertquery .=  "($ticket, $meal, $bunk, $mandatory, $allowmultiple, '$itemdescription', '$availability', '$availablefrom', '$availableto',$itemcost, $eventid) ";
				ba_db_query ($link, $insertquery);
			}


		}
	}

}

if ($eventid > 0) { $eventinfo = getEventDetails($eventid, 0, 'admin.php'); }


?>

<script type="text/javascript" src="../inc/wysiwyg/jquery.wysiwyg.js"></script>
<script type="text/javascript" src="../inc/wysiwyg/wysiwyg.image.js"></script>
<script type="text/javascript" src="../inc/wysiwyg/wysiwyg.link.js"></script>
<script type="text/javascript" src="../inc/wysiwyg/wysiwyg.table.js"></script>
<script type="text/javascript">
$(document).ready(function() {
	$('#txtEventDetails').wysiwyg({
	initialContent: "Event Details",
	controls: {
		html: { visible : true }
	}
	});
	$('#txtEventDescription').wysiwyg({
	initialContent: "Event Description",
	controls: {
		html: { visible : true }
	}
	});
});


var newitemid = 0;

function pad(number, length) {

	var str = '' + number;
	while (str.length < length) {
		str = '0' + str;
	}

	return str;

}

function addnewitem() {
$('#itemtable tr:last').after(generateitemrow('','All',0,0,0,getselecteddateasstring('BookingsOpen'), getselecteddateasstring('BookingsClose'), 0,0,0));
}

function removeitem(itemid) {
	$('#rowItem' + itemid).remove();
	$('#hRemovedItemIDs').val($('#hRemovedItemIDs').val() + "," + itemid);
}

function adddefaultitems()
{
	var onemonthearly = getselecteddate('BookingsClose');
	onemonthearly.setMonth(onemonthearly.getMonth() - 1);
	var early_date = onemonthearly.getDate();
	var early_month = onemonthearly.getMonth();
	early_month++;
	var early_year = onemonthearly.getFullYear();
    var onemonthearlystring = early_year + "-" + pad(early_month, 2) + "-" +pad(early_date, 2);
	$('#itemtable tr:last').after(generateitemrow('Player Ticket (Early)','Player',1,0,0,getselecteddateasstring('BookingsOpen'), onemonthearlystring, 35, 0,1));

	onemonthearly.setDate(onemonthearly.getDate() + 1);
	var early_date = onemonthearly.getDate();
	var early_month = onemonthearly.getMonth();
	early_month++;
	var early_year = onemonthearly.getFullYear();
	var onemonthearlystring = early_year + "-" + pad(early_month, 2) + "-" +pad(early_date, 2);
	$('#itemtable tr:last').after(generateitemrow('Player Ticket (Late)','Player',1,0,0, onemonthearlystring, getselecteddateasstring('BookingsClose'), 45, 0,1));
	$('#itemtable tr:last').after(generateitemrow('Monster Ticket','Monster',1,0,0, getselecteddateasstring('BookingsOpen'), getselecteddateasstring('BookingsClose'), 0, 0,1));
	$('#itemtable tr:last').after(generateitemrow('Staff Ticket','Staff',1,0,0, getselecteddateasstring('BookingsOpen'), getselecteddateasstring('BookingsClose'), 0, 0,1));
	$('#itemtable tr:last').after(generateitemrow('Player Meal','Player',0,1,0, getselecteddateasstring('BookingsOpen'), getselecteddateasstring('BookingsClose'), 20, 0));
	$('#itemtable tr:last').after(generateitemrow('Monster Meal','Monster',0,1,0, getselecteddateasstring('BookingsOpen'), getselecteddateasstring('BookingsClose'), 10, 0));
	$('#itemtable tr:last').after(generateitemrow('Staff Meal','Staff',0,1,0, getselecteddateasstring('BookingsOpen'), getselecteddateasstring('BookingsClose'), 10, 0));
	$('#itemtable tr:last').after(generateitemrow('Bunk','All',0,0,1, getselecteddateasstring('BookingsOpen'), getselecteddateasstring('BookingsClose'), 0, 0));

	$('#btndefaultitemset').hide();
}

function generateitemrow(description, availability, ticket, meal, bunk, availablefrom, availableto, itemcost, allowmultiple, mandatory) {
	newitemid--;
	var itemid = newitemid;
	var rowcontents = "<tr id='rowItem" + itemid + "'>";
	rowcontents += "<td><input type='hidden' name='hItemID"+ itemid +"' value='" + itemid + "'/><input type='text' name='txtItemDescription"+ itemid +"' value='" + description + "' /></td>";
	rowcontents += "<td><select name='cboAvailability"+itemid+"'>";
	rowcontents += "<option "; if (availability == 'All') { rowcontents += 'selected ';} rowcontents += "value='All'>All</option>";
	rowcontents += "<option "; if (availability == 'Player') { rowcontents += 'selected ';} rowcontents += "value='Player'>Player</option>";
	rowcontents += "<option "; if (availability == 'Monster') { rowcontents += 'selected ';} rowcontents += "value='Monster'>Monster</option>";
	rowcontents += "<option "; if (availability == 'Staff') { rowcontents += 'selected ';} rowcontents += "value='Staff'>Staff</option>";
	rowcontents += "</select></td>";
	rowcontents += "<td><input type='checkbox' name='chkTicket"+ itemid +"'";
	if (ticket) { rowcontents += " checked";}
	rowcontents += "/></td>";
	rowcontents += "<td><input type='checkbox' name='chkMeal"+ itemid +"'";
	if (meal) { rowcontents += " checked";}
	rowcontents += "/></td>";
	rowcontents += "<td><input type='checkbox' name='chkBunk"+ itemid +"'";
	if (bunk) { rowcontents += " checked";}
	rowcontents += "/></td>";
	rowcontents += "<td><input size=10 type='text' name='txtAvailableFrom"+ itemid +"' value='" + availablefrom + "' /></td>";
	rowcontents += "<td><input size=10 type='text' name='txtAvailableTo"+ itemid +"' value='" + availableto + "' /></td>";
	rowcontents += "<td><input size=8 type='text' name='txtItemCost"+ itemid +"' value='" + itemcost + "' /></td>";
	rowcontents += "<td><input type='checkbox' name='chkAllowMultiple"+ itemid +"'";
	if (allowmultiple) { rowcontents += " checked";}
	rowcontents += "/></td>";
	rowcontents += "<td><input type='checkbox' name='chkMandatory"+ itemid +"'";
	if (mandatory) { rowcontents += " checked";}
	rowcontents += "/></td>";
	rowcontents += "<td><input type='button' value='Remove' onClick='removeitem(" + itemid + ")' /></td>";
	rowcontents += "</tr>";
	return rowcontents;
}

function getselecteddateasstring(name)
{
	return $("select[name=sel" + name + "Year]").val() + "-" + $("select[name=sel" + name + "Month]").val() + "-" + $("select[name=sel" + name + "Date]").val();
}

function getselecteddate(name)
{
	return new Date($("select[name=sel" + name + "Year]").val(), $("select[name=sel" + name + "Month]").val() - 1, $("select[name=sel" + name + "Date]").val());
}
</script>


<h1><?php echo TITLE?> - Edit Event Details</h1>

<?php
if ($eventid == 0)
{
	echo "<p><a href = 'admin.php'>Admin</a></p>\n";
	echo "<h2>New Event</h2>\n";
}
else
{
	echo "<p><a href = 'admin_manageevent.php?EventID=".$eventinfo['evEventID']."'>Return to event management for - ".htmlentities (stripslashes ($eventinfo['evEventName']))."</a></p>\n";
	echo "<h2>".htmlentities (stripslashes ($eventinfo['evEventName']))."</h2>\n";
}

?>

<form action='admin_editeventdetails.php?EventID=<?php echo $eventinfo['evEventID'];?>' method=POST>
<table>
<tr><td>Event Name</td><td><input type='text' name='txtEventName' value="<?php echo htmlentities(stripslashes ($eventinfo['evEventName']));?>" /></td></tr>
<tr><td>Event Description (HTML allowed)</td><td><textarea name='txtEventDescription' id= 'txtEventDescription' class="eventinfo"><?php echo htmlentities(stripslashes ($eventinfo['evEventDescription']));?></textarea></td></tr>
<tr><td>Event Details (HTML allowed)</td><td><textarea name='txtEventDetails' id='txtEventDetails' class="eventinfo"><?php echo htmlentities(stripslashes ($eventinfo['evEventDetails']));?></textarea></td></tr>
<tr><td>Event Date</td><td><?php DatePickerFullDate("EventDate", $eventinfo['evEventDate'], 3,2)?></td></tr>
<tr><td title="bookings will be open from 00:01 on this date">Bookings Open</td><td><?php DatePickerFullDate("BookingsOpen", $eventinfo['evBookingsOpen'], 3,2)?></td></tr>
<tr><td title="bookings will close at 23:59 on this date">Bookings Close</td><td><?php DatePickerFullDate("BookingsClose", $eventinfo['evBookingsClose'], 3,2)?></td></tr>
<tr><td>Player Spaces</td><td><input type='text' name='txtPlayerSpaces' value='<?php echo $eventinfo['evPlayerSpaces'];?>' /></td></tr>
<tr><td>Monster Spaces</td><td><input type='text' name='txtMonsterSpaces' value='<?php echo $eventinfo['evMonsterSpaces'];?>' /></td></tr>
<tr><td>Staff Spaces</td><td><input type='text' name='txtStaffSpaces' value='<?php echo $eventinfo['evStaffSpaces'];?>' /></td></tr>
<tr><td>Total Spaces</td><td><input type='text' name='txtTotalSpaces' value='<?php echo $eventinfo['evTotalSpaces'];?>' /></td></tr>
<tr><td>Allow Monster Bookings</td><td><input type='checkbox' name='chkAllowMonsterBookings' <?php if ($eventinfo['evAllowMonsterBookings']) {echo " checked";} ?> /></td></tr>
<tr><td>Use Booking Queue</td><td><input type='checkbox' name='chkUseQueue' <?php if ($eventinfo['evUseQueue']) {echo " checked";} ?> /></td></tr>
<tr><td>Player Bunks</td><td><input type='text' name='txtPlayerBunks' value='<?php echo $eventinfo['evPlayerBunks'];?>' /></td></tr>
<tr><td>Monster Bunks</td><td><input type='text' name='txtMonsterBunks' value='<?php echo $eventinfo['evMonsterBunks'];?>' /></td></tr>
<tr><td>Staff Bunks</td><td><input type='text' name='txtStaffBunks' value='<?php echo $eventinfo['evStaffBunks'];?>' /></td></tr>
<tr><td>Total Bunks</td><td><input type='text' name='txtTotalBunks' value='<?php echo $eventinfo['evTotalBunks'];?>' /></td></tr>
<tr><td>Event Items<br>To allow players to get a reduction, add an item with a negative price (eg &quot;Pot washing: -10&quot;)</td><td>
<table id='itemtable'>
<tr><th>Item name</th><th>Availability</th><th>Ticket</th><th>Meal</th><th>Bunk</th><th>From</th><th>To</th><th>Cost</th><th>Multiple</th><th>Mandatory</th></tr>
<?php
$sql = "Select * from {$db_prefix}items where itEventID = $eventid";
$result = ba_db_query($link, $sql);
while ($item = ba_db_fetch_assoc($result))
{
	echo "<tr id='rowItem".$item['itItemID']."'>";
	echo "<td><input type='hidden' name='hItemID".$item['itItemID']."' value='".$item['itItemID']."'/>";
	echo "<input type='text' name='txtItemDescription".$item['itItemID']."' value='".$item['itDescription']."' /></td>";
	echo "<td><select name='cboAvailability".$item['itItemID']."'>";
	echo "<option "; if ($item['itAvailability'] == 'All') { echo 'selected ';} echo "value='All'>All</option>";
	echo "<option "; if ($item['itAvailability'] == 'Player') { echo 'selected ';} echo "value='Player'>Player</option>";
	echo "<option "; if ($item['itAvailability'] == 'Monster') { echo 'selected ';} echo "value='Monster'>Monster</option>";
	echo "<option "; if ($item['itAvailability'] == 'Staff') { echo 'selected ';} echo "value='Staff'>Staff</option>";
	echo"</select></td>";
	echo "<td><input type='checkbox' name='chkTicket".$item['itItemID']."'";
	if ($item['itTicket']) { echo " checked";}
	echo "/></td>";
	echo "<td><input type='checkbox' name='chkMeal".$item['itItemID']."'";
	if ($item['itMeal']) { echo " checked";}
	echo "/></td>";
	echo "<td><input type='checkbox' name='chkBunk".$item['itItemID']."'";
	if ($item['itBunk']) { echo " checked";}
	echo "/></td>";
	echo "<td><input size=10 type='text' name='txtAvailableFrom".$item['itItemID']."' value='".$item['itAvailableFrom']."' /></td>";
	echo "<td><input size=10 type='text' name='txtAvailableTo".$item['itItemID']."' value='".$item['itAvailableTo']."' /></td>";
	echo "<td><input size=8 type='text' name='txtItemCost".$item['itItemID']."' value='".$item['itItemCost']."' /></td>";
	echo "<td><input type='checkbox' name='chkAllowMultiple".$item['itItemID']."'";
	if ($item['itAllowMultiple']) { echo " checked";}
	echo "/></td>";
	echo "<td><input type='checkbox' name='chkMandatory".$item['itItemID']."'";
	if ($item['itMandatory']) { echo " checked";}
	echo "/></td>";
	echo "<td><input type='button' value='Remove' onClick='removeitem(" . $item['itItemID'] . ")' /></td>";
	echo "</tr>\n";
}

echo "<input type='hidden' name='hRemovedItemIDs' id='hRemovedItemIDs' value ='-1' />";
?>
</table>
<input type='button' value='Add new item' onClick='addnewitem()'/>
<?php
if ($eventid == 0) { echo "<input type='button' id='btndefaultitemset' value='Add default item set' onClick='adddefaultitems()'/>"; }
?>
</td></tr>
<tr><td><input type='submit' name='btnSubmit' value='Submit' /></td><td><input type='reset' name='btnReset' value='Reset' /></td></tr>
</table>
</form>


<?php
include ('inc/inc_foot.php');
?>
