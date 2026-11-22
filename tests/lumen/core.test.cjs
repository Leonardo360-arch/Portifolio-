const assert=require('node:assert/strict');
const {interpret,createState}=require('../../assets/js/lumen/core.js');
assert.deepEqual(interpret('Mostre os projetos!'),{type:'navigate',id:'projetos'});
assert.deepEqual(interpret('Altere o tema para azul'),{type:'theme',id:2});
assert.deepEqual(interpret('tema 5'),{type:'theme',id:5});
for(const x of ['tema 6','tema 2; alert(1)','não abra projetos','execute SQL','<script>'])assert.equal(interpret(x).type,'unknown');
assert.equal(interpret('Quais tecnologias?').type,'stack');
assert.equal(interpret('currículo').type,'resume');
const seen=[];const s=createState(v=>seen.push(v));assert.equal(s.move('responding'),false);assert.equal(s.move('processing'),true);assert.equal(s.move('responding'),true);assert.equal(s.move('idle'),true);assert.deepEqual(seen,['processing','responding','idle']);console.log('Lumen core: assertions passed');
