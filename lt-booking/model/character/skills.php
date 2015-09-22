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
	 * isn't actually a problem and means that maintenance is simpeler.
	 *
	 * We enforce our rules using clientside JavaScript rather than serverside.
	 * This allows us to be quite flexible and not invalidate old characters,
	 * but also help players configuring their character.
	 *
	 * V9 introduces the following concepts.
	 *  - GROUP : A group of similar skills: Offense; Defense; Magic; Knowledge
	 *  - SKILL_SET : A skill set (or tree) that belongs to a GROUP.
	 *  - SKILL : A specific skill that belongs to a SKILL_SET.  You can only
	 *            select a single skill from each SKILL_SET
	 *
	 * Here's an example looking at Armour.  As we know there are three armour
	 * classes available to a character and you can only select ONE.
	 *
	 * Defence <- GROUP
	 *     Armour <- SKILL_SET
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
		return;

		$group = $this->addGroup('Offense', 'Offensive skills');
		$group->addSkillSet('Ambi', 'Ambidexterity', 2);   // Passing a points value configures the skill with only one skill
		$group->addSkillSet('Large', 'Large weapon use', 2);
		$group->addSkillSet('Projectile', 'Projectile weapon', 4);
		$group->addSkillSet('Shield', 'Shield use', 2);
		$group->addSkillSet('Thrown', 'Missile weapon', 1);

		$group = $this->addGroup('Defence', 'Defensive skills');
		$group->addSkillSet('BodyDev', 'Body development', array('1' => 4, '2' => 8));
		$group->addSkillSet('Armour', 'Armour Use', array('Light' => 2, 'Medium' => 3, 'Heavy' => 4));

		$group = $this->addGroup('Magic', 'Magical skills');
		$group->addSkillSet('Healing', 'Healing/Channelling', array('1' => 4, '2' => 8));
		$group->addSkillSet('Incantation', 'Incantation', array('1' => 4, '2' => 8));
		$group->addSkillSet('Spellcasting', 'Spellcasting', array('1' => 4, '2' => 8));
		$group->addSkillSet('Ritual', 'Ritual magic', array('1' => 2, '2' => 4, '3' => 6));
		$group->addSkillSet('Contribute', 'Contribute to ritual', 1);
		$group->addSkillSet('Power', 'Power', array('1 (+4)' => 2, '2 (+8)' => 4, '3 (+12)' => 6, '4 (+12)' => 8));
		$group->addSkillSet('Invocation', 'Invocation', 3);

		$group = $this->addGroup('Knowledge', 'Knowledge skills');
		$group->addSkillSet('Potion', 'Potion lore', 3);
		$group->addSkillSet('Poison', 'Poison lore', 4);
		$group->addSkillSet('Cartography', 'Cartography', 1);
		$group->addSkillSet('SenseMagic', 'Sense magic', 1);
		$group->addSkillSet('Evaluate', 'Evaluate', 1);
		$group->addSkillSet('SpotForgery', 'Recognise forgery', 1);
		$group->addSkillSet('Physician', 'Physician', 2);
		$group->addSkillSet('Bind', 'BindWounds', 1);
	}

	private function addGroup($alias, $name) {
		return (object)array();
	}
}