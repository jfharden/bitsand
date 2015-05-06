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

<p><strong>LOGIN_TIMEOUT:</strong> Login timeout in minutes. If user does not request a page in this many minutes, they will have to re-login.</p>
<p><strong>LOGIN_TRIES:</strong> Number of login tries allowed before user account is locked.</p>
<p><strong>MIN_PASS_LEN:</strong> Minimum password length.</p>
<p><strong>SEND_PASSWORD:</strong> If checked, password will be included in e-mails sent to users when registering etc.</p>
<?php
include ('inc_help_foot.php');
?>
