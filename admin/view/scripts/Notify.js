define('Notify', ['Class', 'polyfill'], function(Class) {
	var Notify = Class.extend({
		messages: [],

		init: function() {
			this.$element = document.createElement('div');
			this.$element.classList.add('closed');
			this.$element.classList.add('bits-notify')

			this.$message = document.createElement('div');
			this.$message.classList.add('bits-notify__message');
			this.$element.appendChild(this.$message);

			this.$icon = document.createElement('div');
			this.$icon.classList.add('bits-notify__icon');
			this.$element.appendChild(this.$icon);

			document.body.appendChild(this.$element);

			this.$element.addEventListener('mousedown', function(evt) {
				if (evt.target) {
					this.hide();
				}
			}.bind(this));
		},

		notify: function(type, message, timeout) {
			if (window.Logger != undefined) {
				Logger[type](message, function(){}, timeout);
			} else {
				this.$element.setAttribute('data-type', type);

				if ((typeof message == 'array' || typeof message == 'object') && message.length == 1) {
					message = message[0];
				} else if (typeof message == 'array' || typeof message == 'object') {
					this.messages = message;
					if (timeout == undefined) timeout = 2000;
					this.nextMessage(timeout);
					return;
				}
				this.$message.innerHTML = message;
				this.show();
				if (typeof timeout == 'function') {
					this.callback = timeout;
				} else if (timeout) {
					this.timeout = setTimeout(this.hide.bind(this), timeout);
				}
			}
		},

		nextMessage: function(timeout) {
			message = this.messages.shift();
			this.$message.innerHTML = message;
			this.show();
			if (this.messages.length > 0) {
				this.timeout = setTimeout(this.nextMessage.bind(this, timeout), timeout);
			} else {
				this.timeout = setTimeout(this.hide.bind(this), timeout);
			}
		},

		error: function(message, timeout) { this.notify('error', message, timeout); },
		warning: function(message, timeout) { this.notify('warning', message, timeout); },
		info: function(message, timeout) { this.notify('info', message, timeout); },
		success: function(message, timeout) { this.notify('success', message, timeout); },

		show: function() {
			this.$element.classList.remove('closed');
			var $$anchors = $('a', this.$element);
			if ($$anchors !== null) {
				for (var $$a=0;a<$$anchors.length;a++) {
					$$anchors[a].addEventListener('mousedown', function(event) {
						event.stopPropagation();
					}, true);
				}
			}
		},

		hide: function() {
			if (this.callback) {
				this.callback();
			}
			this.$element.classList.add('closed');
		}
	});

	return new Notify();
});