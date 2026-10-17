/* KaosH&F – header and footer editor */
(function () {
	'use strict';

	var __ = wp.i18n.__;

	var C = window.KhfConfig;
	var root = document.getElementById('khf-editor');

	var S = {
		id: C.templateId, title: '', status: 'draft', opts: null, logoUrl: '',
		device: 'desktop', section: 'layout', dirty: false, saving: false, cell: 0,
		elementorUrl: '', liveUrl: ''
	};
	var $ = {};
	var rows = [];          // fields with visibility conditions
	var previewTimer = 0;

	/* ---------- Utility ---------- */

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

	function api(path, opts) {
		opts = opts || {};
		var headers = { 'Content-Type': 'application/json', 'X-WP-Nonce': C.nonce };
		var method = opts.method || 'GET';
		if (method === 'DELETE' || method === 'PUT') {
			headers['X-HTTP-Method-Override'] = method;
			method = 'POST';
		}
		return fetch(C.restUrl + path, {
			method: method, headers: headers, credentials: 'same-origin',
			body: opts.body ? JSON.stringify(opts.body) : undefined
		}).then(function (r) {
			return r.json().then(function (json) {
				if (!r.ok) { throw new Error(json && json.message ? json.message : (__( 'Error', 'kaos-header-footer' ) + ' ') + r.status); }
				return json;
			});
		});
	}

	function toast(msg, type) {
		var t = h('div', { class: 'khl-toast' + (type ? ' is-' + type : '') }, msg);
		document.body.appendChild(t);
		setTimeout(function () { t.classList.add('is-out'); }, 1800);
		setTimeout(function () { t.remove(); }, 2300);
	}

	function get(path) {
		return path.split('.').reduce(function (o, k) { return o == null ? o : o[k]; }, S.opts);
	}

	function set(path, value) {
		var keys = path.split('.');
		var o = S.opts;
		while (keys.length > 1) { o = o[keys.shift()]; }
		o[keys[0]] = value;
	}

	function isBuilder() { return S.opts.source === 'builder'; }
	function isFooter() { return S.opts.type === 'footer'; }

	/* ---------- Modifiche e anteprima ---------- */

	function changed() {
		S.dirty = true;
		updateTopbar();
		applyWhen();
		schedulePreview();
	}

	function applyWhen() {
		rows.forEach(function (r) {
			r.el.classList.toggle('is-off', !r.when());
		});
	}

	function schedulePreview(now) {
		clearTimeout(previewTimer);
		previewTimer = setTimeout(loadPreview, now ? 0 : 450);
	}

	function loadPreview() {
		$.busy.classList.add('is-on');
		api('/templates/' + S.id + '/draft', { method: 'POST', body: { opts: S.opts } }).then(function (r) {
			var win = $.iframe.contentWindow;
			var y = 0;
			try { y = win.pageYOffset || 0; } catch (e) { y = 0; }
			$.iframe.addEventListener('load', function onLoad() {
				$.iframe.removeEventListener('load', onLoad);
				$.busy.classList.remove('is-on');
				try { $.iframe.contentWindow.scrollTo(0, y); } catch (e) { /* same origin */ }
			});
			$.iframe.src = r.url + '&_=' + Date.now();
		})['catch'](function (err) {
			$.busy.classList.remove('is-on');
			toast(err.message, 'error');
		});
	}

	/** The preview has the real width of the device and is scaled down with a zoom to fit the canvas. */
	function fitFrame() {
		if (!$.frame) { return; }
		var w = S.device === 'mobile' ? 390 : (S.device === 'tablet' ? 820 : 1280);
		var avail = Math.max(240, $.center.clientWidth - 44);
		var k = Math.min(1, avail / w);
		var height = Math.max(360, $.center.clientHeight - 44);
		$.frame.style.width = Math.round(w * k) + 'px';
		$.frame.style.height = height + 'px';
		$.iframe.style.width = w + 'px';
		$.iframe.style.height = Math.round(height / k) + 'px';
		$.iframe.style.transform = 'scale(' + k + ')';
	}

	/* ---------- Salvataggio ---------- */

	function save() {
		if (S.saving) { return Promise.resolve(); }
		S.saving = true;
		updateTopbar();
		return api('/templates/' + S.id, { method: 'POST', body: { title: S.title, status: S.status, opts: S.opts } }).then(function (t) {
			S.dirty = false;
			S.opts = t.opts;
			S.logoUrl = t.logoUrl;
			toast(__( 'Saved', 'kaos-header-footer' ), 'ok');
		})['catch'](function (err) {
			toast(err.message, 'error');
			throw err;
		}).then(function (v) { S.saving = false; updateTopbar(); return v; }, function (e) { S.saving = false; updateTopbar(); throw e; });
	}

	window.addEventListener('beforeunload', function (e) {
		if (S.dirty) { e.preventDefault(); e.returnValue = ''; }
	});
	document.addEventListener('keydown', function (e) {
		if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') { e.preventDefault(); save().catch(function () {}); }
	});

	/* ---------- Controlli ---------- */

	function labelRow(def, extra) {
		return h('div', { class: 'khe-label' }, h('span', null, def.label), extra || null);
	}

	function parseColor(v) {
		v = String(v || '').trim();
		var m = v.match(/^#([0-9a-f]{3}|[0-9a-f]{6})$/i);
		if (m) {
			var hex = m[1].length === 3 ? m[1].replace(/./g, '$&$&') : m[1];
			return { hex: '#' + hex.toLowerCase(), a: 1 };
		}
		m = v.match(/^rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*(?:,\s*([\d.]+)\s*)?\)$/i);
		if (m) {
			var h2 = function (n) { return ('0' + Math.min(255, +n).toString(16)).slice(-2); };
			return { hex: '#' + h2(m[1]) + h2(m[2]) + h2(m[3]), a: m[4] == null ? 1 : Math.max(0, Math.min(1, +m[4])) };
		}
		return null;
	}

	function toRgba(hex, a) {
		if (a >= 1) { return hex; }
		var n = parseInt(hex.slice(1), 16);
		return 'rgba(' + (n >> 16) + ',' + ((n >> 8) & 255) + ',' + (n & 255) + ',' + (Math.round(a * 100) / 100) + ')';
	}

	function colorCtl(def) {
		var cur = parseColor(get(def.path)) || { hex: '#ffffff', a: 1 };
		var text = h('input', { type: 'text', value: get(def.path) || '', placeholder: 'predefinito', 'aria-label': def.label });
		var pick = h('input', { type: 'color', value: cur.hex, 'aria-label': def.label });
		var alpha = h('input', { type: 'range', min: 0, max: 100, value: Math.round(cur.a * 100), title: __( 'Opacity', 'kaos-header-footer' ), 'aria-label': __( 'Opacity', 'kaos-header-footer' ), class: 'khe-alpha' });
		var apply = function () {
			var v = toRgba(pick.value, alpha.value / 100);
			set(def.path, v);
			text.value = v;
			changed();
		};
		pick.addEventListener('input', apply);
		alpha.addEventListener('input', apply);
		text.addEventListener('input', function () {
			set(def.path, text.value.trim());
			var p = parseColor(text.value);
			if (p) { pick.value = p.hex; alpha.value = Math.round(p.a * 100); }
			changed();
		});
		var clear = h('button', {
			type: 'button', class: 'khe-x', title: __( 'Reset', 'kaos-header-footer' ), 'aria-label': __( 'Reset', 'kaos-header-footer' ),
			onclick: function () {
				var v = def.empty != null ? def.empty : '';
				set(def.path, v);
				text.value = v;
				var p = parseColor(v);
				if (p) { pick.value = p.hex; alpha.value = Math.round(p.a * 100); } else { alpha.value = 100; }
				changed();
			}
		}, icon('no-alt'));
		return h('div', { class: 'khe-color' }, pick, text, clear, alpha);
	}

	function mediaCtl(def) {
		var byUrl = def.store === 'url';
		var thumb = h('span', { class: 'khe-media-thumb' });
		var show = function (url) { thumb.style.backgroundImage = url ? 'url("' + url + '")' : ''; };
		show(byUrl ? get(def.path) : S.logoUrl);
		var pick = h('button', {
			type: 'button', class: 'khe-btn', onclick: function () {
				var frame = wp.media({ title: __( 'Choose an image', 'kaos-header-footer' ), multiple: false, library: { type: 'image' } });
				frame.on('select', function () {
					var att = frame.state().get('selection').first().toJSON();
					var thumbUrl = (att.sizes && att.sizes.medium) ? att.sizes.medium.url : att.url;
					set(def.path, byUrl ? att.url : att.id);
					if (!byUrl) { S.logoUrl = thumbUrl; }
					show(thumbUrl);
					changed();
				});
				frame.open();
			}
		}, __( 'Choose', 'kaos-header-footer' ));
		var clear = h('button', {
			type: 'button', class: 'khe-btn', onclick: function () {
				set(def.path, byUrl ? '' : 0);
				if (!byUrl) { S.logoUrl = ''; }
				show('');
				changed();
			}
		}, __( 'Remove', 'kaos-header-footer' ));
		return h('div', { class: 'khe-media' }, thumb, pick, clear);
	}


	/* ---- Color or multi-stop gradient ---- */
	var QUICK_GRADIENTS = [
		[__( 'Sunset', 'kaos-header-footer' ), 'linear-gradient(135deg, #ff7a59 0%, #c2416b 50%, #2d1b3d 100%)'],
		[__( 'Ocean', 'kaos-header-footer' ), 'linear-gradient(135deg, #19b5c9 0%, #0f7ea6 50%, #062a40 100%)'],
		[__( 'Aurora', 'kaos-header-footer' ), 'linear-gradient(135deg, #2ee6b8 0%, #1fbf9f 40%, #0b1f2e 100%)'],
		[__( 'Candy', 'kaos-header-footer' ), 'linear-gradient(135deg, #ff9a9e 0%, #fad0c4 50%, #a18cd1 100%)'],
		[__( 'Neon', 'kaos-header-footer' ), 'linear-gradient(135deg, #00f5d4 0%, #3a0ca3 60%, #0b0b14 100%)'],
		[__( 'Gold', 'kaos-header-footer' ), 'linear-gradient(135deg, #f5e9c8 0%, #e0b84a 50%, #4a3a12 100%)'],
		[__( 'Forest', 'kaos-header-footer' ), 'linear-gradient(135deg, #7bc86c 0%, #3b6b3f 50%, #14261b 100%)'],
		[__( 'Rainbow', 'kaos-header-footer' ), 'linear-gradient(90deg, #ff4d1f 0%, #ffb36b 25%, #7bc86c 50%, #19b5c9 75%, #8b6bff 100%)']
	];

	function splitTop(str) {
		var out = [], depth = 0, cur = '';
		for (var i = 0; i < str.length; i++) {
			var ch = str[i];
			if (ch === '(') { depth++; }
			if (ch === ')') { depth--; }
			if (ch === ',' && depth === 0) { out.push(cur.trim()); cur = ''; } else { cur += ch; }
		}
		if (cur.trim()) { out.push(cur.trim()); }
		return out;
	}

	function parseGradient(v) {
		var m = String(v || '').match(/^(linear|radial|conic)-gradient\((.*)\)$/i);
		if (!m) { return null; }
		var g = { type: m[1].toLowerCase(), angle: m[1].toLowerCase() === 'linear' ? 180 : 0, stops: [] };
		splitTop(m[2]).forEach(function (part, i) {
			var a = part.match(/^(?:from\s+)?(-?[\d.]+)deg$/i);
			if (i === 0 && a) { g.angle = +a[1]; return; }
			if (i === 0 && /^(to |circle|ellipse|closest|farthest|at |from )/i.test(part)) { return; }
			var s = part.match(/^(.*\S)\s+([\d.]+)%$/);
			g.stops.push(s ? { c: s[1], p: +s[2] } : { c: part, p: null });
		});
		var n = g.stops.length;
		g.stops.forEach(function (s, i) { if (s.p == null) { s.p = n > 1 ? Math.round(i / (n - 1) * 100) : 0; } });
		return g.stops.length >= 2 ? g : null;
	}

	function buildGradient(g) {
		var stops = g.stops.map(function (s) { return s.c + ' ' + Math.round(s.p) + '%'; }).join(', ');
		if (g.type === 'radial') { return 'radial-gradient(circle, ' + stops + ')'; }
		if (g.type === 'conic') { return 'conic-gradient(from ' + Math.round(g.angle) + 'deg, ' + stops + ')'; }
		return 'linear-gradient(' + Math.round(g.angle) + 'deg, ' + stops + ')';
	}

	function hexOf(c) {
		var p = parseColor(c);
		return p ? p.hex : '#ffffff';
	}

	function paintCtl(def) {
		var wrap = h('div', { class: 'khe-paint' });
		var gradient = function () { return parseGradient(get(def.path)); };

		function render() {
			wrap.innerHTML = '';
			var g = gradient();
			var isGrad = !!g || /-gradient\(/.test(String(get(def.path) || ''));
			var mode = h('div', { class: 'khe-seg' },
				h('button', { type: 'button', class: !isGrad ? 'is-active' : '', onclick: function () {
					if (isGrad) { set(def.path, g && g.stops[0] ? g.stops[0].c : ''); changed(); render(); }
				} }, __( 'Solid color', 'kaos-header-footer' )),
				h('button', { type: 'button', class: isGrad ? 'is-active' : '', onclick: function () {
					if (!isGrad) {
						var base = get(def.path) || '#ff7a59';
						set(def.path, buildGradient({ type: 'linear', angle: 135, stops: [{ c: base, p: 0 }, { c: '#2d1b3d', p: 100 }] }));
						changed(); render();
					}
				} }, __( 'Gradient', 'kaos-header-footer' )));
			wrap.appendChild(mode);

			if (!isGrad) {
				wrap.appendChild(h('div', { style: 'margin-top:8px' }, colorCtl(def)));
				return;
			}
			if (!g) { g = { type: 'linear', angle: 135, stops: [{ c: '#ff7a59', p: 0 }, { c: '#2d1b3d', p: 100 }] }; }

			var bar = h('div', { class: 'khe-gradbar' });
			var commit = function () { set(def.path, buildGradient(g)); bar.style.background = buildGradient(Object.assign({}, g, { type: 'linear', angle: 90 })); changed(); };
			bar.style.background = buildGradient(Object.assign({}, g, { type: 'linear', angle: 90 }));
			wrap.appendChild(bar);

			var typeSel = h('select', { onchange: function (e) { g.type = e.target.value; commit(); render(); } },
				[['linear', __( 'Linear', 'kaos-header-footer' )], ['radial', __( 'Radial', 'kaos-header-footer' )], ['conic', __( 'Conic', 'kaos-header-footer' )]].map(function (o) { return h('option', { value: o[0], selected: g.type === o[0] ? true : null }, o[1]); }));
			var angle = h('input', { type: 'number', min: 0, max: 360, value: g.angle, oninput: function (e) { g.angle = +e.target.value || 0; commit(); } });
			wrap.appendChild(h('div', { class: 'khe-inline', style: 'margin-top:10px' }, typeSel, g.type !== 'radial' ? h('span', null, __( 'Angle', 'kaos-header-footer' )) : null, g.type !== 'radial' ? angle : null, g.type !== 'radial' ? '°' : null));

			var list = h('div', { class: 'khe-stops' });
			var paintStops = function () {
				list.innerHTML = '';
				g.stops.forEach(function (s, i) {
					var pick = h('input', { type: 'color', value: hexOf(s.c), 'aria-label': (__( 'Color', 'kaos-header-footer' ) + ' ') + (i + 1) });
					var txt = h('input', { type: 'text', value: s.c, 'aria-label': (__( 'Color value', 'kaos-header-footer' ) + ' ') + (i + 1) });
					var pos = h('input', { type: 'number', min: 0, max: 100, value: Math.round(s.p), title: __( 'Position %', 'kaos-header-footer' ), 'aria-label': __( 'Position', 'kaos-header-footer' ) });
					pick.addEventListener('input', function () { s.c = pick.value; txt.value = s.c; commit(); });
					txt.addEventListener('input', function () { s.c = txt.value.trim(); var p = parseColor(s.c); if (p) { pick.value = p.hex; } commit(); });
					pos.addEventListener('input', function () { s.p = Math.max(0, Math.min(100, +pos.value || 0)); commit(); });
					var del = h('button', { type: 'button', class: 'khe-x', title: __( 'Remove', 'kaos-header-footer' ), 'aria-label': __( 'Remove color', 'kaos-header-footer' ), disabled: g.stops.length <= 2 ? true : null, onclick: function () { g.stops.splice(i, 1); commit(); paintStops(); } }, icon('no-alt'));
					list.appendChild(h('div', { class: 'khe-stop' }, pick, txt, pos, '%', del));
				});
			};
			paintStops();
			wrap.appendChild(list);
			wrap.appendChild(h('div', { class: 'khe-inline', style: 'margin-top:8px' },
				h('button', { type: 'button', class: 'khe-btn', onclick: function () {
					var last = g.stops[g.stops.length - 1];
					g.stops.splice(g.stops.length - 1, 0, { c: '#ffffff', p: Math.max(0, Math.min(100, last.p - 20)) });
					commit(); paintStops();
				} }, icon('plus-alt2'), (' ' + __( 'Add color', 'kaos-header-footer' ))),
				h('button', { type: 'button', class: 'khe-btn', onclick: function () {
					g.stops.forEach(function (s) { s.p = 100 - s.p; });
					g.stops.reverse();
					commit(); paintStops();
				} }, icon('randomize'), (' ' + __( 'Reverse', 'kaos-header-footer' )))));
		}

		var quick = h('div', { class: 'khe-chips khe-quick' }, QUICK_GRADIENTS.map(function (q) {
			var b = h('button', { type: 'button', class: 'khe-grad-chip', title: q[0], 'aria-label': (__( 'Gradient', 'kaos-header-footer' ) + ' ') + q[0], onclick: function () { set(def.path, q[1]); changed(); render(); } });
			b.style.background = q[1];
			return b;
		}));
		var root2 = h('div', null, wrap, h('div', { class: 'khe-label', style: 'margin:12px 0 6px' }, h('span', null, __( 'Ready-made gradients', 'kaos-header-footer' ))), quick);
		render();
		return root2;
	}

	function clampNum(value, min, max) {
		var n = Number(value);
		if (isNaN(n)) { n = min != null ? min : 0; }
		if (min != null && n < min) { n = min; }
		if (max != null && n > max) { n = max; }
		return n;
	}

	function control(def) {
		var v = get(def.path);
		switch (def.type) {
			case 'select':
				var sel = h('select', { onchange: function (e) { set(def.path, e.target.value); if (def.onChange) { def.onChange(e.target.value); } changed(); if (def.rerender) { renderAll(); } } },
					def.options.map(function (o) { return h('option', { value: o[0], selected: String(v) === String(o[0]) ? true : null }, o[1]); }));
				return sel;
			case 'number':
				return h('input', {
					type: 'number', min: def.min, max: def.max, step: def.step || 1, value: v,
					oninput: function (e) { set(def.path, e.target.value === '' ? 0 : Number(e.target.value)); changed(); },
					onchange: function (e) { var n = clampNum(e.target.value, def.min, def.max); e.target.value = n; set(def.path, n); changed(); }
				});
			case 'textarea':
				return h('textarea', { rows: 4, placeholder: def.placeholder || '', value: v, oninput: function (e) { set(def.path, e.target.value); changed(); } });
			case 'color':
				return colorCtl(def);
			case 'paint':
				return paintCtl(def);
			case 'image':
				return mediaCtl(def);
			case 'multi':
				var box = h('div', { class: 'khe-chips' });
				def.options.forEach(function (o) {
					var chip = h('button', { type: 'button', class: 'khe-chip' + ((get(def.path) || []).indexOf(o[0]) > -1 ? ' is-active' : '') }, o[1]);
					chip.addEventListener('click', function () {
						var list = (get(def.path) || []).slice();
						var i = list.indexOf(o[0]);
						if (i > -1) { list.splice(i, 1); } else { list.push(o[0]); }
						set(def.path, list);
						chip.classList.toggle('is-active', i < 0);
						changed();
					});
					box.appendChild(chip);
				});
				return box;
			case 'seg':
				var seg = h('div', { class: 'khe-seg' });
				var paint = function () {
					seg.innerHTML = '';
					def.options.forEach(function (o) {
						seg.appendChild(h('button', {
							type: 'button', class: String(get(def.path)) === String(o[0]) ? 'is-active' : '', disabled: o[2] ? true : null,
							onclick: function () { set(def.path, o[0]); paint(); changed(); if (def.rerender) { renderAll(); } }
						}, o[1]));
					});
				};
				paint();
				return seg;
			case 'moods':
				var grid = h('div', { class: 'khe-moods' });
				Object.keys(C.moods).forEach(function (key) {
					var m = C.moods[key];
					var cols = isFooter() ? [m.fbg, m.fbg2, m.accent] : [m.hbg, m.hfg, m.accent];
					var isCustom = key === 'custom';
					var btn = h('button', { type: 'button', class: 'khe-mood' + (get('mood') === key ? ' is-active' : ''), onclick: function () {
						set('mood', key);
						if (key === 'custom' && S.section !== 'colors') { toast(__( 'Choose the colors in the Colors section', 'kaos-header-footer' ), 'ok'); }
						Array.prototype.forEach.call(grid.children, function (b) { b.classList.toggle('is-active', b === btn); });
						changed();
					} }, h('span', { class: 'khe-mood-sw', style: isCustom ? 'background:conic-gradient(#ff4d1f,#ffb36b,#7bc86c,#19b5c9,#8b6bff,#ff4d1f)' : null }, isCustom ? null : cols.map(function (c) { return h('i', { style: 'background:' + c }); })), m.label);
					grid.appendChild(btn);
				});
				return grid;
			default: // text, url, email
				return h('input', { type: def.type || 'text', placeholder: def.placeholder || '', value: v == null ? '' : v, oninput: function (e) { set(def.path, e.target.value); changed(); } });
		}
	}


	var GLASS_PRESETS = [
		[__( 'Light', 'kaos-header-footer' ), { blur: 12, sat: 140, bright: 105, g_edge: 25, g_shine: 10, g_grain: 0, g_depth: 15, g_refract: 0, g_tint: 'rgba(255,255,255,0.1)' }],
		[__( 'Clear', 'kaos-header-footer' ), { blur: 30, sat: 180, bright: 110, g_edge: 50, g_shine: 25, g_grain: 12, g_depth: 30, g_refract: 0, g_tint: 'rgba(255,255,255,0.18)' }],
		[__( 'Dark', 'kaos-header-footer' ), { blur: 30, sat: 160, bright: 80, g_edge: 25, g_shine: 10, g_grain: 12, g_depth: 40, g_refract: 0, g_tint: 'rgba(20,20,24,0.35)' }],
		[__( 'Ice', 'kaos-header-footer' ), { blur: 60, sat: 120, bright: 115, g_edge: 60, g_shine: 35, g_grain: 25, g_depth: 20, g_refract: 0, g_tint: 'rgba(230,240,255,0.25)' }],
		[__( 'Liquid', 'kaos-header-footer' ), { blur: 8, sat: 200, bright: 105, g_edge: 90, g_shine: 45, g_grain: 0, g_depth: 35, g_refract: 40, g_tint: 'rgba(255,255,255,0.06)' }]
	];

	function glassPresetsCtl() {
		var box = h('div', { class: 'khe-seg khe-seg-wrap' });
		GLASS_PRESETS.forEach(function (p) {
			box.appendChild(h('button', {
				type: 'button',
				onclick: function () {
					S.opts.fx.glass = 1;
					Object.keys(p[1]).forEach(function (k) { S.opts.fx[k] = p[1][k]; });
					changed();
					renderProps();
				}
			}, p[0]));
		});
		return h('div', { class: 'khe-field' }, h('div', { class: 'khe-label' }, h('span', null, __( 'Starting styles', 'kaos-header-footer' ))), box,
			h('p', { class: 'khe-help' }, __( 'They set the sliders; then customize them as you like.', 'kaos-header-footer' )));
	}

	function currentMenuItems() {
		var id = String(S.opts.menu || '');
		if (!id || id === '0') { id = Object.keys(C.menuItems)[0]; }
		var items = (C.menuItems[id] || []).slice();
		if (['split', 'custom'].indexOf(S.opts.preset_header) > -1 && S.opts.menu2 && String(S.opts.menu2) !== id) {
			items = items.concat(C.menuItems[String(S.opts.menu2)] || []);
		}
		return items;
	}

	function menuImagesCtl() {
		var items = currentMenuItems();
		if (!S.opts.menu_images || Array.isArray(S.opts.menu_images)) { S.opts.menu_images = {}; }
		var wrap = h('div', { class: 'khe-field' }, h('div', { class: 'khe-label' }, h('span', null, __( 'Image or video for each item', 'kaos-header-footer' ))));
		if (!items.length) {
			wrap.appendChild(h('p', { class: 'khe-help' }, __( 'The selected menu has no items: add them from Appearance → Menus.', 'kaos-header-footer' )));
			return wrap;
		}
		items.forEach(function (it) {
			var thumb = h('span', { class: 'khe-media-thumb' });
			var show = function () {
				var u = S.opts.menu_images[it.id];
				var isVideo = /\.(mp4|webm|ogv|mov|m4v)(\?.*)?$/i.test(u || '');
				thumb.style.backgroundImage = u && !isVideo ? 'url("' + u + '")' : '';
				thumb.textContent = isVideo ? '▶ video' : '';
				thumb.classList.toggle('is-video', isVideo);
			};
			show();
			var pick = h('button', {
				type: 'button', class: 'khe-btn', onclick: function () {
					var frame = wp.media({ title: __( 'Image or video for "', 'kaos-header-footer' ) + it.title + '"', multiple: false, library: { type: ['image', 'video'] } });
					frame.on('select', function () {
						S.opts.menu_images[it.id] = frame.state().get('selection').first().toJSON().url;
						show();
						changed();
					});
					frame.open();
				}
			}, __( 'Choose', 'kaos-header-footer' ));
			var clear = h('button', { type: 'button', class: 'khe-x', 'aria-label': __( 'Remove', 'kaos-header-footer' ), onclick: function () { delete S.opts.menu_images[it.id]; show(); changed(); } }, icon('no-alt'));
			wrap.appendChild(h('div', { class: 'khe-itemrow' }, h('span', { class: 'khe-itemname' }, it.title), thumb, pick, clear));
		});
		return wrap;
	}

	/** Field row: label + control + help + conditional visibility. */
	function field(def) {
		var el;
		if (typeof def.node === 'function') {
			el = def.node();
		} else if (def.type === 'check') {
			var sw = h('span', { class: 'khe-switch' + (get(def.path) ? ' is-on' : ''), role: 'switch', 'aria-checked': get(def.path) ? 'true' : 'false' });
			el = h('div', { class: 'khe-field' },
				h('div', { class: 'khe-toggle', tabindex: '0', onclick: toggle, onkeydown: function (e) { if (e.key === ' ' || e.key === 'Enter') { e.preventDefault(); toggle(); } } },
					h('span', { class: 'khe-t' }, def.label), sw));
			if (def.help) { el.appendChild(h('p', { class: 'khe-help' }, def.help)); }
		} else {
			el = h('div', { class: 'khe-field' }, labelRow(def), control(def));
			if (def.help) { el.appendChild(h('p', { class: 'khe-help' }, def.help)); }
		}
		function toggle() {
			var on = !get(def.path);
			set(def.path, on ? 1 : 0);
			sw.classList.toggle('is-on', on);
			sw.setAttribute('aria-checked', on ? 'true' : 'false');
			changed();
			if (def.rerender) { renderAll(); }
		}
		if (def.when) { rows.push({ el: el, when: def.when }); }
		return el;
	}

	var COLLAPSE_KEY = 'khe_collapsed2';
	var collapsed = (function () {
		try { return JSON.parse(localStorage.getItem(COLLAPSE_KEY) || '{}') || {}; } catch (e) { return {}; }
	})();

	function saveCollapsed() {
		try { localStorage.setItem(COLLAPSE_KEY, JSON.stringify(collapsed)); } catch (e) { /* storage not available */ }
	}

	/** Block with a button title and a small arrow: it opens and closes, and the state is remembered. */
	function collapsible(title, nodes) {
		var key = S.section + ':' + title;
		var wrap = h('div', { class: 'khe-section' + (collapsed[key] !== false ? ' is-collapsed' : ''), 'data-key': key });
		var btn = h('button', {
			type: 'button', class: 'khe-section-title khe-fold', 'aria-expanded': collapsed[key] !== false ? 'false' : 'true',
			onclick: function () {
				var c = !wrap.classList.contains('is-collapsed');
				wrap.classList.toggle('is-collapsed', c);
				btn.setAttribute('aria-expanded', c ? 'false' : 'true');
				collapsed[key] = c;
				saveCollapsed();
			}
		}, h('span', null, title), icon('arrow-down-alt2'));
		wrap.appendChild(btn);
		wrap.appendChild(h('div', { class: 'khe-section-body' }, nodes));
		return wrap;
	}

	function section(title, fields) {
		return title ? collapsible(title, fields.map(field)) : h('div', { class: 'khe-section' }, fields.map(field));
	}

	function foldAll(close) {
		Array.prototype.forEach.call($.right.querySelectorAll('.khe-section[data-key]'), function (s) {
			s.classList.toggle('is-collapsed', close);
			var b = s.querySelector('.khe-fold');
			if (b) { b.setAttribute('aria-expanded', close ? 'false' : 'true'); }
			collapsed[s.getAttribute('data-key')] = close;
		});
		saveCollapsed();
	}

	function opts(obj) {
		return Object.keys(obj).map(function (k) { return [k, obj[k]]; });
	}

	function menuOptions() {
		return [['0', __( '— Automatic / none —', 'kaos-header-footer' )]].concat(C.menus.map(function (m) { return [String(m.id), m.name]; }));
	}

	function schemaField(key, def) {
		var f = {
			path: 'fx.' + key, label: def.label, help: def.help, type: def.type === 'check' ? 'check' : def.type, step: 1,
			min: def.min, max: def.max, store: 'url', empty: def.default
		};
		if (def.options) { f.options = def.type === 'multi' || def.type === 'select' ? opts(def.options) : null; }
		f.when = function () {
			var ok = Object.keys(def.when || {}).every(function (k) {
				return def.when[k].map(String).indexOf(String(S.opts.fx[k])) > -1;
			});
			return ok && (!def.preset_off || isBuilder());
		};
		return f;
	}

	/* ---------- Panel sections ---------- */

	function sectionsFor() {
		var list = [['layout', 'dashboard', __( 'Structure', 'kaos-header-footer' )]];
		if (!isBuilder()) {
			list.push(['content', 'edit', __( 'Content', 'kaos-header-footer' )]);
			if (!isFooter()) {
				list.push(['menu', 'menu', __( 'Menu and images', 'kaos-header-footer' )]);
				list.push(['burger', 'smartphone', __( 'Icon and menu window', 'kaos-header-footer' )]);
			} else {
				if (S.opts.preset_footer === 'grid') { list.push(['grid', 'grid-view', __( 'Grid and cells', 'kaos-header-footer' )]); }
				list.push(['bar', 'align-wide', __( 'Bottom bar', 'kaos-header-footer' )]);
				list.push(['links', 'admin-links', __( 'Footer links', 'kaos-header-footer' )]);
			}
			list.push(['type', 'editor-textcolor', __( 'Typography', 'kaos-header-footer' )]);
		}
		if (isFooter()) { list.push(['btt', 'arrow-up-alt', __( 'Back to top arrow', 'kaos-header-footer' )]); }
		list.push(['colors', 'art', __( 'Colors', 'kaos-header-footer' )]);
		if (!isFooter()) { list.push(['fx', 'performance', __( 'Scroll effects', 'kaos-header-footer' )]); }
		list.push(['show', 'visibility', __( 'Where to show it', 'kaos-header-footer' )]);
		return list;
	}

	function elementorBox() {
		var ok = !!S.elementorUrl;
		return h('div', { class: 'khe-elementor' },
			h('strong', null, __( 'Designed with Elementor', 'kaos-header-footer' )),
			h('p', { style: 'margin:6px 0 0' }, ok
				? __( 'Below, choose where to show it and the effects. You design the layout in Elementor: the preview shows the last saved content.', 'kaos-header-footer' )
				: __( 'Elementor is not active: activate it to edit this template.', 'kaos-header-footer' )),
			ok ? h('button', { type: 'button', class: 'khe-btn khe-primary', onclick: editWithElementor }, icon('edit'), (' ' + __( 'Edit with Elementor', 'kaos-header-footer' ))) : null,
			ok ? h('button', { type: 'button', class: 'khe-btn', style: 'margin-left:6px', onclick: function () { schedulePreview(true); } }, icon('update'), (' ' + __( 'Reload preview', 'kaos-header-footer' ))) : null);
	}


	var ZONE_DEFAULTS = {
		logo: { z: 'c', o: 0, dx: 0 },
		menu: { z: 'l', o: 0, dx: 0 },
		menu2: { z: 'r', o: 0, dx: 0 },
		cta: { z: 'r', o: 1, dx: 0 }
	};

	/** Free arrangement: each element goes in a zone (left / center / right) with an order. */
	function layoutZonesSection() {
		var lay = S.opts.custom_layout;
		if (!lay || Array.isArray(lay) || !lay.logo) {
			S.opts.custom_layout = JSON.parse(JSON.stringify(ZONE_DEFAULTS));
			lay = S.opts.custom_layout;
		}
		var names = [['logo', __( 'Logo', 'kaos-header-footer' )], ['menu', __( __( 'Menu 1', 'kaos-header-footer' ), 'kaos-header-footer' )], ['menu2', __( __( 'Menu 2', 'kaos-header-footer' ), 'kaos-header-footer' )], ['cta', __( 'Button', 'kaos-header-footer' )]];
		var zoneLabels = [['l', __( 'Left', 'kaos-header-footer' )], ['c', __( 'Center', 'kaos-header-footer' )], ['r', __( 'Right', 'kaos-header-footer' )]];

		var rowsEl = names.map(function (n) {
			var row = lay[n[0]] = Object.assign({ z: 'c', o: 0, dx: 0 }, lay[n[0]] || {});
			var seg = h('div', { class: 'khe-seg' });
			var paint = function () {
				seg.innerHTML = '';
				zoneLabels.forEach(function (z) {
					seg.appendChild(h('button', { type: 'button', class: row.z === z[0] ? 'is-active' : '', onclick: function () { row.z = z[0]; paint(); changed(); } }, z[1]));
				});
			};
			paint();
			var num = function (key, min, max, title) {
				return h('input', {
					type: 'number', min: min, max: max, value: row[key], title: title, 'aria-label': title + ' ' + n[1],
					oninput: function (e) { row[key] = clampNum(e.target.value, min, max); changed(); },
					onchange: function (e) { row[key] = clampNum(e.target.value, min, max); e.target.value = row[key]; changed(); }
				});
			};
			return h('div', { class: 'khe-zonerow' }, h('span', { class: 'khe-zonename' }, n[1]), seg, num('o', -9, 9, __( 'Order in the zone', 'kaos-header-footer' )), num('dx', -800, 800, __( 'Shift horizontally (px)', 'kaos-header-footer' )));
		});

		return collapsible(__( 'Arrangement', 'kaos-header-footer' ), [
			h('p', { class: 'khe-help', style: 'margin:0 0 10px' }, __( 'For each element: the zone, the order inside the zone (-9…9) and how many pixels to shift it right (+) or left (−). On mobile only the logo and the menu icon remain.', 'kaos-header-footer' )),
			h('div', { class: 'khe-zonehead' }, h('span', null, __( 'Element', 'kaos-header-footer' )), h('span', null, __( 'Zone', 'kaos-header-footer' )), h('span', null, __( 'Order', 'kaos-header-footer' )), h('span', null, __( 'Shift px', 'kaos-header-footer' ))),
			rowsEl,
			field({ path: 'zone_cgap', type: 'number', label: __( 'Space between zones (px)', 'kaos-header-footer' ), min: 0, max: 600, help: __( 'The minimum distance between left, center and right.', 'kaos-header-footer' ) }),
			field({ path: 'zone_gap', type: 'number', label: __( 'Space between items in the same zone (px)', 'kaos-header-footer' ), min: 0, max: 300 })
		]);
	}

	function layoutSections() {
		var out = [];
		var src = section(__( 'Build', 'kaos-header-footer' ), [
			{ path: 'source', type: 'seg', label: __( 'How it is built', 'kaos-header-footer' ), rerender: true, options: [['preset', __( 'Emotional preset', 'kaos-header-footer' )], ['builder', 'Elementor', !C.elementor]] }
		]);
		out.push(src);
		if (isBuilder()) {
			out.push(elementorBox());
			if (!isFooter()) { /* the effects are in the dedicated section */ }
			return out;
		}
		if (isFooter()) {
			out.push(section(__( 'Design', 'kaos-header-footer' ), [
				{ path: 'preset_footer', type: 'select', label: __( 'Layout', 'kaos-header-footer' ), rerender: true, onChange: function () { S.opts.bar_layout = {}; }, options: opts(C.presets.footer) },
				{ path: 'mood', type: 'moods', label: __( 'Mood', 'kaos-header-footer' ), help: __( 'Sets background, text and accent in one go.', 'kaos-header-footer' ) }
			]));
			out.push(section(__( 'Entrance and details', 'kaos-header-footer' ), [
				{ path: 'footer_mode', type: 'select', label: __( 'Entrance mode', 'kaos-header-footer' ), options: [['normal', __( 'Normal (at the bottom of the page)', 'kaos-header-footer' )], ['reveal', __( 'Reveal: the content slides away and uncovers the footer', 'kaos-header-footer' )], ['reveal-parallax', __( 'Reveal + parallax and fade', 'kaos-header-footer' )]] },
				{ path: 'footer_fx', type: 'select', label: __( 'Entrance effect', 'kaos-header-footer' ), options: [['none', __( 'None', 'kaos-header-footer' )], ['zoom-in', __( 'Zoom in (from small to normal)', 'kaos-header-footer' )], ['zoom-out', __( 'Zoom out (from large to normal)', 'kaos-header-footer' )], ['zoom-top', __( 'Zoom in from top to bottom', 'kaos-header-footer' )], ['zoom-bottom', __( 'Zoom in from bottom to top', 'kaos-header-footer' )], ['zoom-left', __( 'Zoom in from the left', 'kaos-header-footer' )], ['zoom-right', __( 'Zoom in from the right', 'kaos-header-footer' )], ['fade-up', __( 'Fade up from below', 'kaos-header-footer' )]], help: __( 'It combines with the entrance mode and follows the page scroll. If you pick one, it replaces the parallax.', 'kaos-header-footer' ) },
				{ path: 'footer_m_al', type: 'seg', label: __( 'Text alignment on smartphones', 'kaos-header-footer' ), options: [['l', __( 'Left', 'kaos-header-footer' )], ['c', __( 'Center', 'kaos-header-footer' )], ['r', __( 'Right', 'kaos-header-footer' )]], help: __( 'Applies up to 700 px wide.', 'kaos-header-footer' ) },
				{ path: 'animated_bg', type: 'check', label: __( 'Animated background gradient', 'kaos-header-footer' ) }
			]));
			var hasDiv = function () { return S.opts.divider !== 'none'; };
			out.push(section(__( 'Top divider', 'kaos-header-footer' ), [
				{ path: 'divider', type: 'select', label: __( 'Shape', 'kaos-header-footer' ), options: opts(C.dividers) },
				{ path: 'divider_h', type: 'number', label: __( 'Height (px)', 'kaos-header-footer' ), min: 10, max: 320, when: hasDiv },
				{ path: 'divider_c', type: 'color', label: __( 'Color', 'kaos-header-footer' ), when: hasDiv, help: __( 'Empty = page color. Use the picker\'s transparency (or the opacity below) to make it transparent.', 'kaos-header-footer' ) },
				{ path: 'divider_op', type: 'number', label: __( 'Opacity (%)', 'kaos-header-footer' ), min: 0, max: 100, when: hasDiv },
				{ path: 'divider_flip', type: 'check', label: __( 'Flip horizontally', 'kaos-header-footer' ), when: hasDiv }
			]));
		} else {
			out.push(section(__( 'Design', 'kaos-header-footer' ), [
				{ path: 'preset_header', type: 'select', label: __( 'Layout', 'kaos-header-footer' ), rerender: true, options: opts(C.presets.header) },
				{ path: 'mood', type: 'moods', label: __( 'Mood', 'kaos-header-footer' ), help: __( 'Sets background, text and accent in one go.', 'kaos-header-footer' ) }
			]));
			if (S.opts.preset_header === 'split') {
				out.push(h('div', { class: 'khe-hint' }, __( 'Menu 1 on the left, logo in the center, menu 2 on the right with the button. Choose the two menus in Content.', 'kaos-header-footer' )));
			}
			if (S.opts.preset_header === 'custom') {
				out.push(layoutZonesSection());
			}
			var zoned = function () { return ['split', 'custom'].indexOf(S.opts.preset_header) > -1; };
			out.push(section(__( 'Spacing and width', 'kaos-header-footer' ), [
				{ path: 'wrap_w', type: 'number', label: __( 'Content width (px)', 'kaos-header-footer' ), min: 0, max: 2800, help: __( '0 = automatic (1280). Lower = a tighter group.', 'kaos-header-footer' ) },
				{ path: 'zone_align', type: 'seg', label: __( 'Alignment', 'kaos-header-footer' ), options: [['edges', __( 'At the edges', 'kaos-header-footer' )], ['center', __( 'Grouped in the center', 'kaos-header-footer' )]], when: zoned, help: __( '"Grouped" brings the two menus close to the logo, all in the center.', 'kaos-header-footer' ) },
				{ path: 'logo_gap', type: 'number', label: __( 'Space around the logo (px)', 'kaos-header-footer' ), min: 0, max: 300, when: function () { return zoned() && S.opts.zone_align === 'center'; } }
			]));
			out.push(section(__( 'Height', 'kaos-header-footer' ), [
				{ path: 'height', type: 'number', label: __( 'Height (px)', 'kaos-header-footer' ), min: 40, max: 220 },
				{ path: 'p_shrink', type: 'check', label: __( 'Shrink on scroll', 'kaos-header-footer' ) },
				{ path: 'height_shrunk', type: 'number', label: __( 'Reduced height (px)', 'kaos-header-footer' ), min: 30, max: 220, when: function () { return !!S.opts.p_shrink; } }
			]));
		}
		return out;
	}

	function contentSections() {
		var f = [
			{ path: 'logo_id', type: 'image', store: 'id', label: __( 'Logo', 'kaos-header-footer' ) },
			{ path: 'logo_h', type: 'number', label: __( 'Logo height (px)', 'kaos-header-footer' ), min: 16, max: 200 },
			{ path: 'logo_text', type: 'text', label: __( 'Logo text', 'kaos-header-footer' ), help: __( 'Used if you don\'t choose an image. Empty = site name.', 'kaos-header-footer' ) }
		];
		if (!isFooter()) {
			f.splice(2, 0, { path: 'logo_scroll_id', type: 'image', store: 'id', label: __( 'Logo after scroll', 'kaos-header-footer' ), help: __( 'Replaces the logo when the page scrolls. Empty = stays the same.', 'kaos-header-footer' ) });
		}
		var menus = isFooter()
			? [{ path: 'menu', type: 'select', label: __( 'Column 1 menu', 'kaos-header-footer' ), options: menuOptions() }, { path: 'menu2', type: 'select', label: __( 'Column 2 menu', 'kaos-header-footer' ), options: menuOptions() }, { path: 'menu3', type: 'select', label: __( 'Column 3 menu', 'kaos-header-footer' ), options: menuOptions() }]
			: [{ path: 'menu', type: 'select', label: __( 'Menu', 'kaos-header-footer' ), options: menuOptions(), rerender: true, help: __( 'Automatic = the first menu of the site.', 'kaos-header-footer' ) },
				{ path: 'menu2', type: 'select', label: __( __( 'Menu 2', 'kaos-header-footer' ), 'kaos-header-footer' ), options: menuOptions(), rerender: true, when: function () { return ['split', 'custom'].indexOf(S.opts.preset_header) > -1; }, help: __( 'The second menu of the "Split" and "Custom" layouts.', 'kaos-header-footer' ) }];
		var text = [
			{ path: 'cta_text', type: 'text', label: __( 'Button (text)', 'kaos-header-footer' ) },
			{ path: 'cta_url', type: 'url', label: __( 'Button (link)', 'kaos-header-footer' ), placeholder: 'https://' },
			{ path: 'tagline', type: 'text', label: __( 'Phrase / tagline', 'kaos-header-footer' ), help: isFooter() ? __( 'The emotional phrase of the footer.', 'kaos-header-footer' ) : __( 'Shown in the full-screen menu.', 'kaos-header-footer' ) }
		];
		var out = [section(__( 'Identity', 'kaos-header-footer' ), f), section(__( 'Menu', 'kaos-header-footer' ), menus), section(__( 'Call to action', 'kaos-header-footer' ), text)];
		if (isFooter()) {
			out.push(section(__( 'Footer', 'kaos-header-footer' ), [
				{ path: 'headline', type: 'text', label: __( 'Big headline', 'kaos-header-footer' ), help: __( 'For "Big call to action" and for the grid. You can use <br>, <em>, <strong>.', 'kaos-header-footer' ) },
				{ path: 'headline_al', type: 'select', label: __( 'Big headline alignment', 'kaos-header-footer' ), options: [['l', __( 'Left', 'kaos-header-footer' )], ['c', __( 'Centered', 'kaos-header-footer' )], ['r', __( 'Right', 'kaos-header-footer' )]], help: __( 'Also applies to the phrase and the button under the headline.', 'kaos-header-footer' ) },
				{ path: 'headline_ff', type: 'select', label: __( 'Big headline font', 'kaos-header-footer' ), options: Object.keys(C.fonts).map(function (k) { return [k, C.fonts[k]]; }), help: __( 'Empty = the headings font.', 'kaos-header-footer' ) },
				{ path: 'headline_fs', type: 'number', label: __( 'Big headline size (px)', 'kaos-header-footer' ), min: 0, max: 200, help: __( '0 = large, based on the screen.', 'kaos-header-footer' ) },
				{ path: 'headline_fw', type: 'select', label: __( 'Big headline weight', 'kaos-header-footer' ), options: [['', __( 'Default', 'kaos-header-footer' )], ['300', __( 'Light', 'kaos-header-footer' )], ['400', __( 'Normal', 'kaos-header-footer' )], ['600', __( 'Semi-bold', 'kaos-header-footer' )], ['700', __( 'Bold', 'kaos-header-footer' )], ['800', __( 'Extra', 'kaos-header-footer' )], ['900', __( 'Black', 'kaos-header-footer' )]] },
				{ path: 'socials', type: 'textarea', label: __( 'Social links (one URL per line)', 'kaos-header-footer' ), placeholder: 'https://instagram.com/…' },
				{ path: 'social_style', type: 'select', label: __( 'Social links style', 'kaos-header-footer' ), options: [['icons', __( 'Icons only', 'kaos-header-footer' )], ['text', __( 'Text only', 'kaos-header-footer' )], ['both', __( 'Icon and text', 'kaos-header-footer' )]] },
				{ path: 'email', type: 'email', label: __( 'Email', 'kaos-header-footer' ) },
				{ path: 'phone', type: 'text', label: __( 'Phone', 'kaos-header-footer' ) },
				{ path: 'address', type: 'text', label: __( 'Address', 'kaos-header-footer' ) },
				{ path: 'address_map', type: 'check', label: __( 'Clickable address (opens maps)', 'kaos-header-footer' ), help: __( 'On a phone it asks whether to open it with Maps or Google Maps.', 'kaos-header-footer' ) },
				{ path: 'address_url', type: 'url', label: __( 'Google Maps link (optional)', 'kaos-header-footer' ), placeholder: 'https://maps.app.goo.gl/…', help: __( 'Empty = it is built from the address.', 'kaos-header-footer' ), when: function () { return !!S.opts.address_map; } },
				{ path: 'copyright', type: 'text', label: __( 'Copyright', 'kaos-header-footer' ), help: __( 'Use {year} for the current year.', 'kaos-header-footer' ) }
			]));
		}
		return out;
	}

	function menuSections() {
		var fx = opts(C.menuFx);
		var on = function () { return S.opts.hover_img !== 'none'; };
		var mega = function () { return S.opts.sub_style === 'mega'; };
		return [
			section(__( 'Text and spacing of items', 'kaos-header-footer' ), [
				{ path: 'menu_fs', type: 'number', label: __( 'Text size (px)', 'kaos-header-footer' ), min: 0, max: 40, help: __( '0 = same as the theme.', 'kaos-header-footer' ) },
				{ path: 'menu_fw', type: 'select', label: __( 'Weight', 'kaos-header-footer' ), options: [['', __( 'Default', 'kaos-header-footer' )], ['300', __( 'Light', 'kaos-header-footer' )], ['400', __( 'Normal', 'kaos-header-footer' )], ['500', __( 'Medium', 'kaos-header-footer' )], ['600', __( 'Semi-bold', 'kaos-header-footer' )], ['700', __( 'Bold', 'kaos-header-footer' )], ['800', __( 'Extra', 'kaos-header-footer' )]] },
				{ path: 'menu_up', type: 'check', label: __( 'All uppercase', 'kaos-header-footer' ) },
				{ path: 'menu_gap', type: 'number', label: __( 'Space between items (px)', 'kaos-header-footer' ), min: 0, max: 80 },
				{ path: 'menu_cols', type: 'number', label: __( 'Columns in the full-screen menu', 'kaos-header-footer' ), min: 1, max: 3 }
			]),
			section(__( 'Separators between items', 'kaos-header-footer' ), [
				{ path: 'menu_sep', type: 'select', label: __( 'Separator', 'kaos-header-footer' ), options: [['none', __( 'None', 'kaos-header-footer' )], ['bar', __( 'Bar  |', 'kaos-header-footer' )], ['dot', __( 'Dot  •', 'kaos-header-footer' )], ['slash', __( 'Slash  /', 'kaos-header-footer' )], ['dash', __( 'Dash  —', 'kaos-header-footer' )]] },
				{ path: 'c_sep', type: 'color', label: __( 'Separator color', 'kaos-header-footer' ) }
			]),
			section(__( 'Submenu', 'kaos-header-footer' ), [
				{ path: 'sub_style', type: 'select', label: __( 'Style', 'kaos-header-footer' ), options: [['dropdown', __( 'Dropdown', 'kaos-header-footer' )], ['mega', __( 'Column panel (mega menu)', 'kaos-header-footer' )]], help: __( 'In the mega menu, second-level items become columns and third-level items become lists.', 'kaos-header-footer' ) },
				{ path: 'mega_w', type: 'number', label: __( 'Panel width (px)', 'kaos-header-footer' ), min: 400, max: 2000, when: mega },
				{ path: 'caret', type: 'check', label: __( 'Small arrow on items with a submenu', 'kaos-header-footer' ) },
				{ path: 'c_sub_bg', type: 'paint', label: __( 'Submenu background', 'kaos-header-footer' ) },
				{ path: 'c_sub_fg', type: 'color', label: __( 'Submenu text', 'kaos-header-footer' ) },
				{ path: 'c_mega_head', type: 'color', label: __( 'Column headings', 'kaos-header-footer' ), when: mega },
				{ path: 'c_mega_line', type: 'color', label: __( 'Lines between columns', 'kaos-header-footer' ), when: mega }
			]),
			section(__( 'Hover effect', 'kaos-header-footer' ), [
				{ path: 'menu_fx', type: 'select', label: __( 'Item effect', 'kaos-header-footer' ), options: fx },
				{ path: 'menu_fx_c', type: 'color', label: __( 'Effect color', 'kaos-header-footer' ), help: __( 'Line, fill, glow… Empty = accent color.', 'kaos-header-footer' ) },
				{ path: 'mfx_ms', type: 'number', label: __( 'Duration (ms)', 'kaos-header-footer' ), min: 100, max: 1200 }
			]),
			section(__( 'Images on hover', 'kaos-header-footer' ), [
				{ path: 'hover_img', type: 'select', label: __( 'Show an image or a video', 'kaos-header-footer' ), rerender: true, options: [['none', __( 'None', 'kaos-header-footer' )], ['cursor', __( 'Card that follows the cursor', 'kaos-header-footer' )], ['panel', __( 'Panel (full-screen menu) / card under the header', 'kaos-header-footer' )]],
					help: __( 'The image changes every time you hover a menu item.', 'kaos-header-footer' ) },
				{ path: 'hover_img_pos', type: 'seg', label: __( 'Full-screen panel: side', 'kaos-header-footer' ), options: [['left', __( 'Left', 'kaos-header-footer' )], ['right', __( 'Right', 'kaos-header-footer' )]], when: on },
				{ path: 'hover_img_w', type: 'number', label: __( 'Panel: width (%)', 'kaos-header-footer' ), min: 20, max: 60, when: on },
				{ path: 'hover_img_bleed', type: 'check', label: __( 'Full-height panel, no margins', 'kaos-header-footer' ), when: on },
				{ path: 'hover_img_size', type: 'number', label: __( 'Cursor card: width (px)', 'kaos-header-footer' ), min: 120, max: 600, when: on },
				{ node: menuImagesCtl, when: on }
			])
		];
	}

	function burgerSections() {
		var styles = opts(C.burgers);
		var custom = function () { return S.opts.panel_mode === 'custom'; };
		var unitW = [['px', 'px'], ['%', __( '% of the screen', 'kaos-header-footer' )]];
		var unitH = [['vh', __( '% of the screen', 'kaos-header-footer' )], ['px', 'px']];
		return [
			section(__( 'Menu window', 'kaos-header-footer' ), [
				{ path: 'panel_mode', type: 'seg', label: __( 'Size', 'kaos-header-footer' ), rerender: true, options: [['full', __( 'Full screen', 'kaos-header-footer' )], ['custom', __( 'Custom', 'kaos-header-footer' )]], help: __( 'How the menu opens when you press the icon.', 'kaos-header-footer' ) },
				{ path: 'panel_pos', type: 'seg', label: __( 'Opens from', 'kaos-header-footer' ), options: [['left', __( 'Left', 'kaos-header-footer' )], ['right', __( 'Right', 'kaos-header-footer' )]], when: custom },
				{ path: 'panel_w', type: 'number', label: __( 'Width', 'kaos-header-footer' ), min: 200, max: 1600, when: custom },
				{ path: 'panel_wu', type: 'select', label: __( 'Width unit', 'kaos-header-footer' ), options: unitW, when: custom },
				{ path: 'panel_h', type: 'number', label: __( 'Height', 'kaos-header-footer' ), min: 0, max: 2400, when: custom, help: __( '0 = the full available height.', 'kaos-header-footer' ) },
				{ path: 'panel_hu', type: 'select', label: __( 'Height unit', 'kaos-header-footer' ), options: unitH, when: custom },
				{ path: 'panel_r', type: 'number', label: __( 'Rounded corners (px)', 'kaos-header-footer' ), min: 0, max: 120, when: custom },
				{ path: 'panel_off', type: 'number', label: __( 'Distance from the edges (px)', 'kaos-header-footer' ), min: 0, max: 120, when: custom, help: __( 'With a distance the panel becomes a floating card.', 'kaos-header-footer' ) },
				{ path: 'panel_dim', type: 'number', label: __( 'Dim the page (%)', 'kaos-header-footer' ), min: 0, max: 90, when: custom, help: __( 'A click on the dimmed background closes the menu.', 'kaos-header-footer' ) },
				{ path: 'c_panel_bg', type: 'paint', label: __( 'Window background', 'kaos-header-footer' ) },
				{ path: 'c_panel_fg', type: 'color', label: __( 'Window text', 'kaos-header-footer' ) }
			]),
			section(__( 'Full-screen menu content', 'kaos-header-footer' ), [
				{ path: 'panel_t_menu', type: 'text', label: __( 'Small title above the menu', 'kaos-header-footer' ), placeholder: __( 'Discover the pages', 'kaos-header-footer' ) },
				{ path: 'panel_fs', type: 'number', label: __( 'Item size (px)', 'kaos-header-footer' ), min: 0, max: 90, help: __( '0 = large, based on the screen.', 'kaos-header-footer' ) },
				{ path: 'panel_fw', type: 'select', label: __( 'Item weight', 'kaos-header-footer' ), options: [['', __( 'Default', 'kaos-header-footer' )], ['300', __( 'Light', 'kaos-header-footer' )], ['400', __( 'Normal', 'kaos-header-footer' )], ['500', __( 'Medium', 'kaos-header-footer' )], ['600', __( 'Semi-bold', 'kaos-header-footer' )], ['700', __( 'Bold', 'kaos-header-footer' )]] },
				{ path: 'panel_info', type: 'check', label: __( 'Show contacts, social links and legal link', 'kaos-header-footer' ), rerender: false },
				{ path: 'email', type: 'email', label: __( 'Email', 'kaos-header-footer' ), when: function () { return !!S.opts.panel_info; } },
				{ path: 'phone', type: 'text', label: __( 'Phone', 'kaos-header-footer' ), when: function () { return !!S.opts.panel_info; } },
				{ path: 'address', type: 'text', label: __( 'Address', 'kaos-header-footer' ), when: function () { return !!S.opts.panel_info; } },
				{ path: 'address_map', type: 'check', label: __( 'Clickable address (opens maps)', 'kaos-header-footer' ), when: function () { return !!S.opts.panel_info; } },
				{ path: 'address_url', type: 'url', label: __( 'Google Maps link (optional)', 'kaos-header-footer' ), placeholder: 'https://maps.app.goo.gl/…', when: function () { return !!S.opts.panel_info && !!S.opts.address_map; } },
				{ path: 'panel_t_contact', type: 'text', label: __( 'Contacts small title', 'kaos-header-footer' ), placeholder: __( 'Contact us', 'kaos-header-footer' ), when: function () { return !!S.opts.panel_info; } },
				{ path: 'socials', type: 'textarea', label: __( 'Social links (one URL per line)', 'kaos-header-footer' ), placeholder: 'https://instagram.com/…', when: function () { return !!S.opts.panel_info; } },
				{ path: 'social_style', type: 'select', label: __( 'Social links style', 'kaos-header-footer' ), options: [['icons', __( 'Icons only', 'kaos-header-footer' )], ['text', __( 'Text only', 'kaos-header-footer' )], ['both', __( 'Icon and text', 'kaos-header-footer' )]], when: function () { return !!S.opts.panel_info; } },
				{ path: 'panel_t_social', type: 'text', label: __( 'Social links small title', 'kaos-header-footer' ), placeholder: __( 'Follow us', 'kaos-header-footer' ), when: function () { return !!S.opts.panel_info; } },
				{ path: 'panel_legal', type: 'text', label: __( 'Legal link (text)', 'kaos-header-footer' ), placeholder: __( 'Privacy and Terms', 'kaos-header-footer' ), when: function () { return !!S.opts.panel_info; } },
				{ path: 'panel_legal_url', type: 'url', label: __( 'Legal link (address)', 'kaos-header-footer' ), placeholder: 'https://', when: function () { return !!S.opts.panel_info; } }
			]),
			section(__( 'On smartphones', 'kaos-header-footer' ), [
				{ path: 'panel_m_al', type: 'seg', label: __( 'Text alignment', 'kaos-header-footer' ), options: [['l', __( 'Left', 'kaos-header-footer' )], ['c', __( 'Center', 'kaos-header-footer' )], ['r', __( 'Right', 'kaos-header-footer' )]], help: __( 'Applies to menu items, contacts and social links when the full-screen menu opens on a phone.', 'kaos-header-footer' ) },
				{ path: 'panel_m_img', type: 'select', label: __( 'Panel image', 'kaos-header-footer' ), options: [['bg', __( 'Show it as a faded background', 'kaos-header-footer' )], ['none', __( 'Don\'t show it', 'kaos-header-footer' )]], help: __( 'A phone has no room for the side panel: the photo becomes the background behind the text.', 'kaos-header-footer' ), when: function () { return S.opts.hover_img === 'panel'; } }
			]),
			section(__( 'Icon', 'kaos-header-footer' ), [
				{ path: 'burger', type: 'select', label: __( 'Style', 'kaos-header-footer' ), options: styles, help: __( 'It animates into an X (or changes) when the menu opens.', 'kaos-header-footer' ) },
				{ path: 'burger_size', type: 'number', label: __( 'Size (px)', 'kaos-header-footer' ), min: 28, max: 80 },
				{ path: 'burger_w', type: 'number', label: __( 'Thickness (px)', 'kaos-header-footer' ), min: 1, max: 8 },
				{ path: 'burger_shape', type: 'select', label: __( 'Background shape', 'kaos-header-footer' ), options: [['none', __( 'None', 'kaos-header-footer' )], ['circle', __( 'Circle', 'kaos-header-footer' )], ['rounded', __( 'Rounded rectangle', 'kaos-header-footer' )], ['square', __( 'Square', 'kaos-header-footer' )]] }
			]),
			section(__( 'Text next to the icon', 'kaos-header-footer' ), [
				{ path: 'burger_label', type: 'text', label: __( 'Label', 'kaos-header-footer' ), placeholder: __( 'Menu', 'kaos-header-footer' ), help: __( 'Optional. With the "Text only" style it is the only element.', 'kaos-header-footer' ) },
				{ path: 'burger_label_open', type: 'text', label: __( 'Label when the menu is open', 'kaos-header-footer' ), placeholder: __( 'Close', 'kaos-header-footer' ) }
			]),
			section(__( 'Colors', 'kaos-header-footer' ), [
				{ path: 'c_burger', type: 'color', label: __( 'Icon', 'kaos-header-footer' ) },
				{ path: 'c_burger_bg', type: 'paint', label: __( 'Shape background', 'kaos-header-footer' ) }
			])
		];
	}


	var CELL_TYPES = [['empty', __( 'Empty', 'kaos-header-footer' )], ['text', __( 'Text', 'kaos-header-footer' )], ['menu', __( 'Menu', 'kaos-header-footer' )], ['contacts', __( 'Contacts', 'kaos-header-footer' )], ['socials', __( 'Social', 'kaos-header-footer' )], ['image', __( 'Image', 'kaos-header-footer' )], ['logo', __( 'Logo', 'kaos-header-footer' )], ['cta', __( 'Button', 'kaos-header-footer' )]];
	var CELL_BLANK = { t: 'empty', title: '', text: '', menu: 0, img: '', link: '', al: 'l', ff: '', fs: 0, fw: '', c: '' };
	var CELL_SAMPLE = [
		{ t: 'text', title: __( 'About us', 'kaos-header-footer' ), text: __( 'A couple of lines about you: what you do, who you do it for and why.', 'kaos-header-footer' ) },
		{ t: 'menu', title: __( 'Explore', 'kaos-header-footer' ) },
		{ t: 'contacts', title: __( 'Contacts', 'kaos-header-footer' ) },
		{ t: 'socials', title: __( 'Follow us', 'kaos-header-footer' ) }
	];

	function ensureCells() {
		var total = S.opts.grid_cols * S.opts.grid_rows;
		var cells = Array.isArray(S.opts.grid_cells) ? S.opts.grid_cells : [];
		var fresh = !cells.length;
		while (cells.length < total) { cells.push(Object.assign({}, CELL_BLANK, fresh && cells.length < CELL_SAMPLE.length ? CELL_SAMPLE[cells.length] : {})); }
		cells.length = total;
		S.opts.grid_cells = cells;
		if (S.cell >= total) { S.cell = 0; }
		return cells;
	}

	/** Changes rows/columns keeping each cell in its position (row, column). */
	function resizeGrid(cols, rows) {
		var oc = S.opts.grid_cols;
		var old = ensureCells();
		var next = [];
		for (var r = 0; r < rows; r++) {
			for (var c = 0; c < cols; c++) {
				next.push(r < S.opts.grid_rows && c < oc ? old[r * oc + c] : Object.assign({}, CELL_BLANK));
			}
		}
		S.opts.grid_cols = cols;
		S.opts.grid_rows = rows;
		S.opts.grid_cells = next;
		S.cell = 0;
		changed();
		renderProps();
	}

	function gridSizeCtl() {
		var mk = function (key, label) {
			var input = h('input', { type: 'number', min: 1, max: 6, value: S.opts[key], 'aria-label': label,
				onchange: function (e) {
					var n = clampNum(e.target.value, 1, 6);
					e.target.value = n;
					resizeGrid(key === 'grid_cols' ? n : S.opts.grid_cols, key === 'grid_rows' ? n : S.opts.grid_rows);
				} });
			return h('label', { class: 'khe-label khe-inline-label' }, h('span', null, label), input);
		};
		return h('div', { class: 'khe-field khe-two' }, mk('grid_cols', __( 'Columns (1-6)', 'kaos-header-footer' )), mk('grid_rows', __( 'Rows (1-6)', 'kaos-header-footer' )));
	}

	function cellEditor() {
		var cells = ensureCells();
		var i = S.cell;
		var cols = S.opts.grid_cols;
		var p = function (k) { return 'grid_cells.' + i + '.' + k; };
		var t = function () { return cells[i].t; };
		var is = function () { var a = Array.prototype.slice.call(arguments); return function () { return a.indexOf(t()) > -1; }; };
		var fonts = Object.keys(C.fonts).map(function (k) { return [k, C.fonts[k]]; });
		var wrap = h('div', { class: 'khe-celledit' }, h('div', { class: 'khe-section-title', style: 'margin:14px 0 10px' }, (__( 'Cell: row', 'kaos-header-footer' ) + ' ') + (Math.floor(i / cols) + 1) + (__( ', column', 'kaos-header-footer' ) + ' ') + ((i % cols) + 1)));
		var defs = [
			{ path: p('t'), type: 'select', label: __( 'What it contains', 'kaos-header-footer' ), options: CELL_TYPES, rerender: false, onChange: function () { renderProps(); } },
			{ path: p('title'), type: 'text', label: __( 'Title / label', 'kaos-header-footer' ), when: is('text', 'menu', 'contacts', 'socials', 'image', 'cta') },
			{ path: p('text'), type: 'textarea', label: __( 'Text', 'kaos-header-footer' ), placeholder: __( 'Write whatever you like. Bold, italic and links are allowed.', 'kaos-header-footer' ), when: is('text') },
			{ path: p('menu'), type: 'select', label: __( 'Menu', 'kaos-header-footer' ), options: menuOptions(), when: is('menu') },
			{ path: p('img'), type: 'image', store: 'url', label: __( 'Image', 'kaos-header-footer' ), when: is('image') },
			{ path: p('link'), type: 'url', label: __( 'Link', 'kaos-header-footer' ), placeholder: 'https://', when: is('image', 'cta') },
			{ path: p('al'), type: 'seg', label: __( 'Alignment', 'kaos-header-footer' ), options: [['l', __( 'Left', 'kaos-header-footer' )], ['c', __( 'Center', 'kaos-header-footer' )], ['r', __( 'Right', 'kaos-header-footer' )]], when: function () { return t() !== 'empty'; } },
			{ path: p('ff'), type: 'select', label: __( 'Font', 'kaos-header-footer' ), options: fonts, when: is('text', 'menu', 'contacts', 'socials', 'cta') },
			{ path: p('fs'), type: 'number', label: __( 'Size (px)', 'kaos-header-footer' ), min: 0, max: 90, help: __( '0 = same as the footer.', 'kaos-header-footer' ), when: is('text', 'menu', 'contacts', 'socials', 'cta') },
			{ path: p('fw'), type: 'select', label: __( 'Weight', 'kaos-header-footer' ), options: [['', __( 'Default', 'kaos-header-footer' )], ['300', __( 'Light', 'kaos-header-footer' )], ['400', __( 'Normal', 'kaos-header-footer' )], ['500', __( 'Medium', 'kaos-header-footer' )], ['600', __( 'Semi-bold', 'kaos-header-footer' )], ['700', __( 'Bold', 'kaos-header-footer' )], ['800', __( 'Extra', 'kaos-header-footer' )]], when: is('text', 'menu', 'contacts', 'socials') },
			{ path: p('c'), type: 'color', label: __( 'Text color', 'kaos-header-footer' ), when: is('text', 'menu', 'contacts', 'socials') }
		];
		defs.forEach(function (d) { wrap.appendChild(field(d)); });
		applyWhen();
		return wrap;
	}

	function cellsCtl() {
		var wrap = h('div', { class: 'khe-field' });
		var cells = ensureCells();
		var cols = S.opts.grid_cols;
		var names = {};
		CELL_TYPES.forEach(function (c) { names[c[0]] = c[1]; });
		wrap.appendChild(h('div', { class: 'khe-label' }, h('span', null, __( 'Cells (tap to edit)', 'kaos-header-footer' ))));
		wrap.appendChild(h('div', { class: 'khe-cellgrid', style: 'grid-template-columns:repeat(' + cols + ',minmax(0,1fr))' }, cells.map(function (c, i) {
			return h('button', {
				type: 'button', class: 'khe-cellbtn' + (i === S.cell ? ' is-active' : '') + (c.t === 'empty' ? ' is-empty' : ''),
				onclick: function () { S.cell = i; renderProps(); }
			}, h('strong', null, c.t === 'empty' ? '+' : names[c.t]), c.title ? h('small', null, c.title) : null);
		})));
		wrap.appendChild(cellEditor());
		return wrap;
	}

	function gridSections() {
		var glass = function () { return S.opts.cell_style === 'glass'; };
		var styled = function () { return S.opts.cell_style !== 'none'; };
		return [
			section(__( 'Structure', 'kaos-header-footer' ), [
				{ node: gridSizeCtl },
				{ path: 'grid_gap', type: 'number', label: __( 'Space between cells (px)', 'kaos-header-footer' ), min: 0, max: 120 },
				{ path: 'grid_valign', type: 'seg', label: __( 'Vertical alignment', 'kaos-header-footer' ), options: [['start', __( 'Top', 'kaos-header-footer' )], ['center', __( 'Center', 'kaos-header-footer' )], ['end', __( 'Bottom', 'kaos-header-footer' )]] },
				{ path: 'grid_hero', type: 'check', label: __( 'Big headline above the grid', 'kaos-header-footer' ), help: __( 'The headline, phrase and button are written in Content.', 'kaos-header-footer' ) }
			]),
			section(__( 'Cell style', 'kaos-header-footer' ), [
				{ path: 'cell_style', type: 'seg', label: __( 'Style', 'kaos-header-footer' ), options: [['none', __( 'None', 'kaos-header-footer' )], ['card', __( 'Card', 'kaos-header-footer' )], ['glass', __( 'Glass', 'kaos-header-footer' )]] },
				{ path: 'cell_tint', type: 'paint', label: __( 'Background color', 'kaos-header-footer' ), when: styled, help: __( 'Use the picker\'s transparency to choose how much of the background shows.', 'kaos-header-footer' ) },
				{ path: 'cell_blur', type: 'number', label: __( 'Glass blur (px)', 'kaos-header-footer' ), min: 0, max: 60, when: glass },
				{ path: 'cell_r', type: 'number', label: __( 'Rounded corners (px)', 'kaos-header-footer' ), min: 0, max: 60, when: styled },
				{ path: 'cell_pad', type: 'number', label: __( 'Inner spacing (px)', 'kaos-header-footer' ), min: 0, max: 80, when: styled }
			]),
			section(__( 'Cells', 'kaos-header-footer' ), [{ node: cellsCtl }])
		];
	}

	/* ---------- Barra inferiore ---------- */
	var BAR_NAMES = [['logo', __( 'Logo', 'kaos-header-footer' )], ['menu', __( __( 'Menu 1', 'kaos-header-footer' ), 'kaos-header-footer' )], ['menu2', __( __( 'Menu 2', 'kaos-header-footer' ), 'kaos-header-footer' )], ['social', __( 'Social', 'kaos-header-footer' )], ['copy', __( 'Copyright', 'kaos-header-footer' )]];

	function effectiveBar() {
		var def = C.barDefaults[S.opts.preset_footer] || C.barDefaults.columns;
		var cur = (S.opts.bar_layout && !Array.isArray(S.opts.bar_layout)) ? S.opts.bar_layout : {};
		var out = {};
		BAR_NAMES.forEach(function (n) { out[n[0]] = Object.assign({ on: 0, z: 'c', o: 0, dx: 0 }, def[n[0]] || {}, cur[n[0]] || {}); });
		return out;
	}

	function barRow(name, label) {
		var eff = effectiveBar();
		var row = eff[name];
		var commit = function () { S.opts.bar_layout = eff; changed(); };
		var sw = h('span', { class: 'khe-switch' + (row.on ? ' is-on' : ''), role: 'switch', 'aria-checked': row.on ? 'true' : 'false', tabindex: '0',
			onclick: function () { row.on = row.on ? 0 : 1; sw.classList.toggle('is-on', !!row.on); sw.setAttribute('aria-checked', row.on ? 'true' : 'false'); commit(); },
			onkeydown: function (e) { if (e.key === ' ' || e.key === 'Enter') { e.preventDefault(); sw.click(); } } });
		var seg = h('div', { class: 'khe-seg' });
		var paint = function () {
			seg.innerHTML = '';
			[['l', __( 'L', 'kaos-header-footer' )], ['c', __( 'Center', 'kaos-header-footer' )], ['r', __( 'R', 'kaos-header-footer' )]].forEach(function (z) {
				seg.appendChild(h('button', { type: 'button', class: row.z === z[0] ? 'is-active' : '', onclick: function () { row.z = z[0]; paint(); commit(); } }, z[1]));
			});
		};
		paint();
		var num = function (key, min, max, title) {
			return h('input', { type: 'number', min: min, max: max, value: row[key], title: title, 'aria-label': title + ' ' + label,
				oninput: function (e) { row[key] = clampNum(e.target.value, min, max); commit(); },
				onchange: function (e) { row[key] = clampNum(e.target.value, min, max); e.target.value = row[key]; commit(); } });
		};
		return h('div', { class: 'khe-barrow' }, sw, h('span', { class: 'khe-zonename' }, label), seg, num('o', -9, 9, __( 'Order', 'kaos-header-footer' )), num('dx', -800, 800, __( 'Shift px', 'kaos-header-footer' )));
	}

	function barSections() {
		return [
			collapsible(__( 'Bar elements', 'kaos-header-footer' ), [
				h('p', { class: 'khe-help', style: 'margin:0 0 10px' }, __( 'Turn the elements on and choose the zone (left, center, right), order and pixel shift, as in the header.', 'kaos-header-footer' )),
				h('div', { class: 'khe-barhead' }, h('span', null, ''), h('span', null, __( 'Element', 'kaos-header-footer' )), h('span', null, __( 'Zone', 'kaos-header-footer' )), h('span', null, __( 'Order', 'kaos-header-footer' )), h('span', null, __( 'Shift px', 'kaos-header-footer' ))),
				BAR_NAMES.map(function (n) { return barRow(n[0], n[1]); })
			]),
			section(__( 'Spacing', 'kaos-header-footer' ), [
				{ path: 'bar_gap', type: 'number', label: __( 'Space between elements (px)', 'kaos-header-footer' ), min: 0, max: 200 }
			]),
			h('p', { class: 'khe-hint' }, __( 'Choose the two menus in Content (Menu and Menu 2).', 'kaos-header-footer' ))
		];
	}

	/* ---------- Footer links ---------- */
	function linksSections() {
		return [
			section(__( 'On hover', 'kaos-header-footer' ), [
				{ path: 'fmenu_fx', type: 'select', label: __( 'Link effect', 'kaos-header-footer' ), options: opts(C.fmenuFx) }
			]),
			section(__( 'Link text', 'kaos-header-footer' ), [
				{ path: 'menu_fs', type: 'number', label: __( 'Size (px)', 'kaos-header-footer' ), min: 0, max: 40, help: __( '0 = same as the footer.', 'kaos-header-footer' ) },
				{ path: 'menu_fw', type: 'select', label: __( 'Weight', 'kaos-header-footer' ), options: [['', __( 'Default', 'kaos-header-footer' )], ['300', __( 'Light', 'kaos-header-footer' )], ['400', __( 'Normal', 'kaos-header-footer' )], ['500', __( 'Medium', 'kaos-header-footer' )], ['600', __( 'Semi-bold', 'kaos-header-footer' )], ['700', __( 'Bold', 'kaos-header-footer' )]] },
				{ path: 'menu_up', type: 'check', label: __( 'All uppercase', 'kaos-header-footer' ) },
				{ path: 'menu_gap', type: 'number', label: __( 'Space between inline items (px)', 'kaos-header-footer' ), min: 0, max: 80 },
				{ path: 'menu_sep', type: 'select', label: __( 'Separator between inline items', 'kaos-header-footer' ), options: [['none', __( 'None', 'kaos-header-footer' )], ['bar', __( 'Bar  |', 'kaos-header-footer' )], ['dot', __( 'Dot  •', 'kaos-header-footer' )], ['slash', __( 'Slash  /', 'kaos-header-footer' )], ['dash', __( 'Dash  —', 'kaos-header-footer' )]] }
			])
		];
	}

	/* ---------- Freccia "torna su" ---------- */
	function bttSections() {
		var custom = function () { return S.opts.btt_mode === 'custom'; };
		var fixed = function () { return S.opts.btt_mode === 'custom' && S.opts.fb_place === 'fixed'; };
		return [
			section(__( 'Arrow', 'kaos-header-footer' ), [
				{ path: 'btt_mode', type: 'seg', label: __( '"Back to top" arrow', 'kaos-header-footer' ), options: [['global', __( 'As in the settings', 'kaos-header-footer' )], ['custom', __( 'Custom', 'kaos-header-footer' )], ['off', __( 'Hidden', 'kaos-header-footer' )]] }
			]),
			section(__( 'Position', 'kaos-header-footer' ), [
				{ path: 'fb_place', type: 'seg', label: __( 'Where it sits', 'kaos-header-footer' ), options: [['fixed', __( 'Fixed on the screen', 'kaos-header-footer' )], ['footer', __( 'Inside the footer', 'kaos-header-footer' )]], when: custom },
				{ path: 'fb_pos', type: 'seg', label: __( 'Horizontal', 'kaos-header-footer' ), options: [['l', __( 'Left', 'kaos-header-footer' )], ['c', __( 'Center', 'kaos-header-footer' )], ['r', __( 'Right', 'kaos-header-footer' )]], when: custom },
				{ path: 'fb_x', type: 'number', label: __( 'Distance from the edge (px)', 'kaos-header-footer' ), min: -800, max: 800, when: custom, help: __( 'When centered, this is the shift to the right (+) or left (−).', 'kaos-header-footer' ) },
				{ path: 'fb_y', type: 'number', label: __( 'Distance from the bottom (px)', 'kaos-header-footer' ), min: 0, max: 800, when: custom },
				{ path: 'fb_after', type: 'number', label: __( 'Appears after (px of scroll)', 'kaos-header-footer' ), min: 0, max: 5000, when: fixed }
			]),
			section(__( 'Style', 'kaos-header-footer' ), [
				{ path: 'fb_icon', type: 'select', label: __( 'Icon', 'kaos-header-footer' ), options: [['arrow', __( 'Arrow', 'kaos-header-footer' )], ['chevron', __( 'Chevron', 'kaos-header-footer' )], ['double', __( 'Double chevron', 'kaos-header-footer' )], ['caret', __( 'Triangle', 'kaos-header-footer' )], ['text', __( 'Text only', 'kaos-header-footer' )]], when: custom },
				{ path: 'fb_text', type: 'text', label: __( 'Text', 'kaos-header-footer' ), placeholder: __( 'Up', 'kaos-header-footer' ), when: custom },
				{ path: 'fb_size', type: 'number', label: __( 'Size (px)', 'kaos-header-footer' ), min: 28, max: 96, when: custom },
				{ path: 'fb_r', type: 'number', label: __( 'Rounding (%)', 'kaos-header-footer' ), min: 0, max: 50, when: custom },
				{ path: 'fb_bg', type: 'paint', label: __( 'Background', 'kaos-header-footer' ), when: custom },
				{ path: 'fb_fg', type: 'color', label: __( 'Icon and text', 'kaos-header-footer' ), when: custom },
				{ path: 'fb_bd', type: 'color', label: __( 'Border', 'kaos-header-footer' ), when: custom },
				{ path: 'fb_hover', type: 'select', label: __( 'On hover', 'kaos-header-footer' ), options: [['lift', __( 'Lifts up', 'kaos-header-footer' )], ['rotate', __( 'Rotates', 'kaos-header-footer' )], ['none', __( 'No movement', 'kaos-header-footer' )]], when: custom }
			]),
			h('p', { class: 'khe-hint' }, __( 'With "As in the settings" the global arrow from the dashboard applies; with "Hidden" this footer shows none.', 'kaos-header-footer' ))
		];
	}

	function typeSections() {
		var fonts = Object.keys(C.fonts).map(function (k) { return [k, C.fonts[k]]; }).concat([['custom', __( 'Custom (name of a font from fonts.bunny.net)', 'kaos-header-footer' )]]);
		var isCustom = function (key) { return function () { return S.opts[key] === 'custom'; }; };
		return [
			section(__( 'Fonts', 'kaos-header-footer' ), [
				{ path: 'font_family', type: 'select', label: __( 'General font', 'kaos-header-footer' ), options: fonts },
				{ path: 'font_custom', type: 'text', label: __( 'Font name', 'kaos-header-footer' ), placeholder: __( 'E.g. Space Grotesk', 'kaos-header-footer' ), when: isCustom('font_family') },
				{ path: 'font_head', type: 'select', label: __( 'Logo and headings font', 'kaos-header-footer' ), options: fonts, help: __( 'Optional: if empty it uses the general one.', 'kaos-header-footer' ) },
				{ path: 'font_head_custom', type: 'text', label: __( 'Headings font name', 'kaos-header-footer' ), placeholder: __( 'E.g. Playfair Display', 'kaos-header-footer' ), when: isCustom('font_head') },
				{ path: 'font_size', type: 'number', label: __( 'Base text size (px)', 'kaos-header-footer' ), min: 0, max: 40, help: __( '0 = same as the theme. The size and weight of menu items are in "Menu and images".', 'kaos-header-footer' ) }
			]),
			h('p', { class: 'khe-hint' }, __( 'Fonts are loaded as set in the plugin settings (by default they are hosted on your site). "System" downloads nothing.', 'kaos-header-footer' ))
		];
	}

	function colorSections() {
		var out = [];
		var f = [
			{ path: 'color_bg', type: 'paint', label: __( 'Background', 'kaos-header-footer' ), help: isBuilder() ? __( 'Empty = transparent.', 'kaos-header-footer' ) : __( 'Empty = use the mood. You can use a gradient with several colors.', 'kaos-header-footer' ) },
			{ path: 'color_fg', type: 'color', label: __( 'Text', 'kaos-header-footer' ) }
		];
		if (isFooter()) {
			f.splice(1, 0, { path: 'color_bg2', type: 'color', label: __( 'Background 2 (automatic gradient)', 'kaos-header-footer' ), when: function () { return !/-gradient\(/.test(String(S.opts.color_bg || '')); } });
			f.push({ path: 'grad_angle', type: 'number', label: __( 'Automatic gradient angle (°)', 'kaos-header-footer' ), min: 0, max: 360, when: function () { return !/-gradient\(/.test(String(S.opts.color_bg || '')); } });
		}
		if (!isBuilder()) { f.push({ path: 'color_accent', type: 'color', label: __( 'Accent', 'kaos-header-footer' ) }); }
		out.push(section(isFooter() ? __( 'Background and text', 'kaos-header-footer' ) : __( 'Base background and text', 'kaos-header-footer' ), f));

		if (!isFooter()) {
			out.push(section(__( 'Transparent header and on scroll', 'kaos-header-footer' ), [
				{ path: 'fx.overlay', type: 'check', label: __( 'Transparent over the content', 'kaos-header-footer' ), help: __( 'The header takes no space and the hero shows underneath until you scroll.', 'kaos-header-footer' ) },
				{ path: 'fx.bg0', type: 'paint', label: __( 'Background at the top', 'kaos-header-footer' ), help: __( 'Empty = transparent when overlaid.', 'kaos-header-footer' ) },
				{ path: 'fx.fg0', type: 'color', label: __( 'Text at the top', 'kaos-header-footer' ), help: __( 'Color of the logo, menu and icon before scrolling.', 'kaos-header-footer' ) },
				{ path: 'fx.bg1', type: 'paint', label: __( 'Background after scroll', 'kaos-header-footer' ) },
				{ path: 'fx.fg1', type: 'color', label: __( 'Text after scroll', 'kaos-header-footer' ) }
			]));
		}

		if (!isBuilder() && !isFooter()) {
			out.push(section(__( 'Menu items', 'kaos-header-footer' ), [
				{ path: 'c_menu', type: 'color', label: __( 'Item text', 'kaos-header-footer' ) },
				{ path: 'c_menu_h', type: 'color', label: __( 'Text on hover / active item', 'kaos-header-footer' ) },
				{ path: 'c_pill', type: 'paint', label: __( 'Pill background', 'kaos-header-footer' ), help: __( 'Used by the "Pill" effect.', 'kaos-header-footer' ) }
			]));
			out.push(section(__( 'Button', 'kaos-header-footer' ), [
				{ path: 'c_cta_bg', type: 'paint', label: __( 'Background', 'kaos-header-footer' ) },
				{ path: 'c_cta_fg', type: 'color', label: __( 'Text', 'kaos-header-footer' ) }
			]));
			out.push(section(__( 'Submenus and mobile menu', 'kaos-header-footer' ), [
				{ path: 'c_sub_bg', type: 'paint', label: __( 'Submenu background', 'kaos-header-footer' ) },
				{ path: 'c_sub_fg', type: 'color', label: __( 'Submenu text', 'kaos-header-footer' ) },
				{ path: 'c_panel_bg', type: 'paint', label: __( 'Mobile panel background', 'kaos-header-footer' ) },
				{ path: 'c_panel_fg', type: 'color', label: __( 'Mobile panel text', 'kaos-header-footer' ) }
			]));
		}

		if (!isBuilder() && isFooter()) {
			out.push(section(__( 'Links and headings', 'kaos-header-footer' ), [
				{ path: 'c_link', type: 'color', label: __( 'Link', 'kaos-header-footer' ) },
				{ path: 'c_link_h', type: 'color', label: __( 'Link on hover', 'kaos-header-footer' ) },
				{ path: 'c_heading', type: 'color', label: __( 'Column headings', 'kaos-header-footer' ) },
				{ path: 'c_line', type: 'color', label: __( 'Lines and borders', 'kaos-header-footer' ) }
			]));
			out.push(section(__( 'Button and social links', 'kaos-header-footer' ), [
				{ path: 'c_cta_bg', type: 'paint', label: __( 'Button / social links background on hover', 'kaos-header-footer' ) },
				{ path: 'c_cta_fg', type: 'color', label: __( 'Button / social links text on hover', 'kaos-header-footer' ) }
			]));
		}
		return out;
	}

	function fxSections() {
		var out = [];
		Object.keys(C.schema).forEach(function (gk) {
			var g = C.schema[gk];
			var fields = Object.keys(g.fields).map(function (k) { return schemaField(k, g.fields[k]); });
			if (gk === 'glass') { fields.unshift({ node: glassPresetsCtl }); }
			out.push(section(g.label, fields));
		});
		return out;
	}

	function rulesCtl() {
		var wrap = h('div', { class: 'khe-field' });
		var targets = [['id', __( 'A specific page or post…', 'kaos-header-footer' )], ['front', __( __( 'Home page', 'kaos-header-footer' ), 'kaos-header-footer' )], ['blog', __( 'Blog page', 'kaos-header-footer' )], ['archive', __( 'Archives', 'kaos-header-footer' )], ['search', __( 'Search results', 'kaos-header-footer' )], ['404', __( '404 page', 'kaos-header-footer' )]]
			.concat(C.conditions.filter(function (c) { return String(c.key).indexOf('pt:') === 0; }).map(function (c) { return [c.key, c.label]; }));
		var timer = 0;

		function searchBox(rule) {
			var results = h('div', { class: 'khe-results' });
			var input = h('input', { type: 'text', placeholder: rule.id ? __( 'Change page…', 'kaos-header-footer' ) : __( 'Search by title or enter the ID…', 'kaos-header-footer' ), 'aria-label': __( 'Search page or post', 'kaos-header-footer' ) });
			input.addEventListener('input', function () {
				clearTimeout(timer);
				var q = input.value.trim();
				if (q.length < 1) { results.innerHTML = ''; return; }
				timer = setTimeout(function () {
					api('/search?q=' + encodeURIComponent(q)).then(function (list) {
						results.innerHTML = '';
						if (!list.length) { results.appendChild(h('div', { class: 'khe-result-empty' }, __( 'No results', 'kaos-header-footer' ))); }
						list.forEach(function (p) {
							results.appendChild(h('button', {
								type: 'button', class: 'khe-result',
								onclick: function () { rule.id = p.id; rule.title = p.title; changed(); paint(); }
							}, h('strong', null, p.title), h('small', null, p.type + ' · ID ' + p.id)));
						});
					})['catch'](function () { results.innerHTML = ''; });
				}, 250);
			});
			var chip = rule.id ? h('div', { class: 'khe-chosen' }, icon('admin-page'), h('span', null, (rule.title || ('#' + rule.id)) + '  (ID ' + rule.id + ')')) : null;
			return h('div', { class: 'khe-search' }, chip, input, results);
		}

		function paint() {
			wrap.innerHTML = '';
			if (!Array.isArray(S.opts.cond_rules)) { S.opts.cond_rules = []; }
			var rules = S.opts.cond_rules;
			var hasOnly = rules.some(function (r) { return r.m === 'only'; });
			wrap.appendChild(h('div', { class: 'khe-label' }, h('span', null, __( 'Where to see it', 'kaos-header-footer' ))));
			wrap.appendChild(h('div', { class: 'khe-seg', style: 'margin-bottom:10px' },
				h('button', { type: 'button', class: hasOnly ? '' : 'is-active', onclick: function () {
					S.opts.cond_rules = rules.filter(function (r) { return r.m !== 'only'; });
					changed(); paint();
				} }, __( 'Entire site', 'kaos-header-footer' )),
				h('button', { type: 'button', class: hasOnly ? 'is-active' : '', onclick: function () {
					if (!hasOnly) { rules.push({ m: 'only', t: 'id', id: 0, title: '' }); changed(); paint(); }
				} }, __( 'Only on some pages', 'kaos-header-footer' ))));
			wrap.appendChild(h('p', { class: 'khe-help', style: 'margin:0 0 10px' }, hasOnly
				? __( '"Only on" limits the template to those pages; "Except on" removes it from those pages.', 'kaos-header-footer' )
				: __( 'The template shows on the whole site. Add "Except on…" to exclude pages, or choose "Only on some pages" to limit it.', 'kaos-header-footer' )));
			rules.forEach(function (r, i) {
				var mode = h('div', { class: 'khe-seg' },
					[['only', __( 'Only on', 'kaos-header-footer' )], ['except', __( 'Except on', 'kaos-header-footer' )]].map(function (m) {
						return h('button', { type: 'button', class: r.m === m[0] ? 'is-active' : '', onclick: function () { r.m = m[0]; changed(); paint(); } }, m[1]);
					}));
				var target = h('select', { 'aria-label': __( 'Where', 'kaos-header-footer' ), onchange: function (e) { r.t = e.target.value; if (r.t !== 'id') { r.id = 0; r.title = ''; } changed(); paint(); } },
					targets.map(function (t) { return h('option', { value: t[0], selected: r.t === t[0] ? true : null }, t[1]); }));
				var del = h('button', { type: 'button', class: 'khe-x', title: __( 'Delete the rule', 'kaos-header-footer' ), 'aria-label': __( 'Delete the rule', 'kaos-header-footer' ), onclick: function () { rules.splice(i, 1); changed(); paint(); } }, icon('trash'));
				wrap.appendChild(h('div', { class: 'khe-rule' }, h('div', { class: 'khe-rule-head' }, mode, del), target, r.t === 'id' ? searchBox(r) : null));
			});
			wrap.appendChild(h('button', {
				type: 'button', class: 'khe-btn', onclick: function () {
					rules.push({ m: hasOnly ? 'only' : 'except', t: 'id', id: 0, title: '' });
					changed(); paint();
				}
			}, icon('plus-alt2'), (' ' + __( 'Add rule', 'kaos-header-footer' ))));
		}
		paint();
		return wrap;
	}

	function showSections() {
		return [
			section(__( 'Display rules', 'kaos-header-footer' ), [
				{ node: rulesCtl },
				{ path: 'priority', type: 'number', label: __( 'Priority', 'kaos-header-footer' ), min: 0, max: 999, help: __( 'If several templates match, the one with the highest priority wins.', 'kaos-header-footer' ) }
			])
		];
	}

	function renderProps() {
		rows = [];
		$.right.innerHTML = '';
		var meta = sectionsFor().filter(function (s) { return s[0] === S.section; })[0] || sectionsFor()[0];
		var body;
		switch (meta[0]) {
			case 'content': body = contentSections(); break;
			case 'colors': body = colorSections(); break;
			case 'grid': body = gridSections(); break;
			case 'bar': body = barSections(); break;
			case 'links': body = linksSections(); break;
			case 'btt': body = bttSections(); break;
			case 'type': body = typeSections(); break;
			case 'menu': body = menuSections(); break;
			case 'burger': body = burgerSections(); break;
			case 'fx': body = fxSections(); break;
			case 'show': body = showSections(); break;
			default: body = layoutSections();
		}
		$.right.appendChild(h('div', { class: 'khe-props' },
			h('div', { class: 'khe-props-head' }, icon(meta[1]), h('strong', null, meta[2]),
				h('span', { class: 'khe-fold-all' },
					h('button', { type: 'button', class: 'khe-btn khe-icon', title: __( 'Collapse all', 'kaos-header-footer' ), 'aria-label': __( 'Collapse all', 'kaos-header-footer' ), onclick: function () { foldAll(true); } }, icon('arrow-up-alt2')),
					h('button', { type: 'button', class: 'khe-btn khe-icon', title: __( 'Expand all', 'kaos-header-footer' ), 'aria-label': __( 'Expand all', 'kaos-header-footer' ), onclick: function () { foldAll(false); } }, icon('arrow-down-alt2')))),
			body));
		applyWhen();
	}

	function renderNav() {
		$.left.innerHTML = '';
		var nav = h('div', { class: 'khe-nav' }, sectionsFor().map(function (s) {
			return h('button', {
				type: 'button', class: 'khe-nav-item' + (S.section === s[0] ? ' is-active' : ''),
				onclick: function () { S.section = s[0]; renderNav(); renderProps(); }
			}, icon(s[1]), s[2]);
		}));
		$.left.appendChild(h('div', { class: 'khe-panel-title' }, isFooter() ? __( 'Footer', 'kaos-header-footer' ) : __( 'Header', 'kaos-header-footer' )));
		$.left.appendChild(nav);
		$.left.appendChild(h('div', { class: 'khe-tip' },
			h('strong', null, __( 'Tips', 'kaos-header-footer' )),
			(__( 'Scroll the preview to see sticky, transparency and the footer coming in.', 'kaos-header-footer' ) + ' '),
			h('br'), h('strong', { style: 'display:inline' }, 'Ctrl/Cmd + S'), (' ' + __( 'saves.', 'kaos-header-footer' ) + ' '),
			h('br'), (__( 'Sections with class', 'kaos-header-footer' ) + ' '), h('code', null, 'khf-on-dark'), ' / ', h('code', null, 'khf-on-light'), (' ' + __( 'turn on the adaptive colors.', 'kaos-header-footer' ))));
	}

	function renderAll() {
		if (!sectionsFor().some(function (s) { return s[0] === S.section; })) { S.section = 'layout'; }
		renderNav();
		renderProps();
		updateTopbar();
	}

	/* ---------- Barra superiore ---------- */

	function updateTopbar() {
		if (!$.save) { return; }
		$.save.classList.toggle('is-dirty', S.dirty);
		$.save.disabled = S.saving;
		$.save.textContent = S.saving ? __( 'Saving…', 'kaos-header-footer' ) : __( 'Save', 'kaos-header-footer' );
		$.kind.textContent = (isFooter() ? __( 'Footer', 'kaos-header-footer' ) : __( 'Header', 'kaos-header-footer' )) + (isBuilder() ? ' · Elementor' : '');
		$.status.classList.toggle('is-on', S.status === 'publish');
		$.statusLabel.textContent = S.status === 'publish' ? __( 'Active', 'kaos-header-footer' ) : __( 'Inactive', 'kaos-header-footer' );
		$.elementorBtn.style.display = isBuilder() && S.elementorUrl ? '' : 'none';
		fitFrame();
		Array.prototype.forEach.call($.devices.children, function (b) { b.classList.toggle('is-active', b.getAttribute('data-d') === S.device); });
	}

	function editWithElementor() {
		save().then(function () { window.location.href = S.elementorUrl; })['catch'](function () {});
	}

	function build() {
		$.title = h('input', { type: 'text', class: 'khe-title', value: S.title, 'aria-label': __( 'Template name', 'kaos-header-footer' ), oninput: function (e) { S.title = e.target.value; S.dirty = true; updateTopbar(); } });
		$.kind = h('span', { class: 'khe-kind' });
		$.devices = h('div', { class: 'khe-seg' }, [['desktop', 'desktop', __( 'Desktop', 'kaos-header-footer' )], ['tablet', 'tablet', __( 'Tablet', 'kaos-header-footer' )], ['mobile', 'smartphone', __( 'Mobile', 'kaos-header-footer' )]].map(function (d) {
			return h('button', { type: 'button', 'data-d': d[0], title: d[2], 'aria-label': d[2], onclick: function () { S.device = d[0]; updateTopbar(); } }, icon(d[1]));
		}));
		$.statusLabel = h('span', null);
		$.status = h('span', { class: 'khe-switch' });
		var live = h('label', { class: 'khe-live', tabindex: '0', title: __( 'An inactive template does not show on the site', 'kaos-header-footer' ),
			onclick: function () { S.status = S.status === 'publish' ? 'draft' : 'publish'; S.dirty = true; updateTopbar(); },
			onkeydown: function (e) { if (e.key === ' ' || e.key === 'Enter') { e.preventDefault(); this.click(); } } }, $.status, $.statusLabel);
		$.previewBtn = h('button', {
			type: 'button', class: 'khe-btn', title: __( 'Save and open a page of the site with this template', 'kaos-header-footer' ),
			onclick: function () { save().then(function () { window.open(S.liveUrl, '_blank'); })['catch'](function () {}); }
		}, icon('external'), (' ' + __( 'On the site', 'kaos-header-footer' )));
		$.elementorBtn = h('button', { type: 'button', class: 'khe-btn khe-ghost', onclick: editWithElementor }, icon('edit'), ' Elementor');
		$.save = h('button', { type: 'button', class: 'khe-btn khe-primary', onclick: function () { save().catch(function () {}); } }, __( 'Save', 'kaos-header-footer' ));

		var top = h('div', { class: 'khe-top' },
			h('a', { class: 'khe-btn khe-icon', href: C.adminUrl, title: __( 'Back to the dashboard', 'kaos-header-footer' ), 'aria-label': __( 'Back to the dashboard', 'kaos-header-footer' ) }, icon('arrow-left-alt')),
			h('img', { class: 'khe-mark', src: C.markUrl, alt: '' }),
			$.title, $.kind,
			h('div', { class: 'khe-group' }, $.devices),
			h('div', { class: 'khe-spacer' }),
			live,
			$.elementorBtn,
			$.previewBtn, $.save);

		$.left = h('aside', { class: 'khe-left' });
		$.right = h('aside', { class: 'khe-right' });
		$.busy = h('span', { class: 'khe-busy' });
		$.iframe = h('iframe', { title: __( 'Preview', 'kaos-header-footer' ), src: 'about:blank' });
		$.frame = h('div', { class: 'khe-frame' }, $.iframe, $.busy);
		$.center = h('div', { class: 'khe-center' }, $.frame);

		root.innerHTML = '';
		root.append(top, h('div', { class: 'khe-main' }, $.left, $.center, $.right));
		if (window.ResizeObserver) { new ResizeObserver(fitFrame).observe($.center); }
	}

	/* ---------- Avvio ---------- */

	api('/templates/' + S.id).then(function (t) {
		S.title = t.title;
		S.status = t.status;
		S.opts = t.opts;
		S.logoUrl = t.logoUrl;
		S.elementorUrl = t.elementor;
		S.liveUrl = t.liveUrl;
		build();
		renderAll();
		schedulePreview(true);
	})['catch'](function (err) {
		root.innerHTML = '';
		root.appendChild(h('div', { class: 'khe-loading' }, (__( 'Could not open the template:', 'kaos-header-footer' ) + ' ') + err.message + ' ', h('a', { href: C.adminUrl }, __( 'Back to the dashboard', 'kaos-header-footer' ))));
	});
})();
