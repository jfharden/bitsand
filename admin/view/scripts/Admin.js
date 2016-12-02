define('Admin', ['Class', 'Core', 'polyfill'], function(Class) {
	var Admin = Class.extend({
		init: function() {
			var $$hrefs = $$('.mdl-menu__item[href]');
			if ($$hrefs.length) {
				for (var i=0; i<$$hrefs.length; i++) {
					$$hrefs[i].addEventListener('click', function() {
						location.href = this.getAttribute('href');
					});
				}
			}
		}
	});

	new Admin();
});