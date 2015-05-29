<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File admin/admin_edit_ooc.php
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
include ('../inc/inc_forms.php');

//Get player ID of player to be edited
$admin_player_id = (int) $_GET ['pid'];
//Initialise $sWarn
$sWarn = '';
$db_prefix = DB_PREFIX;
$key = CRYPT_KEY;

if ($_POST ['btnSubmit'] != '' && CheckReferrer ('admin_edit_ooc.php')) {
	//Run OOC_Check to perform data validation
	$sWarn = OOC_Check ();

	//Check e-mail address is reasonable
	$sEmail = SafeEmail ($_POST ['txtEmail']);
	//Only check for valid e-mail address if one was included - user may not have an e-mail address
	if ($sEmail != '')
		if (!eregi ("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]*)$", $sEmail))
			$sWarn .= htmlentities ($sEmail) . " is not a valid e-mail address<br>\n";

	if ($sWarn != '')
		$sWarn .= "<br>The details entered so far have been saved, but you will need to correct the above errors at some point";
	//Update database
	//Build up date of birth in YYYYMMDD format
	$dob = (int) $_POST ['selDobYear'];
	if ($_POST ['selDobMonth'] < 10)
		$dob .= '0';
	$dob .= (int) $_POST ['selDobMonth'];
	if ($_POST ['selDobDate'] < 10)
		$dob .= '0';
	$dob .= (int) $_POST ['selDobDate'];
	//Set up $sMedInfo
	if ($_POST ['txtMedicalInfo'] == 'Enter details here')
		$sMedInfo = '';
	else
		$sMedInfo = ba_db_real_escape_string ($link, $_POST ['txtMedicalInfo']);
	//Remove any spaces in car registration
	$sCarReg = ba_db_real_escape_string ($link, str_replace (' ', '', $_POST ['txtCarRegistration']));

	//get value of event pack by post
	if ($_POST ['chkEventPackByPost'] == '')
		$iByPost = 0;
	else
		$iByPost = 1;
	//Set up UPDATE query


	$refnumber = (int) $_POST ["txtRefNumber{$value}"];
	$marshal = stripslashes($_POST ["cboMarshal{$value}"]);

	$sEmail = ba_db_real_escape_string ($link, SafeEmail ($_POST ['txtEmail']));
	$sql = "UPDATE {$db_prefix}players SET plFirstName = '" . ba_db_real_escape_string ($link, $_POST ['txtFirstName']) . "', " .
		"plSurname = '" . ba_db_real_escape_string ($link, $_POST ['txtSurname']) . "', " .
		"pleAddress1 = AES_ENCRYPT('" . ba_db_real_escape_string ($link, $_POST ['txtAddress1']) . "', '$key'), " .
		"pleAddress2 = AES_ENCRYPT('" . ba_db_real_escape_string ($link, $_POST ['txtAddress2']) . "', '$key'), " .
		"pleAddress3 = AES_ENCRYPT('" . ba_db_real_escape_string ($link, $_POST ['txtAddress3']) . "', '$key'), " .
		"pleAddress4 = AES_ENCRYPT('" . ba_db_real_escape_string ($link, $_POST ['txtAddress4']) . "', '$key'), " .
		"plePostcode = AES_ENCRYPT('" . ba_db_real_escape_string ($link, $_POST ['txtPostcode']) . "', '$key'), " .
		"pleTelephone = AES_ENCRYPT('" . ba_db_real_escape_string ($link, $_POST ['txtPhone']) . "', '$key'), " .
		"pleMobile = AES_ENCRYPT('" . ba_db_real_escape_string ($link, $_POST ['txtMobile']) . "', '$key'), " .
		"plEmail = '$sEmail', " .
		"plDOB = '$dob', " .
		"pleMedicalInfo = AES_ENCRYPT('". ba_db_real_escape_string ($link, $sMedInfo)."', '$key'), " .
		"plEmergencyName = '" . ba_db_real_escape_string ($link, $_POST ['txtEmergencyName']) . "', " .
		"pleEmergencyNumber = AES_ENCRYPT('" . ba_db_real_escape_string ($link, $_POST ['txtEmergencyNumber']) . "', '$key'), " .
		"plEmergencyRelationship = '" . ba_db_real_escape_string ($link, $_POST ['txtEmergencyRelationship']) . "', " .
		"plCarRegistration = '$sCarReg', " .
		"plDietary = '" . ba_db_real_escape_string ($link, $_POST ['selDiet']) . "', " .
		"plNotes = '" . ba_db_real_escape_string ($link, $_POST ['txtNotes']) . "', " .
		"plAdminNotes = '" . ba_db_real_escape_string ($link, $_POST ['txtAdminNotes']) . "', ";
		$sql .= "plRefNumber = $refnumber, plMarshal = '$marshal',";
		$sql .="plEventPackByPost = $iByPost ";
		$sql .="WHERE plPlayerID = $admin_player_id";

	//Run UPDATE query
	if (ba_db_query ($link, $sql)) {
		//Query should affect exactly one row. Log a warning if it affected more
		if (ba_db_affected_rows ($link) > 1)
			LogWarning ("More than one row updated during admin OOC update (admin_edit_ooc.php). Player ID: $admin_player_id");
		//Do not redirect if there are any warnings (required fields not filled in, etc)
		if ($sWarn == '') {
			//Make up URL & redirect
			$sURL = fnSystemURL () . "admin_viewdetails.php?pid=$admin_player_id&green=" . urlencode ("OOC details updated");
			header ("Location: $sURL");
		}
	}
	else {
		$sWarn = "There was a problem updating the OOC details";
		LogError ("Error updating OOC information (admin_edit_ooc.php). Player ID: $admin_player_id");
	}
}

