# Integração GPT — piloto administrativo

O adaptador usa POST https://api.openai.com/v1/responses, modelo configurável, store=false, máximo de 600 tokens de saída, timeout de 25 segundos, sem ferramentas de alteração e sem chamadas públicas. O administrador precisa selecionar opt-in na página a cada carregamento. Envia somente a pergunta e até três registros publicados recuperados, com excertos limitados; não inserir dados privados na pergunta. store=false não equivale a garantia de retenção zero no provedor. Respostas podem conter erros; IDs retornados no metadado são registros enviados, não prova de fundamentação. Não há histórico contextual nem Function Calling nesta etapa.

## Instalação
Atualizar branch feat/portfolio-v3-lumen, backup antes da migração. Com Docker ativo, no PowerShell da pasta htdocs:

```powershell
Get-Content -Raw .\migrations\002_lumen_ai_usage.sql | docker compose -p portfolio_lmc exec -T db mysql -ulmc -plmc portfolio_lmc
```

Usar credenciais locais corretas. Instalações novas recebem a tabela pelo schema.sql. Não excluir volumes.

No arquivo .env local, não substituir configurações existentes, adicionar LUMEN_AI_ENABLED=0, OPENAI_API_KEY=, LUMEN_OPENAI_MODEL=, LUMEN_AI_DAILY_LIMIT=10. Somente após aprovação do orçamento e escolha de modelo disponível na conta API, inserir chave local e habilitar com 1. Não compartilhar chave nem commitar .env. Confirmar que arquivo não é rastreado e não pode ser servido por HTTP. Verificar extensão PHP curl e TLS e compatibilidade da hospedagem antes de ativar. Configurar controles de consumo também na plataforma. Limite global de reservas diárias UTC de 1 a 100 (padrão 10); falhas contam. Limite de requisições não é teto monetário. Sem chave, modelo, tabela ou quota, usa fallback local.

## QA pendente
Testar sintaxe, curl disponível, conexão TLS, mock de sucesso e erro HTTP, timeout, resposta incompleta, JSON inválido, concorrência e limite diário, ausência de tabela, opt-in desmarcado, sessão/CSRF e exposição de .env. Conferir que modo público nunca chama provedor e registros não publicados não são enviados. Validar respostas com registros contraditórios e injeção de prompt. Verificar orçamento e segurança da hospedagem. Nenhuma chamada real à API, teste funcional ou contratação realizado nesta etapa. Main preservada.
