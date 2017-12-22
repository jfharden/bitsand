/*
 * Various EMCA 5+ polyfills for backward compatibility
 */
var serialize;

// Credit to Douglas Crockford for this bind method
if (!('bind' in Function.prototype)) {
	Function.prototype.bind = function(oThis) {
		if (typeof this !== 'function') {
			throw new TypeError ('Function.prototype.bind - what is being bound is not callable');
		}

		var aArgs = Array.prototype.slice.call(arguments, 1),
			fToBind = this,
			fNOP = function() {},
			fBound = function() {
				return fToBind.apply(this instanceof fNOP && oThis ? this : oThis, aArgs.concat(Array.prototype.slice.call(arguments)));
			};

		fNOP.prototype = this.prototype;
		fBound.prototype = new fNOP();

		return fBound;
	};
}

(function() {
	if (typeof window.CustomEvent === 'function') return false;

	function CustomEvent(event, params) {
		params = params || {bubbles: false, cancelable: false, detail: undefined};
		var evt = document.createEvent('CustomEvent');
		evt.initCustomEvent(event, params.bubbles, params.cancelable, params.detail);
		return evt;
	}

	CustomEvent.prototype = window.Event.prototype;

	window.CustomEvent = CustomEvent;
})();

(function() {
	if (typeof document.getScroll === 'function') return false;

	document.getScroll = function() {
		if (window.pageYOffset != undefined) {
			return {left: window.pageXOffset, top: window.pageYOffset};
		} else {
			var sx, sr, d = document, r = d.documentElement, b = d.body;
			sx = r.scrollLeft || b.scrollLeft || 0;
			sy = r.scrollTop || b.scrollTop || 0;
			return {left: sx, top: sy};
		}
	}
})();

// https://github.com/inexorabletash/polyfill/blob/master/es5.js
if (!Object.defineProperty || !(function () { try { Object.defineProperty({}, 'x', {}); return true; } catch (e) { return false; } } ())) {
	var orig = Object.defineProperty;
	Object.defineProperty = function (o, prop, desc) {
		// In IE8 try built-in implementation for defining properties on DOM prototypes
		if (orig) { try { return orig(o, prop, desc); } catch (e) {} };

		if (o !== Object(o)) { throw TypeError('Object.defineProperty called on non-object'); }
		if (Object.prototype.__defineGetter__ && ('get' in desc)) {
			Object.prototype.__defineGetter__.call(0, prop, desc.get);
		}
		if (Object.prototype.__defineSetter__ && ('set' in desc)) {
			Object.prototype.__defineSetter__.call(o, prop, desc.set);
		}
		if ('value' in desc) {
			o[prop] = desc.value;
		}
		return 0;
	}
}

// https://github.com/Financial-Times/polyfill-service/blob/master/polyfills/Element/prototype/matches/polyfill.js
if (!('matches' in Element.prototype)) {
	Element.prototype.matches = Element.prototype.webkitMatchesSelector || Element.prototype.oMatchesSelector || Element.prototype.msMatchesSelector || Element.prototype.mozMatchesSelector || function matches(selector) {
		var element = this;
		var elements = (element.document || element.ownerDocument).querySelectorAll(selector);
		var index = 0;

		while (elements[index] && elements[index] !== element) {
			++index;
		}

		return !!elements[index];
	};
}

// https://github.com/Financial-Times/polyfill-service/blob/master/polyfills/Element/prototype/closest/polyfill.js
if (!('closest' in Element.prototype)) {
	Element.prototype.closest = function closest(selector) {
		var node = this;

		while (node) {
			if (node.matches(selector)) return node;
			else node = node.parentElement;
		}

		return null;
	};
}

if (!('insert' in Element.prototype)) {
	Element.prototype.insert = function(HTMLElement) {
		this.insertAdjacentHTML('beforeend', HTMLElement)
	}
}

if (!('insertAfter' in Element.prototype)) {
	Element.prototype.insertAfter = function(HTMLElement) {
		this.insertAdjacentHTML('afterEnd', HTMLElement)
	}
}

