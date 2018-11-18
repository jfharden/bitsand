<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File admin_finalconfirmation.php
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
include ('../inc/inc_commonqueries.php');

$db_prefix = DB_PREFIX;
$key = CRYPT_KEY;

$eventinfo = getEventDetails($_GET['EventID'], 0, 'admin.php');
$eventid = $eventinfo['evEventID'];


?>

<h1><?php echo TITLE?> - Final Confirmation</h1>
<p>
<a href = 'admin_manageevent.php?EventID=<?php echo $eventinfo['evEventID'];?>'>Return to event management for - <?php echo htmlentities (stripslashes ($eventinfo['evEventName']));?></a>
</p>

<h2><?php echo htmlentities (stripslashes ($eventinfo['evEventName']));?></h2>

<p>The text below will be sent as an e-mail to all players fully booked for the upcoming event. Players are considered fully booked if their payment date is not blank, so be sure to mark everyone as paid before sending this e-mail.</p>
<p>You can use the textbox provided to enter some custom text that will be included in your e-mail.</p>
<hr />
<p>
Dear &lt;player name&gt;,
</p>
<p>
Thank you for booking for the upcoming event, &lt;event name&gt;, you can download the event pack from <?php echo SYSTEM_URL ?>eventdetails.php?EventID=<?php echo $eventinfo['evEventID'];?>
</p>
<p>
&lt;customised text&gt;
</p>
<p>
Our records show that:
<ul>
<li>You have booked as a (player/monster/staff member)</li>
<li>Your emergency contact name is &lt;emergency name&gt;</li>
<li>Your emergency contact number is &lt;emergency number&gt;</li>
<li>You have/have not been assigned a bunk space</li>
<li>You have/have not requested a meal ticket</li>
<li>You have paid a total of &pound;&lt;amount paid&gt;</li>
<li>You were expected to pay &pound;&lt;amount expected&gt;</li>
<li>You will need to pay/be refunded &pound;&lt;difference&gt; when you arrive on site</li>
<li>Your In-Character information is as follows:
	<ul>
	<li>Character name:</li>
	<li>Group:</li>
	<li>Faction:</li>
	<li>Ancestor:</li>
	<li>Character Skills:</li>
	<li>Occupational Skills:</li>
	<li>Notes:</li>
	</ul>
</li>
</ul>
</p>
<p>
If any of the above information is incorrect, please let &lt;event contact&gt; (&lt;event contact email&gt;) know as soon as possible.
</p>
<p>
We look forward to seeing you at the event.
</p>
<form action='admin_finalconfirmation.php?EventID=<?php echo $eventinfo['evEventID'];?>' method=post>
<table>
<tr><td>Custom text:</td><td><textarea name='txtCustom' rows='10'><?php echo stripslashes($_POST['txtCustom'])?></textarea></td></tr>

<?php
if (ALLOW_EVENT_PACK_BY_POST)
{
echo "<tr><td>Include people requesting packs by post:</td><td><input type='checkbox' name='chkIncludeByPost'";
if ($_POST['chkIncludeByPost'] == 'on') { echo " checked";}
echo "></td></tr>";
echo "<tr><td>Include people requesting packs by email:</td><td><input type='checkbox' name='chkIncludeByEmail'";
if ($_POST['chkIncludeByEmail'] == 'on') { echo " checked";}
echo "></td></tr>";
}
?>

<tr><td></td><td><input type='submit' value='Send e-mail' name='btnSend'/>&nbsp;<input value='Preview One' name='btnPreviewOne' type='submit' />&nbsp;<input value='Preview All' name='btnPreviewAll' type='submit'/></td></tr>
</table>
</form>


<?php

$buttonpressed = 0;
$customtext = htmlentities( stripslashes($_POST['txtCustom']));

