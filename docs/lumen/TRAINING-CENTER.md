# Training Center — fase 1

O administrador cadastra conhecimentos por categoria, com título, conteúdo, fonte e projeto opcional (obrigatório para categoria projeto). Todo registro novo ou editado retorna a rascunho. Aprovação manual marca como publicado; ainda não há leitura pública, integração GPT, entrevistas guiadas ou síntese de voz neste módulo. Não inserir dados privados. Não é fine-tuning. A futura recuperação deve filtrar publicados e projetos ativos, limitar tamanho e tratar conteúdo como dados não confiáveis. Não aprender de visitantes automaticamente.

## Atualizar banco existente (Docker)
No PowerShell, na pasta htdocs, na branch feat/portfolio-v3-lumen, atualizar com git pull --ff-only. Fazer backup do banco pelo phpMyAdmin. Com os containers ativos:

```powershell
Get-Content -Raw .\migrations\001_lumen_knowledge.sql | docker compose -p portfolio_lmc exec -T db mysql -ulmc -plmc portfolio_lmc
```

Usar as credenciais do ambiente local se diferentes. Alternativamente importar o arquivo SQL pelo phpMyAdmin no banco portfolio_lmc. Não excluir volumes. Instalações novas recebem tabela pelo schema.sql.

## QA pendente
- Acesso anônimo redireciona para login; POST sem CSRF é recusado.
- Criar, editar, publicar, despublicar, excluir; edição de publicado retorna a rascunho.
- Rejeitar campos grandes, categoria inválida e projeto inexistente; escapar HTML.
- Excluir projeto remove conhecimento vinculado (FK cascade); confirmar comportamento.
- Verificar migração em banco existente e novo; conferir persistência após restart.
- Validar interface nos cinco temas e celular.

## Próximos passos
Entrevista guiada e revisão de respostas, consulta local de conteúdo aprovado, integração de provedor GPT após aprovação de custo e segurança, voz opcional. Sem transferência automática de memória do ChatGPT. Testes dinâmicos ainda não realizados. Main não alterada.
