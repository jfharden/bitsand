<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File Bitsand/Utilities/Mailer.php
 ||    Summary: Allows us to send e-mails
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

use Bitsand\Registry;

class Mailer {
	/**
	 * @var string Holds the name of the template file to use
	 */
	private $template;

	/**
	 * @var string Holds the tagging pattern in use within the template
	 */
	private $tag_pattern = '/{(\w+)}/';

	/**
	 * Holds all of the variables to pass to the mail template
	 */
	public $data = array();

	public function __get($key) {
		return Registry::get($key);
	}

	public function __construct() {
		$this->data['year'] = date('Y');
		$this->data['month'] = date('F');
	}

	/**
	 * All e-mail templates should live within the view folder in a directory
	 * called 'email'.
	 *
	 * @param string $template
	 */
	public function setMail($mail_route) {
		if (strpos($mail_route, '/') === false) {
			$mail_route = 'email/' . $mail_route;
		}

		if (file_exists(($template = str_replace('/', DIRECTORY_SEPARATOR, $this->config->getAppPath() . 'view/' . $this->config->getVal('theme') . '/' . $mail_route . '.html')))) {
			$this->template = $template;
		} else {
			// Look in the default theme folder if we don't have one
			if (file_exists(($template = str_replace('/', DIRECTORY_SEPARATOR , $this->config->getAppPath() . 'view/default/' . $mail_route . '.html')))) {
				$this->template = $template;
			}
		}
	}

	public function render() {
		if ($this->template) {
			$template = file_get_contents($this->template);

			$template = $this->_parseTemplate($template);

			$template = $this->_htmlwrap($template, 76);

			echo $template;
		} else {
			throw new \Bitsand\Exceptions\TemplateNotFoundException('Template file not found: ' . $this->template);
		}
	}

	private function _parseTemplate($template) {
		// Yoink out all of the html encoding
		$template = html_entity_decode($template, ENT_QUOTES, 'UTF-8');

		// Strip out all of the comments
		// Strip out all comments
		$comment_regex = array(
			'#<!--.*?-->#',        // Any html comments
			'#<p>/\*.*?\*/</p>#',  // Any /* */ within a paragraph
			'#/\*.*?\*/#s',        // Any /* */ blocks
			'#(?<!:)//.*#'         // Any // comments
		);
		$template = preg_replace($comment_regex, null, $template);

		// Handle any if queries, these check for the existance of a variable and if it exists
		while (preg_match('/{if:(.*?)}(.*?){\/if:.*}/i', $template, $ifs)) {
			$replace = '';

			$value = isset($this->data[$ifs[1]]) ? $this->data[$ifs[1]] : null;

			if (stripos($ifs[0], '{else}') === false) {
				if ($value) {
					$replace = $ifs[2];
				}
			} else {
				if ($value) {
					$replace = substr($ifs[2], 0, stripos($ifs[2], '{else}'));
				} else {
					$replace = substr($ifs[2], stripos($ifs[2], '{else}') + 6);
				}
			}

			$template = str_replace($ifs[0], $replace, $template);
		}


		// Handle any loops in the format {loop:variable}
		preg_match_all('/{loop:(.*?)}(.*?){\/loop}/i', $template, $loops);

		if (isset($loops[1]) && !empty($loops[1])) {
			foreach ($loops[1] as $loop_index => $loop_variable) {
				$loop_data = '';
				if (isset($this->data[$loop_variable])) {
					foreach ($this->data[$loop_variable] as $index => $data) {
						$loop_data .= $this->_replaceTags($loops[2][$loop_index], $data);
					}
				}
				$template = str_replace('{loop:' . $loop_variable . '}' . $loops[2][$loop_index] . '{/loop}', $loop_data, $template);
			}
		}

		// Now handle the main transplants
		$template = $this->_replaceTags($template, $this->data);

		// We now need to correctly html-ify the template
		return $this->_htmlentities($template);
	}

	/**
	 * Replaces all of the curled bracket variables with their PHP versions.
	 * @param string $text
	 * @param array $data
	 * @return string
	 */
	private function _replaceTags($text, $data) {
		$this->_cdata = $data;
		$markup = preg_replace_callback(
			$this->tag_pattern, array(&$this, '_getDataVariable'), $text
		);

		unset($this->_cdata);

		return $markup;
	}

	/**
	 * Retrieves the data for a specific tag
	 * @param string $tag
	 * @return string
	 */
	private function _getDataVariable($tag) {
		if (isset($this->_cdata[$tag[1]])) {
			return $this->_cdata[$tag[1]];
		} else {
			return '';
		}
	}

	/**
	 * Perfoms html encoding on the passed string but preserves any html tags.
	 * Unlike modifying the translation table, this ensures we encode < and >
	 * that are within the body of the text.
	 * @param string $html
	 * @param integer $quote_style
	 * @return string
	 */
	private function _htmlentities($html, $quote_style = ENT_COMPAT) {
		$matches = array();
		$sep = '###HTMLTAG###';

		preg_match_all(":</{0,1}[a-z]+[^>]*>:i", $html, $matches);

		$temp = preg_replace(":</{0,1}[a-z]+[^>]*>:i", $sep, $html);
		$temp = explode($sep, $temp);

		for ($i = 0; $i < count($temp); $i++) {
			$temp[$i] = htmlentities($temp[$i], $quote_style, 'UTF-8', false);
		}

		$temp = join($sep, $temp);

		for ($i = 0; $i < count($matches[0]); $i++) {
			$temp = preg_replace(":$sep:", $matches[0][$i], $temp, 1);
		}

		return $temp;
	}

	/**
	 * Safely wraps HTML without breaking src's or other attributes.
	 * @param string $str
	 * @param integer $width
	 * @param string $break
	 * @param string $nobreak
	 * @return string
	 */
	private function _htmlwrap($string, $length=76, $break = PHP_EOL, $nobreak = '') {
		// Mash into a single line
		$string = str_replace(array("\t", "\r", "\n"),'',$string);

		$in_tag = false;
		$in_attribute = false;
		$wrapped = '';
		$current_line = '';
		$last_space = 0;
		$opener = '';
		$attr = '';

		for($pos = 0; $pos < strlen($string); $pos++) {
			$letter = $string[$pos];

			if ($letter == '<') {
				$in_tag = true;
			} elseif ($letter == '>') {
				$in_tag = false;
				// Special to handle extra long attributes
				if (strlen($current_line) > $length) {
					$last_space = strlen($current_line);
				}
			} elseif ($in_tag && !$in_attribute && ($letter == "'" || $letter == '"')) {
				$attr = substr($current_line, $last_space + 1, -1);
				$in_attribute = strlen($current_line);
				$opener = $letter;
			} elseif ($in_attribute && $letter == $opener) {
				if ($attr == 'src') {
					if (!$last_space) {
						$current_line = str_replace(' ', '%20', $current_line);
					} else {
						$current_line = substr($current_line, 0, $in_attribute) . str_replace(' ', '%20', substr($current_line, $in_attribute));
					}

				}
				$attr = '';
				$in_attribute = false;
				$opener = '';
			} elseif ($letter == ' ' && !$in_attribute) {
				// Don't track spaces within attributes
				$last_space = strlen($current_line);
			}

			if (strlen($current_line) > $length) {
				if ($last_space) {
					$wrapped .= (!empty($wrapped) ? $break: '') . substr($current_line, 0, $last_space);
					$current_line = substr($current_line, $last_space + 1);
					$last_space = 0;
				}
			}

			$current_line .= $letter;
		}
		return $wrapped . $current_line;
	}
}