<?php
declare(strict_types=1);
require_once __DIR__.'/_layout.php';
require_admin();
header('Cache-Control: no-store');
admin_header('L.U.M.E.N. — Comando administrativo'); ?>
<link rel="stylesheet" href="../assets/css/portfolio-v3.css">
<section class="card lmc-stage" id="lumen-admin">
<h2>L.U.M.E.N. ADMIN</h2>
<p>Assistente de operações autenticadas. Microfone opcional, sem escuta contínua. O navegador pode enviar áudio ao serviço de reconhecimento de fala; não dite senhas ou dados privados.</p>
<form class="admin-form" id="lumen-admin-form">
<?= csrf_field() ?>
<label>Comando<input id="lumen-admin-input" maxlength="240" required autocomplete="off" placeholder="Ex.: resumo do painel"></label>
<button type="button" id="lumen-mic" class="btn">Ativar microfone</button>
<button type="submit" class="btn">Executar comando</button>
<label class="check"><input type="checkbox" id="lumen-external"> Permitir GPT nesta página (envia pergunta, conhecimentos publicados e, se necessário, indicadores administrativos e títulos de projetos ativos ao provedor; não inclua dados privados)</label>
<label class="check"><input type="checkbox" id="lumen-speak">Ler respostas por voz do navegador</label>
</form>
<p id="lumen-admin-status" role="status" aria-live="polite">Disponível</p>
<div id="lumen-admin-confirm" hidden>
<p>Confirme somente após revisar o registro. A publicação poderá disponibilizar informações ao assistente público futuramente.</p>
<button class="btn" type="button" id="lumen-confirm">Confirmar alteração</button>
<button class="btn" type="button" id="lumen-cancel">Cancelar</button>
</div>
<h3>Consultas inteligentes com GPT (quando habilitado)</h3>
<p>Exemplos: Quantos projetos estão ativos? Como está o treinamento? Quais projetos possuem lacunas na entrevista técnica? As consultas são somente leitura e podem consumir duas requisições.</p>
<h3>Comandos disponíveis</h3>
<p>Resumo do painel · resumo do conhecimento · abrir projetos · abrir mensagens · abrir configurações · abrir treinamento · iniciar entrevista · publicar conhecimento 12 · retirar conhecimento 12.</p>
<p>Use o identificador do registro mostrado no Training Center. Comandos de publicação exigem confirmação manual.</p>
</section>
<script src="../assets/js/lumen/admin.js" defer></script>
<?php admin_footer();
