<?php
declare(strict_types=1);
require_once __DIR__ . '/_layout.php';
require_admin();
$db = Database::conn();

function url_ok(string $u): bool { return (bool)preg_match('#^https?://#i', $u) && filter_var($u, FILTER_VALIDATE_URL); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $acao = $_POST['acao'] ?? ''; $id = (int)($_POST['id'] ?? 0);
    try {
        if ($acao === 'salvar') {
            $t = trim($_POST['titulo'] ?? ''); $d = trim($_POST['descricao'] ?? '');
            $lt = trim($_POST['link_testar'] ?? ''); $lg = trim($_POST['link_github'] ?? '');
            $tec = trim($_POST['tecnologias'] ?? ''); $ordem = (int)($_POST['ordem'] ?? 0); $ativo = isset($_POST['ativo']) ? 1 : 0;
            if ($t === '' || mb_strlen($t) > 120) throw new RuntimeException('Informe um título de até 120 caracteres.');
            if ($d === '') throw new RuntimeException('Informe a descrição.');
            if (!url_ok($lt)) throw new RuntimeException('O link do botão Testar precisa começar com http:// ou https://.');
            if ($lg !== '' && !url_ok($lg)) throw new RuntimeException('O link do GitHub precisa começar com http:// ou https://.');
            if ($tec === '' || mb_strlen($tec) > 255) throw new RuntimeException('Informe as tecnologias, separadas por vírgula (até 255 caracteres).');
            $temImg = isset($_FILES['imagem']) && $_FILES['imagem']['error'] !== UPLOAD_ERR_NO_FILE;
            $velha = null;
            if ($id) { $s = $db->prepare('SELECT imagem_print FROM projetos WHERE id=?'); $s->execute([$id]); $velha = $s->fetchColumn() ?: null; }
            if (!$id && !$temImg) throw new RuntimeException('Envie o print do sistema.');
            $img = $temImg ? save_image_webp($_FILES['imagem']) : $velha;
            if ($id) {
                $db->prepare('UPDATE projetos SET titulo=?,descricao=?,imagem_print=?,link_testar=?,link_github=?,tecnologias=?,ordem=?,ativo=? WHERE id=?')
                   ->execute([$t,$d,$img,$lt,$lg ?: null,$tec,$ordem,$ativo,$id]);
                if ($temImg) delete_upload($velha);
            } else {
                $db->prepare('INSERT INTO projetos (titulo,descricao,imagem_print,link_testar,link_github,tecnologias,ordem,ativo) VALUES (?,?,?,?,?,?,?,?)')
                   ->execute([$t,$d,$img,$lt,$lg ?: null,$tec,$ordem,$ativo]);
            }
            flash('Projeto salvo.');
        } elseif ($acao === 'excluir' && $id) {
            $s = $db->prepare('SELECT imagem_print FROM projetos WHERE id=?'); $s->execute([$id]);
            $img = $s->fetchColumn();
            $db->prepare('DELETE FROM projetos WHERE id=?')->execute([$id]);
            delete_upload($img ?: null);
            flash('Projeto excluído.');
        } elseif ($acao === 'mover' && $id) {
            $ids = $db->query('SELECT id FROM projetos ORDER BY ordem, id')->fetchAll(PDO::FETCH_COLUMN);
            $i = array_search($id, array_map('intval', $ids), true);
            $j = $i === false ? false : $i + (($_POST['dir'] ?? '') === 'up' ? -1 : 1);
            if ($i !== false && isset($ids[$j])) {
                [$ids[$i], $ids[$j]] = [$ids[$j], $ids[$i]];
                $u = $db->prepare('UPDATE projetos SET ordem=? WHERE id=?');
                foreach ($ids as $pos => $pid) $u->execute([$pos, $pid]);
            }
        }
    } catch (RuntimeException $ex) { flash($ex->getMessage(), 'erro'); }
    catch (Throwable $ex) { error_log($ex->getMessage()); flash('Erro inesperado ao salvar. Tente novamente.', 'erro'); }
    redirect('projetos.php' . (($acao === 'salvar' && !empty($_SESSION['flash']) && $_SESSION['flash'][1] === 'erro' && $id) ? '?editar=' . $id : ''));
}

