<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File Bitsand/Utilities/GitRepository.php
 |    Summary: Provides the mechanism for connecting to a Git(Hub) repository,
 |             using the Github API v3.  Currently will only query the tags,
 |             download a specific tag zip and extract it into the Bitsand
 |             root.
 |   Requires: PHP Curl library
 |             PHP >= 5.2 (ZipArchive library)
 |
 |     Author: Pete Allison
 |  Copyright: (C) 2006 - 2015 The Bitsand Project
 |             (http://github.com/PeteAUK/bitsand)
 |
 | Bitsand is free software; you can redistribute it and/or modify it under the
 | terms of the GNU General Public License as published by the Free Software
 | Foundation, either version 3 of the License, or (at your option) any later
 | version.
 |
 | Bitsand is distributed in the hope that it will be useful, but WITHOUT ANY
 | WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 | FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 | details.
 |
 | You should have received a copy of the GNU General Public License along with
 | Bitsand.  If not, see <http://www.gnu.org/licenses/>.
 +---------------------------------------------------------------------------*/

namespace Bitsand\Utilities;

class GitRepository {
	/**
	 * Holds the path to the repository API
	 * @var string $path
	 */
	private $path;

	/**
	 * Holds the top level repository details
	 * @var array $details
	 */
	private $details = array();

	/**
	 * Holds any tags created for the repository
	 * @var array $tags
	 */
	private $tags = array();

	public function __construct($repository_path, $source = 'github') {
		if (!function_exists('curl_init') || !class_exists('ZipArchive')) {
			return false;
		}

		switch ($source) {
			case 'github' :
				$this->path = 'https://api.github.com/repos' . rtrim(str_replace('https://github.com', '', $repository_path), '/');
				break;
			default :
				return false;
		}

		$repository_details = $this->curlFile($this->path);

		if (empty($repository_details)) {
			return false;
		}

		$this->details = $repository_details;
	}

	/**
	 * Retrieves the latest tag (version) available on Github
	 *
	 * @param boolean $remove_v [Optional] Removes the "v" character from the
	 * beginning of the version number
	 * @return string
	 */
	public function getLatestTag($remove_v = true) {
		$tags = $this->getTags();

		// Always going to be the first item
		if (isset($tags[0])) {
			if ($remove_v) {
				return str_replace('v', '', $tags[0]['name']);
			} else {
				return $tags[0]['name'];
			}
		}
		return false;
	}

	/**
	 * Retrieves all tags created from the Github repository
	 *
	 * @return array
	 */
	public function getTags() {
		if (!$this->tags && $this->details) {
			$this->tags = $this->curlFile($this->details['tags_url']);
		}
		return $this->tags;
	}

	/**
	 * Checks to ensure we've been able to connect to the repository.
	 *
	 * @return boolean
	 */
	public function connected() {
		return !!$this->details;
	}
	/**
	 * Checks to see if we can update automatically.  Checks the server has
	 * been able to download the repository details.
	 *
	 * @return boolean
	 */
	public function canUpdate() {
		return !!$this->details;
	}

	/**
	 * Checks to see if the installation is Git controlled.
	 * @return boolean
	 */
	public function gitControlled() {
		if (file_exists($this->bitsand_path . '.git' . DIRECTORY_SEPARATOR)) {
			return true;
		}
		return false;
	}

	/**
	 * Returns the Zipball URL for the passed version
	 * @param string $tag_version
	 * @return string
	 */
	public function getZipballUrl($tag_version) {
		$tags = $this->getTags();
		$url = '';

		// Loop through all the known tags until we match
		foreach ($tags as $index => $tag) {
			if ($tag['name'] == $tag_version || $tag['name'] == 'v' . $tag_version) {
				$url = $tag['zipball_url'];
				break;
			}
		}

		return $url;
	}

	/**
	 * Performs an inline update to the passed version number.  This only
	 * updates the actual files and doesn't execute any upgrade items.
	 * Additionally it performs no file checking, so any modifications made to
	 * core scripts will be overwritten.
	 *
	 * @param string $tag_version
	 * @param array $skip_folders [Optional] If set, won't replace certain
	 * folders.  This allows us to not upload the install and NON_WEB folders.
	 * @return boolean
	 *
	 * @todo This method needs to be moved into it's own class to keep the
	 * GitRepository class focused on GitHub methods.
	 */
	public function update($tag_version, $skip_folders = array()) {
		if (!$this->gitControlled()) {
			return false;
		}

		$url = $this->getZipballUrl($tag_version);

		if ($url) {
			$tmp_file = $this->download($url);

			if ($tmp_file) {
				// Create a regular expression for skipping files and folders
				if ($skip_folders) {
					$skip_regexp  = '/^' . addslashes($this->bitsand_path . '(' . implode('|', $skip_folders) . ')' . DIRECTORY_SEPARATOR) . '/i';
				}

				/*
				 * ZipArchive is available in PHP 5.2 and above out of the box,
				 * although other options exist, use the lowest available
				 * option.
				 *
				 * We need to extract each file one at a time as the zipball
				 * contains a sub-folder that we don't require - the name
				 * changes with each commit.
				 */
				$archive = new ZipArchive;
				if ($archive->open($tmp_file)) {
					$subfolder = '';
					for ($i = 0; $i < $archive->numFiles; $i++) {
						if (!$subfolder) {
							$subfolder = $archive->getNameIndex($i);
							continue;
						}
						$filename = $archive->getNameIndex($i);
						$fileinfo = pathinfo($filename);
						$destination = $this->bitsand_path . str_replace(array($subfolder, '/'), array('', DIRECTORY_SEPARATOR), $filename);
						if (!$skip_folders || preg_match($skip_regexp, $destination) == false) {
							// Copy file or create folder (if necessary)
							if (substr($filename, -1) != '/') {
								copy('zip://' . $tmp_file . '#' . $filename, $destination);
							} elseif (!file_exists($destination)) {
								mkdir($destination);
							}
						}
					}
					$archive->close();
				}
			} else {
				throw new Error('Unable to download zipball_url');
			}
		} else {
			throw new Error('Unable to locate zipball_url');
		}
	}

	/**
	 * Curl downloads a file.  By default it expects a json encoded response.
	 *
	 * @param string $path Url to download
	 * @param boolean $decode [Optional] Defaults to true and json_decodes the
	 * result.
	 * @param boolean $as_array [Optional] Defaults to true and will return the
	 * result as an array. If false then returns an object
	 * @return array|object
	 */
	private function curlFile($path, $decode = true, $as_array = true) {
		$result = $this->curl($path);

		if ($decode) {
			return json_decode($result, $as_array);
		} else {
			return $result;
		}
	}

	/**
	 * Simple CURL wrapper
	 *
	 * @param string $path
	 * @return string
	 */
	private function curl($path, $options = array()) {
		$options = array(
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_HEADER => false,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_URL => $path,
			CURLOPT_REFERER => $path,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13'
		) + $options;

		$ch = curl_init();
		foreach ($options as $option => $value) {
			curl_setopt($ch, $option, $value);
		}

		$result = curl_exec($ch);
		curl_close($ch);

		return $result;
	}

	/**
	 * Downloads a zip file into a tmp/ folder, returning the name of the
	 * temporary file it was downloaded into.
	 *
	 * @param string $zipball_url
	 * @param string $tmp_name [Optional] Name of the temporary file
	 * @return string
	 */
	private function download($zipball_url, $tmp_name = 'master.zip') {
		$base_path = dirname(__FILE__ . '../');

		$tmp_path = $base_path . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR;
		$tmp_file = $tmp_path . $tmp_name;

		if (!file_exists($tmp_path)) {
			try {
				mkdir($tmp_path);
			} catch (Exception $e) {
				throw new Error('Cannot create tmp/ folder');
			}
		}

		$file_pointer = fopen($tmp_file, 'w');

		$file = $this->curl($zipball_url, array(
			CURLOPT_AUTOREFERER => true,
			CURLOPT_BINARYTRANSFER => true,
			CURLOPT_FILE => $file_pointer
		));

		fclose($file_pointer);

		return $tmp_file;
	}
}