define('details-ooc', ['Class', 'XHR', 'Core', 'polyfill'], function(Class, XHR) {
	var form = Class.extend({
		init: function(options) {
			this.options = options;

			if (!this.options.marshalSelector) this.options.marshalSelector = this.options.marshalBlock.querySelector('select');
			if (!this.options.marshalNumberInput) this.options.marshalNumberInput = this.options.marshalNumber.querySelector('input');
			if (!this.options.marshalNumberLabel) this.options.marshalNumberLabel = this.options.marshalNumber.querySelector('label');
			if (!this.options.newPlayerInput) this.options.newPlayerInput = this.options.playerBlock.querySelector('input[name="new_player"]');
			if (!this.options.playerNumber) this.options.playerNumber = this.options.playerBlock.querySelector('input[name="player_number"]');
			if (!this.options.playerNumberField) this.options.playerNumberField = this.options.playerNumber.parentNode;
			if (!this.options.addressLine1) this.options.addressLine1 = $('input[name="address_1"]', this.options.addressBlock);
			if (!this.options.addressLine2) this.options.addressLine2 = $('input[name="address_2"]', this.options.addressBlock);
			if (!this.options.addressLine3) this.options.addressLine3 = $('input[name="address_3"]', this.options.addressBlock);
			if (!this.options.addressLine4) this.options.addressLine4 = $('input[name="address_4"]', this.options.addressBlock);

			this.options.medicalButton.addEventListener('click', this.toggleMedical.bind(this));
			this.options.dietSelector.addEventListener('change', this.checkDiet.bind(this));
			this.options.marshalSelector.addEventListener('change', this.checkMarshal.bind(this));
			this.options.newPlayerInput.addEventListener('click', this.newPlayer.bind(this));

			if (this.options.postcodeUrl) {
				this.buildPostcode();
			}
		},

		toggleMedical: function() {
			visible = this.options.medicalInfo.getAttribute('class');

			this.options.medicalInfo.setAttribute('class', visible == 'visible' ? 'hidden' : 'visible');
		},

		checkDiet: function() {
			if (this.options.dietSelector.options[this.options.dietSelector.selectedIndex].value.substr(0, 5).toLowerCase() == 'other') {
				this.options.medicalInfo.setAttribute('class', 'visible');
			}
		},

		checkMarshal: function() {
			var marshalType = this.options.marshalSelector.options[this.options.marshalSelector.selectedIndex].value;
			if (marshalType.toLowerCase() == 'no') {
				this.options.marshalNumber.setAttribute('class', 'field hidden');
				this.options.marshalNumberInput.removeAttribute('required');
				this.options.marshalNumberInput.value = '';
			} else {
				this.options.marshalNumberLabel.innerHTML = marshalType + ' Number';
				this.options.marshalNumber.setAttribute('class', 'field visible');
				this.options.marshalNumberInput.setAttribute('placeholder', marshalType + ' Number');
				this.options.marshalNumberInput.setAttribute('required', 'required');
				this.options.marshalNumberInput.value = ''
			}
		},

		newPlayer: function() {
			if (this.options.newPlayerInput.checked) {
				this.options.playerNumber.removeAttribute('required');
				this.options.playerNumberField.setAttribute('class', 'field hidden');
			} else {
				this.options.playerNumber.setAttribute('required', 'required');
				this.options.playerNumberField.setAttribute('class', 'field visible');
			}
		},

		buildPostcode: function() {
			this.options.postcodeButton.addEventListener('click', this.lookupPostcode.bind(this));
			this.options.postcodeInput.addEventListener('keyup', this.validatePostcode.bind(this));

			this.validatePostcode();
		},

		lookupPostcode: function() {
			var postcode = this.options.postcodeInput.value.trim().toUpperCase();

			if (postcode) {
				XHR(this.options.postcodeUrl + postcode, function(response) {
					response = JSON.parse(response);
					if (response.addresses === undefined) {
						this.options.addressLine3.value = response.city ? response.city : '';
						this.options.addressLine4.value = response.county ? response.county: '';
						this.options.postcodeInput.value = response.postcode;
						if (this.options.addressLine2.value == this.options.addressLine3.value) {
							this.options.addressLine2.value = '';
						}
						this.options.addressLine1.focus();
						this.options.addressLine1.select()
					}
				}.bind(this));
			}

			return false;
		},

		validatePostcode: function() {
			if (this.options.postcodeInput.value.trim() != '') {
				this.options.postcodeButton.removeAttribute('disabled');
			} else {
				this.options.postcodeButton.setAttribute('disabled', 'disabled');
			}
		}
	});

	return form;
});