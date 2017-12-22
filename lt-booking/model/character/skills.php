<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File public/model/character/skills.php
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

namespace LTBooking\Model;

use Bitsand\Controllers\Model;

class CharacterSkills extends Model {
	/**
	 * Holds all of the skills
	 * @var array
	 */
	private $_skills;

	private $_loaded = false;

	/**
	 * Initialise the character skills object.
	 *
	 * Now a large amount of procrastination to explain the reason for some
	 * changes added within V9.
	 *
	 * V8 Bitsand treated character skills very simplistically, nothing more
	 * than a database table containing a unique ID, skill name, short name and
	 * associated cost.  Although this works in principal, it was entirely
	 * dependent upon the skills being added in a particular order to ensure a
	 * two-column layout and grouping.  Because V9 implements a responsive
	 * design for mobile devices, turning two-columns into a single column, we
	 * would immediatly encounter an issue with the way it was handled in V8.
	 * This meant that the V8 set up wouldn't work regardless.
	 *
	 * One other disadvantage of the V8 method is that it was entirely feasible
	 * to generate an "impossible" character - Healing 2 with Power 1 and 3,
	 * Body Dev 2 without Body Dev 1 or more than one Armour use skill.
	 *
	 * To this end, V9 almost completely ignores the skills database table
	 * (which wasn't editable anyway) and instead uses a slightly more complex
	 * and hard coded method.  As this is specific to the lt-booking app, this
	 * isn't actually a problem and means that maintenance is simpler.
	 *
	 * We enforce our rules using clientside JavaScript rather than serverside.
	 * This allows us to be quite flexible and not invalidate old characters,
	 * but also help players configuring their character.
	 *
	 * V9 tries to keep a fairly simple concept in that a SKILL can have one or
	 * many child-SKILLs.  By utilising the same SKILL object, we keep the
	 * whole process clean.
	 *
	 * By default a SKILL is "radio" and matches the 2017 Lorien Trust booking
	 * form with it's "OR" options (this includes triage for Bind Wounds and
	 * Physisan)
	 *
	 * Here's an example looking at Armour.  As we know there are three armour
	 * classes available to a character and you can only select ONE.
	 *
	 * Defence <- SKILL
	 *     Armour <- SKILL
	 *         Light    - 2 pts  <- SKILL
	 *         Medium   - 3 pts  <- SKILL
	 *         Heavy    - 4 pts  <- SKILL
	 *
	 * One known fault is that we do not enforce the limitation on Level 2
	 * magical skills, so you can make the illegal build of Healing 2,
	 * Incantation 2.
	 *
	 * @return type
	 */
	public function __construct() {
		$this->_skills = new \LTBooking\Model\Character\Skills\Group;

		$this->defineCharacterSkills();
	}

	/**
	 * Retrieves all of the currently active skills
	 * @param  boolean $simplified
	 * @return array
	 */
	public function getActiveSkills($simplified = true) {
		return $this->_skills->getActive($simplified);
	}

	/**
	 * Retrieves all of the skills available
	 * @return array
	 */
	public function getAvailableSkills() {
		return $this->_skills->getSkills();
	}

