/* RF-04: quadro de física 2D com Matter.js. Requer matter.min.js carregado antes.
   Os badges são elementos DOM (herdam o tema por CSS) sincronizados com os corpos físicos. */
(function () {
  'use strict';
  var board = document.getElementById('stack-board');
  if (!board || typeof Matter === 'undefined') return;

  var M = Matter, engine, runner, mouseC, bodies = [], els = [], visible = true;
  var STACK = (board.dataset.stack ||
    'PHP,JavaScript,MySQL,Bootstrap,Figma,Docker,Gemini,Claude,OpenAI').split(',');
  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function build() {
    teardown();
    var W = board.clientWidth, H = board.clientHeight;
    engine = M.Engine.create();
    engine.gravity.y = 1;
    var t = 60, wall = { isStatic: true, render: { visible: false } };
    M.Composite.add(engine.world, [
      M.Bodies.rectangle(W / 2, H + t / 2, W * 3, t, wall),   // chão
      M.Bodies.rectangle(-t / 2, H / 2, t, H * 4, wall),      // esquerda
      M.Bodies.rectangle(W + t / 2, H / 2, t, H * 4, wall)    // direita
    ]);

    STACK.forEach(function (name, i) {
      var el = document.createElement('div');
      el.className = 'stack-badge'; el.textContent = name.trim();
      board.appendChild(el);
      var w = el.offsetWidth, h = el.offsetHeight;
      var b = M.Bodies.rectangle(
        w / 2 + Math.random() * Math.max(1, W - w), -h - i * (h + 8), w, h,
        { restitution: .5, friction: .2, chamfer: { radius: h / 2 }, angle: (Math.random() - .5) * .6 });
      bodies.push(b); els.push(el);
    });
    M.Composite.add(engine.world, bodies);

    var mouse = M.Mouse.create(board);
    // libera a rolagem da página (Matter captura a roda do mouse por padrão)
    board.removeEventListener('mousewheel', mouse.mousewheel);
    board.removeEventListener('DOMMouseScroll', mouse.mousewheel);
    board.removeEventListener('wheel', mouse.mousewheel);
    mouseC = M.MouseConstraint.create(engine, { mouse: mouse, constraint: { stiffness: .2, render: { visible: false } } });
    M.Composite.add(engine.world, mouseC);

    // "endireita" os badges: sem isso o retangulo pode ficar de cabeca para baixo (180 graus tambem e posicao de repouso)
    M.Events.on(engine, 'beforeUpdate', function () {
      var TWO_PI = Math.PI * 2;
      for (var i = 0; i < bodies.length; i++) {
        var b = bodies[i], n = b.angle - Math.round(b.angle / TWO_PI) * TWO_PI;
        M.Body.setAngularVelocity(b, b.angularVelocity * 0.97 - n * 0.012);
      }
    });

    if (reduced) { for (var s = 0; s < 400; s++) M.Engine.update(engine, 1000 / 60); }
    runner = M.Runner.create();
    M.Runner.run(runner, engine);
    M.Events.on(engine, 'afterUpdate', sync);
    sync();
  }

  function sync() {
    for (var i = 0; i < bodies.length; i++) {
      var b = bodies[i];
      els[i].style.transform = 'translate(' + (b.position.x - els[i].offsetWidth / 2) + 'px,' +
        (b.position.y - els[i].offsetHeight / 2) + 'px) rotate(' + b.angle + 'rad)';
    }
  }

  function teardown() {
    if (runner) M.Runner.stop(runner);
    if (engine) { M.Events.off(engine); M.World.clear(engine.world, false); M.Engine.clear(engine); }
    els.forEach(function (e) { e.remove(); });
    bodies = []; els = [];
  }

  // pausa quando o quadro sai da tela (economiza CPU/bateria)
  if ('IntersectionObserver' in window) {
    new IntersectionObserver(function (en) {
      visible = en[0].isIntersecting;
      if (runner && engine) { runner.enabled = visible; }
    }).observe(board);
  }

  var rt; function rebuildSoon(ms) { clearTimeout(rt); rt = setTimeout(build, ms); }
  window.addEventListener('resize', function () { rebuildSoon(250); });
  document.addEventListener('themechange', function () { rebuildSoon(450); }); // fontes/medidas mudam por tema
  build();
})();
