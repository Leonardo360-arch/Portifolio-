<?php
declare(strict_types=1);

final class LumenKnowledge
{
    public function __construct(private PDO $db) {}
    public static function tokens(string $text): array
    {
        $text=mb_strtolower($text,'UTF-8');
        $text=strtr($text,['á'=>'a','à'=>'a','ã'=>'a','â'=>'a','é'=>'e','ê'=>'e','í'=>'i','ó'=>'o','ô'=>'o','õ'=>'o','ú'=>'u','ç'=>'c']);
        $words=preg_split('/[^\p{L}\p{N}]+/u',$text,-1,PREG_SPLIT_NO_EMPTY) ?: [];
        $stop=['qual','quais','como','porque','por','que','quem','sobre','para','com','uma','dos','das','esse','este','esta','foi','sao','tem','ele','voce','seu','sua','projeto','projetos','lumen','me','fale','mais','detalhes','desenvolvido'];
        return array_values(array_unique(array_filter($words,static fn($w)=>mb_strlen($w)>=2&&!in_array($w,$stop,true))));
    }
    public static function rank(string $question,array $rows): array
    {
        $tokens=self::tokens($question);
        if(!$tokens)return [];
        $ranked=[];
        foreach($rows as $row){
            $title=self::tokens($row['title']);
            $project=self::tokens($row['project_title']??'');
            $body=self::tokens($row['content']);
            $score=0;
            foreach($tokens as $token){
                if(in_array($token,$title,true))$score+=4;
                if(in_array($token,$project,true))$score+=6;
                if(in_array($token,$body,true))$score+=1;
            }
            if($score>=4)$ranked[]=['score'=>$score,'row'=>$row];
        }
        usort($ranked,static fn($a,$b)=>($b['score']<=>$a['score'])?:((int)$a['row']['id']<=>(int)$b['row']['id']));
        return array_column(array_slice($ranked,0,3),'row');
    }
    public function search(string $question): array
    {
        // Public scope only, including when called by the admin console.
        $rows=$this->db->query("SELECT k.id,k.title,k.category,LEFT(k.content,4000) AS content,p.titulo AS project_title FROM lumen_knowledge k LEFT JOIN projetos p ON p.id=k.project_id WHERE k.published=1 AND (k.project_id IS NULL OR p.ativo=1) ORDER BY k.updated_at DESC,k.id DESC LIMIT 300")->fetchAll();
        return self::rank($question,$rows);
    }
    public static function answer(array $rows): string
    {
        if(!$rows)return 'Não encontrei conhecimento aprovado suficiente para responder. Você pode entrar em contato com o desenvolvedor.';
        $out=['Encontrei estes registros aprovados relacionados à pergunta:'];
        foreach($rows as $r)$out[]=($r['project_title']? $r['project_title'].' — ':'').$r['title']."\n".mb_substr($r['content'],0,1200,'UTF-8').' [Registro #'.$r['id'].']';
        return implode("\n\n",$out);
    }
}
