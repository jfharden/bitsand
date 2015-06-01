<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File Bitsand/Utilities/ErrorHandler.php
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

class ErrorHandler {
	protected static $_registered;

	public static function error($error_number, $error_string, $error_file, $error_line, $error_context) {
		\Bitsand\Registry::get('log')->write($error_string, $error_line, $error_file);
		if (\Bitsand\Config\Config::getVal('app', 'display_errors')) {
			echo '<style>table.x{border-style:solid;margin-bottom:6px}table.x td,table.x th[bgcolor="#eeeeec"]{font-family:monospace}</style>' , PHP_EOL;
			echo '<table class="x">';
			if (function_exists('xdebug_get_function_stack')) {
				ob_start();
				xdebug_print_function_stack('XxX'); $stack_line = __LINE__;
				$stack = ob_get_clean();

				$stack = str_replace(array('XxX', __FILE__, $stack_line . '<'), array($error_string, $error_file, $error_line . '<'), $stack);

				echo trim(strip_tags($stack, '<tr>,<th>,<td>,<span>'));
			} else {
				echo '<tbody><tr><th align="left" bgcolor="#f57900" colspan="5"><span style="background-color: #cc0000; color: #fce94f; font-size: x-large;">( ! )</span> Notice: ' , $error_string , ' in ' , $error_file , ' on line <i>' , $error_line , '</i></th></tr></tbody>';
			}
			echo '</table>' , PHP_EOL;
		}
	}

	public static function exception($exception) {
		\Bitsand\Registry::get('log')->write($exception->getMessage(), $exception->getLine(), $exception->getFile());
		if (\Bitsand\Config\Config::getVal('display_errors')) {
			if (isset($exception->xdebug_message)) {
				echo '<style>table.x{border-style:solid;margin-bottom:6px}table.x td,table.x th[bgcolor="#eeeeec"]{font-family:monospace}</style>';
				echo '<table class="x">' , $exception->xdebug_message , '</table>';
			}
		}
	}

	public static function registerHandlers() {
		if (static::$_registered) {
			return false;
		}

		static::$_registered = set_error_handler('Bitsand\Utilities\ErrorHandler::error');
		static::$_registered = set_exception_handler('Bitsand\Utilities\ErrorHandler::exception');

		return static::$_registered;
	}
}