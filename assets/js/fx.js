/* KaosH&F — scroll effects engine (vanilla JS, no dependencies) */
(function () {
	'use strict';

	var d = document;
	var root = d.documentElement;
	var cfg = window.khfCfg || {};
	var tools = cfg.tools || {};
	var bp = cfg.bp || [1024, 767];

	// Nell'editor visuale (Elementor, Divi) gli effetti restano spenti.
	if (/elementor-editor|et-fb|et_fb/.test(d.body.className + ' ' + root.className + ' ' + location.search)) {
		return;
	}

	var EASE = {
		smooth: 'cubic-bezier(.22,.8,.2,1)',
		snappy: 'cubic-bezier(.4,0,.2,1)',
		bounce: 'cubic-bezier(.34,1.56,.64,1)',
		linear: 'linear'
	};
	var SHADOW = {
		soft: '0 6px 20px -10px rgba(0,0,0,.25)',
		medium: '0 10px 34px -16px rgba(0,0,0,.4)',
		strong: '0 18px 50px -14px rgba(0,0,0,.55)'
	};
	var LOGO = '.custom-logo,.site-logo img,.elementor-widget-theme-site-logo img,.khf-logo__img,.et_pb_menu__logo img,#logo';
	var FX_CLASSES = ['khf-fx', 'fx-gl', 'fx-ga', 'fx-ov', 'fx-bg0', 'fx-bg1', 'fx-op', 'fx-sh', 'fx-bf', 'fx-fl', 'fx-pad', 'fx-fg', 'fx-tone', 'fx-ls', 'is-stuck', 'is-hidden', 'is-on-dark', 'is-on-light'];

	var items = [];
	var lastY = 0;
	var ticking = false;
	var device = '';

	function scrollY() {
		return window.pageYOffset || root.scrollTop || 0;
	}

	function n(v, fallback) {
		return typeof v === 'number' ? v : fallback;
	}

	function query(sel, ctx) {
		try { return Array.prototype.slice.call((ctx || d).querySelectorAll(sel)); } catch (e) { return []; }
	}

	function currentDevice() {
		var w = window.innerWidth;
		return w <= bp[1] ? 'mobile' : (w <= bp[0] ? 'tablet' : 'desktop');
	}

	/* ------------------------------------------------------------------ */
	/* Registering the elements                                          */
	/* ------------------------------------------------------------------ */
	function register(el, c) {
		if (!c || el.__khf) {
			return;
		}
		el.__khf = true;
		items.push({
			el: el, c: c, on: false, stuck: false, smartHidden: false, released: false,
			y: scrollY(), acc: 0, h0: 0, gap: 0, spacer: null, logos: [], bar: null,
			io: null, until: null, vars: []
		});
	}

	/* ------------------------------------------------------------------ */
	/* Turning an element on / off                                       */
	/* ------------------------------------------------------------------ */
	function setVar(it, name, value) {
		it.el.style.setProperty(name, value);
		if (it.vars.indexOf(name) < 0) { it.vars.push(name); }
	}

	function flag(it, cls, on) {
		it.el.classList.toggle(cls, !!on);
	}

	function paint(it) {
		var c = it.c;
		var el = it.el;
		var mode = c.mode || 'fixed';
		var sticky = mode !== 'none';

		setVar(it, '--fx-d', n(c.dur, 350) + 'ms');
		setVar(it, '--fx-e', EASE[c.ease] || EASE.smooth);
		el.setAttribute('data-mode', mode);
		el.setAttribute('data-anim', c.anim || 'slide');
		flag(it, 'fx-ov', c.overlay);

		// Sfondi.
		var bg0 = c.bg0 || (c.overlay ? 'transparent' : '');
		if (bg0) { setVar(it, '--fx-bg0', bg0); }
		flag(it, 'fx-bg0', bg0);
		if (c.bg1) { setVar(it, '--fx-bg1', c.bg1); }
		flag(it, 'fx-bg1', c.bg1);

		// Text color: the base is the color computed before applying the classes.
		if (c.fg0 || c.fg1 || c.tone) {
			var base = c.fg0 || getComputedStyle(el).color;
			setVar(it, '--fx-fg0', base);
			setVar(it, '--fx-fg1', c.fg1 || base);
			if (c.tone) {
				setVar(it, '--fx-fgd', c.tone_d || '#ffffff');
				setVar(it, '--fx-fgl', c.tone_l || '#111111');
			}
			flag(it, 'fx-fg', true);
			flag(it, 'fx-tone', c.tone);
		}

		// Opacity.
		if (c.op0 != null || c.op1 != null) {
			setVar(it, '--fx-o0', n(c.op0, 100) / 100);
			setVar(it, '--fx-o1', n(c.op1, 100) / 100);
			flag(it, 'fx-op', true);
		}

		// Ombra e bordo (condividono box-shadow).
		var sh = [];
		if (SHADOW[c.shadow]) { sh.push(SHADOW[c.shadow]); }
		if (c.border) { sh.push('0 ' + c.border + 'px 0 0 ' + (c.border_c || 'rgba(0,0,0,.12)')); }
		if (sh.length) { setVar(it, '--fx-sh', sh.join(',')); }
		flag(it, 'fx-sh', sh.length);

		// Vetro.
		var bf = [];
		if (c.blur) { bf.push('blur(' + c.blur + 'px)'); }
		if (c.sat != null && c.sat !== 100) { bf.push('saturate(' + c.sat + '%)'); }
		if (c.bright != null && c.bright !== 100) { bf.push('brightness(' + c.bright + '%)'); }
		if (c.gray) { bf.push('grayscale(' + c.gray + '%)'); }
		if (bf.length) { setVar(it, '--fx-bf', bf.join(' ')); }
		flag(it, 'fx-bf', bf.length);

		// Liquid glass: tint, edge reflections, shine, grain, depth and (in Chromium) refraction.
		flag(it, 'fx-gl', c.glass);
		flag(it, 'fx-ga', c.glass && c.g_always);
		if (c.glass) { paintGlass(it, bf, sh); }

		// Isola fluttuante.
		var floaty = sticky && (c.inset || c.gap || c.radius);
		if (floaty) {
			setVar(it, '--fx-in', n(c.inset, 0) + 'px');
			setVar(it, '--fx-g', n(c.gap, 0) + 'px');
			setVar(it, '--fx-r', n(c.radius, 0) + 'px');
		}
		flag(it, 'fx-fl', floaty);

		// Logo.
		it.logos = query(c.logo || LOGO, el);
		it.logos.forEach(function (img) {
			img.classList.add('khf-logo-t');
			if (img.tagName === 'IMG' && !img.hasAttribute('data-khf-o')) {
				img.setAttribute('data-khf-o', img.currentSrc || img.src);
			}
		});
		if (c.logo_s != null && c.logo_s < 100) {
			setVar(it, '--fx-ls', c.logo_s / 100);
			flag(it, 'fx-ls', true);
		}
		if (c.logo_f0) { el.setAttribute('data-lf0', c.logo_f0); }
		if (c.logo_f1) { el.setAttribute('data-lf1', c.logo_f1); }

		// Progress bar.
		if (c.progress) {
			it.bar = d.createElement('span');
			it.bar.className = 'khf-fx-bar' + (c.progress === 'top' ? ' is-top' : '');
			it.bar.setAttribute('aria-hidden', 'true');
			if (c.progress_c) { it.bar.style.setProperty('--fx-pc', c.progress_c); }
			if (c.progress_h) { it.bar.style.setProperty('--fx-ph', c.progress_h + 'px'); }
			(c.progress === 'top' ? d.body : el).appendChild(it.bar);
		}

		it.until = c.until ? query(c.until)[0] || null : null;
	}

	var CHROMIUM = !!window.chrome;
	var liqCount = 0;

	function grain(amount) {
		var k = Math.round(amount / 100 * 0.3 * 1000) / 1000;
		var svg = "%3Csvg xmlns='http://www.w3.org/2000/svg' width='160' height='160'%3E%3Cfilter id='g'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.8' numOctaves='3' stitchTiles='stitch'/%3E%3CfeColorMatrix values='1 0 0 0 0 1 0 0 0 0 1 0 0 0 0 0 0 0 0 " + k + "'/%3E%3C/filter%3E%3Crect width='160' height='160' filter='url(%23g)'/%3E%3C/svg%3E";
		return 'url("data:image/svg+xml,' + svg + '") 0 0/160px 160px repeat';
	}

	function paintGlass(it, bf, userShadows) {
		var c = it.c;
		var filter = bf.length ? bf.slice() : ['blur(30px)', 'saturate(180%)', 'brightness(108%)'];

		if (c.g_refract && CHROMIUM) {
			var id = 'khf-liq-' + (++liqCount);
			var svg = d.createElement('div');
			svg.setAttribute('aria-hidden', 'true');
			svg.style.cssText = 'position:absolute;width:0;height:0;overflow:hidden';
			svg.innerHTML = '<svg width="0" height="0"><filter id="' + id + '" x="0" y="0" width="100%" height="100%" color-interpolation-filters="sRGB"><feTurbulence type="fractalNoise" baseFrequency=".008 .014" numOctaves="2" seed="4" result="n"/><feDisplacementMap in="SourceGraphic" in2="n" scale="' + Math.round(c.g_refract * 0.9) + '" xChannelSelector="R" yChannelSelector="G"/></filter></svg>';
			d.body.appendChild(svg);
			it.liq = svg;
			filter.unshift('url(#' + id + ')');
		}
		setVar(it, '--fx-bf', filter.join(' '));

		setVar(it, '--fx-gt', c.g_tint || 'rgba(255,255,255,0.14)');

		var edge = n(c.g_edge, 45) / 100;
		var shine = n(c.g_shine, 25) / 100;
		var depth = n(c.g_depth, 30) / 100;
		var shadows = [];
		if (edge > 0) {
			shadows.push('inset 0 1px 0 rgba(255,255,255,' + (edge * 0.9).toFixed(3) + ')');
			shadows.push('inset 0 0 0 1px rgba(255,255,255,' + (edge * 0.35).toFixed(3) + ')');
			shadows.push('inset 0 -1px 0 rgba(255,255,255,' + (edge * 0.2).toFixed(3) + ')');
		}
		if (depth > 0) { shadows.push('0 10px 40px rgba(0,0,0,' + (depth * 0.45).toFixed(3) + ')'); }
		setVar(it, '--fx-gsh', shadows.concat(userShadows).join(',') || 'none');

		var layers = [];
		if (n(c.g_grain, 0) > 0) { layers.push(grain(c.g_grain)); }
		if (shine > 0) { layers.push('linear-gradient(135deg,rgba(255,255,255,' + (shine * 0.6).toFixed(3) + ') 0%,rgba(255,255,255,' + (shine * 0.12).toFixed(3) + ') 38%,rgba(255,255,255,0) 60%)'); }
		setVar(it, '--fx-gbg', layers.join(',') || 'none');
	}

	function enable(it) {
		if (it.on) {
			return;
		}
		it.on = true;
		it.el.classList.add('khf-fx');
		paint(it);

		var c = it.c;
		if ((c.mode || 'fixed') !== 'none' && !c.overlay) {
			var next = it.el.nextElementSibling;
			if (next && next.classList.contains('khf-spacer')) {
				it.spacer = next;
			} else {
				it.spacer = d.createElement('div');
				it.spacer.className = 'khf-spacer';
				it.spacer.setAttribute('aria-hidden', 'true');
				it.el.parentNode.insertBefore(it.spacer, it.el.nextSibling);
			}
			it.spacer.style.display = '';
		}
		measure(it);
		update(it, scrollY(), 0);
	}

	function disable(it) {
		if (!it.on) {
			return;
		}
		it.on = false;
		it.stuck = it.smartHidden = it.released = false;
		FX_CLASSES.forEach(function (cls) { it.el.classList.remove(cls); });
		it.vars.forEach(function (v) { it.el.style.removeProperty(v); });
		it.vars = [];
		['data-mode', 'data-anim', 'data-lf0', 'data-lf1'].forEach(function (a) { it.el.removeAttribute(a); });
		it.logos.forEach(function (img) { img.classList.remove('khf-logo-t'); swapLogo(img, ''); });
		if (it.bar && it.bar.parentNode) { it.bar.parentNode.removeChild(it.bar); }
		if (it.liq && it.liq.parentNode) { it.liq.parentNode.removeChild(it.liq); it.liq = null; }
		it.bar = null;
		if (it.io) { it.io.disconnect(); it.io = null; }
		if (it.spacer) { it.spacer.style.display = 'none'; }
	}

	/* ------------------------------------------------------------------ */
	/* Misure (altezza naturale, padding da ridurre, segnaposto)           */
	/* ------------------------------------------------------------------ */
	function measure(it) {
		var el = it.el;
		var c = it.c;
		var was = el.classList.contains('is-stuck');
		var prev = el.style.transition;

		el.style.transition = 'none';
		el.classList.remove('is-stuck');

		var cs = getComputedStyle(el);
		it.h0 = el.offsetHeight;
		it.gap = parseFloat(cs.getPropertyValue('--khf-gap')) || 0;

		if (c.shrink && c.shrink < it.h0) {
			var pt = parseFloat(cs.paddingTop) || 0;
			var pb = parseFloat(cs.paddingBottom) || 0;
			var cut = pt + pb ? Math.min(it.h0 - c.shrink, pt + pb) / (pt + pb) : 0;
			setVar(it, '--fx-pt', Math.round(pt * (1 - cut)) + 'px');
			setVar(it, '--fx-pb', Math.round(pb * (1 - cut)) + 'px');
			flag(it, 'fx-pad', true);
		}
		if (it.spacer) {
			it.spacer.style.height = Math.round(it.h0 + it.gap) + 'px';
		}

		if (was) { el.classList.add('is-stuck'); }
		void el.offsetHeight;
		el.style.transition = prev;

		watchTone(it);
	}

	/* ------------------------------------------------------------------ */
	/* Logo                                                                */
	/* ------------------------------------------------------------------ */
	function swapLogo(img, url) {
		if (img.tagName !== 'IMG') { return; }
		var target = url || img.getAttribute('data-khf-o');
		if (!target || img.getAttribute('src') === target) { return; }
		img.removeAttribute('srcset');
		img.setAttribute('src', target);
	}

	function applyLogo(it) {
		var url = it.stuck ? it.c.logo1 : it.c.logo0;
		if (!url && !it.c.logo0 && !it.c.logo1) { return; }
		it.logos.forEach(function (img) { swapLogo(img, url || ''); });
	}

	/* ------------------------------------------------------------------ */
	/* Adaptive colors: watches the sections passing under the header     */
	/* ------------------------------------------------------------------ */
	function watchTone(it) {
		if (!it.c.tone || !('IntersectionObserver' in window)) { return; }
		if (it.io) { it.io.disconnect(); }

		var ab = d.getElementById('wpadminbar');
		var top = (ab && getComputedStyle(ab).position === 'fixed' ? ab.offsetHeight : 0) + Math.round(it.h0 / 2);
		var bottom = Math.max(0, window.innerHeight - top - 1);
		var live = [];

		it.io = new IntersectionObserver(function (entries) {
			entries.forEach(function (e) {
				var i = live.indexOf(e.target);
				if (e.isIntersecting && i < 0) { live.push(e.target); }
				if (!e.isIntersecting && i >= 0) { live.splice(i, 1); }
			});
			var last = live[live.length - 1];
			var dark = last && (last.classList.contains('khf-on-dark') || last.getAttribute('data-khf-tone') === 'dark');
			var light = last && !dark;
			it.el.classList.toggle('is-on-dark', !!dark);
			it.el.classList.toggle('is-on-light', !!light);
		}, { rootMargin: '-' + top + 'px 0px -' + bottom + 'px 0px' });

		query('.khf-on-dark,.khf-on-light,[data-khf-tone]').forEach(function (s) { it.io.observe(s); });
	}

	/* ------------------------------------------------------------------ */
	/* Aggiornamento allo scroll                                           */
	/* ------------------------------------------------------------------ */
	function setHidden(it) {
		var hidden = it.smartHidden || it.released;
		if (it.el.classList.contains('is-hidden') !== hidden) {
			it.el.classList.toggle('is-hidden', hidden);
			stack();
		}
	}

	function update(it, y, dy) {
		var c = it.c;
		var el = it.el;
		var stuck = y > n(c.at, 40);

		if (stuck !== it.stuck) {
			it.stuck = stuck;
			el.classList.toggle('is-stuck', stuck);
			applyLogo(it);
			if (stuck && c.intro) {
				el.classList.remove('fx-in-fade', 'fx-in-drop', 'fx-in-zoom');
				void el.offsetWidth;
				el.classList.add('fx-in-' + c.intro);
			}
		}

		if (c.mode === 'smart') {
			if (dy * it.acc < 0) { it.acc = 0; }
			it.acc += dy;
			if (el.classList.contains('is-open')) {
				it.smartHidden = false;
			} else if (it.acc > 10 && y > n(c.at, 40) + it.h0) {
				it.smartHidden = true;
			} else if (it.acc < -10 || y <= n(c.at, 40)) {
				it.smartHidden = false;
			}
		}

		if (it.until && c.mode !== 'none') {
			it.released = it.until.getBoundingClientRect().bottom <= it.h0;
		}
		setHidden(it);
	}

	/** Stacks several sticky elements one under the other. */
	function stack() {
		var acc = 0;
		var list = items.filter(function (it) { return it.on && it.c.mode !== 'none' && it.c.stack !== 0; });
		if (list.length < 2) { return; }
		list.forEach(function (it) {
			it.el.style.setProperty('--fx-so', acc + 'px');
			if (it.vars.indexOf('--fx-so') < 0) { it.vars.push('--fx-so'); }
			if (!it.el.classList.contains('is-hidden')) { acc += it.el.offsetHeight; }
		});
	}

	function tick() {
		var y = scrollY();
		var dy = y - lastY;
		var max = root.scrollHeight - window.innerHeight;
		var p = max > 0 ? Math.min(1, Math.max(0, y / max)) : 0;

		items.forEach(function (it) {
			if (!it.on) { return; }
			update(it, y, dy);
			if (it.bar) { it.bar.style.transform = 'scaleX(' + p + ')'; }
		});
		lastY = y;
		ticking = false;
	}

	function onScroll() {
		if (!ticking) {
			ticking = true;
			window.requestAnimationFrame(tick);
		}
	}

	function sync() {
		device = currentDevice();
		items.forEach(function (it) {
			var allowed = !it.c.devices || it.c.devices.indexOf(device) > -1;
			if (allowed && !it.on) { enable(it); }
			else if (!allowed) { disable(it); }
			else { measure(it); }
		});
		stack();
	}

	/* ------------------------------------------------------------------ */
	/* Global tools: anchors and "back to top"                           */
	/* ------------------------------------------------------------------ */
	function reduced() {
		return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	}

	function anchorOffset(targetY) {
		var total = n(tools.x, 0);
		items.forEach(function (it) {
			if (!it.on || it.c.mode === 'none') { return; }
			// In smart mode the header hides when scrolling down: no need to offset it.
			if (it.c.mode === 'smart' && targetY > scrollY()) { return; }
			total += it.c.shrink && it.c.shrink < it.h0 ? it.c.shrink : it.h0;
		});
		return total;
	}

	function goTo(el) {
		var y = el.getBoundingClientRect().top + scrollY();
		window.scrollTo({ top: Math.max(0, y - anchorOffset(y)), behavior: reduced() ? 'auto' : 'smooth' });
	}

	function byHash(hash) {
		try { return d.getElementById(decodeURIComponent(hash.slice(1))); } catch (e) { return null; }
	}

	function initTools() {
		if (tools.s) { root.classList.add('khf-smooth'); }

		if (tools.a) {
			d.addEventListener('click', function (e) {
				var a = e.target.closest && e.target.closest('a[href*="#"]');
				if (!a || e.defaultPrevented || e.button || e.metaKey || e.ctrlKey || e.shiftKey) { return; }
				if (/^(tab|button)$/.test(a.getAttribute('role') || '') || a.hasAttribute('data-toggle') || a.hasAttribute('data-bs-toggle')) { return; }
				var u = new URL(a.href, location.href);
				if (u.origin !== location.origin || u.pathname !== location.pathname || u.search !== location.search || u.hash.length < 2) { return; }
				var t = byHash(u.hash);
				if (!t) { return; }
				e.preventDefault();
				goTo(t);
				if (history.pushState) { history.pushState(null, '', u.hash); }
			});
			if (location.hash.length > 1) {
				window.addEventListener('load', function () {
					var t = byHash(location.hash);
					if (t) { setTimeout(function () { goTo(t); }, 60); }
				});
			}
		}

		query('.khf-top').forEach(function (btn) {
			var after = btn.hasAttribute('data-after') ? +btn.getAttribute('data-after') : n(tools.n, 500);
			var inside = btn.classList.contains('is-footer'); // inside the footer it is always visible
			var toggle = function () { if (!inside) { btn.classList.toggle('is-visible', scrollY() > after); } };
			window.addEventListener('scroll', toggle, { passive: true });
			btn.addEventListener('click', function () {
				window.scrollTo({ top: 0, behavior: reduced() ? 'auto' : 'smooth' });
			});
			toggle();
		});
	}

	/* ------------------------------------------------------------------ */
	/* Avvio                                                               */
	/* ------------------------------------------------------------------ */
	function parse(el) {
		try { return JSON.parse(el.getAttribute('data-khf-fx')); } catch (e) { return null; }
	}

	function start() {
		query('[data-khf-fx]').forEach(function (el) { register(el, parse(el)); });
		items.sort(function (a, b) {
			return a.el.compareDocumentPosition(b.el) & 4 ? -1 : 1; // 4 = b segue a
		});

		lastY = scrollY();
		sync();
		window.addEventListener('scroll', onScroll, { passive: true });

		var timer;
		window.addEventListener('resize', function () {
			clearTimeout(timer);
			timer = setTimeout(sync, 120);
		});
		window.addEventListener('load', sync);
		if (d.fonts && d.fonts.ready) { d.fonts.ready.then(sync); }
	}

	initTools();
	start();
})();