//Get existing details if there are any
$sql = "SELECT plFirstName, " .
	"plSurname, " .
	"AES_DECRYPT(pleAddress1, '$key') AS dAddress1, " .
	"AES_DECRYPT(pleAddress2, '$key') AS dAddress2, " .
	"AES_DECRYPT(pleAddress3, '$key') AS dAddress3, " .
	"AES_DECRYPT(pleAddress4, '$key') AS dAddress4, " .
	"AES_DECRYPT(plePostcode, '$key') AS dPostcode, " .
	"AES_DECRYPT(pleTelephone, '$key') AS dTelephone, " .
	"AES_DECRYPT(pleMobile, '$key') AS dMobile, " .
	"plEmail, " .
	"plDOB, " .
	"AES_DECRYPT(pleMedicalInfo, '$key') AS dMedicalInfo, " .
	"plEmergencyName, " .
	"AES_DECRYPT(pleEmergencyNumber, '$key') AS dEmergencyNumber, " .
	"plEmergencyRelationship, " .
	"plCarRegistration, " .
	"plDietary, " .
	"plNotes, " .
	"plAdminNotes, " .
	"plEventPackByPost, ".
	"plRefNumber, ".
	"plMarshal ".
	"FROM {$db_prefix}players WHERE plPlayerID = $admin_player_id";
$result = ba_db_query ($link, $sql);
$playerrow = ba_db_fetch_assoc ($result);

include ('../inc/inc_head_html.php');
include ('../inc/inc_js_forms.php');
?>

<h1><?php echo TITLE?> - Admin OOC Edit</h1>

<?php
if ($sWarn != '')
	echo "<p class = 'warn'>$sWarn</p>";
?>

<p>
<i>Required fields are <span class = "req_colour">shaded</span></i>. Details will appear on the character card <i>exactly</i> as typed.
</p>
<div class = "warn">
<?php
echo "Note that you are editing the OOC details for " .
	htmlentities ($playerrow ['plFirstName']) . " " . htmlentities ($playerrow ['plSurname']) .
	" (" . PID_PREFIX . sprintf ('%03s', $admin_player_id) . ")";
?>
</div>

<form action = 'admin_edit_ooc.php?pid=<?php echo $admin_player_id?>' method = 'post' accept-charset="iso-8859-1">

