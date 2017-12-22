define('Class', [], function() {
	var initializing = false,
		fnTest = /xyz/.test(function(){xyz;}) ? /\b_super\b/ : /.*/;
	// Create an empty function as the base "Class"
	this.Class = function() {};

	Class.extend = function(prop) {
		var _super = this.prototype;

		// Instantiate the base class, but don't fire the init method
		initializing = true;
		var prototype = new this;
		initializing = false;

		// Copy the passed properties onto the new prototype
		for (var name in prop) {
			// Check to see if we're overwriting an existing function
			prototype[name] = typeof prop[name] == 'function' && typeof _super[name] == 'function' && fnTest.test(prop[name]) ?
			(function (name, fn) {
				return function() {
					var tmp = this._super;

					// Add a new _super() method that is the same but on the super-class
					this._super = _super[name];

					var ret = fn.apply(this, arguments);
					this._super = tmp;

					return ret;
				};
			})(name, prop[name]) :
			prop[name];
		}

		// Dummy class constructor
		function Class() {
			// All construction done on the init method
			if (!initializing && this.init)
				this.init.apply(this, arguments);
		}

		// Populate the constructed prototype
		Class.prototype = prototype;

		// Enforce the constructor
		Class.prototype.constructor = Class;

		// And make it extendable
		Class.extend = arguments.callee;

		return Class;
	};

	return Class;
});

/**
 * Binds an object to a method.
 * @param {type} scope
 * @param {type} method
 * @returns {Boolean}
 */
var bind = function(scope, method) {
	if (typeof method == 'string')
		method = scope[method];

	return function() {
		method.apply(scope, arguments);
	};
};

var sprintf = (function(string) {
	for (var i = 1; i < arguments.length; i++) string = string.replace(/%s/, arguments[i]);
	return string;
});

var __nativeST__ = window.setTimeout, __nativeSI__ = window.setInterval;

window.setTimeout = function(vCallback, nDelay /*, argumentToPass1, argumentToPass2 */) {
	var oThis = this, aArgs = Array.prototype.slice.call(arguments, 2);
	return __nativeST__(vCallback instanceof Function ? function() {
		vCallback.apply(oThis, aArgs);
	} : vCallback, nDelay);
}

window.setInterval = function(vCallback, nDelay /*, argumentToPass1, argumentToPass2 */) {
	var oThis = this, aArgs = Array.prototype.slice.call(arguments, 2);
	return __nativeSI__(vCallback instanceof Function ? function() {
		vCallback.apply(oThis, aArgs);
	} : vCallback, nDelay);
}