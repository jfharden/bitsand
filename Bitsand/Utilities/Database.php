<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File Bitsand/Utilities/Database.php
 ||    Summary: The main database routine.
 ||
 ||     Author: Pete Allison
 ||  Copyright: (C) 2006 - 2015 The Bitsand Project
 ||             (http://github.com/PeteAUK/bitsand)
 ||
 || Bitsand is free software; you can redistribute it and/or modify it under the
 || terms of the GNU General Public License as published by the Free Software
 || Foundation, either version 3 of the License, or (at your option) any later
 || version.
 ||
 || Bitsand is distributed in the hope that it will be useful, but WITHOUT ANY
 || WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 || FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 || details.
 ||
 || You should have received a copy of the GNU General Public License along with
 || Bitsand.  If not, see <http://www.gnu.org/licenses/>.
 ++--------------------------------------------------------------------------*/

namespace Bitsand\Utilities;

use Bitsand\Config\Config;

class Database {
	/**
	 * @var mixed $driver Holds the database driver object
	 */
	private $driver;

	public function __construct() {
		try {
			$class = '\Bitsand\Utilities\Drivers\\' . Config::get('db_driver');
			$this->driver = new $class(Config::get('db_host'), Config::get('db_user'), Config::get('db_password'), Config::get('db_database'));
		} catch (\Bitsand\Exceptions\FileNotFoundException $e) {
			// Means the driver doesn't exist, so throw a different exception
			throw new \Bitsand\Exceptions\DriverNotFoundException('The driver "' . Config::get('db_driver') . '" does not exist');
		}

		define('DB_PREFIX', Config::get('db_prefix'));
	}

	/**
	 * Performs a query
	 *
	 * @param string $sql
	 * @return array
	 */
	public function query($sql) {
		return $this->driver->query($sql);
	}

	/**
	 * Escapes the passed string
	 *
	 * @param string $value
	 * @return string
	 */
	public function escape($value) {
		return $this->driver->escape($value);
	}

	/**
	 * Returns the number of rows affected by the last query
	 *
	 * @return integer
	 */
	public function countAffected() {
		return $this->driver->countAffected();
	}

	/**
	 * Retrieves the last auto insert ID
	 *
	 * @return integer
	 */
	public function getLastId() {
		return $this->driver->getLastId();
	}
}