//The user has asked for a preview of all e-mails that will be sent
if ($_POST['btnPreviewAll'] == 'Preview All' || $_POST['btnPreviewOne'] == 'Preview One')
{
	$startPara = "<p>\n";
	$endPara = "</p>\n";
	$startIndent = "<ul>\n<li>";
	$endIndent = "</li></ul>\n";
	$indentItem = "</li>\n<li>";
	$poundsign = "&pound;";
	$buttonpressed = 1;
	$customtext = str_replace("\n", "<br/>", $customtext);
}
elseif ($_POST['btnSend'] == 'Send e-mail')
{
	$startPara = "\n";
	$endPara = "\n";
	$startIndent = "\n\t";
	$endIndent = "";
	$indentItem = "\n\t";
	$poundsign = "£";
	$buttonpressed = 2;
}

if ($buttonpressed > 0  && CheckReferrer ('admin_finalconfirmation.php'))
{
	$sql = "Select".
	" plPlayerID,".
	" plFirstName,".
	" plSurname,".
	" plEmail,".
	" bkBookAs,".
	" plEmergencyName,".
	" IFNULL(AES_DECRYPT(pleEmergencyNumber, '$key'),'') AS dEmergencyNumber,".
	"bkBunkAllocated, " .
	"bkMealTicket, " .
	"bkAmountPaid, ".
	"bkAmountExpected, ".
	"chName, chRace, " .
	"chGroupSel, chGroupText, chFaction, chAncestor, chAncestorSel,".
	"chNotes, chOSP " .
	"from {$db_prefix}players LEFT OUTER JOIN {$db_prefix}bookings ON {$db_prefix}players.plPlayerID = {$db_prefix}bookings.bkPlayerID " .
	" LEFT OUTER JOIN {$db_prefix}characters ON {$db_prefix}players.plPlayerID = {$db_prefix}characters.chPlayerID ".
	" WHERE bkDatePaymentConfirmed != '0000-00-00' and bkEventID = $eventid";

	if (ALLOW_EVENT_PACK_BY_POST)
	{
		if ($_POST['chkIncludeByPost'] == 'on' && $_POST['chkIncludeByEmail'] != 'on') { $sql .= " AND plEventPackByPost = 1"; }
		if ($_POST['chkIncludeByPost'] != 'on' && $_POST['chkIncludeByEmail'] == 'on') { $sql .= " AND plEventPackByPost = 0"; }
		if ($_POST['chkIncludeByPost'] != 'on' && $_POST['chkIncludeByEmail'] != 'on') { $sql .= " AND 1 = 0"; }
	}

	$sql .= " ORDER BY IF(plSurname='',1,0),plSurname";

	if ($_POST['btnPreviewOne'] == 'Preview One') { $sql.= " Limit 1";}

	$result = ba_db_query ($link, $sql);
	echo "<hr/>";

	while ($record = ba_db_fetch_assoc ($result))
	{
		$output = "";
		$output.= $startPara;
		$output.= "Dear ".$record['plFirstName'].",\n";
		$output.= $endPara;
		$output.= $startPara;

		$output.= "Thank you for booking for the upcoming event, ".stripslashes ($eventinfo['evEventName']).". You can download the event pack from ";

		$output.=SYSTEM_URL."eventdetails.php?EventID=".$eventinfo['evEventID'];
		$output.= $endPara;

		if (strlen($customtext) > 0)
		{
				$output.= $startPara;
				$output.= $customtext;
				$output.= $endPara;
		}


		$output.= $startPara;
		$output.= "Our records show that:";
		$output.= $startIndent . "You have booked as a " .$record['bkBookAs'];
		$output.= $indentItem . "Your emergency contact name is ".$record['plEmergencyName'];
		$output.= $indentItem . "Your emergency contact number is ".$record['dEmergencyNumber'];
		$bunk = "have not";
		if ($record['bkBunkAllocated'] == 1) {$bunk = "have";}
		$output.= $indentItem. "You $bunk been assigned a bunk space";
		$meal = "have not";
		if ($record['bkMealTicket'] == 1) {$meal = "have";}
		$output.= $indentItem. "You $meal requested a meal ticket";
		$output.= $indentItem. "You have paid a total of $poundsign".$record['bkAmountPaid'];

		$diff = $record['bkAmountExpected'] - $record['bkAmountPaid'];
		if ($diff != 0)
		{
			$output.= $indentItem. "You were expected to pay $poundsign".$record['bkAmountExpected'];
			if ($diff > 0)
			{
				$output.= $indentItem. "You will need to pay $poundsign$diff when you arrive on site";
			}
			else
			{
			$output.= $indentItem. "You will need to be refunded $poundsign".abs($diff)." when you arrive on site";
			}
		}

		if ($buttonpressed == 1)
			$output.= $indentItem. "Your In Character information is as follows:";
		else
			$output.= "\n\nYour In Character information is as follows:";

		$output.= $startIndent."Character name: ".$record['chName'];

		if ($record ['chAncestor'] == 'Enter name here if not in above list' || $record ['chAncestor'] == '')
			$sAncestor = htmlentities (stripslashes ($record['chAncestorSel']));
		else
			$sAncestor = htmlentities (stripslashes ($record['chAncestor']));

		if ($record ['chGroupText'] == 'Enter name here if not in above list' || $record ['chGroupText'] == '')
			$sGroup = htmlentities (stripslashes ($record ['chGroupSel']));
		else
			$sGroup = htmlentities (stripslashes ($record ['chGroupText']));

		$output.= $indentItem. "Group: ".$sGroup;
		$output.= $indentItem. "Faction: ".$record['chFaction'];
		$output.= $indentItem. "Ancestor: ".$sAncestor;
		$output.= $indentItem. "Character Skills: ";

		$skillsql = "SELECT skID, skName FROM {$db_prefix}skills, {$db_prefix}skillstaken WHERE stPlayerID = ".$record['plPlayerID']." AND skID = stSkillID ORDER BY skName";
		$skillresult = ba_db_query ($link, $skillsql);
		$skilllist = "";
		while ($skillrow = ba_db_fetch_assoc ($skillresult))
			$skilllist.= htmlentities (stripslashes ($skillrow ['skName'])) . ", ";
		if (strlen($skilllist) > 0) {$skilllist = substr_replace($skilllist,"",-2);}

		$output.= $skilllist;

		$osresult = ba_db_query ($link, "SELECT ospName FROM {$db_prefix}ospstaken, {$db_prefix}osps WHERE otPlayerID = ".$record['plPlayerID']." AND ospID = otOspID ORDER BY ospName");
		$oslist = "";
		while ($osrow = ba_db_fetch_assoc ($osresult))
			$oslist.= htmlentities (stripslashes ($osrow ['ospName'])) . ", ";
		if (strlen($oslist) > 0) {$oslist = substr_replace($oslist,"",-2);}


		$output.= $indentItem. "Occupational Skills: ".$oslist;
		$output.= $indentItem. "Notes: ".$record['chNotes'];
		$output.= $endIndent.$endIndent.$endPara;
		$output.= $endIndent.$endPara;
		$output.= $startPara;
		$output.="If any of the above information is incorrect, please let ";
		if ($buttonpressed == 1)
			$output.= EVENT_CONTACT_NAME." (<a href = 'mailto:" .Obfuscate (EVENT_CONTACT_MAIL) . "'>" . EVENT_CONTACT_MAIL . "</a>) know as soon as possible.";
		else
			$output.= EVENT_CONTACT_NAME." (".EVENT_CONTACT_MAIL . ") know as soon as possible.";
		$output.= $endPara;
		$output.= $startPara;
		$output.="We look forward to seeing you at the event.";
		$output.= $endPara;

		if ($buttonpressed == 1)
		{
			$output.= "<hr />";
			echo $output;
		}
		else if ($buttonpressed == 2)
		{
			echo "Sending confirmation e-mail to ".$record['plFirstName']." ".$record['plSurname']." (".PID_PREFIX . sprintf ('%03s', $record['plPlayerID']).")<br />\n";

			ini_set("sendmail_from", EVENT_CONTACT_MAIL);
			$mail = mail ($record ['plEmail'], SYSTEM_NAME . ' - Final Confirmation for '.$eventinfo['evEventName'], $output, "From:" . SYSTEM_NAME . " <" . EVENT_CONTACT_MAIL . ">", '-f'.EVENT_CONTACT_MAIL);
		}

	}
}

?>

<?php
include ('../inc/inc_foot.php');