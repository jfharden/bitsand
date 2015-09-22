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

use Bitsand\Registry;

class Config {
	const OVERWRITE_NOT_EMPTY = 'overwrite_not_empty';
	const OVERWRITE_NONE = 'overwrite_none';
	const OVERWRITE_ALL = 'overwrite_all';

	protected static $_config = array();
	protected static $_base_path;
	protected static $_app_directory;

	public static function loadConfigFile($file_path) {
		if (file_exists($file_path)) {
			$file_content = file_get_contents($file_path);

			$config_data = json_decode($file_content, true);
			static::$_config = array_replace_recursive(static::$_config, $config_data);
		}
	}

	/**
	 * Loads the configuration variables from the database.
	 * The old Bitsand used column names rather than key-indexed items, so run
	 * the resultant data through a cross-reference mechanism
	 *
	 * @param boolean $overwrite [Optional] If true then will replace any items
	 * that have been set within the source file.  Defaults to off.
	 * @todo Change this to use a more traditional key-indexed table
	 */
	public static function loadFromDB($overwrite = self::OVERWRITE_NONE) {
		$db = Registry::get('db');

		$config_query = $db->query("SELECT * FROM " . DB_PREFIX . "config WHERE cnName = 'Default'");

		if ($config_query->num_rows == 0) {
			return;
		}

		$config_row = $config_query->row;

		$translate = array(
			'cnANNOUNCEMENT_MESSAGE'      => 'announcement_message',
			'cnDISCLAIMER_TEXT'           => 'disclaimer',
			'cnEVENT_CONTACT_NAME'        => 'event_contact',
			'cnEVENT_CONTACT_MAIL'        => 'event_contact_email',
			'cnTECH_CONTACT_NAME'         => 'tect_contact',
			'cnTECH_CONTACT_MAIL'         => 'tech_contact_email',
			'cnTITLE'                     => 'site_title',
			'cnSYSTEM_NAME'               => 'site_name',
			'cnBOOKING_FORM_FILE_NAME'    => 'booking_form_file_name',
			'cnBOOKING_LIST_IF_LOGGED_IN' => 'booking_list_if_logged_in',
			'cnLOCATIONS_LABEL'           => 'character_location_label',
			'cnLIST_GROUPS_LABEL'         => 'list_groups_label',
			'cnANCESTOR_DROPDOWN'         => 'ancestor_dropdown',
			'cnDEFAULT_FACTION'           => 'default_faction',
			'cnNON_DEFAULT_FACTION_NOTES' => 'non_default_faction_notes',
			'cnIC_NOTES_TEXT'             => 'ic_notes_text',
			'cnLOGIN_TIMEOUT'             => 'login_timeout',
			'cnMIN_PASS_LEN'              => 'minimum_password_length',
			'cnSEND_PASSWORD'             => 'email_password',
			'cnUSE_PAY_PAL'               => 'use_paypal',
			'cnPAYPAL_EMAIL'              => 'paypal_email',
			'cnNPC_LABEL'                 => 'npc_label',
			'cnPAYPAL_AUTO_MARK_PAID'     => 'paypal_mark_as_paid',
			'cnAUTO_ASSIGN_BUNKS'         => 'auto_assign_bunks',
			'cnUSE_SHORT_OS_NAMES'        => 'use_short_os_names',
			'cnUSE_QUEUE'                 => 'use_queue',
			'cnALLOW_EVENT_PACK_BY_POST'  => 'event_pack_by_post',
			'cnSTAFF_LABEL'               => 'staff_label',
			'cnQUEUE_OVER_LIMIT'          => 'queye_over_limit'
		);

		foreach ($translate as $field => $key) {
			$value = $config_row[$field];
			if (!isset(static::$_config[$key]) || $overwrite == static::OVERWRITE_ALL || ($overwrite == static::OVERWRITE_NOT_EMPTY && $value != '' && !is_null($value))) {
				static::$_config[$key] = is_numeric($value) ? (int)$value : $config_row[$field];
			}
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
			throw new \Bitsand\Exceptions\ConfigKeyNotFoundException("Config key not found: [{$key}]");
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
		Config::$_base_path = $base_path;
	}

	/**
	 * Sets the application directory
	 * @param type $app_directory
	 * @return type
	 */
	public static function setAppDirectory($app_directory) {
		Config::$_app_directory = $app_directory;
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
		return str_replace('/', DIRECTORY_SEPARATOR, static::getBasePath() . static::$_app_directory . DIRECTORY_SEPARATOR);
	}

	/**
	 * Returns the application directory folder name
	 * @return string
	 */
	public static function getAppDirectory() {
		return static::$_app_directory;
	}

	public static function dump() {
		var_dump(Config::$_config);
	}
}