<?php
declare(strict_types=1);
require_once __DIR__ . '/database.php';

function site_defaults(): array
{
    return [
        'hero_nome'    => 'Leonardo Marson Coral',
        'hero_texto'   => 'Desenvolvedor Full Stack e estudante de DSM na FATEC Araras. Crio sistemas web completos, do banco de dados à interface.',
        'whatsapp'     => '5519978175868',
        'whatsapp_msg' => 'Olá, Leonardo! Vi seu portfólio e gostaria de conversar sobre uma oportunidade/projeto.',
        'github_user'  => 'Leonardo360-arch',
        'stack'        => 'PHP,JavaScript,MySQL,Bootstrap,Figma,Docker,Gemini,Claude,OpenAI',
    ];
}

/** Valores salvos no painel por cima dos padrões. Se o banco falhar, usa só os padrões. */
function site_settings(): array
{
    $cfg = site_defaults();
    try {
        foreach (Database::conn()->query('SELECT chave, valor FROM configuracoes') as $r) {
            if (array_key_exists($r['chave'], $cfg) && $r['valor'] !== '') { $cfg[$r['chave']] = $r['valor']; }
        }
    } catch (Throwable $e) { error_log('settings: ' . $e->getMessage()); }
    return $cfg;
}
