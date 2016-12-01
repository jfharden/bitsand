var isArray, isObject, isScalar, merge, mergeArray, mergeObject, _type,
	__slice = [].slice;

_type = Object.prototype.toString;

isScalar = function(variable) {
	var _ref;
	return ((_ref = _type.call(variable)) !== '[object Array]' && _ref !== '[object Object]') || variable === null;
};

isObject = function(variable) {
	return variable !== null && _type.call(variable) === '[object Object]';
};

isArray = function(variable) {
	return _type.call(variable) === '[object Array]';
};

/**
 * Deep merge two objects/arrays - items in the right will take priority over the left
 *
 * @param object left
 * @param object right
 * @return object
 */
merge = function(left, right) {
	var leftType, rightType;
	if (isScalar(left) || isScalar(right)) {
		throw new Error('Can not merge scalar objects.');
	}
	leftType = _type.call(left);
	rightType = _type.call(right);
	if (leftType !== rightType) {
		throw new Error('Can not merge ' + leftType + ' with ' + rightType + '.');
	}
	switch (leftType) {
		case '[object Array]':
			return mergeArray(left, right);
		case '[object Object]':
			// We need to clone the left object, else this will change it
			var newLeft = clone(left);
			return mergeObject(newLeft, right);
		default:
			throw new Error('Can not merge ' + leftType + ' objects.');
	}
};

mergeArray = function(left, right) {
	var add, i, leftValue, rightValue, value, _i, _j, _len, _len1;
	add = [];
	for (i = _i = 0, _len = right.length; _i < _len; i = ++_i) {
		rightValue = right[i];
		leftValue = left[i];
		if ((isObject(leftValue) && isObject(rightValue)) || (isArray(leftValue) && isArray(rightValue))) {
			left[i] = merge(leftValue, rightValue);
		} else if (isObject(rightValue)) {
			add.push(merge({}, rightValue));
		} else if (isArray(rightValue)) {
			add.push(merge([], rightValue));
		} else {
			add.push(rightValue);
		}
	}
	for (_j = 0, _len1 = add.length; _j < _len1; _j++) {
		value = add[_j];
		left.push(value);
	}
	return left;
};

mergeObject = function(left, right) {
	var key, mergeWith, value;
	for (key in right) {
		value = right[key];
		if (right.hasOwnProperty(key) && (key !== '__proto__')) {
			if (isScalar(value)) {
				if (!left.hasOwnProperty(key)) {
					left[key] = value;
				}
			} else {
				if (left.hasOwnProperty(key)) {
					left[key] = merge(left[key], value);
				} else {
					mergeWith = isObject(value) ? {} : [];
					left[key] = merge(mergeWith, value);
				}
			}
		}
	}
	return left;
};

// Shallow clone
function clone(obj) {
	if (obj === null || typeof(obj) !== 'object' || 'isActiveClone' in obj) return obj;

	if (obj instanceof Date)
		var temp = new obj.constructor();
	else
		var temp = obj.constructor();

	for (var key in obj) {
		if (Object.prototype.hasOwnProperty.call(obj, key)) {
			obj['isActiveClone'] = null;
			temp[key] = clone(obj[key]);
			delete obj['isActiveClone'];
		}
	}

	return temp;
}

define('merge', function() {});