$lista = $db->query('SELECT * FROM projetos ORDER BY ordem, id')->fetchAll();
$edit = ['id'=>0,'titulo'=>'','descricao'=>'','link_testar'=>'','link_github'=>'','tecnologias'=>'','ordem'=>count($lista),'ativo'=>1,'imagem_print'=>''];
if (isset($_GET['editar'])) { foreach ($lista as $p) if ((int)$p['id'] === (int)$_GET['editar']) $edit = $p; }

admin_header('Projetos'); ?>
<form method="post" enctype="multipart/form-data" class="card admin-form">
  <h2><?= $edit['id'] ? 'Editar projeto' : 'Novo projeto' ?></h2>
  <?= csrf_field() ?><input type="hidden" name="acao" value="salvar"><input type="hidden" name="id" value="<?= (int)$edit['id'] ?>">
  <label>Título<input name="titulo" maxlength="120" required value="<?= e($edit['titulo']) ?>"></label>
  <label>Descrição curta<textarea name="descricao" rows="3" required><?= e($edit['descricao']) ?></textarea></label>
  <label>Link do botão Testar<input type="url" name="link_testar" required value="<?= e($edit['link_testar']) ?>"></label>
  <label>Link do GitHub (opcional)<input type="url" name="link_github" value="<?= e($edit['link_github']) ?>"></label>
  <label>Tecnologias (separadas por vírgula)<input name="tecnologias" maxlength="255" required value="<?= e($edit['tecnologias']) ?>"></label>
  <label>Print do sistema (JPG, PNG ou WebP; convertido para WebP, máx. 2 MB)
    <input type="file" name="imagem" accept="image/jpeg,image/png,image/webp" <?= $edit['id'] ? '' : 'required' ?>></label>
  <label>Ordem<input type="number" name="ordem" value="<?= (int)$edit['ordem'] ?>"></label>
  <label class="check"><input type="checkbox" name="ativo" <?= $edit['ativo'] ? 'checked' : '' ?>> Visível no site</label>
  <p><button class="btn" type="submit">Salvar projeto</button> <?php if ($edit['id']): ?><a href="projetos.php">Cancelar edição</a><?php endif; ?></p>
</form>

<h2>Projetos cadastrados</h2>
<?php if (!$lista): ?><p>Nenhum projeto ainda. Use o formulário acima para cadastrar o primeiro.</p><?php endif; ?>
<?php foreach ($lista as $p): ?>
<div class="card admin-row">
  <img src="../<?= e($p['imagem_print']) ?>" alt="" width="96" height="60" style="object-fit:cover">
  <div style="flex:1;min-width:160px"><strong><?= e($p['titulo']) ?></strong><br><small><?= $p['ativo'] ? 'Visível' : 'Oculto' ?> · ordem <?= (int)$p['ordem'] ?></small></div>
  <?php foreach (['up' => 'Subir', 'down' => 'Descer'] as $dir => $rot): ?>
  <form method="post"><?= csrf_field() ?><input type="hidden" name="acao" value="mover"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"><input type="hidden" name="dir" value="<?= $dir ?>"><button class="btn"><?= $rot ?></button></form>
  <?php endforeach; ?>
  <a class="btn" href="projetos.php?editar=<?= (int)$p['id'] ?>">Editar</a>
  <form method="post" onsubmit="return confirm('Excluir este projeto e a imagem dele?')"><?= csrf_field() ?><input type="hidden" name="acao" value="excluir"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"><button class="btn">Excluir</button></form>
</div>
<?php endforeach; admin_footer();
