<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File Bitsand/Controllers/Controller.php
 ||    Summary: The base Controller class from where all controllers originate
 ||             from.  This provides access to the registry item and provides
 ||             the core render() method.
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
use Bitsand\Logger\Log;
use Bitsand\Controllers\ActionRoute;
use Bitsand\Utilities\Functions;

abstract class Controller {
	protected $template = '';
	protected $view_route = '';
	protected $children = array();
	protected $data = array();
	protected $output = '';

	public function __get($key) {
		return Registry::get($key);
	}

	public function __set($key, $value) {
		// Not right
		die('something not right in setting');
		$this->registry->set($key, $value);
	}

	protected function forward($route, $args = array()) {
		return new ActionRoute($route, $args);
	}

	protected function redirect($url, $status = 302) {
		header('Status: ' . $status);
		header('Location: ' . str_replace(array('&amp;', "\n", "\r"), array('&', '', ''), $url));
	}

	protected function getTemplate($view_route) {
		if ($this->templateExists($view_route)) {
			return $this->config->getVal('app', 'theme') . '/' . $view_route;
		}
		return '';
	}

	/**
	 * Sets the template to the passed option within the app theme.
	 * @param string $view_route
	 * @param string $built_in [Optional] <p>If the route file doesn't exist
	 *                         then use this file from the built in folder.</p>
	 */
	protected function setView($view_route, $built_in = null) {
		$this->view_route = $view_route;
		$template = $this->config->getAppPath() . '/view/' . $this->config->getVal('theme') . '/' . $view_route . '.phtml';
		if (file_exists($template)) {
			$this->template = $template;
		} elseif (!empty($built_in)) {
			$template = $this->config->getBasePath() . '/Bitsand/Builtin/View/' . $built_in . '.phtml';
			if (file_exists($template)) {
				$this->template = $template;
			}
		}
		var_dump($template);die();
	}

	/**
	 * Converts a child route into an Action, initialises it and then returns the output
	 * @param type $child
	 * @param type $args
	 * @return type
	 */
	protected function getChild($child, $args = array()) {
		/*
		 * We take the assumption that all children are controllers/routes, in
		 * theory we could link in other classes if we wished to.
		 */
		$action_details = new ActionRoute($child, $args);
		$class = $action_details->getClass();
		$method = $action_details->getMethod();

		if (method_exists($class, $method)) {
			$controller = new $class();
			call_user_func_array(array($controller, $method), $args);

			if (empty($controller->output)) {
				return $controller->render();
			} else {
				return $controller->output;
			}
		} else {
			throw new \Bitsand\Exceptions\ControllerNotFoundException('Controller method not found: ' . $action_details->getAction() . '->' . $method);
		}
	}

	/**
	 * Processes the template that has been set and catches the response as a
	 * textual string.
	 * @return string
	 */
	protected function render() {
		foreach ($this->children as $child) {
			$this->data[basename($child)] = $this->getChild($child);
		}

		if (empty($this->view_route)) {
			$template = $this->config->getAppPath() . '/view/' . $this->template;
			$view_route = $this->template;
		} else {
			$template = $this->template;
			$view_route = $this->view_route;
		}

		if (file_exists($template)) {
			$this->data['view_route'] = $view_route;

			ob_start();

			// Safe include breaks the inheritance of this controller so the view doesn't have
			Functions\safeinclude($template, $this->data);

			$this->output = ob_get_contents();

			ob_end_clean();

			return $this->output;
		} else {
			throw new \Bitsand\Exceptions\TemplateNotFoundException('Template file not found: ' . $view_route);
		}
	}
}