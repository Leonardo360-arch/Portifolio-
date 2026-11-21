/* RF-05: repositórios do GitHub com cache de 6h em localStorage. */
(function () {
  'use strict';
  var box = document.getElementById('github-repos');
  if (!box) return;
  var USER = box.dataset.user || 'Leonardo360-arch';
  var URL_API = 'https://api.github.com/users/' + encodeURIComponent(USER) + '/repos?sort=updated&per_page=30';
  var KEY = 'lmc_gh_' + USER, TTL = 6 * 60 * 60 * 1000;

  function getCache() {
    try { return JSON.parse(localStorage.getItem(KEY)); } catch (e) { return null; }
  }
  function setCache(data) {
    try { localStorage.setItem(KEY, JSON.stringify({ t: Date.now(), data: data })); } catch (e) {}
  }
  function el(tag, cls, text) {
    var n = document.createElement(tag);
    if (cls) n.className = cls;
    if (text != null) n.textContent = text;   // textContent evita XSS
    return n;
  }

  function render(repos, note) {
    box.textContent = '';
    var list = repos.filter(function (r) { return !r.fork; });
    if (!list.length) { box.appendChild(el('p', 'repo-empty', 'Nenhum repositório público encontrado.')); return; }
    list.forEach(function (r) {
      var card = el('article', 'card repo');
      var a = el('a', 'repo-name', r.name);
      a.href = r.html_url; a.target = '_blank'; a.rel = 'noopener noreferrer';
      var h = el('h3'); h.appendChild(a);
      card.appendChild(h);
      card.appendChild(el('p', 'repo-desc', r.description || 'Sem descrição.'));
      var meta = el('div', 'repo-meta');
      if (r.language) meta.appendChild(el('span', 'tag', r.language));
      meta.appendChild(el('span', 'tag', '★ ' + r.stargazers_count));
      meta.appendChild(el('span', 'tag', 'Atualizado em ' + new Date(r.pushed_at || r.updated_at).toLocaleDateString('pt-BR')));
      card.appendChild(meta);
      box.appendChild(card);
    });
    if (note) box.appendChild(el('p', 'repo-note', note));
  }

  var c = getCache();
  if (c && Date.now() - c.t < TTL) { render(c.data); return; }

  fetch(URL_API, { headers: { Accept: 'application/vnd.github+json' } })
    .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
    .then(function (data) { setCache(data); render(data); })
    .catch(function () {
      if (c) render(c.data, 'Exibindo dados salvos: não foi possível atualizar agora.');
      else { box.textContent = ''; box.appendChild(el('p', 'repo-empty', 'Não foi possível carregar os repositórios. Tente novamente mais tarde.')); }
    });
})();
