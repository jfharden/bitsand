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

class Group {
	/**
	 * Adds a child option onto this skill
	 * @param string $alias
	 * @param string $name
	 * @param integer [optional] $cost
	 */
	public function addSkill($alias, $name, $cost = null) {
		$this->{$alias} = new Skill($alias, $name, $cost);
		return $this->{$alias};
	}

	/**
	 * Lookups up a particular skill and returns a reference to it
	 * @param  string $lookup
	 * @return boolean
	 */
	public function get($lookup) {
		$trail = $this;

		foreach (explode('/', $lookup) as $path) {
			if (!isset($trail->{$path})) {
				return false;
			}
			$trail = $trail->{$path};
		}

		return $trail;
	}

	/**
	 * Recursivly retrieves all of the skills that are active.
	 * @param  boolean $simplified If true then returns the alias paths only
	 * @return array
	 */
	public function getActive($simplified = true) {
		$active = array();
		foreach (get_object_vars($this) as $alias => $skill) {
			foreach ($skill->getActive() as $skill) {
				if ($simplified) {
					$active[] = $skill->parent . '/' . $skill->alias;
				} else {
					$active[$alias][] = $skill;
				}
			}
		}

		return $active;
	}

	/**
	 * Returns an array containing each grouping skill and references to all of
	 * its sub-skills
	 * @return array
	 */
	public function getSkills() {
		$skills = array();
		foreach (get_object_vars($this) as $alias => $skill) {
			if (is_object($skill)) {
				$skills[$alias] = $skill;
			}
		}
		return $skills;
	}
}