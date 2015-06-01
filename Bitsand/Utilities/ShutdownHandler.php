<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File Bitsand/Utilities/ShutdownHandler.php
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

use Bitsand\Registry;
use Bitsand\Config\Config;

class ShutdownHandler {
	protected static $_registered;

	public static function shutdownBitsand() {
		$temp_path = Config::getBasePath() . '/var/tmp';
		if (is_dir($temp_path)) {
			$directory_handle = opendir($temp_path);
			while ($file = readdir($directory_handle)) {
				clearstatcache();
				unlink($temp_path . '/' . $file);
			}
			closedir($directory_handle);
		}

		$log_file = Registry::get('log')->getLogFile();
		if (is_file($log_file) && file_exists($log_file)) {
			$file_handle = fopen($log_file, 'r');
			flock($file_handle, LOCK_UN);
			fclose($file_handle);
		}
	}

	public static function registerShutdown() {
		static::$_registered = register_shutdown_function(function() {
			ShutdownHandler::shutdownBitsand();
		});
	}
}