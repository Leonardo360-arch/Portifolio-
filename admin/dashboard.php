<?php
declare(strict_types=1);
require_once __DIR__ . '/_layout.php';
require_admin();
$db = Database::conn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $acao = $_POST['acao'] ?? ''; $id = (int)($_POST['id'] ?? 0);
    try {
        switch ($acao) {
            case 'msg_lida':   $db->prepare('UPDATE mensagens SET lida = 1 - lida WHERE id=?')->execute([$id]); break;
            case 'msg_excluir': $db->prepare('DELETE FROM mensagens WHERE id=?')->execute([$id]); flash('Mensagem excluída.'); break;
            case 'curso_add':
                $t = trim($_POST['titulo'] ?? '');
                if ($t === '' || mb_strlen($t) > 150) throw new RuntimeException('Informe o nome do curso (até 150 caracteres).');
                $ch = ($_POST['carga'] ?? '') === '' ? null : max(0, (int)$_POST['carga']);
                $db->prepare('INSERT INTO cursos (titulo, instituicao, carga_horaria, ordem) VALUES (?,?,?,?)')
                   ->execute([$t, trim($_POST['instituicao'] ?? '') ?: null, $ch, (int)$db->query('SELECT COUNT(*) FROM cursos')->fetchColumn()]);
                flash('Curso adicionado.'); break;
            case 'curso_toggle': $db->prepare('UPDATE cursos SET ativo = 1 - ativo WHERE id=?')->execute([$id]); break;
            case 'curso_excluir': $db->prepare('DELETE FROM cursos WHERE id=?')->execute([$id]); flash('Curso excluído.'); break;
            case 'curriculo':
                $path = save_pdf($_FILES['pdf'] ?? []);
                $db->prepare("INSERT INTO configuracoes (chave, valor) VALUES ('curriculo_pdf', ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)")->execute([$path]);
                flash('Currículo atualizado.'); break;
        }
    } catch (RuntimeException $ex) { flash($ex->getMessage(), 'erro'); }
    catch (Throwable $ex) { error_log($ex->getMessage()); flash('Erro inesperado. Tente novamente.', 'erro'); }
    redirect('dashboard.php');
}

$msgs   = $db->query('SELECT * FROM mensagens ORDER BY lida ASC, criado_em DESC LIMIT 100')->fetchAll();
$cursos = $db->query('SELECT * FROM cursos ORDER BY ordem, id')->fetchAll();
$nProj  = (int)$db->query('SELECT COUNT(*) FROM projetos')->fetchColumn();
$nNova  = (int)$db->query('SELECT COUNT(*) FROM mensagens WHERE lida = 0')->fetchColumn();
$cv = $db->query("SELECT valor FROM configuracoes WHERE chave='curriculo_pdf'")->fetchColumn();

admin_header('Painel'); ?>
<p><?= $nProj ?> projeto(s) cadastrado(s) · <?= $nNova ?> mensagem(ns) nova(s). <a href="projetos.php">Gerenciar projetos</a></p>

<h2>Mensagens recebidas</h2>
<?php if (!$msgs): ?><p>Nenhuma mensagem ainda.</p><?php endif; ?>
<?php foreach ($msgs as $m): ?>
<article class="card admin-msg" style="<?= $m['lida'] ? 'opacity:.7' : '' ?>">
  <strong><?= e($m['nome']) ?></strong> &lt;<a href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a>&gt;
  <small> — <?= e(date('d/m/Y H:i', strtotime($m['criado_em']))) ?></small>
  <?php if ($m['assunto']): ?><p><em><?= e($m['assunto']) ?></em></p><?php endif; ?>
  <p><?= nl2br(e($m['mensagem'])) ?></p>
  <div style="display:flex;gap:.5rem;flex-wrap:wrap">
    <form method="post"><?= csrf_field() ?><input type="hidden" name="acao" value="msg_lida"><input type="hidden" name="id" value="<?= (int)$m['id'] ?>"><button class="btn"><?= $m['lida'] ? 'Marcar como não lida' : 'Marcar como lida' ?></button></form>
    <form method="post" onsubmit="return confirm('Excluir esta mensagem?')"><?= csrf_field() ?><input type="hidden" name="acao" value="msg_excluir"><input type="hidden" name="id" value="<?= (int)$m['id'] ?>"><button class="btn">Excluir</button></form>
  </div>
</article>
<?php endforeach; ?>

<h2>Cursos</h2>
<form method="post" class="card admin-form"><?= csrf_field() ?><input type="hidden" name="acao" value="curso_add">
  <label>Nome do curso<input name="titulo" maxlength="150" required></label>
  <label>Instituição<input name="instituicao" maxlength="150"></label>
  <label>Carga horária (horas)<input type="number" name="carga" min="0"></label>
  <p><button class="btn">Adicionar curso</button></p>
</form>
<?php foreach ($cursos as $c): ?>
<div class="card admin-row">
  <div style="flex:1"><strong><?= e($c['titulo']) ?></strong><br><small><?= e($c['instituicao']) ?><?= $c['carga_horaria'] ? ' · ' . (int)$c['carga_horaria'] . ' h' : '' ?> · <?= $c['ativo'] ? 'Visível' : 'Oculto' ?></small></div>
  <form method="post"><?= csrf_field() ?><input type="hidden" name="acao" value="curso_toggle"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>"><button class="btn"><?= $c['ativo'] ? 'Ocultar' : 'Mostrar' ?></button></form>
  <form method="post" onsubmit="return confirm('Excluir este curso?')"><?= csrf_field() ?><input type="hidden" name="acao" value="curso_excluir"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>"><button class="btn">Excluir</button></form>
</div>
<?php endforeach; ?>

<h2>Currículo (PDF)</h2>
<form method="post" enctype="multipart/form-data" class="card admin-form"><?= csrf_field() ?><input type="hidden" name="acao" value="curriculo">
  <p><?= $cv ? 'Arquivo atual: <a href="../' . e($cv) . '" target="_blank" rel="noopener">abrir PDF</a>' : 'Nenhum currículo enviado ainda.' ?></p>
  <label>Novo PDF (máx. 5 MB)<input type="file" name="pdf" accept="application/pdf" required></label>
  <p><button class="btn">Enviar currículo</button></p>
</form>
<?php admin_footer();
