# ADR-002 — Voz personalizada

Status: proposta registrada a pedido do proprietário; provedor, orçamento e voz definitiva pendentes.

Requisitos: permitir selecionar voz do proprietário ou da esposa mediante consentimento explícito da pessoa gravada e verificação exigida pelo provedor; alternativa de voz padrão e texto. Português brasileiro. Não imitar identidade humana sem avisar que se trata de voz sintética.

Arquitetura: interface VoiceProvider no backend quando houver integração real; adaptar ElevenLabs ou OpenAI conforme elegibilidade, custo e suporte da hospedagem. Avaliar primeiro ElevenLabs IVC; PVC exige que o titular realize a própria verificação. OpenAI requer amostra e gravação de consentimento para clonagem. Web Speech API como fallback sem clonagem. Chaves e identificadores privados no servidor; nunca publicar gravações, tokens ou amostras no GitHub. Não criar endpoint público de TTS arbitrário: somente respostas do assistente, com limites de tamanho, frequência e orçamento. Permitir revogação e desativação da voz; remover amostras conforme regras do provedor.

Painel: seleção administrativa da voz autorizada, habilitar/desabilitar áudio, velocidade se suportada, teste controlado. Visitante pode silenciar e cancelar áudio, microfone somente por interação e permissão; sem autoplay ou escuta contínua. UX/IHC: sincronizar estado visual com reprodução, fallback textual em falha. QA: verificar consentimento, acesso administrativo, limites, falhas, cancelamento e cinco temas.

Backlog: S4 voz e acessibilidade; S5 integração de provedor, após aprovação de custo e titular. Nenhuma clonagem executada nesta etapa.

Referências: https://elevenlabs.io/docs/eleven-api/guides/how-to/voices/instant-voice-cloning ; https://developers.openai.com/api/docs/guides/custom-voices .
