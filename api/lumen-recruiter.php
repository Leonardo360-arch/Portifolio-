<?php
declare(strict_types=1);
require_once __DIR__.'/../config/security.php';
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/lumen-knowledge.php';
send_security_headers();header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');
function respond(int $status,array $body): never {http_response_code($status);echo json_encode($body,JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);exit;}
if($_SERVER['REQUEST_METHOD']!=='POST')respond(405,['error'=>'Método inválido.']);
if((int)($_SERVER['CONTENT_LENGTH']??0)>4096)respond(413,['error'=>'Solicitação muito grande.']);
secure_session();
$csrf=$_POST['csrf']??null;
if(!is_string($csrf)||!is_string($_SESSION['csrf']??null)||!hash_equals($_SESSION['csrf'],$csrf))respond(419,['error'=>'Recarregue a página.']);
$now=time();$hits=array_filter($_SESSION['lumen_read_hits']??[],static fn($t)=>$t>$now-60);
if(count($hits)>=12)respond(429,['error'=>'Limite temporário de consultas.']);
$hits[]=$now;$_SESSION['lumen_read_hits']=$hits;session_write_close();
$catalog=require __DIR__.'/../config/lumen-interview.php';
try{
 $db=Database::conn();$mode=$_POST['mode']??'free';
 if($mode==='free'){
  $q=$_POST['question']??null;
  if(!is_string($q)||trim($q)===''||mb_strlen($q)>240)respond(400,['error'=>'Pergunta inválida.']);
  $rows=(new LumenKnowledge($db))->search($q);
 }elseif($mode==='project'){
  $id=filter_var($_POST['project_id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
  $topic=$_POST['topic']??null;
  if(!$id||!is_string($topic)||!isset($catalog['projeto']['questions'][$topic]))respond(400,['error'=>'Selecione projeto e tema válidos.']);
  $s=$db->prepare('SELECT id FROM projetos WHERE ativo=1 AND id=?');$s->execute([$id]);if(!$s->fetchColumn())respond(404,['error'=>'Projeto indisponível.']);
  $s=$db->prepare("SELECT k.id,k.title,LEFT(k.content,1200) AS content,p.titulo AS project_title FROM lumen_knowledge k JOIN projetos p ON p.id=k.project_id WHERE k.published=1 AND p.ativo=1 AND k.project_id=? AND k.category='projeto' AND k.title=? ORDER BY k.updated_at DESC,k.id DESC LIMIT 3");
  $s->execute([$id,$catalog['projeto']['questions'][$topic]]);$rows=$s->fetchAll();
 }elseif($mode==='topic'){
  $topic=$_POST['topic']??null;$map=['formacao'=>['perfil','formacao'],'experiencia'=>['perfil','experiencia'],'valores'=>['personalidade','valores'],'metodologia'=>['metodologia','planejamento'],'competencias'=>['perfil','competencias']];
  if(!is_string($topic)||!isset($map[$topic]))respond(400,['error'=>'Tema inválido.']);
  [$category,$key]=$map[$topic];
  $s=$db->prepare("SELECT id,title,LEFT(content,1200) AS content,NULL AS project_title FROM lumen_knowledge WHERE published=1 AND project_id IS NULL AND category=? AND title=? ORDER BY updated_at DESC,id DESC LIMIT 3");
  $s->execute([$category,$catalog[$category]['questions'][$key]]);$rows=$s->fetchAll();
 }else respond(400,['error'=>'Modo inválido.']);
 respond(200,['message'=>LumenKnowledge::answer($rows),'mode'=>'local']);
}catch(Throwable $e){error_log('Recruiter knowledge unavailable');respond(503,['error'=>'Consulta temporariamente indisponível.']);}
