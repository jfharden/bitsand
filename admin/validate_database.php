<?php

/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File admin/admin.php
 |     Author: Pete Allison
 |  Copyright: (C) 2006 - 2017 The Bitsand Project
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

/**
 * Validates and updates the database.  Long term (v9) will be to implement a
 * complete schema comparison to ensure the database is 100% correct
 */
function validateDatabase() {
	global $link, $db_prefix;
	$updated = false;

	$query = ba_db_query($link, "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = '{$db_prefix}players' AND COLUMN_NAME = 'plPlayerNumber'");
	$column = ba_db_fetch_assoc($query);

	if (empty($column)) {
		ba_db_query($link, "ALTER TABLE `{$db_prefix}players` ADD COLUMN `plPlayerNumber` VARCHAR(24) NULL AFTER `plSurname`");
		$updated = true;
	}

	return $updated;
}