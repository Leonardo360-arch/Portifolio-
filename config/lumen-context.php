<?php
declare(strict_types=1);

/** Temporary context, scoped to the authenticated session. Never used as evidence. */
final class LumenContext {
    public static function read(array $session,string $admin): array {
        $state=$session['lumen_context']??null;
        if(!is_array($state)||($state['admin']??null)!==$admin||($state['expires']??0)<time())return [];
        return array_slice($state['turns']??[],-4);
    }
    public static function append(array &$session,string $admin,string $question,string $answer): void {
        $turns=self::read($session,$admin);
        $turns[]=['question'=>mb_substr($question,0,240),'answer'=>mb_substr($answer,0,800)];
        $session['lumen_context']=['admin'=>$admin,'expires'=>time()+1800,'turns'=>array_slice($turns,-4)];
    }
    public static function clear(array &$session): void {unset($session['lumen_context']);}
}
