<?php
declare(strict_types=1);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/security.php';
send_security_headers();
secure_session(); // antes de qualquer saida HTML: o CSRF do formulario depende do cookie de sessao

$projetos = []; $cursos = []; $curriculo = null;
try {
    $db = Database::conn();
    $projetos = $db->query('SELECT titulo, descricao, imagem_print, link_testar, link_github, tecnologias
                            FROM projetos WHERE ativo = 1 ORDER BY ordem ASC, id DESC')->fetchAll();
    $r = $db->query("SELECT valor FROM configuracoes WHERE chave = 'curriculo_pdf'")->fetch();
    $curriculo = $r['valor'] ?? null;
    $cursos = $db->query('SELECT titulo, instituicao, carga_horaria FROM cursos WHERE ativo = 1 ORDER BY ordem, id')->fetchAll();
} catch (Throwable $e) { error_log('index: ' . $e->getMessage()); }

$wa = 'https://wa.me/5519978175868?text=' . rawurlencode(
    'Olá, Leonardo! Vi seu portfólio e gostaria de conversar sobre uma oportunidade/projeto.');
?><!DOCTYPE html>
<html lang="pt-BR"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Leonardo Marson Coral — Desenvolvedor Full Stack</title>
<meta name="description" content="Portfólio de Leonardo Marson Coral: projetos web, stack e repositórios.">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=JetBrains+Mono:wght@500;700&display=swap">
<link rel="stylesheet" href="assets/css/themes.css">
<link rel="stylesheet" href="assets/css/style.css">
<script src="assets/js/themes.js"></script>
</head><body>

<header class="container hero" style="padding:14vh 1.25rem 8vh">
  <h1 style="font-size:clamp(2.2rem,6vw,4rem)">Leonardo Marson Coral</h1>
  <p style="max-width:56ch;font-size:1.15rem;color:var(--muted)">
    Desenvolvedor Full Stack e estudante de DSM na FATEC Araras. Crio sistemas web completos, do banco de dados à interface.</p>
  <p style="display:flex;gap:.75rem;flex-wrap:wrap">
    <a class="btn" href="#projetos">Ver projetos</a>
    <a class="btn" href="<?= e($wa) ?>" target="_blank" rel="noopener noreferrer">Falar no WhatsApp</a>
    <?php if ($curriculo): ?><a class="btn" href="<?= e($curriculo) ?>" target="_blank" rel="noopener">Baixar currículo</a><?php endif; ?>
  </p>
</header>

<?php include __DIR__ . '/partials/theme-tabs.php'; ?>

<main>
  <section id="projetos" class="container" style="padding-bottom:4rem">
    <h2>Projetos</h2>
    <?php if (!$projetos): ?>
      <p style="color:var(--muted)">Os projetos serão publicados em breve.</p>
    <?php else: ?>
    <div class="repos" style="grid-template-columns:repeat(auto-fill,minmax(300px,1fr))">
      <?php foreach ($projetos as $p): ?>
      <article class="card" style="overflow:hidden;display:flex;flex-direction:column">
        <img src="<?= e($p['imagem_print']) ?>" alt="Captura de tela do projeto <?= e($p['titulo']) ?>"
             loading="lazy" style="width:100%;aspect-ratio:16/10;object-fit:cover;display:block">
        <div style="padding:1rem 1.1rem;display:flex;flex-direction:column;gap:.6rem;flex:1">
          <h3><?= e($p['titulo']) ?></h3>
          <p style="color:var(--muted);margin:0"><?= e($p['descricao']) ?></p>
          <div class="repo-meta">
            <?php foreach (array_filter(array_map('trim', explode(',', $p['tecnologias']))) as $t): ?>
              <span class="tag"><?= e($t) ?></span><?php endforeach; ?>
          </div>
          <p style="margin:auto 0 0;display:flex;gap:.5rem;flex-wrap:wrap;padding-top:.5rem">
            <a class="btn" href="<?= e(safe_url($p['link_testar'])) ?>" target="_blank" rel="noopener noreferrer">Testar</a>
            <?php if ($p['link_github']): ?>
              <a href="<?= e(safe_url($p['link_github'])) ?>" target="_blank" rel="noopener noreferrer">Código</a><?php endif; ?>
          </p>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </section>

  <?php if ($cursos): ?>
  <section id="cursos" class="container" style="padding-bottom:4rem">
    <h2>Cursos e formação</h2>
    <ul style="padding-left:1.1rem">
      <?php foreach ($cursos as $c): ?>
      <li><strong><?= e($c['titulo']) ?></strong><?= $c['instituicao'] ? ' — ' . e($c['instituicao']) : '' ?><?= $c['carga_horaria'] ? ' (' . (int)$c['carga_horaria'] . ' h)' : '' ?></li>
      <?php endforeach; ?>
    </ul>
  </section>
  <?php endif; ?>

  <?php include __DIR__ . '/partials/sections-stack-github.php'; ?>

  <?php include __DIR__ . '/partials/contact.php'; ?>
</main>

<footer class="container" style="padding:3rem 1.25rem;color:var(--muted)">
  © <?= date('Y') ?> Leonardo Marson Coral · <a href="admin/" rel="nofollow" style="color:inherit">Área administrativa</a>
</footer>
</body></html>
