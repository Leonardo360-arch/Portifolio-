# L.U.M.E.N. Personal — Roadmap 4.0

## Visão do proprietário
Transformar L.U.M.E.N. em agente particular do cotidiano, representante, ajudante e assistente, administrável por voz e com autonomia delegada pelo proprietário. Esta visão é futura, não altera o escopo de entrega da versão 3.0 e não representa funcionalidades implementadas.

## Domínios propostos
- Organização pessoal: agenda, tarefas, lembretes e prioridades.
- Representação profissional: perfil autorizado, contatos e propostas de agendamento, identificando-se como agente digital.
- Acadêmico: planejamento de estudos e acompanhamento de atividades.
- Engenharia: repositórios, documentação, demandas, integração com ferramentas de desenvolvimento.
- Empresarial: apoio à gestão da EVITUS, indicadores e processos.
- Financeiro: organização e análise; pagamentos e compromissos exigem autorização específica.
- Operador digital: aplicativos, arquivos e rotinas locais permitidas.

## Arquitetura proposta
Orquestrador central, interfaces web/desktop/mobile, entrada e saída por voz, identidade e autenticação, memória de trabalho e memória persistente supervisionada, recuperação de conhecimento, adaptadores de modelos, ferramentas com contratos definidos, agendador e filas de execução, políticas de autorização independentes do modelo, registro de auditoria e mecanismo de interrupção. Separar contextos pessoal, profissional, empresarial e público; o portfólio não deve acessar a memória privada. Sugestões a validar: Python/FastAPI/PySide6, Flutter, React/TypeScript, PostgreSQL/pgvector, Redis, WebSocket, Docker, modelos locais ou API, STT/TTS e n8n. Manter a versão 3.0 em PHP/MySQL. Não migrar sem decisão arquitetural posterior.

## Autonomia e controle
N0 consultivo; N1 prepara ações; N2 executa tarefas específicas previamente autorizadas; N3 processos recorrentes limitados; N4 atuação proativa dentro de políticas explícitas. Conceder permissões por ferramenta, finalidade, destinatário, prazo, orçamento e escopo. Confirmação independente para ações irreversíveis, compromissos externos, pagamentos e alterações sensíveis. Voz não é prova suficiente de identidade; autenticação forte e confirmação adicional. Revogação, pausa, auditoria, limites de consumo e acesso mínimo. Não coletar áudio continuamente por padrão. Voz personalizada somente com consentimento explícito da pessoa titular. Não se passar pelo proprietário nem assumir obrigações sem autorização.

## Roadmap sugerido
1. Concluir e testar a versão 3.0.
2. Extrair núcleo privado isolado e aplicativo desktop com comandos locais.
3. Integrar agenda, tarefas e arquivos por conectores autorizados.
4. Memória pessoal segregada, recuperação semântica, políticas e auditoria.
5. Automações agendadas, notificações e execução limitada com filas e revalidação de autorização.
6. Interface móvel, voz personalizada consentida e integração segura em múltiplos dispositivos.
7. Módulos especializados e avaliação de produto comercial EVITUS, somente após validação pessoal.

## Critérios de avanço
Testes de autorização/revogação, isolamento de dados, resistência a injeção de instruções, validação de ações, recuperação de falhas, custo, privacidade e disponibilidade. Prova de conceito local antes de exposição pública. Dependências externas, infraestrutura, custos, APIs, permissões e requisitos legais devem ser avaliados por integração. Nenhuma contratação, ativação de API, mudança de código executável ou alteração da main nesta etapa.
