<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File Bitsand/Controllers/View.php
 ||    Summary: Handles passing variables set within the controller to the view
 ||             and tackles compression methods.
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

namespace Bitsand\Controllers;

use Bitsand\Config\Config;
use Bitsand\Registry;

class View {
	private $_headers = array();
	private $_compression_level = 6;
	private $_output;
	private $_post_output_callbacks = array();
	/**
	 * With investigation, gzcompress can offer a small (~3.8ms) speed
	 * advantage over gzencode. The only disadvantage is gzcompress
	 * requires additional output to screen.
	 * @var string
	 */
	private $_compression_method = 'gzcompress';

	public function __construct() {
		if (Config::getVal('pci_harden')) {
			$this->headers[] = 'X-XSS-Protection:1; mode=block';
			$this->headers[] = 'X-Frame-Options: SAMEORIGIN';
			$this->headers[] = 'X-Content-Type-Options: nosniff';
		}
		$this->request = Registry::get('request');
	}

	/**
	 * Registers a specific header to be sent
	 * @param string $header
	 * @param string $value
	 */
	public function addHeader($header, $value) {
		$this->_headers[$header] = $header . ': ' . $value;
	}

	/**
	 * Changes the default compression level to use when outputting the page.
	 * @param integer $level
	 */
	public function setCompression($level) {
		if ((int)$level < 0) {
			$level = 0;
		} elseif ((int)$level > 9) {
			$level = 9;
		}
		$this->_compression_level = (int)$level;
	}

	/**
	 * Sets the actual output to use for the page.
	 * @param string $output
	 */
	public function setOutput($output) {
		$this->_output = $output;
	}

	/**
	 * Adds a post callback Action hook.  Post callbacks occur after the main
	 * page content has been output to the user, this allows the option to
	 * perform various processor intensive functions without it impacting the
	 * viewer.  Care should be taken however as this will place additional load
	 * onto the server, so some form of control needs to be put in place.
	 * @param Bitsand\Controllers\Action $action
	 */
	public function addPostCallback($action) {
		$this->_post_output_callbacks[] = $action;
	}

	/**
	 * Outputs the page content to screen
	 */
	public function output() {
		if (!empty($this->_output)) {
			if ($this->_post_output_callbacks) {
				ob_start();
			}

			// Compress if level is above 0 (upto 9) and there is enough output to justify it
			if ($this->_compression_level > 0 && strlen($this->_output) > 2048) {
				$output = $this->compress($this->_output, $this->_compression_level);
			} else {
				$output = $this->_output;
				$this->_compression_level = 0;
			}

			if ($this->_compression_level > 0 && $this->_compression_method == 'gzcompress') {
				print("\x1f\x8b\x08\x00\x00\x00\x00\x00");
			}

			if (!headers_sent()) {
				foreach ($this->_headers as $idx => $header) {
					header($header, true);
				}
			}

			echo $output;
		}
	}

	/**
	 * Sends the post callbacks
	 */
	public function sendPostCallbacks() {
		if (!empty($this->_post_output_callbacks)) {
			$size = ob_get_length();

			set_time_limit(5 * 60);

			header('Content-Length: ' . $size);
			header('Connection: close');

			ob_end_flush();
			@ob_flush();
			flush();

			if (session_id()) {
				session_write_close();
			}

			foreach ($this->_post_output_callbacks as $action_details) {
				$class = $action_details->getClass();
				$method = $action_details->getMethod();
				$args = $action_details->getArgs();

				if (method_exists($class, $method)) {
					if ($action_details->isController()) {
						$controller = new $class();
						$action = call_user_func_array(array($controller, $method), $args);
					} else {
						$action = call_user_func_array(array($class, $method), $args);
					}
				} else {
					Registry::get('log')->write('Post callback class file not found: ' . $class . '->' . $method);
					//throw new ClassNotFoundException('Class file not found: ' . $class . '->' . $method);
				}
			}
		}
	}

	/**
	 * Performs compression at the approproate level
	 * @param type $data
	 * @param type $level
	 * @return type
	 */
	private function compress($data, $level = 0) {
		if (isset($this->request->server['HTTP_ACCEPT_ENCODING'])) {
			if (strpos($this->request->server['HTTP_ACCEPT_ENCODING'], 'x-gzip') !== false) {
				$encoding = 'x-gzip';
			} elseif (strpos($this->request->server['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
				$encoding = 'gzip';
			}
		}

		if (!isset($encoding) || !extension_loaded('zlib') || ini_get('zlib.output_compression') || headers_sent() || connection_status()) {
			$this->_compression_level = 0;
			return $data;
		}

		$this->addHeader('Content-Encoding', $encoding);
		if ($this->_compression_method != 'gzcompress') {
			return gzencode($data, (int)$level);
		} else {
			return gzcompress($data, (int)$level);
		}
	}
}