/* KaosH&F — mobile menu and "reveal" footer (vanilla JS) */
(function () {
	'use strict';

	var doc = document;
	var root = doc.documentElement;
	var cfg = window.khfHf || {};

	function ready(fn) {
		if (doc.readyState !== 'loading') { fn(); } else { doc.addEventListener('DOMContentLoaded', fn); }
	}

	function scrollY() {
		return window.pageYOffset || root.scrollTop || 0;
	}

	/* ---------------------------------------------------------------- */
	/* Menu mobile / a schermo intero                                    */
	/* ---------------------------------------------------------------- */
	function initMenu(h) {
		var burger = h.querySelector('.khf-burger');
		if (!burger) { return; }

		h.querySelectorAll('.khf-menu > li').forEach(function (li, i) {
			li.style.setProperty('--i', i);
		});

		var label = burger.querySelector('.khf-bl');

		function setOpen(open) {
			h.classList.toggle('is-open', open);
			doc.body.classList.toggle('khf-nav-open', open);
			burger.setAttribute('aria-expanded', open ? 'true' : 'false');
			if (label) { label.textContent = label.getAttribute(open ? 'data-open' : 'data-closed'); }
			h.dispatchEvent(new CustomEvent('khf:toggle', { detail: { open: open } }));
		}

		burger.addEventListener('click', function () { setOpen(!h.classList.contains('is-open')); });
		h.addEventListener('click', function (e) {
			if (!h.classList.contains('is-open')) { return; }
			// Menu link, or a click on the dimmed background (the header pseudo-element).
			if ((e.target.closest && e.target.closest('.khf-nav a')) || e.target === h) { setOpen(false); }
		});
		doc.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && h.classList.contains('is-open')) { setOpen(false); burger.focus(); }
		});
	}

	/* ---------------------------------------------------------------- */
	/* Images that change on hover over the menu items                  */
	/* ---------------------------------------------------------------- */
	function initHoverImages(h) {
		var nav = h.querySelector('.khf-nav');
		var media = h.querySelector('.khf-menu-media');
		var mode = nav && nav.getAttribute('data-hi');
		if (!media || !mode || mode === 'none') { return; }

		var imgs = [].slice.call(media.querySelectorAll('[data-u]'));
		var menus = h.querySelectorAll('.khf-menu');
		var cursor = mode === 'cursor';
		var x = 0, y = 0, tx = 0, ty = 0, raf = 0;

		if (cursor) {
			// Outside the header: an ancestor with backdrop-filter would break the coordinates of "fixed".
			doc.body.appendChild(media);
			media.classList.add('khf-menu-media--cursor');
		}

		function show(url) {
			imgs.forEach(function (img) {
				var on = img.getAttribute('data-u') === url;
				img.classList.toggle('is-on', on);
				if (img.tagName === 'VIDEO') {
					if (on) { var pr = img.play(); if (pr && pr.catch) { pr.catch(function () {}); } } else { img.pause(); }
				}
			});
			if (cursor && !media.classList.contains('is-shown')) { x = tx; y = ty; place(); }
			media.classList.add('is-shown');
		}
		function hide() {
			// In the full-screen menu the last media stays visible, like a gallery.
			if (h.classList.contains('is-open') && !cursor) { return; }
			media.classList.remove('is-shown');
		}
		// On open it shows the first media as the starting image.
		h.addEventListener('khf:toggle', function (e) {
			if (e.detail.open && !cursor && imgs.length) { show(imgs[0].getAttribute('data-u')); }
		});
		function place() {
			var w = media.offsetWidth;
			var px = x + 28 + w > window.innerWidth ? x - w - 28 : x + 28;
			media.style.transform = 'translate3d(' + Math.round(px) + 'px,' + Math.round(y + 20) + 'px,0)';
		}
		function loop() {
			x += (tx - x) * 0.2;
			y += (ty - y) * 0.2;
			place();
			raf = Math.abs(tx - x) > 0.5 || Math.abs(ty - y) > 0.5 ? window.requestAnimationFrame(loop) : 0;
		}

		h.querySelectorAll('.khf-menu a[data-khf-img]').forEach(function (a) {
			var url = a.getAttribute('data-khf-img');
			a.addEventListener('mouseenter', function () { show(url); });
			a.addEventListener('focus', function () { show(url); });
		});
		menus.forEach(function (menu) {
			menu.addEventListener('mouseleave', hide);
			menu.addEventListener('focusout', function (e) { if (!menu.contains(e.relatedTarget)) { hide(); } });
		});
		if (cursor) {
			h.addEventListener('mousemove', function (e) {
				tx = e.clientX;
				ty = e.clientY;
				if (!raf) { raf = window.requestAnimationFrame(loop); }
			});
		}
	}

	/* ---------------------------------------------------------------- */
	/* Footer                                                            */
	/* ---------------------------------------------------------------- */
	function pageBackground() {
		var els = [doc.body, root];
		for (var i = 0; i < els.length; i++) {
			var c = getComputedStyle(els[i]).backgroundColor;
			if (c && c !== 'transparent' && c !== 'rgba(0, 0, 0, 0)') { return c; }
		}
		return '#ffffff';
	}

	function findRevealTargets(f) {
		var selectors = cfg.content ? [cfg.content] : [];
		selectors = selectors.concat(['#page-container', '#page', '#wrapper', '#main-wrapper', '#outer-wrap', '#site-wrapper', '.wp-site-blocks', '.site']);
		for (var i = 0; i < selectors.length; i++) {
			var el = null;
			try { el = doc.querySelector(selectors[i]); } catch (e) { el = null; }
			if (el && !el.contains(f) && !f.contains(el)) { return [el]; }
		}
		// No container: we use the direct children of the body that are part of the flow.
		var skip = /^(SCRIPT|STYLE|LINK|NOSCRIPT|TEMPLATE|SVG)$/i;
		var list = [];
		Array.prototype.forEach.call(doc.body.children, function (el) {
			var cls = String(el.getAttribute('class') || '');
			if (skip.test(el.tagName) || el === f || el.id === 'wpadminbar' || /(^|\s)khf-/.test(cls)) { return; }
			var cs = getComputedStyle(el);
			if (cs.display === 'none' || cs.position === 'fixed' || cs.position === 'absolute') { return; }
			list.push(el);
		});
		return list;
	}

	function initFooter(f) {
		var mode = f.getAttribute('data-mode') || 'normal';
		root.style.setProperty('--khf-page-bg', pageBackground());

		function inView() { f.classList.add('is-in'); }

		// With an entrance effect the footer follows the scroll even in normal mode.
		var hasFx = f.hasAttribute('data-fx') && !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		function enterProgress() {
			var r = f.getBoundingClientRect();
			var span = Math.max(1, Math.min(r.height, window.innerHeight * 0.9));
			var p = Math.min(Math.max((window.innerHeight - r.top) / span, 0), 1);
			f.style.setProperty('--khf-p', p.toFixed(3));
		}

		if (mode.indexOf('reveal') !== 0) {
			f.classList.add('is-ready');
			if (hasFx) {
				var tick = false;
				var run = function () { if (!tick) { tick = true; window.requestAnimationFrame(function () { enterProgress(); tick = false; }); } };
				window.addEventListener('scroll', run, { passive: true });
				window.addEventListener('resize', run);
				enterProgress();
			}
			if ('IntersectionObserver' in window) {
				var io = new IntersectionObserver(function (entries) {
					if (entries[0].isIntersecting) { inView(); io.disconnect(); }
				}, { threshold: 0.12 });
				io.observe(f);
			} else {
				inView();
			}
			return;
		}

		var targets = findRevealTargets(f);
		var spacer = doc.createElement('div');
		spacer.className = 'khf-reveal-spacer';
		spacer.setAttribute('aria-hidden', 'true');
		var active = false;
		var H = 0;

		function activate() {
			if (active) { return; }
			active = true;
			targets.forEach(function (el) {
				el.classList.add('khf-reveal-content');
				var c = getComputedStyle(el).backgroundColor;
				if (!c || c === 'transparent' || c === 'rgba(0, 0, 0, 0)') { el.classList.add('khf-need-bg'); }
			});
			f.parentNode.insertBefore(spacer, f);
			f.classList.add('is-reveal');
		}

		function deactivate() {
			if (!active) { return; }
			active = false;
			targets.forEach(function (el) { el.classList.remove('khf-reveal-content', 'khf-need-bg'); });
			if (spacer.parentNode) { spacer.parentNode.removeChild(spacer); }
			f.classList.remove('is-reveal');
			f.style.removeProperty('--khf-p');
		}

		function progress() {
			if (!active) { return; }
			var distance = root.scrollHeight - (scrollY() + window.innerHeight);
			var p = 1 - Math.min(Math.max(distance / (H || 1), 0), 1);
			f.style.setProperty('--khf-p', p.toFixed(3));
			if (p > 0.18) { inView(); }
		}

		function layout() {
			// A footer taller than the screen cannot be "revealed": it goes back to the normal behavior.
			var natural = f.classList.contains('is-reveal') ? f.offsetHeight : f.getBoundingClientRect().height;
			if (natural > window.innerHeight * 0.92) { deactivate(); inView(); return; }
			activate();
			H = f.offsetHeight;
			spacer.style.height = H + 'px';
			progress();
		}

		var ticking = false;
		window.addEventListener('scroll', function () {
			if (!ticking) {
				ticking = true;
				window.requestAnimationFrame(function () { progress(); ticking = false; });
			}
		}, { passive: true });
		window.addEventListener('resize', layout);
		window.addEventListener('load', layout);
		if (window.ResizeObserver) {
			new ResizeObserver(function () { if (active && f.offsetHeight !== H) { layout(); } }).observe(f);
		}

		layout();
		f.classList.add('is-ready');
	}

	/* ---------------------------------------------------------------- */
	/* Address: choice of the maps app (on phones and Mac)                */
	/* ---------------------------------------------------------------- */
	function initMaps() {
		var ua = navigator.userAgent || '';
		var apple = /iPhone|iPad|iPod|Macintosh/.test(ua);
		var android = /Android/.test(ua);
		if (!apple && !android) { return; } // on a computer the link opens Google Maps directly.

		function close(sheet) { if (sheet.parentNode) { sheet.parentNode.removeChild(sheet); } doc.removeEventListener('keydown', sheet._esc); }

		function open(url, q, L) {
			var enc = encodeURIComponent(q);
			var sheet = doc.createElement('div');
			sheet.className = 'khf-mapsheet';
			sheet.setAttribute('role', 'dialog');
			sheet.setAttribute('aria-label', q);
			var box = doc.createElement('div');
			box.className = 'khf-mapsheet__box';
			var title = doc.createElement('p');
			title.textContent = q;
			box.appendChild(title);
			function add(label, href) {
				var a = doc.createElement('a');
				a.href = href; a.textContent = label;
				if (/^https?:/.test(href)) { a.target = '_blank'; a.rel = 'noopener'; }
				a.addEventListener('click', function () { close(sheet); });
				box.appendChild(a);
			}
			add(L.maps, apple ? 'https://maps.apple.com/?q=' + enc : 'geo:0,0?q=' + enc);
			add(L.google, url);
			var cancel = doc.createElement('button');
			cancel.type = 'button'; cancel.textContent = L.cancel;
			cancel.addEventListener('click', function () { close(sheet); });
			box.appendChild(cancel);
			sheet.appendChild(box);
			sheet.addEventListener('click', function (e) { if (e.target === sheet) { close(sheet); } });
			sheet._esc = function (e) { if (e.key === 'Escape') { close(sheet); } };
			doc.addEventListener('keydown', sheet._esc);
			doc.body.appendChild(sheet);
		}

		doc.addEventListener('click', function (e) {
			var a = e.target.closest ? e.target.closest('.khf-addr') : null;
			if (!a) { return; }
			e.preventDefault();
			open(a.href, a.getAttribute('data-q') || a.textContent, {
				maps: a.getAttribute('data-l-maps') || 'Maps',
				google: a.getAttribute('data-l-google') || 'Google Maps',
				cancel: a.getAttribute('data-l-cancel') || 'Cancel'
			});
		});
	}

	ready(function () {
		initMaps();
		var header = doc.querySelector('.khf-header');
		var footer = doc.querySelector('.khf-footer');
		if (header) { initMenu(header); initHoverImages(header); }
		if (footer) { initFooter(footer); }
	});
})();
