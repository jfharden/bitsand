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
//Load config file
require ('../inc/inc_config.php');
include ('../inc/inc_error.php');

//Function to obfuscate an e-mail address - returns address made up of HTML entities
function Obfuscate ($email) {
	$sReturn = '';
	for ($i = 0; $i < strlen ($email); $i++) {
		$bit = $email [$i];
		$sReturn .= '&#' . ord ($bit) . ';';
	}
	return $sReturn;
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<link rel = "shortcut icon" href = "favicon.ico">
<link rel = 'stylesheet' type = 'text/css' href = '../inc/main.css' media = 'screen'>
<link rel = 'stylesheet' type = 'text/css' href = '../inc/body.css' media = 'screen'>
<link rel = 'stylesheet' type = 'text/css' href = '../inc/help.css' media = 'screen'>
<link rel = 'stylesheet' type = 'text/css' href = '../inc/print.css' media = 'print'>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
<title>Help</title>
</head>
<body class="help">
