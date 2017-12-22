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

define('XHR', [], function() {
	return function(url, callback, postData, modifyReq) {
		if (typeof postData == 'undefined') postData = null;
		var req = new XMLHttpRequest,
			method = postData === {} || postData === null ? 'GET' : 'POST';

		req.open(method, url, true);
		if (method === 'POST') {
			/**
			 * @todo encodeURIComponent each value being posted
			 */
			req.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=' + (typeof postData.encoding != 'undefined' ? postData.encoding : 'UTF-8'));
			req.setRequestHeader('Data-Type', 'application/json; charset=' + (typeof postData.encoding != 'undefined' ? postData.encoding : 'UTF-8'));
			if (typeof postData.encoding != 'undefined') delete postData.encoding
		} else {
			req.setRequestHeader('Content-Type', 'application/x-javascript');
		}
		req.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

		req.onreadystatechange = function() {
			if (req.readyState != 4) return;
			if (req.status != 200 && req.status != 304) return;

			if (typeof callback == 'function') {
				callback(req.responseText);
			}
		};

		if (req.readyState == 4) return;
		req.send(typeof postData == 'object' ? serialize(postData) : postData);
	};
});