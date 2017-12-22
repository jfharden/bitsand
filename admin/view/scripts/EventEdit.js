define('EventEdit', ['Class', 'Notify', 'pikaday', 'ckeditor', 'Core'], function(Class, Notify, Pikaday) {
	var EventEdit = Class.extend({
		/** @type {RegExp} Regular Expression to retrieve the ID within a item */
		regex: /\[((new){0,1}[0-9]{1,})\]/,
		newRowCount: 1,
		pickers: {},

		init: function(options) {
			/*var ckOptions = {
				toolbar: [ 'headings', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'undo', 'redo', 'paste' ],
				heading: {
					options: [
						{ modelElement: 'paragraph', title: 'paragraph' , class: 'ck-heading_paragraph' },
						{ modelElement: 'heading2', viewElement: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
						{ modelElement: 'heading3', viewElement: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' }
					]
				}
			}
			ClassicEditor.create($('#description'), ckOptions).then(function(editor){console.log(Array.from(editor.ui.componentFactory.names()))});
			ClassicEditor.create($('#details'), ckOptions);*/

			//BalloonEditor.create($('#slug'));

			var ckOptions = {
				toolbar: [
					{ items: [ 'Bold', 'Italic', 'Strike', 'Underline' ] },
					{ items: [ 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', ] },
					{ items: [ 'Indent', 'Outdent', '-', ] },
					{ items: [ 'Subscript', 'Superscript', '-', ] },
					{ items: [ 'Undo', 'Redo' ] },
					{ items: [ 'NumberedList', 'BulletedList', '-', ] },
					{ items: [ 'Link', 'HorizontalRule', 'Image', 'Table', '-' ] },
					{ items: [ 'Styles', '-' ] },
					{ items: [ 'Source' ] },
					{ items: [ 'RemoveFormat'] }
				],
				bodyClass: 'feature-block',
				contentsCss: options.editorCss
			};

			CKEDITOR.replace('description', ckOptions);
			CKEDITOR.replace('details', ckOptions);

			$('#slug').addEventListener('keyup', this.slugSize);

			new Pikaday({ field: $('#event_date'), format: 'D MMM YYYY' });
			new Pikaday({ field: $('#booking_open'), format: 'D MMM YYYY' });
			new Pikaday({ field: $('#booking_close'), format: 'D MMM YYYY' });

			var $$items = $$('.items_list tbody tr');
			for (var i=0; $row=$$items[i],i<$$items.length; i++) {
				if ((idx = this.handleItems($row)).match(/^new/)) {
					this.newRowCount = parseInt(idx.replace(/^new/, '')) + 1;
				}
			}

			$('#add-item').addEventListener('click', this.addRow.bind(this));
		},

		handleItems: function($row) {
			var idx = $('input', $row).getAttribute('name').match(this.regex)[1];

			this.pickers[idx] = {
				from: new Pikaday({ field: $('input[name*="from"]', $row), format: 'D MMM YYYY' }),
				to: new Pikaday({ field: $('input[name*="to"]', $row), format: 'D MMM YYYY' })
			}
			var $delete = $('.delete', $row)
			$delete.addEventListener('click', this.removeRow.bind(this, $delete));

			var $cost = $('input[name*="cost"]', $row);
			$cost.addEventListener('blur', function() {
				if (isNaN(this.value)) {
					Notify.warning('Please enter a valid cost', 1500);
					return false;
				}
				var cost = parseFloat(this.value);
				this.value = cost.toFixed(2);
			})

			return idx;
		},

		slugSize: function() {
			this.setAttribute('size', this.value.length);
		},

		addRow: function() {
			var $row = $('.items_list tbody tr').cloneNode(true),
				$$inputs = $$('input', $row);
			for (var i=0; $input=$$inputs[i], i<$$inputs.length; i++) {
				if ($input.getAttribute('type') == 'text') {
					if (!$input.getAttribute('name').match(/cost/)) {
						$input.value = '';
					} else {
						$input.value = '0.00';
					}
				} else if ($input.getAttribute('type') == 'checkbox') {
					$input.checked = false;
				}

				$input.setAttribute('name', $input.getAttribute('name').replace(this.regex, '[new' + this.newRowCount + ']'));
			}

			this.newRowCount++;

			$('.items_list tbody').appendChild($row);

			this.handleItems($row);
		},

		removeRow: function($delete) {
			var $row = $delete.parentNode.parentNode,
				idx = $('input', $row).getAttribute('name').match(this.regex)[1];

			this.pickers[idx].from.destroy();
			this.pickers[idx].to.destroy();

			$row.remove();
		}
	});

	return EventEdit;
});