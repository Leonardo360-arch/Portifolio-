<?php /* Incluir logo após o Hero: <?php include __DIR__.'/partials/theme-tabs.php'; ?> */
$vinhetas = [
  1 => ['Grafite',  '#E2E8F0'],
  2 => ['Cobalto',  '#38BDF8'],
  3 => ['Esmeralda','#10B981'],
  4 => ['Âmbar',    '#F97316'],
  5 => ['Violeta',  '#A855F7'],
]; ?>
<nav aria-label="Escolher tema visual">
  <ul class="theme-tabs">
    <?php foreach ($vinhetas as $id => [$nome, $cor]): ?>
      <li><button type="button" class="theme-tab" data-theme-id="<?= $id ?>"
        style="--c:<?= $cor ?>" aria-pressed="false"
        aria-label="Tema <?= $nome ?> (tecla <?= $id ?>)"><?= $id ?> <?= $nome ?></button></li>
    <?php endforeach; ?>
  </ul>
</nav>
