# Sprint 0 — Auditoria e plano L.U.M.E.N. (v3)
Base auditada: ZIP do projeto, commit 8baade644f3fdd89f9ba77a14bd00889b5d9c9d1. Branch de trabalho: feat/portfolio-v3-lumen. Main preservada.

## Evidências
45 arquivos versionados; 20 PHP e 4 JS. Todos passaram em php -l (PHP 8.4.24 CLI) e node --check (Node 22). Não foi executado Docker, banco, navegador ou smoke HTTP neste ambiente. Não constitui aprovação de segurança nem comparação integral com remoto.

## Achados e encaminhamentos
| ID | Prioridade | Evidência | Responsável | Critério |
|---|---|---|---|---|
| SEC-01 | Alta | admin/setup.php aceita IP privado sem token | Full stack e segurança | Token obrigatório fora de modo local explicitamente configurado; testar proxy e criação simultânea |
| SEC-02 | Alta | admin/login.php limita por sessão | Backend e DBA | Limitação persistente, testar troca de cookie e expiração |
| SEC-03 | Alta | docker-compose.yml publica portas sem bind loopback | DevOps | Padrão 127.0.0.1, preservar variáveis de portas |
| SEC-04 | Média | config/upload.php decodifica imagem sem limite prévio de pixels | Backend | Limite de dimensões e pixels antes de decodificar; testar falhas de escrita |
| SEC-05 | Média | partials/sections-stack-github.php CDN Matter.js sem SRI | Front-end | Integridade ou dependência local e fallback |
| QA-01 | Média | docker/smoke.sh aceita 200 no painel anônimo | QA | Exigir 302 e Location de login; validar conteúdo e bloqueio HTTP |
| UX-01 | Média | assets/js/themes.js expõe set sem validação | Front-end | Validar inteiro de 1 a 5 |
| SEC-06 | Alta | Repositório público; configurações de desenvolvimento e histórico precisam de revisão | Segurança/DevOps | Varredura de segredos sem divulgar valores; revogar se necessário; proteger .env e .git |
| SEC-07 | Alta | .dockerignore não exclui .env; verificar Dockerfile e contexto | DevOps | Excluir segredos do contexto e testar imagem |
| UX-02 | Média | index.php combina consulta, composição e HTML | Engenharia | Separação incremental sem quebrar rotas |

## ADR-001 — Arquitetura
Preservar PHP/MySQL, painel e cinco temas. Separar módulo Lumen: interface JS/CSS (tokens de tema), roteador de intenções, catálogo fechado de comandos e adaptadores. Inicialmente comandos locais sem API; nenhuma execução arbitrária de JS, SQL ou shell. Interface de provedor de respostas somente quando houver integração real. State para transições idle/listening/processing/responding/error; Observer via eventos quando necessário; Command para ações validadas; Strategy para provedores quando houver alternativas. Evitar abstrações sem necessidade.
Dados públicos de projetos ativos, cursos ativos e configurações explicitamente permitidas; nunca mensagens, hashes, credenciais ou sessões. Não enviar dados privados ao provedor. Se IA habilitada futuramente, backend com chave fora do Git, timeout, limites persistentes, orçamento e fallback. RAG somente se volume justificar; inicialmente catálogo estruturado. HTTPS e consentimento explícito para microfone, voz opcional, alternativa textual, sem escuta contínua. Compatibilidade e custo do provedor/hospedagem ainda não validados.
Comandos MVP: navegar para projetos/stack/repositórios/contato; selecionar tema válido; abrir currículo se existente; filtrar projetos por tecnologia cadastrada. Ações externas exigem interação apropriada; nenhuma ação administrativa.

## Backlog e gates
- S0: auditoria, arquitetura, backlog (este documento). Pendente validação dinâmica e segurança histórica.
- S1: corrigir achados prioritários, layout responsivo e navegação; UX valida 320/768/1440px, teclado e foco, cinco temas, redução de movimento.
- S2: núcleo visual acessível e estados, painel de texto; sem autoplay.
- S3: comandos locais com testes unitários, intenções desconhecidas não executam ações.
- S4: voz opcional com detecção de suporte, permissão e cancelamento, sem impedir texto.
- S5: integração opcional LLM backend após aprovação de provedor e orçamento; teste de injeção de prompt, limites e falhas.
- S6: base pública de conhecimento e GitHub com cache e fallback; respostas sem inventar competências.
- S7: regressão Docker, autenticação, CRUD, upload, contato/Mailpit, currículo, acessibilidade, consumo e segurança.
- S8: documentação, PR para revisão; merge somente com autorização do proprietário.

## Responsabilidades e definição de pronto
Gestor prioriza; engenharia interpreta; arquitetura valida contratos; UX/IHC desenham fluxo; IA define conhecimento e limites; DBA valida persistência; front-end/full stack implementam; segurança e QA revisam; DevOps registra commits. São papéis de revisão, não agentes autônomos. Falhas voltam ao responsável; cada tarefa inclui critérios, evidência de testes e commit rastreável. Main não alterada. Não declarar sprint concluída sem evidência dos gates.
