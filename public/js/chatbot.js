/**
 * CyberneticsNexa Chatbot – front-end behaviour.
 * Config is provided by PHP as window.CNX_DATA (see class-cnx-frontend.php).
 * All text is inserted with textContent / text nodes, never innerHTML.
 */
(function () {
	'use strict';

	function init(root, cfg) {

	var launcher = root.querySelector('.cnx-launcher');
	var win      = root.querySelector('.cnx-window');
	var list     = root.querySelector('.cnx-messages');
	var chipBox  = root.querySelector('.cnx-chips');
	var form     = root.querySelector('.cnx-form');
	var input    = root.querySelector('.cnx-input');
	var closeBtn = root.querySelector('.cnx-close');

	var URL_RE   = /(https?:\/\/[^\s<]+[^\s<.,;:!?)"'\]])/g;
	var HUMAN_RE = /\b(human|agent|person|someone|somebody|live|whatsapp|representative|operator|support|talk to|speak to)\b/i;
	var STORE    = 'cnx_auto_opened';

	var replies  = Array.isArray(cfg.replies) ? cfg.replies : [];
	var wa       = cfg.whatsapp || null;
	var busy     = false;
	var typingEl = null;

	/* ------------------------------------------------------------ helpers */

	function fill(node, text) {
		String(text).split(URL_RE).forEach(function (part, i) {
			if (i % 2 === 1) {
				var a = document.createElement('a');
				a.href = part;
				a.target = '_blank';
				a.rel = 'noopener noreferrer';
				a.textContent = part;
				node.appendChild(a);
			} else if (part) {
				node.appendChild(document.createTextNode(part));
			}
		});
	}

	function toBottom() {
		list.scrollTop = list.scrollHeight;
	}

	function bubble(text, who, act) {
		var b = document.createElement('div');
		b.className = 'cnx-msg cnx-' + who;
		fill(b, text);

		if (act && act.href) {
			var br = document.createElement('br');
			var a  = document.createElement('a');
			a.className = 'cnx-action' + (act.wa ? ' cnx-wa' : '');
			a.href = act.href;
			a.target = '_blank';
			a.rel = 'noopener noreferrer';
			a.textContent = act.label;
			b.appendChild(br);
			b.appendChild(a);
		}

		list.appendChild(b);
		toBottom();
		return b;
	}

	function showTyping() {
		typingEl = document.createElement('div');
		typingEl.className = 'cnx-msg cnx-bot cnx-typing';
		typingEl.setAttribute('aria-label', cfg.i18n.typing);
		for (var i = 0; i < 3; i++) { typingEl.appendChild(document.createElement('span')); }
		list.appendChild(typingEl);
		toBottom();
	}

	function hideTyping() {
		if (typingEl && typingEl.parentNode) { typingEl.parentNode.removeChild(typingEl); }
		typingEl = null;
	}

	function botSay(text, act) {
		busy = true;
		showTyping();
		var wait = Math.min(1100, 420 + String(text).length * 5);
		setTimeout(function () {
			hideTyping();
			bubble(text, 'bot', act);
			busy = false;
		}, wait);
	}

	function waAction() {
		return wa ? { href: wa.url, label: wa.button, wa: true } : null;
	}

	/* ------------------------------------------------------------ chat logic */

	function openWhatsApp() {
		if (!wa) { return; }
		window.open(wa.url, '_blank', 'noopener');
	}

	function respond(item) {
		if (item.type === 'whatsapp' && wa) {
			openWhatsApp();
			botSay(wa.reply, waAction());
			return;
		}
		if (item.type === 'url' && item.url) {
			window.open(item.url, '_blank', 'noopener');
			botSay(cfg.i18n.opening, { href: item.url, label: item.title });
			return;
		}
		botSay(item.content || item.title);
	}

	function findMatch(text) {
		var q = text.toLowerCase();
		var best = null;
		var bestScore = 0;

		replies.forEach(function (r) {
			var terms = (r.keywords ? r.keywords.toLowerCase().split(',') : [])
				.concat(r.title.toLowerCase().split(/\s+/));
			var score = 0;
			terms.forEach(function (t) {
				t = t.trim();
				if (t.length >= 3 && q.indexOf(t) !== -1) { score += t.length; }
			});
			if (score > bestScore) { best = r; bestScore = score; }
		});
		return best;
	}

	function onChip(item) {
		if (busy) { return; }
		bubble(item.title, 'user');
		respond(item);
	}

	function onWhatsAppChip() {
		if (busy || !wa) { return; }
		bubble(wa.label, 'user');
		openWhatsApp();
		botSay(wa.reply, waAction());
	}

	function onSubmit(e) {
		e.preventDefault();
		var text = input.value.trim();
		if (!text || busy) { return; }
		input.value = '';
		bubble(text, 'user');

		var match = findMatch(text);
		if (match) { respond(match); return; }

		if (wa && HUMAN_RE.test(text)) {
			openWhatsApp();
			botSay(wa.reply, waAction());
			return;
		}
		botSay(cfg.fallback, waAction());
	}

	function addChip(label, handler, extraClass, withIcon) {
		var c = document.createElement('button');
		c.type = 'button';
		c.className = 'cnx-chip' + (extraClass ? ' ' + extraClass : '');
		if (withIcon) {
			// Static, trusted SVG (no user data).
			c.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21l1.65-4.8A8.5 8.5 0 1 1 8 19.4L3 21Z"/><path d="M9 10c0 3 2 5 5 5l1.2-1.2-1.7-1-.8.6c-.9-.4-1.6-1.1-2-2l.6-.8-1-1.7L9 10Z"/></svg>';
		}
		c.appendChild(document.createTextNode(label));
		c.addEventListener('click', handler);
		chipBox.appendChild(c);
	}

	function buildChips() {
		chipBox.textContent = '';
		replies.forEach(function (r) {
			addChip(r.title, function () { onChip(r); });
		});
		if (wa) {
			addChip(wa.label, onWhatsAppChip, 'cnx-chip-wa', true);
		}
	}

	/* ------------------------------------------------------------ open / close */

	function isOpen() { return root.classList.contains('cnx-open'); }

	function open() {
		root.classList.add('cnx-open');
		win.setAttribute('aria-hidden', 'false');
		launcher.setAttribute('aria-expanded', 'true');
		toBottom();
		if (window.innerWidth > 600) {
			setTimeout(function () { input.focus({ preventScroll: true }); }, 260);
		}
	}

	function close() {
		root.classList.remove('cnx-open');
		win.setAttribute('aria-hidden', 'true');
		launcher.setAttribute('aria-expanded', 'false');
	}

	function toggle() { isOpen() ? close() : open(); }

	function seenAutoOpen() {
		try { return window.sessionStorage.getItem(STORE) === '1'; } catch (e) { return false; }
	}
	function markAutoOpen() {
		try { window.sessionStorage.setItem(STORE, '1'); } catch (e) { /* ignore */ }
	}

	/* ------------------------------------------------------------ init */

	bubble(cfg.hello, 'bot');
	buildChips();

	launcher.addEventListener('click', toggle);
	closeBtn.addEventListener('click', function () { close(); launcher.focus(); });
	form.addEventListener('submit', onSubmit);

	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape' && isOpen()) { close(); launcher.focus(); }
	});

	// Any element with class "cnx-open-chat" or a link to #cnx-open opens the chat.
	document.addEventListener('click', function (e) {
		var t = e.target.closest ? e.target.closest('.cnx-open-chat, a[href="#cnx-open"]') : null;
		if (t) { e.preventDefault(); open(); }
	});

	var delay = Number(cfg.autoOpen) || 0;
	if (delay > 0 && !seenAutoOpen() && window.innerWidth > 600) {
		setTimeout(function () {
			if (!isOpen()) { open(); }
			markAutoOpen();
		}, delay * 1000);
	}

	// Public API: CNXChatbot.open(), .close(), .toggle()
	window.CNXChatbot = { open: open, close: close, toggle: toggle };
	}

	/* Boot: works whether the script runs before or after the markup is in the DOM. */
	function boot() {
		var root = document.getElementById('cnx-root');
		var cfg  = window.CNX_DATA;
		if (!root || !cfg || root.getAttribute('data-cnx-ready') === '1') { return; }
		root.setAttribute('data-cnx-ready', '1');
		init(root, cfg);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
