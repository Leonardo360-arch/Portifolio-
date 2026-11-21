# Portfolio Interativo LMC (v2)

PHP 8 + MySQL, pronto para InfinityFree. Autor: Leonardo Marson Coral (DSM, FATEC Araras).

## Deploy
1. Crie o banco no painel do InfinityFree e importe `schema.sql`.
2. Copie `.env.example` para `.env` e preencha DB e SMTP (Gmail exige senha de app).
3. Envie o conteudo desta pasta para `/htdocs`. Garanta permissao de escrita em `uploads/`.
4. Crie o admin: gere o hash com `php -r "echo password_hash('SENHA', PASSWORD_BCRYPT);"` e rode o INSERT comentado no fim do `schema.sql`.
5. Acesse `/admin/login.php`, cadastre projetos, cursos e o PDF do curriculo.

## Checklist de QA (testar no ambiente real)
- [ ] `php -l` em todos os `.php`
- [ ] RF-01: 5 temas por clique, arrasto e teclas 1-5; tema persiste ao recarregar
- [ ] RF-02: login, bloqueio apos 5 erros, CRUD, ordenar, curriculo, logout
- [ ] RF-03: cards com print WebP, tags e botao Testar
- [ ] RF-04: badges colidem e respondem ao arraste (mouse e toque)
- [ ] RF-05: repositorios carregam; segunda visita usa cache (6 h)
- [ ] RF-06: mensagem aparece no painel; e-mail chega (se SMTP liberado)
- [ ] RF-07: botao WhatsApp abre com texto pre-preenchido
- [ ] `.env` e `schema.sql` retornam 403 no navegador
- [ ] Upload de imagem grande sai menor que 2 MB em WebP (precisa de GD com WebP)

## Limites conhecidos
- Plano gratuito do InfinityFree pode bloquear SMTP de saida; mensagens seguem salvas no banco.
- Sem Content-Security-Policy, por causa de scripts injetados pela hospedagem gratuita.
