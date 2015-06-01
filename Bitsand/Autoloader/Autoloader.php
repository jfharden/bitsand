<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File Bitsand/Autoloader/Autoloader.php
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

namespace Bitsand\Autoloader;

use Bitsand\Config\Config;

class Autoloader {
	protected static $_registered = false;

	/**
	 * Load a class by splitting the namespace to create a file path, as per
	 * PSR-4 for the core construction.  Other files follow a slightly
	 * different configuration that is designed for improved readability.
	 *
	 * All namespaces are separated using the backslash character (\)
	 * @param string $class
	 * @return boolean
	 */
	public static function loadClass($class) {
		$class = ltrim($class, '\\');

		if (preg_match('/^(Bitsand)/', $class)) {
			// Core Bitsand
			$file_path = str_replace('\\', DIRECTORY_SEPARATOR, Config::getBasePath() . $class . '.php');
		} else {
			$app_parts = explode('\\', $class);
			$class_name = array_pop($app_parts);
			$file_path = str_replace('/', DIRECTORY_SEPARATOR, Config::getAppPath() . strtolower(implode('/', $app_parts)) . '/');
			$path = '';

			$parts = preg_split('/(?=[A-Z])/', $class_name, 0 , PREG_SPLIT_NO_EMPTY);

			// We need to loop through until we match everything
			foreach ($parts as $part) {
				$part = strtolower($part);

				if (is_dir($file_path . $part . $path)) {
					$path .= $part . DIRECTORY_SEPARATOR;
					array_shift($parts);
					continue;
				}

				if (is_file($file_path . str_replace('../', '', $path) . strtolower(implode('_', $parts)) . '.php')) {
					$file_path .= str_replace('../', '', $path) . strtolower(implode('_', $parts)) . '.php';
					break;
				}

				$path .= $part;
			}
		}

		if (!file_exists($file_path)) {
			throw new \Bitsand\Exceptions\FileNotFoundException('File does not exist ' . $file_path);
		} else {
			return !!include($file_path);
		}
	}

	/**
	 * Registers the loadClass method to handle loading classes on the fly.
	 * This means that whenever we have a "use Bitsand\Class\Method" we
	 * automatically include the necessary file.
	 * @return boolean
	 */
	public static function autoloadRegister() {
		if (static::$_registered) {
			return false;
		}

		spl_autoload_extensions('.php');
		static::$_registered = spl_autoload_register('Bitsand\Autoloader\Autoloader::loadClass');

		return static::$_registered;
	}
}