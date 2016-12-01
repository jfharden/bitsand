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