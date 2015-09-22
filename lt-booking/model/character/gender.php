<?php
/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File public/model/character/gender.php
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

class CharacterGender extends Model {
	private $_genders = array(
		'Male',
		'Female');

	/**
	 * Returns all of the genders available to book as
	 *
	 * @return array
	 * @todo This should pull genders from a database table/option rather than
	 * being statically assigned.
	 */
	public function getAll() {
		$genders = $this->_genders;

		sort($genders);

		return $genders;
	}

}