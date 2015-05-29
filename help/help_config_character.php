<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File help/help_config_character.php
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

<p><strong>LOCATIONS_LABEL:</strong> If you wish to have your characters inform you where they're from, set a label for the drop-down box here. If it is left blank, the drop-down box will not appear.</p>
<p><strong>LIST_GROUPS_LABEL:</strong> If you wish to have your characters inform you what group they are in, set a label for the drop-down box here (this is configurable, as some factions may refer to groups as "clans" or "houses", for instance). If it is left blank, the drop-down box will not appear.</p>
<p><strong>ANCESTOR_DROPDOWN:</strong> Check this setting to display a dropdown list of ancestors, in addition to the free text field.</p>
<p><strong>USE_SHORT_OS_NAMES:</strong> In the booking list export, if this is true, short OS names will be used, otherwise the full name will be exported.</p>
<p><strong>DEFAULT_FACTION:</strong> This faction will be selected by default in the drop-down list on the IC booking form.</p>
<p><strong>NON_DEFAULT_FACTION_NOTES:</strong> If the user selects another faction, they will be prompted to put something in the Notes box to explain who invited them, or their IC reason for attending, unless this value is unchecked.</p>
<p><strong>IC_NOTES_TEXT:</strong> This value is for the text above the notes box on the IC Form.</p>
<p><strong>ALLOW_EVENT_PACK_BY_POST:</strong> This value determines if people can request an event pack to be sent by post.</p>
<?php
include ('inc_help_foot.php');
?>