<table><tr>
<td>First name:</td>
<td><input type = "text" class = "required" name = "txtFirstName" value = "<?php echo htmlentities (stripslashes ($playerrow ['plFirstName']))?>"></td>
</tr><tr>
<td>Surname:</td>
<td><input type = "text" class = "required" name = "txtSurname" value = "<?php echo htmlentities (stripslashes ($playerrow ['plSurname']))?>"></td>
</tr><tr><td colspan = "2">&nbsp;</td></tr><tr>
<td>Address:</td>
<td><input type = "text" class = "required" name = "txtAddress1" value = "<?php echo htmlentities (stripslashes ($playerrow ['dAddress1']))?>"><br>
<input type = "text" class = "text" name = "txtAddress2" value = "<?php echo htmlentities (stripslashes ($playerrow ['dAddress2']))?>"><br>
<input type = "text" class = "text" name = "txtAddress3" value = "<?php echo htmlentities (stripslashes ($playerrow ['dAddress3']))?>"><br>
<input type = "text" class = "text" name = "txtAddress4" value = "<?php echo htmlentities (stripslashes ($playerrow ['dAddress4']))?>"></td>
</tr><tr><td colspan = "2">&nbsp;</td></tr><tr>
<td>Postcode:</td>
<td><input type = "text" class = "text" name = "txtPostcode" value = "<?php echo htmlentities (stripslashes ($playerrow ['dPostcode']))?>"></td>
</tr><tr>
<td>Telephone number:</td>
<td><input type = "text" class = "text" name = "txtPhone" value = "<?php echo htmlentities (stripslashes ($playerrow ['dTelephone']))?>"></td>
</tr><tr>
<td>Mobile number:</td>
<td><input type = "text" class = "text" name = "txtMobile" value = "<?php echo htmlentities (stripslashes ($playerrow ['dMobile']))?>"></td>
</tr><tr>
<td>E-mail address:</td>
<td><input type = "text" class = "text" name = "txtEmail" value = "<?php echo htmlentities (stripslashes ($playerrow ['plEmail']))?>"></td>
</tr><tr>
<td colspan = "2">&nbsp;</td>
</tr><tr>
<td>Date of birth:</td>
<td>

<?php
$sDoB = $playerrow ['plDOB'];
if ($sDoB != '') {
	$iDobYear = substr ($sDoB, 0, 4);
	$iMonth = substr ($sDoB, 4, 2);
	$iDate = substr ($sDoB, 6, 2);
	$iYear = getdate ();
	$iYear = $iDobYear - $iYear ['year'];
	DatePicker ('Dob', $iYear, $iMonth, $iDate);
}
else
	DatePicker ('Dob', -25);
?>

</td>
</tr><tr>
<td>Tick if you have any medical<br>issues we need to know about:</td>
<?php
$sMedInfo = htmlentities (stripslashes ($playerrow ['dMedicalInfo']));
if ($sMedInfo == '')
	echo "<td><input name = 'chkMedical' type = 'checkbox' onclick = 'fnShowMedical ()'>\n";
else
	echo "<td><input name = 'chkMedical' type = 'checkbox' checked onclick = 'fnShowMedical ()'>\n";
?>
<!--
SPAN is used to hide/show medical info box. JavaScript is used to write
SPAN tags so that, if JS is disabled, medical info box is always shown
-->
<script>
<?php
if ($sMedInfo == '')
	echo "document.write ('<span id = \"spMedicalInfo\" style = \"display: none\">')\n";
else
	echo "document.write ('<span id = \"spMedicalInfo\" style = \"display: inline\">')\n";
?>
</script>
<br><textarea class = "text" name = "txtMedicalInfo">
<?php
$sMedInfo = htmlentities (stripslashes ($playerrow ['dMedicalInfo']));
if ($sMedInfo == '')
	echo 'Enter details here';
else
	echo $sMedInfo;
?>
</textarea>
<script>
document.write ('</span>')
</script>
</td></tr><tr>
<td colspan = "2">&nbsp;</td>
</tr><tr>
<td>Emergency contact name:</td>
<td><input type = "text" class = "required" name = "txtEmergencyName" value = "<?php echo htmlentities (stripslashes ($playerrow ['plEmergencyName']))?>"></td>
</tr><tr>
<td>Emergency contact number:</td>
<?php
if ($playerrow ['dEmergencyNumber'] == '')
	$sValue = '("On site" is OK)';
