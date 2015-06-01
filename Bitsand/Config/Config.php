<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File Bitsand/Config/Config.php
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

namespace Bitsand\Config;

class Config {
	protected static $_config = array();
	protected static $_base_path;

	public static function loadConfigFile($file_path) {
		if (file_exists($file_path)) {
			$file_content = file_get_contents($file_path);

			$config_data = json_decode($file_content, true);
			static::$_config = array_replace_recursive(static::$_config, $config_data);
		}
	}

	/**
	 * Sets a value in the config.  This acts as a global variable.
	 * @param string $key
	 * @param mixed $value
	 * @return boolean
	 */
	public static function setVal($key, $value) {
		if (static::$_config[$key] = $value) {
			return true;
		}
	}

	/**
	 * Retrieve a config value
	 * @param string $key
	 * @param boolean $required
	 * @return mixed
	 */
	public static function getVal($key, $required = false) {
		if (isset(Config::$_config[$key])) {
			return Config::$_config[$key];
		} elseif ($required === true) {
			throw new Bitsand\Config\Exceptions\ConfigKeyNotFoundException("Config key not found: [{$key}]");
		}
	}

	/**
	 * Alias to getVal
	 * @param string $key
	 * @return mixed
	 */
	public static function get($key) {
		return Config::getVal($key, false);
	}

	/**
	 * Sets the base bath
	 * @param string $base_path
	 */
	public static function setBasePath($base_path) {
		$base_path = rtrim($base_path, '/');

		Config::$_base_path = $base_path;
	}



	/**
	 * Retrieves the base path
	 * @return string
	 */
	public static function getBasePath() {
		return str_replace('/', DIRECTORY_SEPARATOR, Config::$_base_path);
	}

	/**
	 * Retrieves the main application path
	 * @return string
	 */
	public static function getAppPath() {
		return str_replace('/', DIRECTORY_SEPARATOR, static::getBasePath() . 'booking/');
	}

	public static function dump() {
		var_dump(Config::$_config);
	}
}