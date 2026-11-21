<?php
declare(strict_types=1);
require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/../config/settings.php';
require_admin();
$db = Database::conn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    try {
        $nome  = trim($_POST['hero_nome'] ?? '');
        $texto = trim($_POST['hero_texto'] ?? '');
        $wa    = preg_replace('/\D/', '', $_POST['whatsapp'] ?? '');
        $msg   = trim($_POST['whatsapp_msg'] ?? '');
        $gh    = trim($_POST['github_user'] ?? '');
        $stack = array_values(array_filter(array_map('trim', explode(',', $_POST['stack'] ?? ''))));

        if ($nome === '' || mb_strlen($nome) > 80) throw new RuntimeException('Informe o nome (até 80 caracteres).');
        if ($texto === '' || mb_strlen($texto) > 255) throw new RuntimeException('O texto de apresentação deve ter até 255 caracteres.');
        if (strlen($wa) < 10 || strlen($wa) > 15) throw new RuntimeException('WhatsApp: use só números, com código do país e DDD (ex.: 5519978175868).');
        if ($msg === '' || mb_strlen($msg) > 200) throw new RuntimeException('A mensagem do WhatsApp deve ter até 200 caracteres.');
        if (!preg_match('/^[A-Za-z0-9-]{1,39}$/', $gh)) throw new RuntimeException('Usuário do GitHub inválido.');
        if (!$stack || count($stack) > 15) throw new RuntimeException('Informe de 1 a 15 tecnologias, separadas por vírgula.');
        foreach ($stack as $t) if (mb_strlen($t) > 20) throw new RuntimeException('Cada tecnologia pode ter até 20 caracteres.');
        $stackStr = implode(',', $stack);
        if (mb_strlen($stackStr) > 255) throw new RuntimeException('A lista de tecnologias ficou longa demais.');

        $up = $db->prepare('INSERT INTO configuracoes (chave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)');
        foreach (['hero_nome' => $nome, 'hero_texto' => $texto, 'whatsapp' => $wa, 'whatsapp_msg' => $msg,
                  'github_user' => $gh, 'stack' => $stackStr] as $k => $v) { $up->execute([$k, $v]); }
        flash('Configurações salvas. O site já está atualizado.');
    } catch (RuntimeException $ex) { flash($ex->getMessage(), 'erro'); }
    catch (Throwable $ex) { error_log($ex->getMessage()); flash('Erro inesperado ao salvar.', 'erro'); }
    redirect('configuracoes.php');
}

$c = site_settings();
admin_header('Configurações do site'); ?>
<p>Edite os textos e contatos que aparecem no site. Projetos e currículo continuam em suas próprias telas.</p>
<form method="post" class="card admin-form"><?= csrf_field() ?>
  <label>Seu nome (título do site)<input name="hero_nome" maxlength="80" required value="<?= e($c['hero_nome']) ?>"></label>
  <label>Texto de apresentação (até 255 caracteres)<textarea name="hero_texto" rows="3" maxlength="255" required><?= e($c['hero_texto']) ?></textarea></label>
  <label>WhatsApp (só números, com 55 + DDD)<input name="whatsapp" inputmode="numeric" required value="<?= e($c['whatsapp']) ?>"></label>
  <label>Mensagem pré-preenchida do WhatsApp<textarea name="whatsapp_msg" rows="2" maxlength="200" required><?= e($c['whatsapp_msg']) ?></textarea></label>
  <label>Usuário do GitHub<input name="github_user" maxlength="39" required value="<?= e($c['github_user']) ?>"></label>
  <label>Tecnologias do quadro interativo (separadas por vírgula, até 15)<input name="stack" maxlength="255" required value="<?= e($c['stack']) ?>"></label>
  <p><button class="btn" type="submit">Salvar configurações</button></p>
</form>
<?php admin_footer();
