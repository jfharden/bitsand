<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File Bitsand/Controllers/Document.php
 ||    Summary: Provides a container for html document items such as titles,
 ||             scripts, stylesheets and similar.
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

class Document {
	private $title;
	private $description;
	private $keywords;
	private $styles = array();
	private $scripts = array();
	private $footer_scripts = array();

	public function __construct() {
		$this->route = Registry::get('router');
	}

	/**
	 * Sets the document title
	 * @param string $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * Retrieves the document title
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Sets the document meta description
	 * @param string $description
	 */
	public function setDescription($description) {
		$this->description = $description;
	}

	/**
	 * Retrieves the meta description
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Sets the document meta keywords
	 * @param string $keywords
	 */
	public function setKeywords($keywords) {
		$this->keywords = $keywords;
	}

	/**
	 * Retrieves the meta description
	 * @return string
	 */
	public function getKeywords() {
		return $this->keywords;
	}

	/**
	 * Adds a stylesheet to the document
	 * @param string $href
	 * @param string $rel [Optional]
	 * @param string $media [Optional]
	 */
	public function addStyle($href, $rel = 'stylesheet', $media = 'screen') {
		$this->styles[md5($href)] = array(
			'href'  => $this->route->getBaseUrl(false) . $href,
			'rel'   => $rel,
			'media' => $media
		);
	}

	/**
	 * Retrieves all stylesheets within the document
	 * @return array
	 */
	public function getStyles() {
		return $this->styles;
	}

	/**
	 * Adds javascript to the document
	 * @param [type]  $script [description]
	 * @param boolean $header [description]
	 * @param string Any number of extra parameters - including "defer"
	 */
	public function addScript($script, $header = true) {
		$additional = array();
		if (func_num_args() > 2) {
			$arguments = func_get_args();
			$additional = array_splice($arguments, 2);
		}

		if (!preg_match('~(http|\/\/)~', $script)) {
			$script = $this->route->getBaseUrl(false) . $script;
		}

		if ($header) {
			$this->scripts[md5($script)] = array(
				'href'  => $script,
				'defer' => in_array('defer', $additional)
			);
		} else {
			$this->footer_scripts[md5($script)] = array(
				'href'  => $script,
				'defer' => in_array('defer', $additional)
			);
		}
	}

	/**
	 * Retrieves all scripts within the document
	 * @return array
	 */
	public function getScripts($header = true) {
		if ($header) {
			return $this->scripts;
		} else {
			return $this->footer_scripts;
		}
	}
}