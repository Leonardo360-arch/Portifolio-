# ADR-003 — Apresentação profissional e pré-entrevista

Status: requisito aprovado pelo proprietário; implementação pendente.

## Objetivo
Permitir ao visitante conversar por texto ou voz com Lumen para conhecer o desenvolvedor e aprofundar detalhes dos projetos, suas motivações e decisões técnicas. O assistente é identificado como IA e não se apresenta como o próprio profissional.

## Conhecimento autorizado
Ficha administrativa por projeto: título, motivação, problema, público, requisitos, arquitetura, tecnologias, justificativas, padrões, desafios, soluções, contribuição individual, participação de equipe e IA, estado atual, limitações, próximos passos, fontes e data de revisão. Perfil profissional: formação, experiência, competências comprovadas, objetivos e canais públicos autorizados. Separar fatos publicados, planos e opiniões. Rascunhos não são públicos. Não inferir competências nem inventar resultados.

## Fluxo
Visitante seleciona apresentação profissional ou pré-entrevista; pergunta livre ou roteiro sugerido; assistente recupera somente registros publicados, identifica projeto, responde com fontes quando disponíveis e oferece detalhamento. Caso falte informação, declara a lacuna e oferece contato humano. Não assume compromissos, negocia salário, responde avaliações em nome do candidato ou realiza seleção automatizada. Não coleta dados de recrutadores sem finalidade e consentimento; nenhuma gravação por padrão.

## Arquitetura e backlog
DBA: extensão de conhecimento por projeto e perfil com publicação explícita e fontes. Full stack: CRUD administrativo protegido e API somente de leitura pública com campos permitidos. IA: recuperação estruturada inicial, posteriormente LLM com respostas fundamentadas, proteção contra injeção de prompt e limites de consumo. UX/IHC: modo entrevista com perguntas sugeridas, texto acessível e voz opcional; identificação clara de voz sintética. QA: testar projeto inexistente, informação ausente, tentativa de acessar rascunhos/credenciais, afirmações sem fonte, comandos indevidos, acessibilidade e navegação. Segurança: nenhuma credencial ou informação privada no contexto. DevOps: manter branch atual, sem merge automático.

## Aceite
Responder por que um projeto foi criado, como funciona, por que a arquitetura foi escolhida, qual foi a contribuição do profissional e quais limitações existem, exclusivamente quando cadastradas. Distinguir trabalho individual e coletivo. Para informação ausente, indicar desconhecimento. Voz personalizada somente com consentimento e provedor aprovado. Não publicar exemplos hipotéticos como fatos. Integração prevista nas sprints de IA e base de conhecimento; testes e implementação ainda pendentes.