else
	$sValue = $playerrow ['dEmergencyNumber'];
?>
<td><input type = "text" class = "required" name = "txtEmergencyNumber" value = '<?php echo htmlentities (stripslashes ($sValue))?>'></td>
</tr><tr>
<td>Relationship to emergency contact:</td>
<td><input type = "text" class = "required" name = "txtEmergencyRelationship" value = "<?php echo htmlentities (stripslashes ($playerrow ['plEmergencyRelationship']))?>"></td>
</tr><tr>
<td colspan = "2">&nbsp;</td>
</tr><tr>
<td>Car registration:</td>
<?php
if ($playerrow ['plCarRegistration'] == '')
	$sValue = 'Enter NA if you do not drive';
else
	$sValue = $playerrow ['plCarRegistration'];
?>
<td><input type = "text" class = "required" name = "txtCarRegistration" value = '<?php echo htmlentities (stripslashes ($sValue))?>'></td>
</tr><tr>
<td>Dietary requirements:</td>
<td><select name = "selDiet" class = "req_colour">
<?php
if ($playerrow ['plDietary'] == '')
	$sValue = 'Select one';
else
	$sValue = $playerrow ['plDietary'];
$asOptions = array ('Select one', 'Omnivore', 'Vegetarian', 'Vegan', 'Other/allergy (details in Medical Information box)');
foreach ($asOptions as $sOption) {
	echo "<option value = '$sOption'";
	if ($sOption == $sValue)
		echo ' selected';
	echo ">$sOption</option>\n";
}
?>
</select>
</td>
</tr>

<?php
echo "<tr><td>Are you a Ref or Marshal</td>";
echo "<td><select name='cboMarshal'>";
echo "<option "; if ($playerrow ['plMarshal']== "No") { echo "selected"; }; echo " >No</option>";
echo "<option "; if ($playerrow ['plMarshal']== "Marshal") { echo "selected"; }; echo " >Marshal</option>";
echo "<option "; if ($playerrow ['plMarshal']== "Referee") { echo "selected"; }; echo " >Referee</option>";
echo "<option "; if ($playerrow ['plMarshal']== "Senior Referee") { echo "selected"; }; echo " >Senior Referee</option>";
echo "</select></td>\n";
echo "<tr><td>Ref Number:</td><td><input type=text name='txtRefNumber' size=5 value='" . htmlentities (stripslashes ($playerrow ['plRefNumber'])) . "'/></td>\n";
echo "</tr>";

if (ALLOW_EVENT_PACK_BY_POST)
{
echo "<tr><td>Tick to request event pack by post:</td>";
	if ($playerrow ['plEventPackByPost'] == 1)
		$sTick = ' checked';
	else
		$sTick = '';
	echo "<td><input type = 'checkbox' name = 'chkEventPackByPost' value = 'ByPost'$sTick>";
	echo "</td></tr>\n";
}
?>
<tr><td>General Notes (not medical/allergy):</td>
<td><textarea class = "text" name = 'txtNotes'><?php echo htmlentities (stripslashes ($playerrow ['plNotes']))?></textarea></td></tr>
<tr><td>Admin Notes (only visible to admins):</td>
<td><textarea class = "text" name = 'txtAdminNotes'><?php echo htmlentities (stripslashes ($playerrow ['plAdminNotes']))?></textarea></td></tr>
<tr><td colspan = '2'>
<div class = "warn">Note that you are editing the OOC details for player ID <?php echo PID_PREFIX . sprintf ('%03s', $admin_player_id)?>.</div>
</td></tr>
<tr><td class = 'mid'><input type = 'submit' value = "Submit" name = "btnSubmit"></td>
<td class = 'mid'><input type = 'reset' value = "Reset form"></td></tr>
</table>

</form>

<?php
include ('../inc/inc_foot.php');
?>
