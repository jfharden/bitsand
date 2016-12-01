<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File Bitsand\Utilities\Functions.php
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

namespace Bitsand\Utilities\Functions {
	/**
	 * Makes the first character of the passed string uppercase.  This should be
	 * used in conjunction with a preg_replace_callback for camel casing strings
	 * such as used within Bitsand\Controllers\ActionRoute.
	 * @param array $arr
	 * @return string
	 */
	function ucfirst_array($arr) {
		return strtoupper($arr[1]);
	}

	/**
	 * Utilises a global variable function for quickly outputing an html encoded
	 * string to the screen.  This is available primarily for use within the code
	 * of the various .phtml view pages.
	 */
	global $_;
	$_ = (function($string) {
		echo htmlentities($string, ENT_QUOTES, 'UTF-8', true);
	});

	/**
	 * This function performs a "safe" include of the passed file, this is useful
	 * as it prevents (breaks) the object scope inheritance, ensuring the
	 * Controller methods aren't available for the template files.  This in turn
	 * forces good practice of making templates "dumb".
	 * @param string $file
	 * @param array $data
	 */
	function safeinclude($file, $data) {
		global $_;
		// Oddly this has a better performance than extract
		foreach ($data as $key => $value) {
			$$key = $value;
		}

		include($file);
	}

	/**
	 * A simple wrapper function that performs the same function as
	 * file_get_contents but for a URL.  The big difference is that if the server
	 * has allow_url_fopen disabled this will fallback to using curl.
	 *
	 * If no response is received then this function will simply return an empty
	 * string.
	 *
	 * @param string $url
	 * @return string
	 */
	function url_get_contents($url) {
		if ((int)ini_get('allow_url_fopen') == 0) {
			// Need to use curl
			$options = array(
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_HEADER => false,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_URL => $url,
				CURLOPT_REFERER => $path,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13'
			);

			$ch = curl_init();
			foreach ($options as $option => $value) {
				curl_setopt($ch, $option, $value);
			}

			$result = curl_exec($ch);
			curl_close($ch);
		} else {
			// Can use file_get_contents
			$headers = get_headers($url);
			$response = substr($headers[0], 9, 3);

			if ($response != '200') {
				$result = '';
			} else {
				$result = file_get_contents($url);
			}
		}

		return $result;
	}
}

namespace {
	/**
	 * Removes all funny accents and similar, whilst also converting to lowercase.
	 * @param string $string
	 * @return string
	 */
	function strtosafelower($string) {
		$converted = strtr($string,'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ','aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
		return preg_replace('/&#?[a-z0-9]+;/i', '', htmlentities(strtolower($string), ENT_QUOTES, 'UTF-8', true));
	}
}