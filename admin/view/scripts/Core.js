define('Core', function() {
	var Function = typeof Window == 'function' ? this.Function : this.Object;

	Function.prototype.overloadSetter = function(usePlural){
		var self = this;
		return function(a, b){
			if (a == null) return this;
			if (usePlural || typeof a != 'string'){
				for (var k in a) self.call(this, k, a[k]);
				/*<ltIE8>*/
				forEachObjectEnumberableKey(a, self, this);
				/*</ltIE8>*/
			} else {
				self.call(this, a, b);
			}
			return this;
		};
	};

	Function.prototype.overloadGetter = function(usePlural){
		var self = this;
		return function(a){
			var args, result;
			if (typeof a != 'string') args = a;
			else if (arguments.length > 1) args = arguments;
			else if (usePlural) args = [a];
			if (args){
				result = {};
				for (var i = 0; i < args.length; i++) result[args[i]] = self.call(this, args[i]);
			} else {
				result = self.call(this, a);
			}
			return result;
		};
	};

	Function.prototype.implement = function(key, value) {
		this.prototype[key] = value;
	}.overloadSetter();

	/**
	 * Queries the document (singular match)
	 * @param {string} el Element to query
	 * @param {Element} scope Scope to query (optional)
	 * @return {Element}
	 */
	if (window.$ == undefined) Window.implement('$', function(el, scope) {
		if (scope == undefined) {
			return document.querySelector(el);
		} else {
			return scope.querySelector(el);
		}
	});

	/**
	 * Queries the document (multiple matches)
	 * @param {string} el Element to query
	 * @param {Element} scope Scope to query (optional)
	 * @return {Array}
	 */
	if (window.$$ == undefined) Window.implement('$$', function(el, scope) {
		if (scope == undefined) {
			return document.querySelectorAll(el);
		} else {
			return scope.querySelectorAll(el);
		}
	});
});