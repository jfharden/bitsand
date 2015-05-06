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
include ('inc_help_head.php');
?>

<p><strong>SYSTEM_NAME:</strong> System name - used in e-mails, etc.</p>
<p><strong>TITLE:</strong> Title for HTML pages.</p>
<p><strong>EVENT_NAME:</strong> Name of upcoming event.</p>
<p><strong>ANNOUNCEMENT_MESSAGE:</strong> An announcement can be defined here. HTML is allowed.</p>
<p><strong>DISCLAIMER_TEXT:</strong> Disclaimer text. Used on signature form. It is suggested that you include the event name &amp; date.</p>
<p><strong>EVENT_CONTACT_NAME, EVENT_CONTACT_MAIL, TECH_CONTACT_NAME, TECH_CONTACT_MAIL:</strong> Contact details. Note that there are event and technical contacts. E-mail addresses will be obfuscated to try and stop spammers.</p>
<p><strong>BOOKING_FORM_FILE_NAME:</strong> Printable booking form file name. Place the booking form in the "img" directory.</p>
<p><strong>BOOKING_LIST_IF_LOGGED_IN:</strong> If checked, hides the booked list if user is not logged in.</p>
<p><strong>QUEUE_OVER_LIMIT:</strong> If checked, then bookings can still be made when they are over the limit for the type, they are just placed in the Queue.</p>
<p><strong>STAFF_LABEL:</strong> This is the label used for 'Staff' bookings, can be set to display as you require, e.g. 'Crew', 'Organisers' etc.</p>
<p><strong>NPC_LABEL:</strong> This is the label for the NPC checkbox.</p>
<?php
include ('inc_help_foot.php');
?>
