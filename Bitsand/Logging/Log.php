<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File Bitsand/Logging/Logging.php
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

namespace Bitsand\Logging;

use Bitsand\Config\Config;

class Log {
	private $_log_file = '';

	public function write($message, $file='', $line='') {
		if (!$this->_log_file) {
			$this->_log_file = str_replace('/', DIRECTORY_SEPARATOR, Config::getVal('log') ? Config::getVal('log') : Config::getBasePath() . 'var/logs/errors.log');

			if (!file_exists(dirname($this->_log_file))) {
				mkdir(dirname($this->_log_file));
			}
		}

		$file_handle = fopen($this->_log_file, 'a+');
		// Don't log the LogFolderNotCreatable exception!
		if (!$file_handle && strpos($file, 'Log.php') === false) {
			throw new \Bitsand\Logging\Exceptions\LogFolderNotCreatable('Log file cannot be created');
		} else {
			// Separate each log record using a vertical tab in addition to line feed
			fwrite($file_handle, date('Y-m-d G:i:s') . ' - ' . stripslashes(var_export($message, true)) . chr(11) . PHP_EOL);
			fclose($file_handle);
		}
	}

	public function getLogFile() {
		return $this->_log_file;
	}
}