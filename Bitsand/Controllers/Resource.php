<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File Bitsand/Controllers/Resource.php
 ||    Summary: Provides access to the POST and GET variables, but handled so
 ||             as to prevent any XSS injection issues.
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

use Bitsand\Registry;
use Bitsand\Config\Config;

class Resource {
	private $file;

	public function __construct() {
		$this->request = Registry::get('request');
	}

	/**
	 * Checks to see if the requested resource exists
	 * @return boolean
	 */
	public function exists() {
		if (file_exists($file = Config::getAppPath() . 'view' . DIRECTORY_SEPARATOR . Config::getVal('theme') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $this->request->get['_resource_']))) {
			$this->file = $file;
		} elseif (file_exists($file = Config::getAppPath() . 'view' . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $this->request->get['_resource_']))) {
			$this->file = $file;
		} else {
			$this->request->get['_route_'] = 'error/not_found';
		}

		return !!$this->file;
	}

	/**
	 * Outputs the resource to the browser using the necessary headers
	 */
	public function output() {
		// If no file set then we need to check one exists
		if (!$this->file) {
			if (!$this->exists()) {
				throw new \Bitsand\Exceptions\ResourceNotFoundException($this->request->get['_resource_']);
			}
		}

		$mime_type = $this->mimeType($this->file);
		header('Content-Type: ' . $mime_type);

		// If we have the Apache X-Sendfile module, use this - most optimal method
		if (in_array('mod_xsendfile', apache_get_modules())) {
			header('X-Sendfile: ' . $this->file);
		} else {
			header('Content-Length: ' . filesize($this->file));
			readfile($this->file);
		}

		// Don't process anything else
		exit();
	}

	/**
	 * A crude, but fast method for determining the mime type of commonly
	 * served files.  Although it doesn't cover as many as finfo_file, it is
	 * significantly quicker.  Mime types taken from:
	 * http://pastie.org/5668002#140,176,278,359,543,590,609,815,912
	 *
	 * @param string $file
	 * @return string
	 */
	private function mimeType($file) {
		$suffixes = array(
			'css'  => 'text/css',
			'csv'  => 'text/csv',
			'doc'  => 'application/msword',
			'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'gif'  => 'image/gif',
			'jpe'  => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'jpg'  => 'image/jpeg',
			'js'   => 'application/javascript',
			'json' => 'application/json',
			'odt'  => 'application/vnd.oasis.opendocument.text',
			'pdf'  => 'application/pdf',
			'png'  => 'image/png',
			'rtf'  => 'application/rtf',
			'svg'  => 'image/svg+xml',
			'svgz' => 'image/svg+xml',
			'ttc'  => 'application/x-font-ttf',
			'ttf'  => 'application/x-font-ttf',
			'woff' => 'application/x-font-woff',
			'zip'  => 'application/zip'
		);
		$suffix = strtolower(preg_replace('/^.*\./', '', $file));

		if (isset($suffixes[$suffix])) {
			return $suffix;
		} else {
			return 'text/plain';
		}
	}
}