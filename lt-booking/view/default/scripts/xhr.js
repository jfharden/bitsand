/*+----------------------------------------------------------------------------
 || Bitsand - an online booking system for Live Role Play events
 ||
 || File xhr.js
 ||    Summary: Provide AJAX functionality without the need for a framework.
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

function xhr(url, callback, postData) {
	if (typeof postData == undefined) postData = {};
	var req = new XMLHttpRequest,
		method = postData === {} ? 'GET' : 'POST';

	req.open(method, url, true);
	if (method === 'POST') {
		/**
		 * @todo encodeURIComponent each value being posted
		 */
		req.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
	} else {
		req.setRequestHeader('Content-Type', 'application/x-javascript');
	}
	req.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

	req.onreadystatechange = function() {
		if (req.readyState != 4) return;
		if (req.status != 200 && req.status != 304) return;

		callback(req.responseText);
	};

	if (req.readyState == 4) return;
	req.send(postData);
}