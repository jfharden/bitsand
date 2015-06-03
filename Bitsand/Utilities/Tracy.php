<?php
/*+----------------------------------------------------------------------------
 || Class PaMVC\Utilities\Tracy
 ||     Author:  Pete Allison
 ||
 ||    Purpose:  Loads up the files required for the Tracy debugging tool.
 ||       Written for v2.3.1 of Tracy
 ||
 ||       https://github.com/nette/tracy
 ||
 ++--------------------------------------------------------------------------*/
namespace PaMVC\Utilities;

use PaMVC\Config\Config;
use PaMVC\Registry;

class Tracy {
	public static function init() {
		$tracy_path = Config::getBasePath() . '/PaMVC/Utilities/Tracy/';
		require($tracy_path . 'IBarPanel.php');
		require($tracy_path . 'Bar.php');
		require($tracy_path . 'BlueScreen.php');
		require($tracy_path . 'DefaultBarPanel.php');
		require($tracy_path . 'Dumper.php');
		require($tracy_path . 'ILogger.php');
		require($tracy_path . 'FireLogger.php');
		require($tracy_path . 'Helpers.php');
		require($tracy_path . 'Logger.php');
		require($tracy_path . 'Debugger.php');
		require($tracy_path . 'OutputDebugger.php');

		\Tracy\Debugger::enable(\Tracy\Debugger::DEVELOPMENT, Registry::get('log')->getLogFile());
		if (Config::getVal('error_strict') == true) {
			\Tracy\Debugger::$strictMode = true;
		}
	}
}