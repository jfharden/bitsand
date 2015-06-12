/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File datepicker.js
 ||    Summary: This script provides a simple date picker mechanism.  If the
 ||             browser has a native type="date" element then we will just use
 ||             that as it's actually more efficient.
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

(function() {
	if (!canI()) {
		/*
		 * Enumerate all forms within the content for inputs that should be a
		 * date.
		 */
		var forms = document.getElementById('content').getElementsByTagName('form');
		for (var i = 0; i <= forms.length; i++) {
			var form = forms[i],
				elements = form.getElementsByTagName('input');
			for (ei = 0; i < elements.length; i++) {
				var element = elements[i],
					type = element.hasAttribute('type') ? element.getAttribute('type') : null

				if (type == 'date') {
					element.setAttribute('placeholder', 'dd/mm/yyyy');
					element.onclick = showDatePicker;
				}
			}
		}
	}

	/**
	 * Checks to see if the browser supports the html5 date input field.
	 * @return {boolean}
	 */
	function canI() {
		var temp = document.createElement('input');
		temp.setAttribute('type', 'date');
		return temp.type == 'date';
	}

	function showDatePicker() {
		// this = the current element
		var date = parseDate(this.value);
		if (isNaN(date)) date = new Date();
		console.log(date)
	}


	function parseDate(text) {
		if (text == '') return new Date('NotADate');
		var a = text.split('-');
		if (a.length != 3) return new Date(text);
		var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
			month = months.indexOf(a[1]);
		return new Date(a[2], month, a[0], 0,0,0,0);
	}
})();

