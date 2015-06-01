<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File Bitsand/Utilities/Device.php
 ||    Summary: Attempts to determine if the viewer is on a mobile, tablet or
 ||             dekstop.
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
use Bitsand\Config\Config;

final class Device {
	private $_agent_matches = array();
	private $_mobile_agents = array('iPod','iPhone','webOS','BlackBerry','windows phone','symbian','vodafone','opera mini','windows ce','smartphone','palm','midp');
	private $_tablet_agents = array('iPad','RIM Tablet','hp-tablet','Kindle Fire','Android');

	public function __construct() {
		$this->request = Registry::get('request');
		$this->session = Registry::get('session');

		if (isset($this->request->get['change_device'])) {
			$device_name = $this->request->get['change_device'];
			$this->session->data['set_device'] = $device_name;
		}

		if (!isset($this->session->data['set_device'])) {
			if (!isset($this->session->data['device']) || !isset($this->request->cookie['device'])) {
				if ($this->isTablet()) {
					$this->set('tablet');
				} elseif ($this->isMobile()) {
					$this->set('mobile');
				} else {
					$this->set('desktop');
				}
			}
		} elseif (isset($this->request->get['change_device'])) {
			if ($device_name == 'mobile_desktop' || $device_name == 'tablet_desktop') {
				$this->set('desktop');
			} elseif ($device_name == 'mobile') {
				$this->set('mobile');
			} elseif ($device_name == 'tablet') {
				$this->set('tablet');
			}
		}
	}

	/**
	 * Sets the device to be a specific type (mobile/tablet/desktop)
	 * @param string $device
	 */
	public function set($device) {
		$this->session->data['device'] = $device;
	}

	/**
	 * Checks to see if the client device is a mobile device or not
	 * @return boolean
	 */
	public function isMobile() {
		if (isset($this->session->data['device']) && $this->session->data['device'] == 'mobile') {
			return true;
		}
		if (isset($this->request->server['HTTP_USER_AGENT'])) {
			if (!isset($this->_agent_matches[$this->request->server['HTTP_USER_AGENT']]['mobile'])) {
				$mobile = false;

				foreach ($this->_mobile_agents as $mobile_agent) {
					if (stripos($this->request->server['HTTP_USER_AGENT'], $mobile_agent)) {
						$mobile = true;
						break;
					}
				}
				if (!$mobile && stripos($this->request->server['HTTP_USER_AGENT'], 'Android') && stripos($this->request->server['HTTP_USER_AGENT'], 'mobile')) {
					$mobile = true;
				}

				$this->_agent_matches[$this->request->server['HTTP_USER_AGENT']]['mobile'] = $mobile;
			}

			return $this->_agent_matches[$this->request->server['HTTP_USER_AGENT']]['mobile'];
		}

		return false;
	}

	/**
	 * Checks to see if the client device is a tablet or not
	 * @return boolean
	 */
	public function isTablet() {
		if (isset($this->session->data['device']) && $this->session->data['device'] == 'tablet') {
			return true;
		}
		if (isset($this->request->server['HTTP_USER_AGENT'])) {
			if (!isset($this->_agent_matches[$this->request->server['HTTP_USER_AGENT']]['tablet'])) {
				$tablet = false;

				foreach ($this->_tablet_agents as $tablet_agent) {
					if (stripos($this->request->server['HTTP_USER_AGENT'], $tablet_agent)) {
						$tablet = true;
						break;
					}
				}
				if (!$tablet && stripos($this->request->server['HTTP_USER_AGENT'], 'Android') && stripos($this->request->server['HTTP_USER_AGENT'], 'tablet')) {
					$tablet = false;
				}

				$this->_agent_matches[$this->request->server['HTTP_USER_AGENT']]['tablet'] = $tablet;
			}

			return $this->_agent_matches[$this->request->server['HTTP_USER_AGENT']]['tablet'];
		}

		return false;
	}
}