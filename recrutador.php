<?php
declare(strict_types=1);
require_once __DIR__.'/config/security.php';
require_once __DIR__.'/config/database.php';
secure_session();send_security_headers();header('Cache-Control: no-store');
$projects=[];
try {$projects=Database::conn()->query('SELECT id,titulo FROM projetos WHERE ativo=1 ORDER BY ordem,id')->fetchAll();}
catch(Throwable $e){error_log('Recruiter projects unavailable');}
?><!DOCTYPE html>
<html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>L.U.M.E.N. — Apresentação profissional</title>
<link rel="stylesheet" href="assets/css/themes.css"><link rel="stylesheet" href="assets/css/style.css"><link rel="stylesheet" href="assets/css/portfolio-v3.css">
<script src="assets/js/themes.js"></script><script src="assets/js/lumen/recruiter.js" defer></script></head>
<body><main class="container recruiter-shell">
<nav aria-label="Navegação"><a href="index.php">Voltar ao portfólio</a></nav>
<header><p class="lmc-eyebrow">Apresentação profissional</p><h1>Conheça o desenvolvedor com L.U.M.E.N.</h1><p>Sou um assistente digital, não o candidato. Consulte informações autorizadas sobre perfil e projetos. Não realizo seleção, não assumo compromissos e não substituo uma entrevista humana.</p></header>
<div class="recruiter-grid">
<section class="card recruiter-topics"><h2>Roteiro de pré-entrevista</h2><p>Escolha um tema para consultar os conhecimentos aprovados.</p>
<div class="lumen-quick">
<?php foreach(['formacao'=>'Formação','experiencia'=>'Experiência','valores'=>'Valores','metodologia'=>'Metodologia','competencias'=>'Competências'] as $key=>$label): ?>
<button type="button" data-topic="<?= e($key) ?>"><?= e($label) ?></button>
<?php endforeach; ?></div>
<label for="recruiter-project">Projeto de interesse</label>
<select id="recruiter-project"><option value="">Selecione um projeto</option><?php foreach($projects as $p): ?><option value="<?= (int)$p['id'] ?>"><?= e($p['titulo']) ?></option><?php endforeach; ?></select>
<div class="lumen-quick"><?php foreach(['motivacao'=>'Motivação','arquitetura'=>'Arquitetura','tecnologias'=>'Tecnologias','contribuicao'=>'Contribuição','desafios'=>'Desafios','validacao'=>'Testes','limites'=>'Situação atual'] as $key=>$label): ?><button type="button" data-project-topic="<?= e($key) ?>"><?= e($label) ?></button><?php endforeach; ?></div>
<p><a class="btn" href="index.php#contato">Entrar em contato</a></p></section>
<section class="card recruiter-conversation lumen-chat"><h2>Converse com L.U.M.E.N.</h2><p>Consulta local à memória profissional. Sem GPT público nesta versão.</p>
<div id="recruiter-log" role="log" aria-live="polite" aria-label="Respostas da pré-entrevista" tabindex="0"><p>Escolha uma pergunta ou escreva sua própria pergunta.</p></div>
<form id="recruiter-form" method="post"><?= csrf_field() ?><label for="recruiter-question">Pergunta</label><input id="recruiter-question" maxlength="240" required autocomplete="off"><button class="btn" type="submit">Consultar</button></form>
<p id="recruiter-status" role="status"></p>
<label><input type="checkbox" id="recruiter-speak"> Ler resposta em voz alta (voz do navegador)</label>
<button type="button" class="btn" id="recruiter-clear">Limpar conversa</button>
<p>Não informe dados pessoais ou confidenciais. As perguntas não são gravadas na memória permanente.</p></section>
</div></main><?php include __DIR__.'/partials/theme-tabs.php'; ?></body></html>
