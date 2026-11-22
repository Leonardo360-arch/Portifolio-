(function(){
'use strict';
const form=document.getElementById('recruiter-form');if(!form)return;
const log=document.getElementById('recruiter-log'),status=document.getElementById('recruiter-status'),input=document.getElementById('recruiter-question'),project=document.getElementById('recruiter-project'),speak=document.getElementById('recruiter-speak');
let busy=false,version=0,controller=null;
function append(who,message){const p=document.createElement('p');const b=document.createElement('strong');b.textContent=who+': ';p.append(b,document.createTextNode(message));log.append(p);while(log.children.length>16)log.firstElementChild.remove();log.scrollTop=log.scrollHeight;}
async function ask(label,params){
 if(busy)return;busy=true;const current=++version;controller=new AbortController();append('Você',label);status.textContent='Consultando conhecimentos aprovados…';
 const timeout=setTimeout(()=>controller?.abort(),10000);
 try{const res=await fetch('api/lumen-recruiter.php',{method:'POST',credentials:'same-origin',cache:'no-store',signal:controller.signal,body:new URLSearchParams({csrf:form.elements.csrf.value,...params})});const data=await res.json();if(current!==version)return;if(!res.ok)throw new Error(data.error||'Consulta indisponível.');append('L.U.M.E.N.',data.message);status.textContent='Consulta concluída.';if(speak.checked&&'speechSynthesis' in window){const u=new SpeechSynthesisUtterance(data.message);u.lang='pt-BR';window.speechSynthesis.cancel();window.speechSynthesis.speak(u);}}
 catch(e){if(current===version)status.textContent=e.name==='AbortError'?'Consulta interrompida.':e.message;}
 finally{clearTimeout(timeout);if(version===current){busy=false;controller=null;}}
}
form.addEventListener('submit',e=>{e.preventDefault();const question=input.value.trim();if(!question||busy)return;input.value='';ask(question,{mode:'free',question});});
document.querySelectorAll('[data-topic]').forEach(b=>b.addEventListener('click',()=>ask(b.textContent,{mode:'topic',topic:b.dataset.topic})));
document.querySelectorAll('[data-project-topic]').forEach(b=>b.addEventListener('click',()=>{if(!project.value){status.textContent='Selecione um projeto primeiro.';project.focus();return;}ask(project.selectedOptions[0].textContent+' — '+b.textContent,{mode:'project',project_id:project.value,topic:b.dataset.projectTopic});}));
document.getElementById('recruiter-clear').addEventListener('click',()=>{version++;controller?.abort();controller=null;busy=false;log.replaceChildren();input.value='';status.textContent='Conversa limpa.';if('speechSynthesis' in window)window.speechSynthesis.cancel();});
})();
