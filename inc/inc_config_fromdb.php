<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File inc/inc_config_fromdb.php
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

/* ******************************************************************
Items below here are accessed through the Database
****************************************************************** */

//SELECT THE CONFIG ITEMS

$sql = "SELECT * FROM ".DB_PREFIX."config";

if ($result = ba_db_query ($link, $sql)) {

	if (ba_db_num_rows($result) == 1)
	{
		$row = ba_db_fetch_assoc ($result);

		//DEFINE THEM
		define ('LOGIN_TIMEOUT', stripslashes($row['cnLOGIN_TIMEOUT']));
		define ('LOGIN_TRIES', stripslashes($row['cnLOGIN_TRIES']));
		define ('MIN_PASS_LEN', stripslashes($row['cnMIN_PASS_LEN']));
		define ('SEND_PASSWORD', stripslashes($row['cnSEND_PASSWORD']));
		define ('ANNOUNCEMENT_MESSAGE', stripslashes ($row['cnANNOUNCEMENT_MESSAGE']));
		define ('DISCLAIMER_TEXT', stripslashes($row['cnDISCLAIMER_TEXT']));
		define ('EVENT_CONTACT_NAME', stripslashes($row['cnEVENT_CONTACT_NAME']));
		define ('EVENT_CONTACT_MAIL', stripslashes($row['cnEVENT_CONTACT_MAIL']));
		define ('TECH_CONTACT_NAME', stripslashes($row['cnTECH_CONTACT_NAME']));
		define ('TECH_CONTACT_MAIL', stripslashes($row['cnTECH_CONTACT_MAIL']));
		define ('TITLE', stripslashes($row['cnTITLE']));
		define ('SYSTEM_NAME', stripslashes($row['cnSYSTEM_NAME']));
		define ('BOOKING_FORM_FILE_NAME', stripslashes($row['cnBOOKING_FORM_FILE_NAME']));
		define ('BOOKING_LIST_IF_LOGGED_IN', stripslashes($row['cnBOOKING_LIST_IF_LOGGED_IN']));
		define ('LOCATIONS_LABEL', stripslashes($row['cnLOCATIONS_LABEL']));
		define ('LIST_GROUPS_LABEL', stripslashes($row['cnLIST_GROUPS_LABEL']));
		define ('ANCESTOR_DROPDOWN', stripslashes($row['cnANCESTOR_DROPDOWN']));
		define ('DEFAULT_FACTION', stripslashes($row['cnDEFAULT_FACTION']));
		define ('NON_DEFAULT_FACTION_NOTES', stripslashes($row['cnNON_DEFAULT_FACTION_NOTES']));
		define ('IC_NOTES_TEXT', stripslashes($row['cnIC_NOTES_TEXT']));
		define ('USE_PAY_PAL', (bool) $row['cnUSE_PAY_PAL']);
		define ('PAYPAL_EMAIL', stripslashes($row['cnPAYPAL_EMAIL']));
		define ('NPC_LABEL', stripslashes($row['cnNPC_LABEL']));
		define ('PAYPAL_AUTO_MARK_PAID', stripslashes($row['cnPAYPAL_AUTO_MARK_PAID']));
		define ('AUTO_ASSIGN_BUNKS', stripslashes($row['cnAUTO_ASSIGN_BUNKS']));
		define ('USE_SHORT_OS_NAMES', stripslashes($row['cnUSE_SHORT_OS_NAMES']));
		define ('ALLOW_EVENT_PACK_BY_POST', stripslashes($row['cnALLOW_EVENT_PACK_BY_POST']));
		define ('STAFF_LABEL', stripslashes($row['cnSTAFF_LABEL']));
		define ('QUEUE_OVER_LIMIT', stripslashes($row['cnQUEUE_OVER_LIMIT']));
	}
}