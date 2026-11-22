<?php
declare(strict_types=1);
require_once __DIR__.'/../config/security.php';
require_once __DIR__.'/../config/database.php';
secure_session();
send_security_headers();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
function reply(int $code,array $data): never { http_response_code($code); echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE); exit; }
if(empty($_SESSION['admin_id'])) reply(401,['error'=>'Sessão encerrada. Entre novamente.']);
if($_SERVER['REQUEST_METHOD']!=='POST') reply(405,['error'=>'Método inválido.']);
$token=$_POST['csrf']??null;
if(!is_string($token)||empty($_SESSION['csrf'])||!hash_equals($_SESSION['csrf'],$token)) reply(419,['error'=>'Sessão inválida. Recarregue a página.']);
try {
 $db=Database::conn();
 $a=$db->prepare('SELECT id FROM admin_users WHERE id=?');$a->execute([$_SESSION['admin_id']]);
 if(!$a->fetchColumn())reply(403,['error'=>'Administrador não autorizado.']);
 $action=$_POST['action']??'';
 require_once __DIR__.'/../config/lumen-context.php';
 if($action==='clear_context'){LumenContext::clear($_SESSION);reply(200,['message'=>'Contexto da conversa limpo.']);}
 if($action==='learn_preview'||$action==='learn_confirm'){
  require_once __DIR__.'/../config/lumen-learning.php';
  if($action==='learn_preview'){
   $raw=$_POST['content']??null;
   if(!is_string($raw))reply(400,['error'=>'Aprendizado inválido.']);
   try{$draft=LumenLearning::propose($raw);}catch(InvalidArgumentException $e){reply(400,['error'=>$e->getMessage()]);}
   if(($_POST['correction']??'')==='1')$draft['category']='correcao';
   $related=LumenLearning::related($db,$draft['content']);
   $nonce=bin2hex(random_bytes(24));
   unset($_SESSION['lumen_pending']);
   $_SESSION['lumen_learning_pending']=['nonce'=>$nonce,'draft'=>$draft,'admin'=>(string)$_SESSION['admin_id'],'expires'=>time()+90];
   reply(200,['message'=>'Revise a proposta. Salvar não publica o conhecimento.','draft'=>$draft,'related'=>$related,'nonce'=>$nonce]);
  }
  $pending=$_SESSION['lumen_learning_pending']??null;
  $nonce=$_POST['nonce']??null;
  if(!$pending||!is_string($nonce)||!hash_equals($pending['nonce'],$nonce)||$pending['expires']<time()||$pending['admin']!==(string)$_SESSION['admin_id'])reply(409,['error'=>'Confirmação inválida ou expirada.']);
  $category=$_POST['category']??null;
  if(!in_array($category,['personalidade','perfil','tecnico','metodologia','correcao'],true))reply(400,['error'=>'Categoria inválida.']);
  unset($_SESSION['lumen_learning_pending']);
  $draft=$pending['draft'];$draft['category']=$category;
  $id=LumenLearning::save($db,$draft);
  reply(200,['message'=>'Rascunho #'.$id.' salvo. Revise no Training Center antes de publicar.','id'=>$id]);
 }
 if($action==='ask'){
  require_once __DIR__.'/../config/lumen-ai.php';
  $q=$_POST['question']??null;
  if(!is_string($q)||trim($q)===''||mb_strlen($q)>240)reply(400,['error'=>'Pergunta inválida.']);
  $service=new LumenAI($db);
  $admin=(string)$_SESSION['admin_id'];
  $external=($_POST['external']??'')==='1';
  $remember=($_POST['remember']??'')==='1';
  if(!$remember)LumenContext::clear($_SESSION);
  $context=$remember?LumenContext::read($_SESSION,$admin):[];
  // Keep session lock until the bounded request ends to serialize clear/append.
  $result=$service->answer($q,$external,$context);
  if($remember)LumenContext::append($_SESSION,$admin,$q,$result['message']);
  reply(200,$result);
 }
 if($action==='summary'){
  $p=(int)$db->query('SELECT COUNT(*) FROM projetos')->fetchColumn();
  $m=(int)$db->query('SELECT COUNT(*) FROM mensagens WHERE lida=0')->fetchColumn();
  reply(200,['message'=>"Você possui $p projetos cadastrados e $m mensagens não lidas."]);
 }
 if($action==='knowledge'){
  $d=(int)$db->query('SELECT COUNT(*) FROM lumen_knowledge WHERE published=0')->fetchColumn();
  $p=(int)$db->query('SELECT COUNT(*) FROM lumen_knowledge WHERE published=1')->fetchColumn();
  reply(200,['message'=>"Conhecimentos: $d rascunhos e $p aprovados."]);
 }
 if($action==='preview'||$action==='confirm'){
  if($action==='preview'){
   $id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
   $op=$_POST['operation']??'';
   if(!$id||!in_array($op,['publish','unpublish'],true))reply(400,['error'=>'Comando inválido.']);
   $s=$db->prepare('SELECT id,title,category,content,source,project_id,published,updated_at FROM lumen_knowledge WHERE id=?');$s->execute([$id]);$row=$s->fetch();
   if(!$row)reply(404,['error'=>'Conhecimento não encontrado.']);
   $nonce=bin2hex(random_bytes(24));
   unset($_SESSION['lumen_learning_pending']);
   $_SESSION['lumen_pending']=['nonce'=>$nonce,'id'=>$id,'op'=>$op,'hash'=>hash('sha256',serialize($row)),'expires'=>time()+90,'admin'=>(string)$_SESSION['admin_id']];
   reply(200,['message'=>($op==='publish'?'Aprovar publicação':'Retirar publicação').' do registro #'.$id.' — '.$row['title'].'? Revise o conteúdo no Training Center.','nonce'=>$nonce]);
  }
  $pending=$_SESSION['lumen_pending']??null;
  $nonce=$_POST['nonce']??null;
  if(!$pending||!is_string($nonce)||!hash_equals($pending['nonce'],$nonce)||$pending['expires']<time()||$pending['admin']!==(string)$_SESSION['admin_id'])reply(409,['error'=>'Confirmação expirada ou inválida.']);
  unset($_SESSION['lumen_pending']);
  $db->beginTransaction();
  $s=$db->prepare('SELECT id,title,category,content,source,project_id,published,updated_at FROM lumen_knowledge WHERE id=? FOR UPDATE');$s->execute([$pending['id']]);$row=$s->fetch();
  if(!$row||!hash_equals($pending['hash'],hash('sha256',serialize($row)))){$db->rollBack();reply(409,['error'=>'Registro alterado. Solicite novamente.']);}
  $db->prepare('UPDATE lumen_knowledge SET published=? WHERE id=?')->execute([$pending['op']==='publish'?1:0,$pending['id']]);
  $db->commit();
  reply(200,['message'=>'Registro #'.$pending['id'].' atualizado.']);
 }
 if($action==='cancel'){unset($_SESSION['lumen_pending'],$_SESSION['lumen_learning_pending']);reply(200,['message'=>'Operação cancelada.']);}
 reply(400,['error'=>'Ação não autorizada.']);
} catch(Throwable $e){if(isset($db)&&$db->inTransaction())$db->rollBack();error_log('Lumen admin: '.$e->getCode());reply(500,['error'=>'Não foi possível concluir. Verifique o banco e a migração.']);}
