define('Admin', ['Class', 'polyfill'], function(Class) {
	var Admin = Class.extend({
		init: function() {
			console.log('initialised');
		}
	});

	new Admin();
});