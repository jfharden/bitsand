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

----------------------------------------------------------------------

Function to generate random string
$iMinLen: minimum length
$iMaxLen: maximum length
Returns string of random alpha-numeric characters
*/
function RandomString ($iMinLen, $iMaxLen) {
	//Initialise string to return and get length of string to create
	$sGenerated = '';
	$iLen = rand ($iMinLen, $iMaxLen);
	//Loop from 1 to length of return string
	for ($iPos = 1; $iPos <= $iLen; $iPos++)
		//Get random number from 1 to three to decide which set of ASCII characters this character is from
		switch (rand (1, 3)) {
			//Get random number
			case 1:
				$sGenerated .= chr (rand (48, 57));
				break;
			//Get random upper-case letter
			case 2:
				$sGenerated .= chr (rand (65, 90));
				break;
			//Get random lower-case letter
			case 3:
				$sGenerated .= chr (rand (97, 122));
				break;
		}
	return $sGenerated;
}

/*
Function to replace illegal characters in an e-mail, and to make it lower case.
This ensures that e-mail check is case-insensitive, and guards against bad entry
*/
function SafeEmail ($email) {
	//Check for characters in e-mail that aren't legal. Replace with '-'
	$email = str_replace (array (';', ',', ' ', '(', ')'), '-', $email);
	//E-mail returned in lower case to avoid problems caused by mis-matched case
	$email = strtolower ($email);
	return $email;
}
?>
