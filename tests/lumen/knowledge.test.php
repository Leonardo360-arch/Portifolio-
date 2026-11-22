<?php
declare(strict_types=1);
require_once __DIR__.'/../../config/lumen-knowledge.php';
require_once __DIR__.'/../../config/lumen-context.php';
require_once __DIR__.'/../../config/lumen-learning.php';
function check(bool $ok,string $label): void {if(!$ok)throw new RuntimeException($label);echo "PASS: $label\n";}
check(in_array('etica',LumenKnowledge::tokens('Ética profissional'),true),'accent normalization');
check(LumenLearning::propose('Valorizo a ética')['category']==='personalidade','personality classification');
check(LumenLearning::propose('Utilizo Java')['category']==='tecnico','technical classification');
try{LumenLearning::propose(' ');throw new RuntimeException('Accepted empty learning');}catch(InvalidArgumentException $e){echo "PASS: empty learning rejected\n";}
$session=[];
for($i=0;$i<6;$i++)LumenContext::append($session,'1','Q'.$i,'A'.$i);
check(count(LumenContext::read($session,'1'))===4,'bounded context');
check(LumenContext::read($session,'2')===[],'admin isolation');
$session['lumen_context']['expires']=time()-1;
check(LumenContext::read($session,'1')===[],'expiration');
LumenContext::clear($session);
check(!isset($session['lumen_context']),'clear');
$rows=[['id'=>1,'title'=>'Arquitetura','project_title'=>'Mon Glacé','content'=>'PHP e MySQL'],['id'=>2,'title'=>'Formação','project_title'=>null,'content'=>'Curso técnico']];
check(LumenKnowledge::rank('Mon Glacé',$rows)[0]['id']===1,'project retrieval');
check(LumenKnowledge::rank('astronomia',$rows)===[],'no unsupported match');
