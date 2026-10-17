/* KaosH&F – grid dashboard */
(function () {
	'use strict';

	var __ = wp.i18n.__;

	var C = window.KhfConfig;
	var app = document.getElementById('khl-app');
	var items = [];
	var query = '';
	var filter = 'all';

	/* ---------- Utility ---------- */

	function api(path, opts) {
		opts = opts || {};
		var headers = { 'Content-Type': 'application/json', 'X-WP-Nonce': C.nonce };
		var method = opts.method || 'GET';
		// Some hosts block DELETE/PUT: POST with an override header is used, supported by the WordPress REST API.
		if (method === 'DELETE' || method === 'PUT') {
			headers['X-HTTP-Method-Override'] = method;
			method = 'POST';
		}
		return fetch(C.restUrl + path, {
			method: method,
			headers: headers,
			body: opts.body ? JSON.stringify(opts.body) : undefined,
			credentials: 'same-origin'
		}).then(function (r) {
			return r.json().then(function (json) {
				if (!r.ok) { throw new Error(json && json.message ? json.message : (__( 'Error', 'kaos-header-footer' ) + ' ') + r.status); }
				return json;
			});
		});
	}

	function h(tag, attrs) {
		var el = document.createElement(tag);
		var kids = Array.prototype.slice.call(arguments, 2);
		Object.keys(attrs || {}).forEach(function (k) {
			var v = attrs[k];
			if (v == null || v === false) { return; }
			if (k === 'class') { el.className = v; }
			else if (k.indexOf('on') === 0 && typeof v === 'function') { el.addEventListener(k.slice(2).toLowerCase(), v); }
			else if (k === 'value') { el.value = v; }
			else { el.setAttribute(k, v === true ? '' : v); }
		});
		kids.forEach(function add(c) {
			if (c == null || c === false) { return; }
			if (Array.isArray(c)) { c.forEach(add); return; }
			el.appendChild(c.nodeType ? c : document.createTextNode(String(c)));
		});
		return el;
	}

	function icon(name) {
		return h('span', { class: 'dashicons dashicons-' + name, 'aria-hidden': 'true' });
	}

	function editUrl(id) {
		return C.adminUrl + '&edit=' + id;
	}

	function toast(msg, type) {
		var t = h('div', { class: 'khl-toast' + (type ? ' is-' + type : '') }, msg);
		document.body.appendChild(t);
		setTimeout(function () { t.classList.add('is-out'); }, 1800);
		setTimeout(function () { t.remove(); }, 2300);
	}

	/* Modal window (replaces the browser prompt/confirm). */
	function modal(title, body, actions) {
		return new Promise(function (resolve) {
			var close = function (val) {
				document.removeEventListener('keydown', onKey);
				back.remove();
				resolve(val);
			};
			var onKey = function (e) { if (e.key === 'Escape') { close(null); } };
			var foot = h('div', { class: 'khl-modal-foot' }, actions.map(function (a) {
				return h('button', {
					type: a.submit ? 'submit' : 'button',
					class: 'khl-btn' + (a.primary ? ' is-primary' : '') + (a.danger ? ' is-danger' : ''),
					onclick: a.submit ? null : function () { close(a.value); }
				}, a.label);
			}));
			var form = h('form', { class: 'khl-modal', role: 'dialog', 'aria-modal': 'true', 'aria-label': title },
				h('div', { class: 'khl-modal-head' }, h('h2', null, title),
					h('button', { type: 'button', class: 'khl-icon-btn', 'aria-label': __( 'Close', 'kaos-header-footer' ), onclick: function () { close(null); } }, icon('no-alt'))),
				h('div', { class: 'khl-modal-body' }, body), foot);
			form.addEventListener('submit', function (e) {
				e.preventDefault();
				var a = actions.filter(function (x) { return x.submit; })[0];
				close(a ? a.value : true);
			});
			var back = h('div', { class: 'khl-modal-back', onclick: function (e) { if (e.target === back) { close(null); } } }, form);
			document.body.appendChild(back);
			document.addEventListener('keydown', onKey);
			var first = form.querySelector('input, button.is-primary');
			if (first) { first.focus(); }
		});
	}

	function confirmModal(title, text, label) {
		return modal(title, h('p', null, text), [
			{ label: __( 'Cancel', 'kaos-header-footer' ), value: false },
			{ label: label || __( 'Confirm', 'kaos-header-footer' ), value: true, primary: true, danger: true, submit: true }
		]);
	}

	/* ---------- Grid ---------- */

	function render() {
		app.innerHTML = '';

		var search = h('input', {
			type: 'search', class: 'khl-search', placeholder: __( 'Search headers and footers…', 'kaos-header-footer' ), value: query, 'aria-label': __( 'Search', 'kaos-header-footer' ),
			oninput: function (e) { query = e.target.value; renderGrid(grid); }
		});
		var head = h('div', { class: 'khl-head' },
			h('div', { class: 'khl-brand' }, h('img', { src: C.logoUrl, alt: 'KaosH&F' }), h('small', { class: 'khl-version' }, 'v' + C.version)),
			search,
			h('div', { class: 'khl-head-actions' },
				h('button', { type: 'button', class: 'khl-btn', onclick: openSettings }, icon('admin-generic'), (' ' + __( 'Settings', 'kaos-header-footer' ))),
				h('button', { type: 'button', class: 'khl-btn', onclick: function () { document.getElementById('khl-import-file').click(); } }, icon('upload'), (' ' + __( 'Import', 'kaos-header-footer' ))),
				h('button', { type: 'button', class: 'khl-btn is-primary', onclick: createTemplate }, icon('plus-alt2'), (' ' + __( 'New', 'kaos-header-footer' ))))
		);

		var chips = h('div', { class: 'khl-filters' }, [['all', __( 'All', 'kaos-header-footer' )], ['header', __( 'Header', 'kaos-header-footer' )], ['footer', __( 'Footer', 'kaos-header-footer' )]].map(function (f) {
			return h('button', {
				type: 'button', class: 'khl-chip' + (filter === f[0] ? ' is-active' : ''),
				onclick: function () { filter = f[0]; render(); }
			}, f[1]);
		}));

		var grid = h('div', { class: 'khl-grid' });
		app.appendChild(head);
		if (!C.elementor) {
			app.appendChild(h('div', { class: 'khl-notice' }, __( 'Elementor is not active: you can use the presets, but you cannot design headers and footers with Elementor or use the "Kaos Sticky" panel.', 'kaos-header-footer' )));
		}
		app.appendChild(chips);
		app.appendChild(grid);
		app.appendChild(h('p', { class: 'khl-help' }, __( 'A template is active when it is switched on and its display rules match the page. If several templates match, the one with the highest priority wins. For effects on a single Elementor element: Advanced tab → Kaos Sticky.', 'kaos-header-footer' )));
		renderGrid(grid);
	}

	function renderGrid(grid) {
		grid.innerHTML = '';
		grid.appendChild(h('button', { type: 'button', class: 'khl-card khl-new', onclick: createTemplate },
			h('span', { class: 'khl-new-plus' }, icon('plus-alt2')), h('strong', null, __( 'New template', 'kaos-header-footer' )), h('small', null, __( 'Header or footer, from scratch', 'kaos-header-footer' ))));

		var q = query.trim().toLowerCase();
		var list = items.filter(function (t) {
			return (filter === 'all' || t.type === filter) && (!q || t.title.toLowerCase().indexOf(q) > -1 || String(t.id) === q);
		});
		list.forEach(function (t) { grid.appendChild(card(t)); });
		if (q && !list.length) {
			grid.appendChild(h('p', { class: 'khl-empty' }, __( 'No results for "', 'kaos-header-footer' ) + query + '".'));
		}
		observeThumbs(grid);
	}

	function card(t) {
		var act = function (name, label, fn, cls) {
			return h('button', { type: 'button', class: 'khl-icon-btn' + (cls ? ' ' + cls : ''), title: label, 'aria-label': label, onclick: function (e) { e.preventDefault(); e.stopPropagation(); fn(t); } }, icon(name));
		};
		var go = function () { window.location.href = editUrl(t.id); };
		var thumb = h('div', {
			class: 'khl-thumb', role: 'link', tabindex: '0', 'aria-label': (__( 'Edit', 'kaos-header-footer' ) + ' ') + t.title,
			onclick: go, onkeydown: function (e) { if (e.key === 'Enter') { go(); } }
		},
			h('span', { class: 'khl-badges' },
				h('span', { class: 'khl-badge' }, t.type === 'footer' ? __( 'Footer', 'kaos-header-footer' ) : __( 'Header', 'kaos-header-footer' )),
				t.source === 'builder' ? h('span', { class: 'khl-badge is-accent' }, 'Elementor') : null),
			h('span', { class: 'khl-hover' },
				h('span', { class: 'khl-edit' }, icon('edit'), __( 'Edit', 'kaos-header-footer' )),
				h('span', { class: 'khl-actions' },
					act('visibility', __( 'Preview on the site', 'kaos-header-footer' ), function (x) { window.open(x.liveUrl, '_blank'); }),
					act('admin-page', __( 'Duplicate', 'kaos-header-footer' ), duplicateTemplate),
					act('download', __( 'Export JSON', 'kaos-header-footer' ), exportTemplate),
					act('trash', __( 'Delete', 'kaos-header-footer' ), deleteTemplate, 'is-danger'))));

		// The preview is the real page, loaded only when the card enters the screen.
		thumb.insertBefore(h('div', { class: 'khl-thumb-empty', 'data-src': t.thumbUrl }, icon('format-image'), __( 'Preview…', 'kaos-header-footer' )), thumb.firstChild);

		var date = new Date(t.modified).toLocaleString(document.documentElement.lang || undefined, { dateStyle: 'short', timeStyle: 'short' });
		var on = t.status === 'publish';
		return h('div', { class: 'khl-card' + (on ? '' : ' is-draft'), 'data-id': t.id },
			thumb,
			h('div', { class: 'khl-meta' },
				h('a', { class: 'khl-name', href: editUrl(t.id), title: t.title }, t.title),
				h('div', { class: 'khl-sub', title: t.summary }, t.summary + ' · ' + date),
				h('div', { class: 'khl-row' },
					h('button', {
						type: 'button', class: 'khl-status' + (on ? ' is-on' : ''), title: __( 'Turn on or off', 'kaos-header-footer' ),
						onclick: function () { toggleStatus(t); }
					}, on ? __( 'Active', 'kaos-header-footer' ) : __( 'Inactive', 'kaos-header-footer' )),
					h('span', { class: 'khl-id' }, 'ID ' + t.id))));
	}

	/* Previews: full 1280×720 page scaled down to the width of the card. */
	function observeThumbs(root) {
		var targets = root.querySelectorAll('.khl-thumb-empty[data-src]');
		var load = function (box) {
			var thumb = box.parentNode;
			var frame = h('iframe', { src: box.getAttribute('data-src'), title: __( 'Preview', 'kaos-header-footer' ), tabindex: '-1', loading: 'lazy', 'aria-hidden': 'true' });
			frame.addEventListener('load', function () {
				var win = frame.contentWindow;
				try { win.scrollTo(0, win.document.documentElement.scrollHeight); } catch (e) { /* same origin: it should not fail */ }
			});
			thumb.appendChild(frame);
			fit(thumb, frame);
			box.remove();
		};
		if (!('IntersectionObserver' in window)) { Array.prototype.forEach.call(targets, load); return; }
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (e) { if (e.isIntersecting) { io.unobserve(e.target); load(e.target); } });
		}, { rootMargin: '200px' });
		Array.prototype.forEach.call(targets, function (t) { io.observe(t); });
	}

	function fit(thumb, frame) {
		frame.style.transform = 'scale(' + (thumb.clientWidth / 1280) + ')';
	}
	window.addEventListener('resize', function () {
		Array.prototype.forEach.call(app.querySelectorAll('.khl-thumb'), function (t) {
			var f = t.querySelector('iframe');
			if (f) { fit(t, f); }
		});
	});

	function load() {
		return api('/templates').then(function (list) {
			items = list;
			render();
		})['catch'](function (e) {
			app.innerHTML = '';
			app.appendChild(h('div', { class: 'khl-error' }, (__( 'Could not load the templates:', 'kaos-header-footer' ) + ' ') + e.message));
		});
	}

	/* ---------- Azioni ---------- */

	function createTemplate() {
		var name = h('input', { type: 'text', class: 'khl-input', value: '', placeholder: __( 'E.g. Main header', 'kaos-header-footer' ), required: true });
		var type = 'header';
		var source = 'preset';

		var seg = function (opts, get, set) {
			var box = h('div', { class: 'khl-seg' });
			var paint = function () {
				box.innerHTML = '';
				opts.forEach(function (o) {
					box.appendChild(h('button', {
						type: 'button', class: o[0] === get() ? 'is-active' : '', disabled: o[3] ? true : null,
						onclick: function () { set(o[0]); paint(); }
					}, h('strong', null, o[1]), h('small', null, o[2])));
				});
			};
			paint();
			return box;
		};

		var body = h('div', null,
			h('label', { class: 'khl-label' }, __( 'Name', 'kaos-header-footer' ), name),
			h('div', { class: 'khl-label khl-mt' }, __( 'Type', 'kaos-header-footer' )),
			seg([['header', __( 'Header', 'kaos-header-footer' ), __( 'Header at the top of the page', 'kaos-header-footer' )], ['footer', __( 'Footer', 'kaos-header-footer' ), __( 'Page footer', 'kaos-header-footer' )]], function () { return type; }, function (v) { type = v; }),
			h('div', { class: 'khl-label khl-mt' }, __( 'How you build it', 'kaos-header-footer' )),
			seg([
				['preset', __( 'Emotional preset', 'kaos-header-footer' ), __( 'Start from a ready-made design and customize it', 'kaos-header-footer' )],
				['builder', __( 'With Elementor', 'kaos-header-footer' ), C.elementor ? __( 'You design it freely in Elementor', 'kaos-header-footer' ) : __( 'Elementor is not active', 'kaos-header-footer' ), !C.elementor]
			], function () { return source; }, function (v) { source = v; }));

		modal(__( 'New template', 'kaos-header-footer' ), body, [{ label: __( 'Cancel', 'kaos-header-footer' ), value: null }, { label: __( 'Create and open the editor', 'kaos-header-footer' ), value: true, primary: true, submit: true }]).then(function (ok) {
			if (!ok) { return; }
			api('/templates', { method: 'POST', body: { title: name.value, type: type, source: source } }).then(function (t) {
				window.location.href = editUrl(t.id);
			})['catch'](function (err) { toast(err.message, 'error'); });
		});
	}

	function toggleStatus(t) {
		var next = t.status === 'publish' ? 'draft' : 'publish';
		api('/templates/' + t.id, { method: 'POST', body: { status: next } }).then(function () {
			t.status = next;
			toast(next === 'publish' ? __( 'Template turned on', 'kaos-header-footer' ) : __( 'Template turned off', 'kaos-header-footer' ), 'ok');
			render();
		})['catch'](function (err) { toast(err.message, 'error'); });
	}

	function duplicateTemplate(t) {
		api('/templates/' + t.id + '/duplicate', { method: 'POST' }).then(function () {
			toast(__( 'Template duplicated', 'kaos-header-footer' ), 'ok');
			load();
		})['catch'](function (err) { toast(err.message, 'error'); });
	}

	function exportTemplate(t) {
		api('/templates/' + t.id).then(function (full) {
			var blob = new Blob([JSON.stringify({ khf: C.version, title: full.title, opts: full.opts }, null, 2)], { type: 'application/json' });
			var a = h('a', { href: URL.createObjectURL(blob), download: 'kaos-hf-' + full.id + '.json' });
			document.body.appendChild(a);
			a.click();
			setTimeout(function () { URL.revokeObjectURL(a.href); a.remove(); }, 100);
			if (full.source === 'builder') { toast(__( 'Content designed with Elementor is not included: export it from Elementor.', 'kaos-header-footer' ), 'ok'); }
		})['catch'](function (err) { toast(err.message, 'error'); });
	}

	function deleteTemplate(t) {
		confirmModal(__( 'Delete the template?', 'kaos-header-footer' ), '"' + t.title + __( '" will be permanently deleted.', 'kaos-header-footer' ), __( 'Delete', 'kaos-header-footer' )).then(function (ok) {
			if (!ok) { return; }
			api('/templates/' + t.id, { method: 'DELETE' }).then(function () {
				toast(__( 'Template deleted', 'kaos-header-footer' ), 'ok');
				load();
			})['catch'](function (err) { toast(err.message, 'error'); });
		});
	}

	/* ---------- Impostazioni globali ---------- */

	function openSettings() {
		api('/settings').then(function (s) {
			var f = {};
			var text = function (key, label, ph, area) {
				f[key] = h(area ? 'textarea' : 'input', { type: 'text', rows: area ? 2 : null, value: s[key], placeholder: ph || '' });
				return h('label', { class: 'khl-label khl-mt' }, label, f[key]);
			};
			var num = function (key, label, min, max) {
				f[key] = h('input', { type: 'number', min: min, max: max, value: s[key] });
				return h('label', { class: 'khl-label', style: 'margin:0' }, label, f[key]);
			};
			var check = function (key, label, note) {
				f[key] = h('input', { type: 'checkbox', checked: s[key] ? true : null });
				return h('label', { class: 'khl-radio' }, f[key], h('span', null, h('strong', null, label), note ? h('small', null, note) : null));
			};

			var fontMode = function () {
				f.font_mode = h('select', null, [['local', __( 'Hosted on this site (recommended)', 'kaos-header-footer' )], ['bunny', __( 'Bunny Fonts (European CDN, no tracking)', 'kaos-header-footer' )], ['google', __( 'Google Fonts (direct link)', 'kaos-header-footer' )]].map(function (o) { return h('option', { value: o[0], selected: s.font_mode === o[0] ? true : null }, o[1]); }));
				return h('div', null, h('label', { class: 'khl-label', style: 'margin:0' }, __( 'How fonts are loaded', 'kaos-header-footer' ), f.font_mode), h('p', { class: 'khl-note' }, __( 'With "Hosted on this site" the font files are downloaded once to your server when you save a template, and visitors never contact a third party.', 'kaos-header-footer' )));
			};

			var body = h('div', null,
				h('div', { class: 'khl-label' }, __( 'Theme header and footer', 'kaos-header-footer' )),
				check('hide_theme_header', __( 'Hide the theme header', 'kaos-header-footer' ), __( 'When a Kaos header is active on the page.', 'kaos-header-footer' )),
				text('header_selectors', __( 'CSS selectors of the theme header', 'kaos-header-footer' ), '#masthead, .site-header', true),
				check('hide_theme_footer', __( 'Hide the theme footer', 'kaos-header-footer' ), __( 'When a Kaos footer is active on the page.', 'kaos-header-footer' )),
				text('footer_selectors', __( 'CSS selectors of the theme footer', 'kaos-header-footer' ), '#colophon, .site-footer', true),
				h('p', { class: 'khl-note' }, __( 'Leave the selectors empty for automatic detection (Hello Elementor, Astra, GeneratePress, Kadence, OceanWP, Blocksy, block themes, Elementor Theme Builder).', 'kaos-header-footer' )),
				text('content_selector', __( 'Page container (footer reveal)', 'kaos-header-footer' ), '#page'),
				h('div', { class: 'khl-label khl-mt' }, __( 'Breakpoints and layers', 'kaos-header-footer' )),
				h('div', { class: 'khl-grid2' }, num('bp_tablet', __( 'Tablet up to (px)', 'kaos-header-footer' ), 480, 1600), num('bp_mobile', __( 'Mobile up to (px)', 'kaos-header-footer' ), 320, 1200), num('breakpoint', __( 'Mobile menu of the presets (px)', 'kaos-header-footer' ), 320, 1600), num('z_index', 'z-index', 1, 2147483000)),
				h('div', { class: 'khl-label khl-mt' }, __( 'Fonts', 'kaos-header-footer' )),
				fontMode(),
				h('div', { class: 'khl-label khl-mt' }, __( 'Navigation', 'kaos-header-footer' )),
				check('anchor', __( 'Offset sticky headers in anchors', 'kaos-header-footer' ), __( 'Links to #section stop below the header.', 'kaos-header-footer' )),
				num('anchor_extra', __( 'Extra anchor margin (px)', 'kaos-header-footer' ), 0, 400),
				check('smooth', __( 'Smooth scrolling', 'kaos-header-footer' ), __( 'Respects the system "reduce motion" setting.', 'kaos-header-footer' )),
				check('btt', __( '"Back to top" button', 'kaos-header-footer' ), __( 'Appears after scrolling the page.', 'kaos-header-footer' )),
				h('div', { class: 'khl-grid2' }, num('btt_after', __( 'Appears after (px)', 'kaos-header-footer' ), 0, 5000), num('btt_size', __( 'Size (px)', 'kaos-header-footer' ), 28, 96), num('btt_off', __( 'Distance from the edge (px)', 'kaos-header-footer' ), 0, 120), num('btt_r', __( 'Rounding (%)', 'kaos-header-footer' ), 0, 50)));

			f.btt_pos = h('select', null, [['right', __( 'Right', 'kaos-header-footer' )], ['left', __( 'Left', 'kaos-header-footer' )]].map(function (o) { return h('option', { value: o[0], selected: s.btt_pos === o[0] ? true : null }, o[1]); }));
			f.btt_bg = h('input', { type: 'color', value: s.btt_bg });
			f.btt_fg = h('input', { type: 'color', value: s.btt_fg });
			body.appendChild(h('div', { class: 'khl-grid2 khl-mt' },
				h('label', { class: 'khl-label', style: 'margin:0' }, __( 'Position', 'kaos-header-footer' ), f.btt_pos),
				h('label', { class: 'khl-label', style: 'margin:0' }, __( 'Background', 'kaos-header-footer' ), f.btt_bg),
				h('label', { class: 'khl-label', style: 'margin:0' }, __( 'Icon', 'kaos-header-footer' ), f.btt_fg)));

			return modal(__( 'Settings', 'kaos-header-footer' ), body, [{ label: __( 'Cancel', 'kaos-header-footer' ), value: null }, { label: __( 'Save', 'kaos-header-footer' ), value: true, primary: true, submit: true }]).then(function (ok) {
				if (!ok) { return; }
				var out = {};
				Object.keys(f).forEach(function (k) {
					var el = f[k];
					out[k] = el.type === 'checkbox' ? (el.checked ? 1 : 0) : el.value;
				});
				return api('/settings', { method: 'POST', body: out }).then(function () { toast(__( 'Settings saved', 'kaos-header-footer' ), 'ok'); });
			});
		})['catch'](function (err) { toast(err.message, 'error'); });
	}

	document.getElementById('khl-import-file').addEventListener('change', function (e) {
		var file = e.target.files[0];
		if (!file) { return; }
		var reader = new FileReader();
		reader.onload = function () {
			var json;
			try { json = JSON.parse(reader.result); } catch (err) { toast(__( 'The file is not a valid JSON.', 'kaos-header-footer' ), 'error'); return; }
			api('/import', { method: 'POST', body: json }).then(function () {
				toast(__( 'Template imported', 'kaos-header-footer' ), 'ok');
				load();
			})['catch'](function (err) { toast(err.message, 'error'); });
		};
		reader.readAsText(file);
		e.target.value = '';
	});

	load();
})();
