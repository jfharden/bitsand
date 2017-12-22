<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File public/model/character/skills/group.php
 ||     Author: Pete Allison
 ||  Copyright: (C) 2006 - 2017 The Bitsand Project
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

namespace LTBooking\Model\Character\Skills;

class Skill implements \Iterator, \Countable {
	public $alias;
	public $name;
	public $cost;
	public $parent;
	public $type = 'checkbox';
	public $index = 0;

	private $state = false;

	private $keys = array();

	public function __construct($alias, $name, $cost = null, $parent = null) {
		$this->alias = $alias;
		$this->name = $name;

		if ($cost) {
			$this->cost = (int)$cost;
		}

		if ($parent) {
			$this->parent = ($parent->parent ? $parent->parent . '/' : '') . $parent->alias;
		}

		return $this;
	}

	/**
	 * Adds a child option onto this skill
	 * @param string $alias
	 * @param string $name
	 * @param integer [optional] $cost
	 */
	public function addSkill($alias, $name, $cost = null) {
		$this->{$alias} = new Skill($alias, $name, $cost, $this);
		$this->keys = array();
		if (is_null($cost)) {
			return $this->{$alias};
		} else {
			return $this;
		}
	}

	/**
	 * Gets or sets the state of this skill
	 * @param  boolean $set
	 * @return boolean
	 */
	public function state($set = null) {
		if (!is_null($set)) {
			$this->state = !!$set;
		}

		return $this->state;
	}

	/**
	 * Sets the grouping type to either radio or checkbox.
	 * @param  string $type
	 */
	public function type($type) {
		$this->type = $type;
	}

	/**
	 * Recursivly retrieves all of the skills that are active.
	 * @return array
	 */
	public function getActive() {
		$active = array();
		foreach (get_object_vars($this) as $key => $skill) {
			if (is_object($skill)) {
				if ($skill->state()) {
					$active[] = $skill;
				}
				$active = array_merge($active, $skill->getActive());
			}
		}

		return $active;
	}

	/**
	 * Recursive method that retrieves all the children in a flattend format
	 * @return array
	 */
	public function getChildren() {
		$children = array();

		foreach ($this as $skill) {
			$children[$skill->alias] = array(
				'key'   => $skill->parent . '/' . $skill->alias,
				'value' => $skill->alias,
				'name'  => $skill->name,
				'type'  => $skill->type,
				'cost'  => $skill->cost
			);

			if (count($skill)) {
				$children[$skill->alias]['children'] = $skill->getChildren();
			}
		}

		return $children;
	}

	// Iterator methods
	public function current() {;
		return $this->{$this->key()};
	}
	public function next() {
		$this->index++;
	}
	public function key() {
		if (!$this->keys) {
			foreach (get_object_vars($this) as $key => $skill) {
				if (is_object($skill)) {
					$this->keys[] = $key;
				}
			}
		}
		if (isset($this->keys[$this->index])) {
			return $this->keys[$this->index];
		} else {
			return;
		}
	}
	public function valid() {
		return isset($this->{$this->key()});
	}
	public function rewind() {
		$this->index = 0;
	}

	public function count() {
		$this->key();
		return count($this->keys);
	}
}