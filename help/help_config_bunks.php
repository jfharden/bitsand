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

<p><strong>PLAYER_BUNKS:</strong> Number of bunks for player bookings.</p>
<p><strong>MONSTER_BUNKS:</strong> Number of bunks for monster bookings.</p>
<p><strong>STAFF_BUNKS:</strong> Number of bunks for staff bookings.</p>
<p><strong>TOTAL_BUNKS:</strong> TOTAL_BUNKS can be used if you have a limited number of bunks, but do not mind how they are split up. If the total number of bunks booked is at least this many, no more bunk bookings of any type will be accepted. If set to zero, no bunk bookings of any type will be accepted (bunks can still be assigned manually)</p>
<?php
include ('inc_help_foot.php');
?>
