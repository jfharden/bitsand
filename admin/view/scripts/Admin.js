define('Admin', ['Class', 'Notify', 'Core', 'polyfill'], function(Class, Notify) {
	var Admin = Class.extend({
		init: function() {
			//
		},
		messages: function(messages) {
			if (messages.error) {
				Notify.error(messages.error);
			}
			if (messages.success) {
				Notify.success(messages.success);
			}
			if (messages.warning) {
				Notify.warning(messages.warning);
			}
		}
	});

	return new Admin();
});