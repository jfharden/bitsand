<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File Bitsand/Routing/Router.php
 ||    Summary: The Router is the central point of the routing for Bitsand. It
 ||             is responsible to translating all requests into their
 ||             appropriate controller in both directions.  It is a slightly
 ||             bastardised version of AltoRouter
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

namespace Bitsand\Routing;

use Bitsand\Controllers\ActionRoute;

class Router {
	/**
	 * @var string $base_path Contains the relative path to the root
	 */
	protected $base_path = '';

	/**
	 * @var string $current_route The current route
	 */
	protected $current_route = '';

	/**
	 * @var array $routes Holds all of the routes defined
	 */
	protected $routes = array();

	/**
	 * @var array $named_routes Holds the name (or controller route) for all
	 * defined routes
	 */
	protected $named_routes = array();

	/**
	 * @var array $match_types The various match types for translating routes
	 * into their appropriate variables
	 */
	protected $match_types = array(
		'i'  => '[0-9]++',
		'a'  => '[0-9A-Za-z]++',
		'h'  => '[0-9A-Fa-f]++',
		'*'  => '.+?',
		'**' => '.++',
		''   => '[^/\.]++'
	);


	public function __construct() {
		$this->base_path = str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);
	}

	/**
	 * Returns a resource file if the we're requesting a resource
	 *
	 * @return Resource|boolean
	 */
	public function getResource() {
		if (isset($_GET['_resource_'])) {
			return new \Bitsand\Controllers\Resource(htmlentities($_GET['_resource_'], ENT_QUOTES, 'UTF-8', true));
		}
		return false;
	}

	/**
	 * Returns the base url.  This is the root path of Bitsand relative to the
	 * server:
	 * http://localhost/Bitsand9/ would return /Bitsand9/
	 *
	 * @return string
	 */
	public function getBaseUrl() {
		return $this->base_path;
	}

	/**
	 * Tries to match the current route to the appropriate controller file.
	 *
	 * @param boolean $as_action If true then will return an ActionRoute.
	 * @return ActionRoute|string|boolean
	 */
	public function matchCurrentRoute($as_action = false) {
		$matched = $this->match();
		if (!$matched) {
			$matched = ActionRoute::controllerExists($this->getCurrentRoute());
			if ($matched) {
				/*
				 * Map this so we don't have to remember it
				 * @todo Log all routes that haven't been explicitly defined
				 */
				$this->map($this->getCurrentMethod(), $this->getCurrentRoute(), $matched['controller']);
			}
		}

		if ($matched && $as_action) {
			return new ActionRoute($matched['controller']);
		} elseif ($matched) {
			return $matched['controller'];
		} else {
			return false;
		}
	}

	/**
	 * Retrieves the current route from either the _route_ or the raw url
	 * @return string
	 */
	private function getCurrentRoute() {
		if (empty($this->current_route)) {
			if (isset($_GET['_route_'])) {
				// Use the passed route if we can, it's a lot less processing
				$request_url = htmlentities($_GET['_route_']);
			} else {
				// Set Request Url if it isn't passed as parameter
				$request_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';

				// Strip base path from request url to make it relative
				$request_url = substr($request_url, strlen($this->base_path));

				// Strip query string (?a=b) from Request Url
				if (($strpos = strpos($request_url, '?')) !== false) {
					$request_url = substr($request_url, 0, $strpos);
				}
			}
			$this->current_route = $request_url;
		}

		return $this->current_route;
	}

	/**
	 * Returns the current request method (GET, POST, etc)
	 *
	 * @return string
	 */
	private function getCurrentMethod() {
		return isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
	}

	/*
	 * AltoRouter source
	 * All of the items below are taken from the AltoRouter source, however a
	 * few Bitand changes have been made.  Firstly all references to target
	 * have been changed into controller, within Bitsand all URLs will point to
	 * a Controller class within the application folder.  Additionally any
	 * routes defined will automatically be stored as a "named route" if they
	 * handle any GET methods.
	 *
	 * Additionally the code has been tidied up to follow the standard defined.
	 */

	/**
	 * Add multiple routes at once from array in the following format:
	 *
	 *   $routes = array(
	 *      array($method, $route, $controller, $name)
	 *   );
	 *
	 * @param array $routes
	 * @return void
	 * @author Koen Punt
	 */
	public function addRoutes($routes){
		if (!is_array($routes) && !$routes instanceof Traversable) {
			throw new \Exception('Routes should be an array or an instance of Traversable');
		}
		foreach ($routes as $route) {
			call_user_func_array(array($this, 'map'), $route);
		}
	}

	/**
	 * Add named match types. It uses array_merge so keys can be overwritten.
	 *
	 * @param array $match_types The key is the name and the value is the regex.
	 */
	public function addMatchTypes($match_types) {
		$this->match_types = array_merge($this->match_types, $match_types);
	}
	/**
	 * Map a route to a controller
	 *
	 * @param string $method One of 5 HTTP Methods, or a pipe-separated list of multiple HTTP Methods (GET|POST|PATCH|PUT|DELETE)
	 * @param string $route The route regex, custom regex must start with an @. You can use multiple pre-set regex filters, like [i:id]
	 * @param mixed $controller The controller where this route should point to. Can be anything.
	 * @param string $name Optional name of this route. Supply if you want to reverse route this url in your application.
	 */
	public function map($method, $route, $controller, $name = null) {
		// If no name passed but this is a GET request, store as a named route
		if ($name === null && strpos($method, 'GET') !== false) {
			$name = $controller;
		}
		if ($route != '/') {
			$route = ltrim($route, '/');
		}
		$this->routes[] = array($method, $route, $controller, $name);
		if ($name) {
			if (isset($this->named_routes[$name])) {
				throw new \Exception("Can not redeclare route '{$name}'");
			} else {
				$this->named_routes[$name] = $route;
			}
		}
		return;
	}
	/**
	 * Reversed routing
	 *
	 * Generate the URL for a named route. Replace regexes with supplied parameters
	 *
	 * @param string $route_name The name of the route.
	 * @param array @params Associative array of parameters to replace placeholders with.
	 * @return string The URL of the route with named parameters in place.
	 */
	public function link($route_name, array $params = null, $scheme = \Bitsand\NONSSL, $absolute = false) {
		if (isset($this->named_routes[$route_name])) {
			$route = $this->named_routes[$route_name];
		} else {
			// If a named route hasn't been specified, then take the decision that it's a direct reference to a controller
			$route = $route_name . (substr($route_name, -1) !== '/' ? '/' : '');
		}

		// Prepend base path to route url again
		$url = $this->base_path . $route;
		if (preg_match_all('`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`', $route, $matches, PREG_SET_ORDER)) {
			foreach ($matches as $match) {
				list($block, $pre, $type, $param, $optional) = $match;
				if ($pre) {
					$block = substr($block, 1);
				}
				if (isset($params[$param])) {
					$url = str_replace($block, $params[$param], $url);
				} elseif ($optional) {
					$url = str_replace($pre . $block, '', $url);
				}
			}
		}

		// Prefix http or https
		if ($scheme === \Bitsand\SSL && ($absolute || $_SERVER['SERVER_PORT'] == '80')) {
			$url = HTTPS_SERVER . $url;
		} elseif ($scheme === \Bitsand\NONSSL && ($absolute || $_SERVER['SERVER_PORT'] != '80')) {
			$url = HTTP_SERVER . $url;
		}

		return $url;
	}
	/**
	 * Match a given Request Url against stored routes
	 * @param string $request_url
	 * @param string $request_method
	 * @return array|boolean Array with route information on success, false on failure (no match).
	 */
	public function match($request_url = null, $request_method = null) {
		$params = array();
		$match = false;

		if ($request_url === null) {
			$request_url = $this->getCurrentRoute();
		}

		if (empty($request_url)) {
			// If we don't have a request url then it's going to be the root
			$request_url = '/';
		} else {
			// Remove any trailing slashes
			$request_url = rtrim($request_url, '/');
		}

		// Set Request Method if it isn't passed as a parameter
		if ($request_method === null) {
			$request_method = $this->getCurrentMethod();
		}

		// Force request_order to be GP
		// http://www.mail-archive.com/internals@lists.php.net/msg33119.html
		$_REQUEST = array_merge($_GET, $_POST);
		foreach ($this->routes as $handler) {
			list($method, $_route, $controller, $name) = $handler;
			$methods = explode('|', $method);
			$method_match = false;

			// Check if request method matches. If not, abandon early. (CHEAP)
			foreach ($methods as $method) {
				if (strcasecmp($request_method, $method) === 0) {
					$method_match = true;
					break;
				}
			}
			// Method did not match, continue to next route.
			if (!$method_match) continue;

			// Check for a wildcard (matches all)
			if ($_route === '*') {
				$match = true;
			} elseif (isset($_route[0]) && $_route[0] === '@') {
				$pattern = '`' . substr($_route, 1) . '`u';
				$match = preg_match($pattern, $request_url, $params);
			} else {
				$route = null;
				$regex = false;
				$j = 0;
				$n = isset($_route[0]) ? $_route[0] : null;
				$i = 0;
				// Find the longest non-regex substring and match it against the URI
				while (true) {
					if (!isset($_route[$i])) {
						break;
					} elseif (false === $regex) {
						$c = $n;
						$regex = $c === '[' || $c === '(' || $c === '.';
						if (false === $regex && false !== isset($_route[$i+1])) {
							$n = $_route[$i + 1];
							$regex = $n === '?' || $n === '+' || $n === '*' || $n === '{';
						}
						if (false === $regex && $c !== '/' && (!isset($request_url[$j]) || $c !== $request_url[$j])) {
							continue 2;
						}
						$j++;
					}
					$route .= $_route[$i++];
				}
				$regex = $this->compileRoute($route);
				$match = preg_match($regex, $request_url, $params);
			}
			if (($match == true || $match > 0)) {
				if ($params) {
					foreach ($params as $key => $value) {
						if (is_numeric($key)) unset($params[$key]);
					}
				}
				return array(
					'controller' => $controller,
					'params' => $params,
					'name' => $name
				);
			}
		}
		return false;
	}
	/**
	 * Compile the regex for a given route (EXPENSIVE)
	 * @param string $route
	 * @return string
	 */
	private function compileRoute($route) {
		if (preg_match_all('`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`', $route, $matches, PREG_SET_ORDER)) {
			$match_types = $this->match_types;
			foreach ($matches as $match) {
				list($block, $pre, $type, $param, $optional) = $match;
				if (isset($match_types[$type])) {
					$type = $match_types[$type];
				}
				if ($pre === '.') {
					$pre = '\.';
				}
				//Older versions of PCRE require the 'P' in (?P<named>)
				$pattern = '(?:'
						. ($pre !== '' ? $pre : null)
						. '('
						. ($param !== '' ? "?P<$param>" : null)
						. $type
						. '))'
						. ($optional !== '' ? '?' : null);
				$route = str_replace($block, $pattern, $route);
			}
		}
		return "`^$route$`u";
	}
}