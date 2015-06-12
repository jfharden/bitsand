<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File Bitsand/Utilities/Tracy.php
 ||    Summary: Loads up the files required for the Tracy debugging tool.
 ||             Written for v2.3.1 of Tracy
 ||
 ||             https://github.com/nette/tracy
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
use Bitsand\Registry;

class Tracy {
	public static function init() {
		$tracy_path = Config::getBasePath() . '/Bitsand/Utilities/Tracy/';
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

		\Tracy\Debugger::enable(\Tracy\Debugger::DEVELOPMENT, Registry::get('log')->getLogPath());
		if (Config::getVal('error_strict') == true) {
			\Tracy\Debugger::$strictMode = true;
		}
	}
}