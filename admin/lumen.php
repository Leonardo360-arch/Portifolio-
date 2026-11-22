<?php
declare(strict_types=1);
require_once __DIR__ . '/_layout.php';
require_admin();
header('Cache-Control: no-store');
$db = Database::conn();
$categories = ['personalidade'=>'Personalidade','perfil'=>'Perfil profissional','tecnico'=>'Conhecimento técnico','projeto'=>'Projetos','metodologia'=>'Metodologia','correcao'=>'Correções'];
try {
    $db->query('SELECT id FROM lumen_knowledge LIMIT 1');
} catch (PDOException $ex) {
    error_log('Lumen knowledge unavailable: ' . $ex->getCode());
    admin_header('L.U.M.E.N. Training Center');
    echo '<p>Base indisponível. Verifique a conexão e a migração 001_lumen_knowledge.sql conforme o manual.</p>';
    admin_footer(); exit;
}
function lumen_text(string $key, int $max, bool $required = true): string {
    $v = $_POST[$key] ?? '';
    if (!is_string($v)) throw new InvalidArgumentException('Campo inválido.');
    $v = trim($v);
    if (($required && $v === '') || mb_strlen($v) > $max) throw new InvalidArgumentException('Confira os limites dos campos.');
    return $v;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    try {
        $action = lumen_text('action', 20);
        $id = filter_var($_POST['id'] ?? '0', FILTER_VALIDATE_INT, ['options'=>['min_range'=>0]]);
        if ($id === false) throw new InvalidArgumentException('Identificador inválido.');
        if ($action === 'save') {
            $category = lumen_text('category', 20);
            if (!isset($categories[$category])) throw new InvalidArgumentException('Categoria inválida.');
            $title = lumen_text('title', 150);
            $content = lumen_text('content', 10000);
            $source = lumen_text('source', 500, false);
            $project = filter_var($_POST['project_id'] ?? '0', FILTER_VALIDATE_INT, ['options'=>['min_range'=>0]]);
            if ($project === false || ($category === 'projeto' && !$project)) throw new InvalidArgumentException('Selecione um projeto.');
            if ($category !== 'projeto') $project = 0;
            if ($project) {
                $s = $db->prepare('SELECT id FROM projetos WHERE id=?'); $s->execute([$project]);
                if (!$s->fetchColumn()) throw new InvalidArgumentException('Projeto inexistente.');
            }
            $values = [$category,$project ?: null,$title,$content,$source];
            if ($id) {
                $db->prepare('UPDATE lumen_knowledge SET category=?,project_id=?,title=?,content=?,source=?,published=0 WHERE id=?')->execute([...$values,$id]);
            } else {
                $db->prepare('INSERT INTO lumen_knowledge(category,project_id,title,content,source) VALUES(?,?,?,?,?)')->execute($values);
            }
            flash('Conhecimento salvo como rascunho. Revise antes de publicar.');
        } elseif ($id && in_array($action,['publish','unpublish','delete'],true)) {
            if ($action === 'delete') $db->prepare('DELETE FROM lumen_knowledge WHERE id=?')->execute([$id]);
            else $db->prepare('UPDATE lumen_knowledge SET published=? WHERE id=?')->execute([$action === 'publish' ? 1 : 0,$id]);
            flash('Registro atualizado.');
        } else throw new InvalidArgumentException('Ação inválida.');
    } catch (InvalidArgumentException $ex) { flash($ex->getMessage(),'erro'); }
    catch (Throwable $ex) { error_log('Lumen save failure: '.$ex->getCode()); flash('Não foi possível salvar.','erro'); }
    redirect('lumen.php');
}
$projects = $db->query('SELECT id,titulo FROM projetos ORDER BY titulo')->fetchAll();
$rows = $db->query('SELECT k.id,k.category,k.title,k.published,k.updated_at,p.titulo AS project_title FROM lumen_knowledge k LEFT JOIN projetos p ON p.id=k.project_id ORDER BY k.id DESC LIMIT 200')->fetchAll();
$edit = ['id'=>0,'category'=>'personalidade','project_id'=>null,'title'=>'','content'=>'','source'=>''];
$id = filter_var($_GET['editar'] ?? '0', FILTER_VALIDATE_INT, ['options'=>['min_range'=>0]]);
if ($id) { $s=$db->prepare('SELECT id,category,project_id,title,content,source FROM lumen_knowledge WHERE id=?'); $s->execute([$id]); $edit=$s->fetch() ?: $edit; }
admin_header('L.U.M.E.N. Training Center'); ?>
<p>Cadastre fatos profissionais, exemplos de respostas e decisões. Não inclua senhas, dados privados ou informações de terceiros sem autorização. Publicar marca o registro como aprovado para futura consulta pelo assistente; a integração pública ainda não está ativa.</p>
<form method="post" class="card admin-form">
 <?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int)$edit['id'] ?>">
 <h2><?= $edit['id'] ? 'Editar conhecimento' : 'Novo conhecimento' ?></h2>
 <label>Categoria<select name="category"><?php foreach ($categories as $key=>$label): ?><option value="<?= e($key) ?>" <?= $edit['category']===$key?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?></select></label>
 <label>Projeto (obrigatório na categoria Projetos)<select name="project_id"><option value="0">Nenhum</option><?php foreach ($projects as $p): ?><option value="<?= (int)$p['id'] ?>" <?= (int)$edit['project_id']===(int)$p['id']?'selected':'' ?>><?= e($p['titulo']) ?></option><?php endforeach; ?></select></label>
 <label>Título ou pergunta<input name="title" maxlength="150" required value="<?= e($edit['title']) ?>"></label>
 <label>Resposta ou conhecimento<textarea name="content" maxlength="10000" rows="8" required><?= e($edit['content']) ?></textarea></label>
 <label>Fonte ou referência<input name="source" maxlength="500" value="<?= e($edit['source']) ?>"></label>
 <button class="btn">Salvar rascunho</button>
 <?php if ($edit['id']): ?><a href="lumen.php">Cancelar edição</a><?php endif; ?>
</form>
<h2>Conhecimentos cadastrados</h2>
<p>Até 200 registros mais recentes. Editar um registro publicado retorna-o a rascunho.</p>
<?php foreach ($rows as $r): ?>
<div class="card admin-row">
 <div style="flex:1;min-width:150px"><strong><?= e($r['title']) ?></strong><br><?= e($categories[$r['category']]) ?> · <?= $r['published']?'Publicado':'Rascunho' ?> <?= e($r['project_title'] ?? '') ?></div>
 <a class="btn" href="?editar=<?= (int)$r['id'] ?>">Editar</a>
 <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button class="btn" name="action" value="<?= $r['published']?'unpublish':'publish' ?>"><?= $r['published']?'Retirar publicação':'Aprovar publicação' ?></button></form>
 <form method="post" onsubmit="return confirm('Excluir conhecimento definitivamente?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button class="btn" name="action" value="delete">Excluir</button></form>
</div>
<?php endforeach; admin_footer();
