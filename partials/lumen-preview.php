<aside id="lumen-panel" data-state="idle" class="card lmc-stage" aria-labelledby="lumen-title">
  <svg class="lumen-orb" viewBox="0 0 240 240" aria-hidden="true" focusable="false">
    <defs><radialGradient id="lumen-glow"><stop offset="0" stop-color="currentColor" stop-opacity=".25"/><stop offset="1" stop-color="currentColor" stop-opacity="0"/></radialGradient></defs>
    <circle cx="120" cy="120" r="116" fill="url(#lumen-glow)"/>
    <circle class="lumen-ring" cx="120" cy="120" r="105" stroke-dasharray="36 12 3 9" opacity=".6"/>
    <circle class="lumen-ring reverse" cx="120" cy="120" r="91" stroke-width="2" stroke-dasharray="65 18 12 15"/>
    <circle class="lumen-ring" cx="120" cy="120" r="76" stroke-dasharray="3 8"/>
    <circle cx="120" cy="120" r="55" fill="none" stroke="currentColor" opacity=".65"/>
    <path d="M78 120h15l9-17 10 34 10-48 11 55 10-24h19" fill="none" stroke="currentColor" stroke-width="3" stroke-linejoin="round"/>
  </svg>
  <h2 id="lumen-title">L.U.M.E.N.</h2>
  <p role="status">Disponível</p>
  <p>Assistente local · comandos de navegação e temas</p>
  <div class="lumen-chat">
    <div role="log" aria-label="Conversa com Lumen" aria-live="polite" tabindex="0"><p><strong>L.U.M.E.N.:</strong> Olá! Posso apresentar as seções do portfólio. Experimente um comando.</p></div>
    <form>
      <label for="lumen-message">Sua mensagem</label>
      <input id="lumen-message" maxlength="240" required autocomplete="off" placeholder="Ex.: ver projetos">
      <button class="btn" type="submit">Enviar</button>
    </form>
    <div class="lumen-quick" aria-label="Comandos rápidos">
      <button type="button" data-command="ver projetos">Projetos</button>
      <button type="button" data-command="quais tecnologias">Tecnologias</button>
      <button type="button" data-command="ajuda">Ajuda</button>
    </div>
  </div>
  <noscript><p>Ative JavaScript para usar o assistente.</p></noscript>
</aside>
