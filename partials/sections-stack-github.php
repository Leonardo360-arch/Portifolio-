<?php /* Incluir no index.php. Matter.js via cdnjs (versão fixa). */ ?>
<section id="stack" class="container">
  <h2>Tecnologias que uso</h2>
  <p>Arraste as peças para brincar com elas.</p>
  <div id="stack-board" class="stack-board" aria-label="Quadro interativo de tecnologias"></div>
</section>

<section id="repositorios" class="container">
  <h2>Repositórios no GitHub</h2>
  <div id="github-repos" class="repos" data-user="Leonardo360-arch" aria-live="polite">Carregando…</div>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/matter-js/0.19.0/matter.min.js"></script>
<script src="assets/js/physics-stack.js" defer></script>
<script src="assets/js/github-repos.js" defer></script>
