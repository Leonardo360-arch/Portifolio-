/* RF-01: troca dinâmica de tema com persistência.
   Carregar no <head> (sem defer) para evitar flash do tema padrão. */
(function () {
  'use strict';
  var KEY = 'lmc_theme', MAX = 5;

  function read() {
    try { var v = parseInt(localStorage.getItem(KEY), 10); return v >= 1 && v <= MAX ? v : 1; }
    catch (e) { return 1; }
  }
  function save(id) { try { localStorage.setItem(KEY, String(id)); } catch (e) {} }

  var current = read();
  function apply(id, persist) {
    current = id;
    document.body ? document.body.setAttribute('data-theme', id)
                  : document.documentElement.setAttribute('data-pending-theme', id);
    if (persist) save(id);
    document.querySelectorAll('.theme-tab').forEach(function (t) {
      t.setAttribute('aria-pressed', String(+t.dataset.themeId === id));
    });
    document.dispatchEvent(new CustomEvent('themechange', { detail: { id: id } }));
  }

  document.addEventListener('DOMContentLoaded', function () {
    apply(current, false);

    document.querySelectorAll('.theme-tab').forEach(function (tab) {
      var id = +tab.dataset.themeId, startX = null, moved = false;

      tab.addEventListener('click', function () {
        if (moved) { moved = false; return; }   // clique gerado após arrasto
        apply(id, true);
      });
      // gesto de "puxar" para a esquerda
      tab.addEventListener('pointerdown', function (e) {
        startX = e.clientX; moved = false; tab.setPointerCapture(e.pointerId);
      });
      tab.addEventListener('pointermove', function (e) {
        if (startX === null) return;
        var dx = Math.min(0, e.clientX - startX);
        if (dx < -6) { tab.classList.add('dragging'); tab.style.transform = 'translateX(' + Math.max(dx, -24) + 'px)'; }
        if (dx < -24 && !moved) { moved = true; apply(id, true); }
      });
      function end() { startX = null; tab.classList.remove('dragging'); tab.style.transform = ''; }
      tab.addEventListener('pointerup', end);
      tab.addEventListener('pointercancel', end);
    });

    // atalhos 1 a 5 (ignora campos de texto e combinações com modificadores)
    document.addEventListener('keydown', function (e) {
      if (e.ctrlKey || e.metaKey || e.altKey) return;
      var t = e.target, tag = t && t.tagName;
      if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || (t && t.isContentEditable)) return;
      var n = parseInt(e.key, 10);
      if (n >= 1 && n <= MAX) apply(n, true);
    });
  });

  window.LMCTheme = { get: function () { return current; }, set: function (n) { apply(n, true); } };
})();