// Event listener bits from Mozilla: https://developer.mozilla.org/en-US/docs/Web/API/EventTarget/addEventListener
if (!('preventDefault' in Event.prototype)) {
	Event.prototype.preventDefault = function() {
		this.returnValue = false;
	};
}
if (!('stopPropagation' in Event.prototype)) {
	Event.prototype.stopPropagation = function() {
		this.cancelBubble = true;
	};
}
if (!('addEventListener' in Element.prototype)) {
	var pfEventListeners = [],

		pfAddEventListener = function(type, listener /*, useCapture (will be ignored) */) {
		var self = this,
			wrapper = function(e) {
				e.target = e.srcElement;
				e.currentTarget = self;
				if (typeof listener.handleEvent != 'undefined') {
					listener.handleEvent(e);
				} else {
					listener.call(self, e);
				}
			};
		if (type == 'DOMContentLoaded') {
			/*var wrapper2 = function(e) {
				if (document.readyState == 'complete') {
					wrapper(e);
				}
			};

			document.attachEvent('onreadystatechange', wrapper2);
			pfEventListeners.push({
				object: this,
				type: type,
				listener: listener,
				wrapper: wrapper2
			});

			if (document.readyState == 'complete') {
				//var e = new CustomEvent(type);
				//e.srcElement = window;
				//wrapper2(e);
			}*/
		} else {
			this.attachEvent('on' + type, wrapper);
			pfEventListeners.push({
				object: this,
				type: type,
				listener: listener,
				wrapper: wrapper
			});
		}
	};

	var pfRemoveEventListener = function(type, listener /*, useCapture (will be ignored) */) {
		var counter = 0;
		while (counter < pfEventListeners.length) {
			var pfEventListener = pfEventListeners[counter];
			if (pfEventListener.object == this && pfEventListener.type == type && pfEventListener.listener == listener) {
				if (type == 'DOMContentLoaded') {
					this.detachEvent('onreadystatechange', pfEventListener.wrapper);
				} else {
					this.detachEvent('on' + type, pfEventListener.wrapper);
				}

				pfEventListeners.splice(counter, 1);
				break;
			}
			++counter;
		}
	};

	Element.prototype.addEventListener = pfAddEventListener;
	Element.prototype.removeEventListener = pfRemoveEventListener;

	if (HTMLDocument && !('addEventListener' in HTMLDocument.prototype)) {
		HTMLDocument.prototype.addEventListener = pfAddEventListener;
		HTMLDocument.prototype.removeEventListener = pfRemoveEventListener;
	}
	if (Window && !('addEventListener' in Window.prototype)) {
		Window.prototype.addEventListener = pfAddEventListener;
		Window.prototype.removeEventListener = pfRemoveEventListener;
	}
}

//https://developer.mozilla.org/en-US/docs/Web/API/ChildNode/remove
if (!('remove' in Element.prototype)) {
	Element.prototype.remove = function() {
		if (this.parentNode) {
			this.parentNode.removeChild(this);
		}
	}
}

if (!('unique' in Array.prototype)) {
	Array.prototype.unique = function() {
		var prims = {"boolean":{}, "number":{}, "string":{}}, objs = [];

		return this.reduce(function(a,b) {
			if (a.indexOf(b) < 0) a.push(b);
			return a;
		}, []);
	}
}

if (!('forEach' in NodeList.prototype)) {
	NodeList.prototype.forEach = Array.prototype.forEach;
}

serialize = function(obj, prefix) {
	var str = [];
	for(var p in obj) {
		if (obj.hasOwnProperty(p)) {
			var k = prefix ? prefix + "[" + p + "]" : p, v = obj[p];
			str.push(typeof v == "object" ? serialize(v, k) : encodeURIComponent(k) + "=" + encodeURIComponent(v));
		}
	}
	return str.join("&");
}

if (!window.console) {
	window.console = {
		log: function() {},
		error: function() {}
	}
}

// http://ryanmorr.com/detecting-css-style-support/
(function(win){
	'use strict';

	var el = win.document.createElement('div'),
		camelRe = /-([a-z][0-9])/ig,
		support,
		camel;

	win.isStyleSupported = function(prop, value) {
		value = arguments.length === 2 ? value : 'inherit';

		if ('CSS' in win && 'supports' in win.CSS) {
			return win.CSS.supports(prop, value);
		} else if ('supportsCSS' in win) {
			return win.supportsCSS(prop, value);
		}

		camel = prop.replace(camelRe, function(all, letter) {
			return (letter + '').toUpperCase();
		});

		support = (camel in el.style);

		el.style.cssText = prop + ':' + value;

		return support && (el.style[camel] != '');
	}
})(this);

define('pollyfill', function() {});