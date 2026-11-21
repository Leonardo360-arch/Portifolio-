#!/usr/bin/env bash
# Rodar: docker compose exec web bash docker/smoke.sh
cd "$(dirname "$0")/.." || exit 1
BASE=http://localhost; fail=0
ok(){ echo "[OK] $1"; }
bad(){ echo "[FALHOU] $1"; fail=1; }
code(){ curl -s -o /dev/null -w '%{http_code}' "$@"; }

errs=$(find . -name '*.php' -not -path './.git/*' -print0 | xargs -0 -n1 php -l 2>&1 | grep -v '^No syntax errors' || true)
if [ -z "$errs" ]; then ok "Sintaxe de todos os .php"; else bad "Sintaxe PHP"; echo "$errs"; fi

c=$(code "$BASE/");                    [ "$c" = 200 ] && ok "Pagina inicial (200)" || bad "Pagina inicial ($c)"
c=$(code "$BASE/admin/login.php");     [ "$c" = 200 ] && ok "Tela de login (200)" || bad "Tela de login ($c)"
c=$(code "$BASE/admin/dashboard.php"); { [ "$c" = 302 ] || [ "$c" = 200 ]; } && ok "Painel protegido ($c)" || bad "Painel ($c)"
c=$(code "$BASE/enviar_contato.php");  [ "$c" = 405 ] && ok "Contato recusa GET (405)" || bad "Contato GET ($c)"

for f in .env schema.sql config/env.php .htaccess; do
  c=$(code "$BASE/$f")
  { [ "$c" = 403 ] || [ "$c" = 404 ]; } && ok "Bloqueado: /$f ($c)" || bad "EXPOSTO: /$f ($c)"
done

echo '<?php echo "EXECUTED";' > uploads/_smoke.php
body=$(curl -s "$BASE/uploads/_smoke.php"); rm -f uploads/_smoke.php
if echo "$body" | grep -q EXECUTED; then bad "PHP em uploads/ foi executado"; else ok "PHP em uploads/ nao executa"; fi

[ "$fail" = 0 ] && echo "Todos os testes passaram" || echo "Ha falhas: veja as linhas [FALHOU]"
exit $fail
