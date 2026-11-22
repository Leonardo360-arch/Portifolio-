(function(){
'use strict';
const panel=document.getElementById('lumen-panel'), core=window.LumenCore;
if(!panel||!core)return;
const form=panel.querySelector('form'), input=panel.querySelector('input'), log=panel.querySelector('[role=log]'), status=panel.querySelector('[role=status]');
const state=core.createState(s=>{panel.dataset.state=s;status.textContent={idle:'Disponível',processing:'Interpretando',responding:'Respondendo',error:'Falha'}[s];});
function append(who,message){const p=document.createElement('p');const b=document.createElement('strong');b.textContent=who+': ';p.append(b,document.createTextNode(message));log.append(p);while(log.children.length>20)log.firstElementChild.remove();log.scrollTop=log.scrollHeight;}
function execute(cmd){
 switch(cmd.type){
 case 'theme':return window.LMCTheme?.set(cmd.id)?'Tema atualizado.':'Não consegui alterar o tema.';
 case 'navigate':{const el=document.getElementById(cmd.id);if(!el)return 'Esta seção ainda não está disponível.';const heading=el.querySelector('h1,h2')||el;heading.setAttribute('tabindex','-1');heading.focus({preventScroll:true});el.scrollIntoView({block:'start'});return 'Seção aberta.';}
 case 'stack':{const value=document.getElementById('stack-board')?.dataset.stack;return value?'Tecnologias cadastradas: '+value:'Nenhuma tecnologia cadastrada.';}
 case 'resume':{const link=document.getElementById('lmc-resume');if(!link)return 'O currículo ainda não está disponível.';const a=document.createElement('a');a.href=link.href;a.target='_blank';a.rel='noopener noreferrer';a.textContent='Abrir currículo';const p=document.createElement('p');p.append(a);log.append(p);return 'Use o link acima para abrir o currículo.';}
 case 'help':return 'Sou o L.U.M.E.N., assistente local do portfólio. Posso abrir projetos, tecnologias, GitHub, formação, contato e currículo ou trocar o tema de 1 a 5.';
 default:return 'Ainda não compreendo essa solicitação. Experimente “ver projetos”, “quais tecnologias” ou “tema azul”.';
 }
}
function submit(value){value=value.trim();if(!value||value.length>240||state.get()!=='idle')return;append('Você',value);state.move('processing');requestAnimationFrame(()=>{try{const reply=execute(core.interpret(value));state.move('responding');append('L.U.M.E.N.',reply);}catch(e){state.move('error');append('L.U.M.E.N.','Não foi possível executar o comando.');}finally{state.move('idle');}});}
form.addEventListener('submit',e=>{e.preventDefault();const value=input.value;input.value='';submit(value);});
panel.querySelectorAll('[data-command]').forEach(b=>b.addEventListener('click',()=>submit(b.dataset.command)));
})();
