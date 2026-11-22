<?php
declare(strict_types=1);

/** Read-only tools. Never expose raw messages, draft contents, credentials or SQL. */
final class LumenAdminTools {
    public function __construct(private PDO $db) {}
    public static function definitions(): array {
        $tools=[];
        foreach([
            'project_overview'=>'Count active and inactive projects.',
            'knowledge_overview'=>'Count approved and draft knowledge records, without their contents.',
            'project_training_gaps'=>'List up to 20 active projects and missing standard interview questions. Exact title match is only a coverage indicator, not quality validation.'
        ] as $name=>$description) {
            $tools[]=['type'=>'function','name'=>$name,'description'=>$description,'strict'=>true,
            'parameters'=>['type'=>'object','properties'=>(object)[],'required'=>[],'additionalProperties'=>false]];
        }
        return $tools;
    }
    public function invoke(string $name,string $arguments): array {
        $args=json_decode($arguments,false,8,JSON_THROW_ON_ERROR);
        if(!is_object($args)||count(get_object_vars($args))!==0)throw new InvalidArgumentException('Invalid arguments');
        return match($name){
            'project_overview'=>$this->db->query('SELECT COUNT(*) AS total, COALESCE(SUM(ativo=1),0) AS active, COALESCE(SUM(ativo<>1 OR ativo IS NULL),0) AS inactive FROM projetos')->fetch(),
            'knowledge_overview'=>$this->db->query('SELECT COUNT(*) AS total, COALESCE(SUM(published=1),0) AS approved, COALESCE(SUM(published=0),0) AS drafts FROM lumen_knowledge')->fetch(),
            'project_training_gaps'=>$this->gaps(),
            default=>throw new InvalidArgumentException('Tool not permitted')
        };
    }
    private function gaps(): array {
        $catalog=require __DIR__.'/lumen-interview.php';
        $questions=$catalog['projeto']['questions'];
        $projects=$this->db->query('SELECT id,titulo FROM projetos WHERE ativo=1 ORDER BY id LIMIT 20')->fetchAll();
        $s=$this->db->prepare("SELECT DISTINCT title FROM lumen_knowledge WHERE project_id=? AND category='projeto' AND published=1");
        $out=[];
        foreach($projects as $p){
            $s->execute([$p['id']]);$titles=$s->fetchAll(PDO::FETCH_COLUMN);
            $missing=array_values(array_filter($questions,static fn($q)=>!in_array($q,$titles,true)));
            $out[]=['id'=>(int)$p['id'],'title'=>$p['titulo'],'missing_exact_title'=>$missing];
        }
        return ['projects'=>$out,'limit'=>20,'method'=>'Exact approved interview titles only; other records may cover the same topic.'];
    }
}
