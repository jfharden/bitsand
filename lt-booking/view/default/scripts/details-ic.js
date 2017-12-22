define('details-ic', ['Class', 'XHR', 'Core', 'fuse', 'autoComplete', 'polyfill'], function(Class, XHR) {
	var Fuse = require('fuse'),

		form = Class.extend({
		init: function(options) {
			this.options = options;

			// Handle select boxes with an 'other' option
			this.options.groupSelect.addEventListener('change', this.checkOther.bind(this, this.options.groupSelect));
			this.options.raceSelect.addEventListener('change', this.checkOther.bind(this, this.options.raceSelect));
			if (this.options.ancestorSelect != null) {
				this.options.ancestorSelect.addEventListener('change', this.checkOther.bind(this, this.options.ancestorSelect));
			}

			// Activate guild select
			this.options.guildSelect = $('select', this.options.guildBlock);
			this.options.guildSelect.addEventListener('change', this.addGuildPanel.bind(this));

			// Handle the existing guild list
			this.options.guildList = $('.guild-list', this.options.guildBlock);
			if (this.options.guildList != null) {
				var guilds = $$('li', this.options.guildList);
				for (var g=0; guild=guilds[g], g<guilds.length; g++) {
					guild.addEventListener('click', this.removeGuildPanel.bind(this, guild));
				}
			}

			// Total up points spent
			this.characterPoints();
			var characterSkills = $$('input', this.options.characterSkills);
			for (var i=0; skill=characterSkills[i], i<characterSkills.length; i++) {
				skill.addEventListener('change', this.characterPoints.bind(this));
			}
			// Now allow us to untick radios
			var radios = $$('input[type="radio"]', this.options.characterSkills);
			for (var i=0; input=radios[i], i<radios.length; i++) {
				input.addEventListener('click', this.uncheckRadio.bind(this, input));
			}

			// Osp list
			$('.lookup', this.options.ospBlock).setAttribute('disabled', 'disabled');
			this.loadOSPs();

			this.options.ospList = $('.osp-list', this.options.ospBlock);
			var osps = $$('div[data-key]', this.options.ospList);
			for (var o=0; div=osps[o], o<osps.length; o++) {
				$('.name', div).addEventListener('click', this.removeOSP.bind(this, div));
			}

			this.autoComplete = new autoComplete({
				selector: $('.lookup', this.options.ospBlock),
				minChars: 1,
				source: this.lookupOSP.bind(this),
				renderItem: function(item, search) {
					if (item.item != undefined) {
						var score = item.score;
						item = item.item;
					} else {
						var score = 0;
					}
					return '<div class="autocomplete-suggestion" data-val="' + item.short_name + '" data-id="' + item.osp_id + '">' + item.name + '</div>';
				},
				onSelect: this.selectOSP.bind(this)
			})
		},

		checkOther: function(item) {
			var otherBlock = $('.other', item.parentNode),
				selectedIndex = item.options[item.selectedIndex].value;

			if (otherBlock == null) {
				otherBlock = $('.other', item.parentNode.parentNode);
			}

			if (selectedIndex != 'other') {
				otherBlock.classList.add('is-hidden');
				$('input', otherBlock).removeAttribute('required');
			} else {
				otherBlock.classList.remove('is-hidden');
				$('input', otherBlock).setAttribute('required', 'required');
			}
		},

		removeGuildPanel: function(panel) {
			var selectedId = $('input', panel).value,
				guilds = this.options.guildSelect.options;

			for (var g=0; guild=guilds[g], g<guilds.length; g++) {
				if (guild.value == selectedId) {
					guild.removeAttribute('disabled');
					break;
				}
			}

			panel.remove();
		},

		addGuildPanel: function() {
			if (!this.options.guildList) {
				this.addGuildList();
			}

			var guildSelect = this.options.guildSelect,
				selectedId = guildSelect.options[guildSelect.selectedIndex].value,
				selectedText = guildSelect.options[guildSelect.selectedIndex].text,
				newPanel = document.createElement('li');

			newPanel.innerHTML = '<input type="hidden" name="guild[]" value="' + selectedId + '" />' + selectedText;
			newPanel.addEventListener('click', this.removeGuildPanel.bind(this, newPanel));

			this.options.guildList.appendChild(newPanel);

			guildSelect.options[guildSelect.selectedIndex].setAttribute('disabled', 'disabled');
			guildSelect.selectedIndex = 0;
		},

		addGuildList: function() {
			this.options.guildList = document.createElement('ul');
			this.options.guildList.classList.add('guild-list');
			this.options.guildBlock.insertBefore(this.options.guildList, $('div.field', this.options.guildBlock));
		},

		characterPoints: function() {
			var points = 0,
				checked = $$('input', this.options.charcterSkills);

			for(var i=0; input=checked[i], i<checked.length; i++) {
				if (input.checked) {
					points += parseInt(input.getAttribute('data-cost'));
				}
				if (input.getAttribute('type') == 'radio') {
					if (input.checked) {
						input.setAttribute('data-checked', true);
					} else {
						input.removeAttribute('data-checked');
					}
				}
			}

			this.options.characterPoints.innerHTML = points;

			if (points == this.options.maxCharacterPoints) {
				this.options.characterPoints.className = 'points';
			} else if (points > this.options.maxCharacterPoints) {
				this.options.characterPoints.className = 'points error';
			} else {
				this.options.characterPoints.className = 'points warning';
			}
		},

		uncheckRadio: function(input) {
			if (input.checked && input.getAttribute('data-checked') == 'true') {
				input.checked = false;
				this.characterPoints();
			}
		},

		loadOSPs: function() {
			XHR(this.options.ospUrl, function(response) {
				this.allOsps = JSON.parse(response);

				this.fuse = new Fuse(this.allOsps, {
					keys: ['name'],
					include: ['score'],
					threshold: 0.35,
					distance: 80
				});

				this.fuse2 = new Fuse(this.allOsps, {
					keys: ['osp_id'],
					threshold: 0,
					distance: 0
				});

				$('.lookup', this.options.ospBlock).removeAttribute('disabled');
			}.bind(this), {});
		},

		removeOSP: function(div) {
			div.remove();
		},

		lookupOSP: function(term, suggest) {
			suggest(this.fuse.search(term));
		},

		selectOSP: function(evt, choice, el) {
			// In order to maintain the nice looking display, we use a different data variable from data-val
			var osp = this.fuse2.search(el.getAttribute('data-id'))[0],
				div = document.createElement('div'),
				input = document.createElement('input'),
				span = document.createElement('span'),
				html = '',
				hasOther = osp.has_other == '1';

			div.setAttribute('data-key', osp.short_name);
			if (!hasOther) {
				input.setAttribute('type', 'hidden');
				input.setAttribute('name', 'osp[' + osp.osp_id + ']');
				input.value = osp.osp_id;
				div.appendChild(input);
			}
			span.className = 'name';
			span.innerHTML = osp.name;
			div.appendChild(span);

			if (hasOther) {
				div.className = 'has-other';
				input = document.createElement('input');
				input.setAttribute('type', 'text');
				input.setAttribute('name', 'osp[' + osp.osp_id + '][]');
				input.setAttribute('required', 'required');
				div.appendChild(input);
			}

			this.options.ospList.appendChild(div);
			$('.lookup', this.options.ospBlock).value = '';
		}
	});

	return form;
});