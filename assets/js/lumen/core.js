(function(root){
'use strict';
function normalize(s){return String(s).normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase().trim().replace(/[.!?]+$/,'');}
function interpret(input){
 const q=normalize(input);
 const theme=q.match(/^(?:(?:mude|troque|altere) (?:o )?)?tema (?:para )?(1|2|3|4|5|azul|verde|laranja|roxo|escuro)$/);
 if(theme){const names={escuro:1,azul:2,verde:3,laranja:4,roxo:5};return {type:'theme',id:names[theme[1]]||Number(theme[1])};}
 const sections={projetos:'projetos',tecnologias:'stack',github:'repositorios',repositorios:'repositorios',contato:'contato',formacao:'cursos',cursos:'cursos',inicio:'inicio'};
 const nav=q.match(/^(?:(?:mostre|ver|abrir|abra|ir para|quero ver) (?:os |as |o |a )?)?(projetos|tecnologias|github|repositorios|contato|formacao|cursos|inicio)$/);
 if(nav)return {type:'navigate',id:sections[nav[1]]};
 if(/^(?:quais (?:sao )?(?:as )?tecnologias|quais tecnologias (?:ele usa|voce usa))$/.test(q))return {type:'stack'};
 if(/^(?:quem e voce|o que e lumen|ola|oi|ajuda)$/.test(q))return {type:'help'};
 if(/^(?:(?:mostre|ver|abrir|baixar) (?:o )?)?curriculo$/.test(q))return {type:'resume'};
 return {type:'unknown'};
}
const transitions={idle:['processing'],processing:['responding','error'],responding:['idle'],error:['idle']};
function createState(onChange){let state='idle';return {get:()=>state,move(next){if(!transitions[state].includes(next))return false;state=next;onChange(state);return true;}};}
const api={interpret,createState};if(typeof module==='object'&&module.exports)module.exports=api;else root.LumenCore=Object.freeze(api);
})(typeof globalThis!=='undefined'?globalThis:this);
