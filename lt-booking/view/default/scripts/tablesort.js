define('TableSort', ['Class', 'Core', 'polyfill'], function(Class) {
	// Based off https://www.w3schools.com/howto/howto_js_sort_table.asp
 	var TableSort = Class.extend({
		init: function($table, sortCol) {
			this.$table = $table;
			this.$head = $('thead', this.$table);
			this.defaultSortColumn = sortCol - 1;

			var $$columns = $$('td', this.$thead);
			for(var col=0; col<$$columns.length; col++) {
				$$columns[col].setAttribute('data-col', col);
				if (col == sortCol - 1) {
					$$columns[col].setAttribute('class', 'asc');
					this.currentColumn = col;
				}
				$$columns[col].addEventListener('click', this.sort.bind(this, col))
			}
			this.$$headColumns = $$columns;

		},

		sort: function(column) {
			var switching = true,
				direction = 'asc',
				x, y, shouldSwitch = false,
				$tbody = $('tbody', this.$table);

			if (!$tbody) $tbody = this.$table;

			if (this.currentColumn == column) {
				direction = this.$$headColumns[column].getAttribute('class') == 'asc' ? 'desc' : 'asc';
			} else {
				this.$$headColumns[this.currentColumn].removeAttribute('class');
			}

			this.$$headColumns[column].setAttribute('class', direction);
			this.currentColumn = column;

			while (switching) {
				switching = false;
				rows = $tbody.getElementsByTagName('TR');
				for (var i=0; i<rows.length - 1; i++) {
					shouldSwitch = false;
					x = rows[i].getElementsByTagName('TD')[column].innerHTML.toLowerCase();
					y = rows[i + 1].getElementsByTagName('TD')[column].innerHTML.toLowerCase();

					// If identical then use the default column to sort by
					if (x === y) {
						x = rows[i].getElementsByTagName('TD')[this.defaultSortColumn].innerHTML.toLowerCase();
						y = rows[i + 1].getElementsByTagName('TD')[this.defaultSortColumn].innerHTML.toLowerCase();
					}


					// Ensure we put empty items at the bottom
					if (x == '') x = 'zzzzz';
					if (y == '') y = 'zzzzz';

					if ((direction == 'asc' && x > y) || (direction == 'desc' && x < y)) {
						shouldSwitch = true;
						break;
					}
				}
				if (shouldSwitch) {
					rows[i].parentNode.insertBefore(rows[i+1], rows[i]);
					switching = true;
				}
			}
		}
	});

	return TableSort;
});