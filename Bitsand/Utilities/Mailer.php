<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File Bitsand/Utilities/Mailer.php
 ||    Summary: Provides a complete e-mail solution for Bitsand.
 ||             It may seem a little counter-productive to have a bespoke
 ||             mailing solution for Bitsand when there are many alternatives
 ||             available (such as Swiftmail).  This class provides a simple
 ||             templating system that allows variable replacement (similar to
 ||             Smarty with it's curled brackets) in addition to sending a mail
 ||             using PHP's mail() function and an SMTP connection.  This
 ||             routine has been proven over many thousand mails and minimises
 ||             the number of anti-spam errors.
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
	const HTML = 'html';

	const PLAIN_TEXT = 'plain';

	const NL = "\n";

	const CR = "\r\n";

	/**
	 * @var string Holds the name of the HTML template file to use
	 */
	private $_html_file;

	/**
	 * @var string Holds the name of the plain text template file to use
	 */
	private $_plain_text_file;

	/**
	 * @var string Holds the contents of the HTML template to use
	 */
	private $_html;

	/**
	 * @var string Holds the contents of the plain text template to use
	 */
	private $_plain_text;

	/**
	 * @var string Holds the subject heading
	 */
	private $_subject;

	/**
	 * @var string Holds the sender
	 */
	private $_from;

	/**
	 * @var string Holds the tagging pattern in use within the template
	 */
	private $_tag_pattern = '/{(\w+)}/';

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

		$this->config = Registry::get('config');
	}


	/**
	 * Sets the subject line
	 * @param string $subject
	 */
	public function setSubject($subject) {
		$this->_subject = $subject;
	}

	/**
	 * Sets the e-mail of the sender
	 * @param string $from
	 */
	public function setFrom($from) {
		$this->_from = $from;
	}

	/**
	 * Sets the textual name of the sender
	 * @param string $sender
	 */
	public function setSender($sender) {
		$this->_sender = $sender;
	}

	/**
	 * All e-mail templates should live within the view folder in a directory
	 * called 'email'.
	 *
	 * @param string $template The template file to use
	 * @param string $type
	 */
	public function setMail($template = '', $type = self::HTML) {
		if (strpos($template, '/') === false) {
			$template = 'email/' . $template;
		}

		if (file_exists(($file = str_replace('/', DIRECTORY_SEPARATOR, $this->config->getAppPath() . 'view/' . $this->config->getVal('theme') . '/' . $template . '.html')))) {
			$template_file = $file;
		} else {
			// Look in the default theme folder if we don't have one
			if (file_exists(($file = str_replace('/', DIRECTORY_SEPARATOR , $this->config->getAppPath() . 'view/default/' . $template . '.html')))) {
				$template_file = $file;
			}
		}

		if (isset($template_file)) {
			if ($type == self::HTML) {
				$this->_html_file = $template_file;
			} elseif ($type == self::PLAIN_TEXT) {
				$this->_plain_text_file = $template_file;
			}
		}
	}

	/**
	 * Processes a template.  Used internally, but left as
	 * a public method as you never know if it might be useful.
	 *
	 * @param string $type Indicates what template to use
	 * @return string
	 */
	public function render($type = self::HTML) {
		if ($type === self::HTML) {
			$content = $this->_html;
			$file = $this->_html_file;
		} elseif ($type === self::PLAIN_TEXT) {
			$content = $this->_plain_text;
			$file = $this->_plain_text_file;
		}
		if (empty($content) && !empty($file)) {
			if (file_exists($file)) {
				$content = file_get_contents($file);
			} else {
				throw new \Bitsand\Exceptions\TemplateNotFoundException('Template file not found: ' . $file);
			}
		}

		if (!empty($content)) {
			$parsed_content = $this->_parseTemplate($content);

			if ($type === self::HTML) {
				$parsed_content = $this->_htmlwrap($parsed_content, 76);
				// This ensures that we don't need to load the file again
				$this->_html = $content;
			} else {
				$parsed_content = wordwrap($parsed_content, 76);
				// This ensures that we don't need to load the file again
				$this->_plain_text = $content;
			}
		} else {
			$parsed_content = '';
		}

		return $parsed_content;
	}

	/**
	 * Composes and sends the e-mail to the recipients
	 *
	 * @param string $recipient
	 * @return boolean
	 */
	public function sendTo($recipient, $extra_mail_parameters = '') {
		if (!$this->_validateData()) {
			throw new \Bitsand\Exceptions\IncompleteMailerException('Mailer does not have all fields configured');
		}

		$start = microtime(true);

		$html = $this->render(self::HTML);
		$plain_text = $this->render(self::PLAIN_TEXT);
		$subject = $this->_parseTemplate($this->_subject, $this->data);

		echo 'Render time: ' , (microtime(true) - $start) * 1000 , '<br/>';

		$boundary = '----=_NextPart_' . md5(time());

		$header = 'MIME-Version: 1.0' . self::NL;

		if ($this->config->get('mail_protocol') != 'mail') {
			$header .= 'To: ' . $recipient . self::NL;
			$header .= 'Subject: ' . $subject . self::NL;
		}

		$header .= 'Date: ' . date('D, d M Y H:i:s O') . self::NL;
		$header .= 'From: ' . $this->_sender . ' <' . $this->_from . '>' . self::NL;
		$header .= 'Sender: =?UTF-8?B?' . base64_encode($this->_sender) . '?= <' . $this->_from . '>' . self::NL;
		$header .= 'Return-Path: ' . $this->_from . self::NL;
		$header .= 'X-Mailer: PHP/' . phpversion() . self::NL;
		$header .= 'Content-Type: multipart/alternative; boundary="' . $boundary . '"' . self::NL . self::NL;

		if (empty($html)) {
			$message  = '--' . $boundary . self::NL;
			$message .= 'Content-Type: text/plain; charset="utf-8"' . self::NL;
			$message .= 'Content-Transfer-Encoding: 8bit' . self::NL . self::NL;
			$message .= $plain_text . self::NL;
		} else {
			$message  = '--' . $boundary . self::NL;
			$message .= 'Content-Type: multipart/alternative; boundary="' . $boundary . '_alt"' . self::NL . self::NL;
			$message .= '--' . $boundary . '_alt' . self::NL;
			$message .= 'Content-Type: text/plain; charset="utf-8"' . self::NL;
			$message .= 'Content-Transfer-Encoding: 8bit' . self::NL . self::NL;

			if (!empty($plain_text)) {
				$message .= $plain_text . self::NL . self::NL;
			} else {
				$message .= 'This is a HTML e-mail and your e-mail client software does not support HTML e-mail' . self::NL . self::NL;
			}

			$message .= '--' . $boundary . '_alt' . self::NL;
			$message .= 'Content-Type: text/html; charset="utf-8"' . self::NL;
			$message .= 'Content-Transfer-Encoding: 8bit' . self::NL . self::NL;
			$message .= $html . self::NL . self::NL;
			$message .= '--' . $boundary . '_alt--' . self::NL;
		}

		// Attachment mechanism would go here

		$message .= '--' . $boundary . '--' . self::NL;

		echo 'Compose time: ' , (microtime(true) - $start) * 1000 , '<br/>';

		if ($this->config->get('mail_protocol') == 'mail') {
			ini_set('sendmail_from', $this->_from);
			if (empty($extra_mail_parameters)) {
				mail($recipient, '=?UTF-8?B?' . base64_encode($subject) . '?=', $message, $header);
			} else {
				mail($recipient, '=?UTF-8?B?' . base64_encode($subject) . '?=', $message, $header, $extra_mail_parameters);
			}
		} elseif ($this->config->get('mail_protocol') == 'smtp') {
			$error_no = 0;
			$error_string = '';

			$handle = fsockopen($this->config->get('mail_hostname'), (int)$this->config->get('mail_port'), $error_no, $error_string, (float)$this->config->get('mail_timeout'));

			if (!$handle) {
				Registry::get('log')->write('[SMTP Error] ' . $error_string . ' (' . $error_no . ')', __FILE__, __LINE__);
				return false;
			}

			if (substr(PHP_OS, 0, 3) != 'WIN') {
				socket_set_timeout($handle, $this->config->get('mail_timeout'), 0);
			}

			while ($line = fgets($handle, 515)) {
				if (substr($line, 3, 1) == ' ') {
					break;
				}
			}

			if (substr($this->config->get('mail_hostname'), 0, 3) == 'tls') {
				fputs($handle, 'STARTTTLS' . self::CR);

				$reply = '';
				while ($line = fgets($handle, 515)) {
					$reply .= $line;
					if (substr($line, 3, 1) == ' ') {
						break;
					}
				}

				if (substr($reply, 0, 3) != '220') {
					Registry::get('log')->write('[SMTP Error] STARTTLS not accepted from server', __FILE__, __LINE__);
					return false;
				}
			}

			fputs($handle, 'EHLO ' . getenv('SERVER_NAME') . self::CR);

			$reply = '';
			while ($line = fgets($handle, 515)) {
				$reply .= $line;
				if (substr($line, 3, 1) == ' ') {
					break;
				}
			}

			if (substr($reply, 0, 3) != '250') {
				Registry::get('log')->write('[SMTP Error] EHLO not accepted from server', __FILE__, __LINE__);
				return false;
			}

			if (!empty($this->config->get('mail_username')) && !empty($this->config->get('mail_password'))) {
				fputs($handle, 'AUTH LOGIN' . self::CR);

				$reply = '';
				while ($line = fgets($handle, 515)) {
					$reply .= $line;
					if (substr($line, 3, 1) == ' ') {
						break;
					}
				}

				if (substr($reply, 0, 3) != '334') {
					Registry::get('log')->write('[SMTP Error] AUTH LOGIN not accepted from server', __FILE__, __LINE__);
					return false;
				}

				//
				fputs($handle, base64_encode($this->config->get('mail_username')) . self::CR);

				$reply = '';
				while ($line = fgets($handle, 515)) {
					$reply .= $line;
					if (substr($line, 3, 1) == ' ') {
						break;
					}
				}

				if (substr($reply, 0, 3) != '334') {
					Registry::get('log')->write('[SMTP Error] Username "' . $this->config->get('mail_username') . '" not accepted from server', __FILE__, __LINE__);
					return false;
				}

				//
				fputs($handle, base64_encode($this->config->get('mail_password')) . self::CR);

				$reply = '';
				while ($line = fgets($handle, 515)) {
					$reply .= $line;
					if (substr($line, 3, 1) == ' ') {
						break;
					}
				}

				if (substr($reply, 0, 3) != '235') {
					Registry::get('log')->write('[SMTP Error] Password not accepted from server', __FILE__, __LINE__);
					return false;
				}
			}

			fputs($handle, 'MAIL FROM: <' . $this->_from . '>' . ($this->config->get('mail_verp') ? 'XVERP' : '') . self::CR);

			$reply = '';
			while ($line = fgets($handle, 515)) {
				$reply .= $line;
				if (substr($line, 3, 1) == ' ') {
					break;
				}
			}

			if (substr($reply, 0, 3) != '250') {
				Registry::get('log')->write('[SMTP Error] MAIL FROM "' . $this->_from . '" not accepted from server', __FILE__, __LINE__);
				return false;
			}

			//
			fputs($handle, 'RCPT TO: <' . $recipient. '>' . self::CR);

			$reply = '';
			while ($line = fgets($handle, 515)) {
				$reply .= $line;
				if (substr($line, 3, 1) == ' ') {
					break;
				}
			}

			if (substr($reply, 0, 3) != '250' && substr($reply, 0, 3) != '251') {
				Registry::get('log')->write('[SMTP Error] RCPT TO "' . $recipient . '" not accepted from server', __FILE__, __LINE__);
				return false;
			}

			//
			fputs($handle, 'DATA' . self::CR);

			$reply = '';
			while ($line = fgets($handle, 515)) {
				$reply .= $line;
				if (substr($line, 3, 1) == ' ') {
					break;
				}
			}

			if (substr($reply, 0, 3) != '354') {
				Registry::get('log')->write('[SMTP Error] DATA not accepted from server', __FILE__, __LINE__);
				return false;
			}

			// According to rfc 821 we shouldn't send more than 1000 characters including the CR
			$message = str_replace("\r\n", "\n", $header . $message);
			$message = str_replace("\r", "\n", $message);
			$lines = explode("\n", $message);

			foreach ($lines as $line) {
				$results = str_split($line, 998);

				foreach ($results as $result) {
					if (substr(PHP_OS, 0, 3) != 'WIN') {
						fputs($handle, $result . self::CR);
					} else {
						fputs($handle, str_replace("\n", "\r\n", $result) . self::CR);
					}
				}
			}

			fputs($handle, '.' . self::CR);

			$reply = '';
			while ($line = fgets($handle, 515)) {
				$reply .= $line;
				if (substr($line, 3, 1) == ' ') {
					break;
				}
			}

			if (substr($reply, 0, 3) != '250') {
				Registry::get('log')->write('[SMTP Error] Message not accepted from server', __FILE__, __LINE__);
				return false;
			}

			//
			fputs($handle, 'QUIT' . self::CR);

			$reply = '';
			while ($line = fgets($handle, 515)) {
				$reply .= $line;
				if (substr($line, 3, 1) == ' ') {
					break;
				}
			}

			if (substr($reply, 0, 3) != '221') {
				Registry::get('log')->write('[SMTP Error] QUIT not accepted from server', __FILE__, __LINE__);
				return false;
			}

			fclose($handle);
		}

		echo 'Total time: ' , (microtime(true) - $start) * 1000 , '<br/>';
	}

	/**
	 * Enumerates the passed content for smarty style tags and merges the
	 * necessary variables in.  Also removes any HTML comments if they exist.
	 * @param string $content
	 * @return string
	 */
	private function _parseTemplate($content) {
		// Yoink out all of the html encoding
		$content = html_entity_decode($content, ENT_QUOTES, 'UTF-8');

		// Strip out all of the comments
		// Strip out all comments
		$comment_regex = array(
			'#<!--.*?-->#',        // Any html comments
			'#<p>/\*.*?\*/</p>#',  // Any /* */ within a paragraph
			'#/\*.*?\*/#s',        // Any /* */ blocks
			'#(?<!:)//.*#'         // Any // comments
		);
		$content = preg_replace($comment_regex, null, $content);

		// Handle any if queries, these check for the existance of a variable and if it exists
		while (preg_match('/{if:(.*?)}(.*?){\/if:.*}/simxU', $content, $ifs)) {
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

			$content = str_replace($ifs[0], $replace, $content);
		}

		// Handle any loops in the format {loop:variable}
		preg_match_all('/{loop:(.*?)}(.*?){\/loop}/i', $content, $loops);

		if (isset($loops[1]) && !empty($loops[1])) {
			foreach ($loops[1] as $loop_index => $loop_variable) {
				$loop_data = '';
				if (isset($this->data[$loop_variable])) {
					foreach ($this->data[$loop_variable] as $index => $data) {
						$loop_data .= $this->_replaceTags($loops[2][$loop_index], $data);
					}
				}
				$content = str_replace('{loop:' . $loop_variable . '}' . $loops[2][$loop_index] . '{/loop}', $loop_data, $content);
			}
		}

		// Now handle the main transplants
		$content = $this->_replaceTags($content, $this->data);

		// We now need to correctly html-ify the template
		return $this->_htmlentities($content);
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
			$this->_tag_pattern, array(&$this, '_getDataVariable'), $text
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

	/**
	 * Checks that we've set all of the fields
	 * @return boolean
	 */
	private function _validateData() {
		$errors = array();

		if (empty($this->_from)) {
			$errors['from'] = true;
		}

		if (empty($this->_sender)) {
			$errors['sender'] = true;
		}

		if (empty($this->_subject)) {
			$errors['subject'] = true;
		}

		if (empty($this->_html) && empty($this->_html_file) && empty($this->_plain_text) && empty($this->_plain_text_file)) {
			$errors['message'] = true;
		}

		return empty($errors);
	}
}