	/**
	 * Performs a comparative update of a users OSPs and returns the number of changes made
	 * @param  int $user_id
	 * @param  array $data
	 * @return int
	 */
	public function updateUser($user_id, $data) {
		$changed = 0;

		$this->loadUser($user_id);

		$current_skills = $this->getActiveSkills(true);

		foreach ($data as $skill) {
			if (!in_array($skill, $current_skills)) {
				// Insert
				$this->db->query("INSERT INTO " . DB_PREFIX . "character_skills (csPlayerID, csSkill) VALUES ('" . (int)$user_id . "', '" . $this->db->escape($skill) . "')");
				$changed += $this->db->countAffected();
			} else {
				$idx = array_search($skill, $current_skills);
				unset($current_skills[$idx]);
			}
		}

		if ($current_skills) {
			foreach ($current_skills as $skill) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "character_skills WHERE csPlayerID = '" . (int)$user_id . "' AND csSkill = '" . $this->db->escape($skill) . "'");
				$changed += $this->db->countAffected();
			}
		}

		// Tidy up the old table
		$this->db->query("DELETE FROM " . DB_PREFIX . "skillstaken WHERE stPlayerID = '" . (int)$user_id . "'"); // Get rid of old ones

		return $changed;
	}



	/**
	 * Populates all of the LT skills.  Added as it's own method to keep this
	 * class a bit tidier
	 */
	private function defineCharacterSkills() {
		// Offensive skills
		$group = $this->_skills->addSkill('offence', 'Offensive skills');
		$group->addSkill('ambi', 'Ambidexterity', 2);
		$group->addSkill('large', 'Large weapon use', 2);
		$group->addSkill('projectile', 'Projectile weapon', 4);
		$group->addSkill('thrown', 'Missile weapon', 1);
		$group->addSkill('shield', 'Shield use', 2);

		// Defensive skills
		$group = $this->_skills->addSkill('defence', 'Defensive skills');
		$group->addSkill('bodydev', 'Body Development')
						->addSkill('bodydev1', '1', 4)
						->addSkill('bodydev2', '2', 8)
						->type('radio');
		$group->addSkill('armour', 'Armour Use')
						->addSkill('light', 'Light', 2)
						->addSkill('medium', 'Medium', 3)
						->addSkill('heavy', 'Heavy', 4)
						->type('radio');

		// Magic skills
		$group = $this->_skills->addSkill('magic', 'Magical skills');
		$group->addSkill('healing', 'Channelling')
						->addSkill('heal1', 'Healing 1', 4)
						->addSkill('heal2', 'Healing 2', 8)
						->type('radio');
		$group->addSkill('incantation', 'Incantation')
						->addSkill('incant1', 'Incantation 1', 4)
						->addSkill('incant2', 'Incantation 2', 8)
						->type('radio');
		$group->addSkill('spellcasting', 'Spellcasting')
						->addSkill('spell1', 'Spellcasting 1', 4)
						->addSkill('spell2', 'Spellcasting 2', 8)
						->type('radio');
		$group->addSkill('ritual', 'Ritual Magic')
						->addSkill('rit1', 'Ritual Magic 1', 2)
						->addSkill('rit2', 'Ritual Magic 2', 4)
						->addSkill('rit3', 'Ritual Magic 3', 6)
						->type('radio');
		$group->addSkill('contribute', 'Contribute to Ritual', 1);
		$group->addSkill('power', 'Power')
						->addSkill('power1', 'Power 1 (+4)', 2)
						->addSkill('power2', 'Power 2 (+8)', 4)
						->addSkill('power3', 'Power 3 (+12)', 6)
						->addSkill('power4', 'Power 4 (+16)', 8)
						->type('radio');
		$group->addSkill('invoke', 'Invocation', 3);

		// Knowledge skills
		$group = $this->_skills->addSkill('knowledge', 'Knowledge skills');
		$group->addSkill('sensemagic', 'Sense Magic', 1);
		$group->addSkill('potion', 'Potion Lore', 3);
		$group->addSkill('poison', 'Poison Lore', 4);
		$group->addSkill('cartography', 'Cartography', 1);
		$group->addSkill('evaluate', 'Evaluate', 1);
		$group->addSkill('spotforgery', 'Recognise Forgery', 1);
		$group->addSkill('triage', 'Triage Skills')
						->addSkill('physician', 'Physician', 2)
						->addSkill('bind', 'Bind Wounds', 1)
						->type('radio');
	}

	/**
	 * Loads the users character skills from the database.  Supports both old
	 * and new tables
	 * @param  int $user_id
	 * @param  boolean $forced
	 */
	public function loadUser($user_id, $forced = false) {
		if ($this->_loaded && !$forced) {
			return;
		}
		$character_skill_query = $this->db->query("
			SELECT
			  stID      AS `skillstaken_id`,
			  stSkillID AS `skill_id`
			FROM " . DB_PREFIX . "skillstaken WHERE stPlayerID = '" . (int)$user_id . "'");

		$character_skills = array();

		foreach ($character_skill_query->rows as $character_skill) {
			$character_skills[$character_skill['skill_id']] = $character_skill['skillstaken_id'];
		}

		if ($character_skills) {
			$this->loadOld($character_skills);
		}

		// Now for the new table
		$character_skill_query = $this->db->query("
			SELECT
			  csSkill
			FROM " . DB_PREFIX . "character_skills WHERE csPlayerID = '" . (int)$user_id . "'");

		$character_skills = array_column($character_skill_query->rows, 'csSkill');
		if ($character_skills) {
			$this->load($character_skills);
		}

		$this->_loaded = true;
	}

	/**
	 * Loads a set of character skills
	 * @param  array $character_skills
	 */
	private function load($character_skills) {
		// Loop through each skill and set the appropriate flag to active
		foreach ($character_skills as $skill) {
			$skill = $this->_skills->get($skill);

			if ($skill) {
				$skill->state(true);
			}
		}
	}

	private function loadOld($character_skills) {
		// Bit nasty but hardcode the old BA shortnames to match the new ones
		// Shortnames should be consistent across installations
		$cross_reference = array(
			'Ambidex'      => 'offence/ambi',
			'Ritual 2'     => 'magic/ritual/rit2',
			'Lg Melee Wpn' => 'offence/large',
			'Ritual 3'     => 'magic/ritual/rit3',
			'Proj Weapon'  => 'offence/projectile',
			'Contribute'   => 'magic/contribute',
			'Shield Use'   => 'offence/shield',
			'Invocation'   => 'magic/invocation',
			'Thrown'       => 'offence/thrown',
			'Power 1'      => 'magic/power/power1',
			'Body Dev 1'   => 'defence/bodydev/bodydev1',
			'Power 2'      => 'magic/power/power2',
			'Body Dev 2'   => 'defence/bodydev/bodydev2',
			'Power 3'      => 'magic/power/power3',
			'Light Armour' => 'defence/armour/light',
			'Power 4'      => 'magic/power/power4',
			'Med Armour'   => 'defence/armour/medium',
			'Potion Lore'  => 'knowledge/potion',
			'Heavy Armour' => 'defence/armour/heavy',
			'Poison Lore'  => 'knowledge/poison',
			'Healing 1'    => 'magic/healing/heal1',
			'Cartography'  => 'knowledge/cartography',
			'Healing 2'    => 'magic/healing/heal2',
			'Sense Magic'  => 'knowledge/sensemagic',
			'Spell 1'      => 'magic/spellcasting/spell1',
			'Evaluate'     => 'knowledge/evaluate',
			'Spell 2'      => 'magic/spellcasting/spell2',
			'Rec Forgery'  => 'knowledge/spotforgery',
			'Incant 1'     => 'magic/incantation/incant1',
			'Physician'    => 'knowledge/triage/physician',
			'Incant 2'     => 'magic/incantation/incant2',
			'Bind Wounds'  => 'knowledge/triage/bind',
			'Ritual 1'     => 'magic/ritual/rit1'
		);

		// Retrieve the short names from the list of skills
		$skill_ids = array_map(function($skill_id){ return "'" . (int)$skill_id . "'"; }, array_keys($character_skills));
		$query = $this->db->query("SELECT skID AS `skill_id`, skShortName AS `short_name` FROM " . DB_PREFIX . "skills WHERE skID IN (" . implode(',', $skill_ids) . ")");
		$old_skills = array_combine(array_column($query->rows, 'skill_id'), array_column($query->rows, 'short_name'));

		// Loop through each skill and set the appropriate flag to active
		foreach ($character_skills as $skill_id => $idx) {
			if (!isset($old_skills[$skill_id])) {
				$this->log->write('Unable to find skill id ' . $skill_id . ' (' . $idx . ')');
				continue;
			}
			$short_name = $old_skills[$skill_id];

			if (!isset($cross_reference[$short_name])) {
				$this->log->write('Unable to cross reference skill ' . $short_name);
				continue;
			}

			// Toggle this skill to be "on"
			$this->_skills->get($cross_reference[$short_name])->state(true);
		}
	}
}