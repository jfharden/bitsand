define('Booking', ['Class', 'Core'], function(Class) {
	var Booking = Class.extend({
		init: function() {
			var $$types = $$('fieldset legend input[name="type"]');

			for (var i=0; i<$$types.length; i++) {
				$$types[i].addEventListener('click', this.changeBookingType.bind(this));
			}
		},

		changeBookingType: function() {
			var bookingType = $('fieldset legend input:checked').value,
				$$fieldsets = $$('fieldset');

			for (var i=0; i<$$fieldsets.length; i++) {
				var $fieldset = $$fieldsets[i],
					className = $fieldset.getAttribute('class');

				if (className.match('type-' + bookingType)) {
					$fieldset.setAttribute('class', className.replace('hidden', 'visible'));
				} else {
					$fieldset.setAttribute('class', className.replace('visible', 'hidden'));
				}
			}
		}
	});

	return Booking;
});