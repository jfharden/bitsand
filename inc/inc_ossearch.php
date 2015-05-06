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
error_reporting(E_ALL);
ini_set('display_errors', '1');

include ('inc_head_db.php');
$db_prefix = DB_PREFIX;
$q = strtolower($_GET["term"]);
$q = ba_db_real_escape_string($link, $q);

if (!$q) return;
	$sql = "SELECT * FROM {$db_prefix}osps where ospName like '%".$q."%' ORDER BY ospName limit 10";
	$result = ba_db_query ($link, $sql);

$results = array();
while ($row = ba_db_fetch_assoc ($result))
{
$results[] = array(value=>$row['ospID'], label=>$row['ospName'], allowadditional=>$row['ospAllowAdditionalText']);

}

echo json_encode($results);
?>
