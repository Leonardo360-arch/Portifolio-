<?php
declare(strict_types=1);
require_once __DIR__.'/../config/security.php';
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/lumen-knowledge.php';
send_security_headers();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
function lumen_reply(int $code,array $data): never {http_response_code($code);echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);exit;}
if($_SERVER['REQUEST_METHOD']!=='POST')lumen_reply(405,['error'=>'Método inválido.']);
if((int)($_SERVER['CONTENT_LENGTH']??0)>4096)lumen_reply(413,['error'=>'Solicitação muito grande.']);
secure_session();
$token=$_POST['csrf']??null;
if(!is_string($token)||empty($_SESSION['csrf'])||!hash_equals($_SESSION['csrf'],$token))lumen_reply(419,['error'=>'Recarregue a página.']);
$q=$_POST['question']??null;
if(!is_string($q)||trim($q)===''||mb_strlen($q)>240)lumen_reply(400,['error'=>'Informe uma pergunta de até 240 caracteres.']);
$now=time();
$hits=array_filter($_SESSION['lumen_read_hits']??[],static fn($t)=>$t>$now-60);
if(count($hits)>=12)lumen_reply(429,['error'=>'Limite temporário de consultas. Tente novamente em instantes.']);
$hits[]=$now;$_SESSION['lumen_read_hits']=$hits;session_write_close();
try{
 $service=new LumenKnowledge(Database::conn());
 lumen_reply(200,['message'=>LumenKnowledge::answer($service->search($q)),'mode'=>'local']);
}catch(Throwable $e){error_log('Lumen public knowledge: '.$e->getCode());lumen_reply(503,['error'=>'Conhecimento temporariamente indisponível.']);}
