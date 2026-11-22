<?php
declare(strict_types=1);
require_once __DIR__ . '/_layout.php';
require_admin();
header('Cache-Control: no-store');
$catalog = require __DIR__ . '/../config/lumen-interview.php';
$category = $_GET['category'] ?? 'personalidade';
if (!is_string($category) || !isset($catalog[$category])) $category = 'personalidade';
$db = Database::conn();
try { $db->query('SELECT id FROM lumen_knowledge LIMIT 1'); }
catch (PDOException $ex) { admin_header('Entrevista de treinamento'); echo '<p>Base indisponível. Execute a migração 001 e verifique a conexão.</p>'; admin_footer(); exit; }
$projects = $db->query('SELECT id,titulo FROM projetos ORDER BY titulo')->fetchAll();
$answers = [];
$project = 0;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    try {
        $raw = $_POST['answers'] ?? [];
        if (!is_array($raw) || count($raw) > count($catalog[$category]['questions'])) throw new InvalidArgumentException('Respostas inválidas.');
        $project = filter_var($_POST['project_id'] ?? '0', FILTER_VALIDATE_INT, ['options'=>['min_range'=>0]]);
        if ($project === false) throw new InvalidArgumentException('Projeto inválido.');
        if ($category === 'projeto') {
            $s=$db->prepare('SELECT id FROM projetos WHERE id=?'); $s->execute([$project]);
            if (!$s->fetchColumn()) throw new InvalidArgumentException('Selecione um projeto cadastrado.');
        } else $project=0;
        $pending = [];
        foreach ($raw as $key=>$value) {
            if (!isset($catalog[$category]['questions'][$key]) || !is_string($value) || mb_strlen($value)>10000) throw new InvalidArgumentException('Resposta inválida ou acima de 10.000 caracteres.');
            $answers[$key]=trim($value);
            if ($answers[$key] !== '') $pending[$key]=$answers[$key];
        }
        if (!$pending) throw new InvalidArgumentException('Responda ao menos uma pergunta.');
        $db->beginTransaction();
        $insert=$db->prepare('INSERT INTO lumen_knowledge(category,project_id,title,content,source,published) VALUES(?,?,?,?,?,0)');
        foreach ($pending as $key=>$value) $insert->execute([$category,$project ?: null,$catalog[$category]['questions'][$key],$value,'Entrevista supervisionada do administrador']);
        $db->commit();
        flash(count($pending).' resposta(s) salva(s) como rascunho. Revise e aprove individualmente.');
        redirect('lumen.php');
    } catch (InvalidArgumentException $ex) { if($db->inTransaction())$db->rollBack(); $error=$ex->getMessage(); }
    catch (Throwable $ex) { if($db->inTransaction())$db->rollBack(); error_log('Lumen interview: '.$ex->getCode()); $error='Não foi possível salvar a entrevista.'; }
}
admin_header('L.U.M.E.N. — Entrevista de treinamento'); ?>
<p>Responda com suas próprias palavras. Você pode deixar perguntas em branco. As respostas não serão publicadas automaticamente nem enviadas a uma API.</p>
<p><a href="lumen.php">Voltar ao Training Center</a></p>
<nav aria-label="Categorias de entrevista" class="admin-nav">
<?php foreach ($catalog as $key=>$item): ?><a href="?category=<?= e($key) ?>" <?= $key===$category?'aria-current="page"':'' ?>><?= e($item['label']) ?></a><?php endforeach; ?>
</nav>
<?php if ($error): ?><p role="alert" class="flash flash-erro"><?= e($error) ?></p><?php endif; ?>
<form method="post" class="card admin-form" action="?category=<?= e($category) ?>">
<?= csrf_field() ?>
<h2><?= e($catalog[$category]['label']) ?></h2>
<?php if ($category==='projeto'): ?>
<label>Projeto<select name="project_id" required><option value="">Selecione</option><?php foreach($projects as $p): ?><option value="<?= (int)$p['id'] ?>" <?= (int)$project===(int)$p['id']?'selected':'' ?>><?= e($p['titulo']) ?></option><?php endforeach; ?></select></label>
<?php endif; ?>
<?php $n=0; foreach($catalog[$category]['questions'] as $key=>$question): ?>
<label><?= ++$n ?>. <?= e($question) ?><textarea name="answers[<?= e($key) ?>]" rows="4" maxlength="10000"><?= e($answers[$key] ?? '') ?></textarea></label>
<?php endforeach; ?>
<button class="btn" type="submit">Salvar respostas como rascunhos</button>
</form>
<?php admin_footer();
