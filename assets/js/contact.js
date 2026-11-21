/* RF-06: envio AJAX do formulário de contato. */
(function () {
  'use strict';
  var form = document.getElementById('contact-form');
  if (!form) return;
  var status = document.getElementById('contact-status');
  var btn = form.querySelector('button[type="submit"]');

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    if (!form.checkValidity()) { form.reportValidity(); return; }
    btn.disabled = true; status.textContent = 'Enviando…';

    fetch(form.action, { method: 'POST', body: new FormData(form), credentials: 'same-origin' })
      .then(function (r) { return r.json().catch(function () { return { ok: false, msg: 'Resposta inesperada do servidor.' }; }); })
      .then(function (d) {
        status.textContent = d.msg;
        if (d.ok) form.reset();
      })
      .catch(function () { status.textContent = 'Sem conexão. Verifique a internet e tente novamente.'; })
      .then(function () { btn.disabled = false; });
  });
})();
