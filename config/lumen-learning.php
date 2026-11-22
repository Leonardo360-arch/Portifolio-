<?php
declare(strict_types=1);
require_once __DIR__.'/lumen-knowledge.php';
final class LumenLearning {
 public static function propose(string $text): array {
  $text=trim($text);
  if($text===''||mb_strlen($text)>240)throw new InvalidArgumentException('Informe um aprendizado de até 240 caracteres.');
  $words=LumenKnowledge::tokens($text);
  $category='perfil';
  foreach(['personalidade'=>['valores','etica','responsabilidade','personalidade','respeito','honestidade'],'metodologia'=>['metodologia','planejamento','testes','qualidade','processo'],'tecnico'=>['php','java','mysql','arquitetura','tecnologia','solid']] as $key=>$terms){
   if(array_intersect($words,$terms)){$category=$key;break;}
  }
  return ['category'=>$category,'title'=>mb_substr($text,0,150),'content'=>$text];
 }
 public static function related(PDO $db,string $text): array {
  $rows=(new LumenKnowledge($db))->search($text);
  return array_map(static fn($r)=>['id'=>(int)$r['id'],'title'=>$r['title']],$rows);
 }
 public static function save(PDO $db,array $draft): int {
  $s=$db->prepare("INSERT INTO lumen_knowledge(category,project_id,title,content,source,published) VALUES(?,NULL,?,?,?,0)");
  $s->execute([$draft['category'],$draft['title'],$draft['content'],'Aprendizado por conversa administrativa']);
  return (int)$db->lastInsertId();
 }
}
