<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File Bitsand/Utilities/Database/MySQLi.php
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

namespace Bitsand\Utilities\Drivers;

use Bitsand\Config\Config;

class MySQLi {
	private $link;

	public function __construct($hostname, $username, $password, $database) {
		$this->link = new \mysqli($hostname, $username, $password, $database);

		if (mysqli_connect_error()) {
			throw new \Bitsand\Exceptions\DatabaseLinkError('Could not make a database link (' . mysqli_connect_errno() . ') ' . mysqli_connect_error());
		}

		$this->link->set_charset('utf8');
		$this->link->query("SET SQL_MODE = ''");
	}

	public function query($sql) {
		$query = $this->link->query($sql);

		if (!$this->link->errno) {
			if (isset($query->num_rows)) {
				$data = array();

				while ($row = $query->fetch_assoc()) {
					$data[] = $row;
				}

				$result = new \stdClass();
				$result->num_rows = $query->num_rows;
				$result->row = isset($data[0]) ? $data[0] : array();
				$result->rows = $data;

				unset($data);

				$query->close();

				return $result;
			} else {
				return true;
			}
		} else {
			throw new \Bitsand\Exceptions\DatabaseQueryError('Error: ' . $this->link->error . '<br />Error No: ' . $this->link->errno . '<br />' . $sql);
		}
	}

	/**
	 * Escapes the passed string
	 *
	 * @param string $value
	 * @return string
	 */
	public function escape($value) {
		return $this->link->real_escape_string($value);
	}

	/**
	 * Returns the number of rows affected in the last query
	 *
	 * @return integer
	 */
	public function countAffected() {
		return (int)$this->link->affected_rows;
	}

	/**
	 * Returns the last insert ID
	 *
	 * @return integer
	 */
	public function getLastId() {
		return (int)$this->link->insert_id;
	}

	/**
	 * Closes the link when the class destructs
	 */
	public function __destruct() {
		$this->link->close();
	}
}