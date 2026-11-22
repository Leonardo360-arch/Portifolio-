# Entrevista guiada — treinamento supervisionado

Acesso: Training Center > Iniciar entrevista de treinamento. Quatro roteiros, vinte perguntas (4 personalidade, 4 perfil, 4 metodologia, 8 projeto). Projeto deve existir no cadastro. Respostas opcionais individualmente, ao menos uma obrigatória. Salvamento transacional como rascunhos independentes, revisão e publicação manual no Training Center. Não há nova migração; depende da 001. Não há chamadas a IA, voz ou aprendizagem pública nesta etapa. Evitar dados privados e revisar informações antes da publicação.

## QA pendente
- Anônimo redirecionado, POST sem CSRF rejeitado.
- Respostas vazias, chaves desconhecidas, arrays indevidos, campos acima do limite rejeitados.
- Projeto inexistente rejeitado; categoria não projeto ignora vínculo.
- Respostas parciais salvas em transação, nenhuma publicação automática; falha reverte lote.
- Validar escape de HTML e revisão posterior; recarregar após sucesso não reenvia POST.
- Testar quatro roteiros, celular, temas e navegação. Trocar categoria antes de salvar perde respostas não salvas; repetição intencional cria novos rascunhos.

Testes dinâmicos não executados nesta etapa. Próximo passo: recuperação pública restrita a conhecimentos aprovados e projetos ativos, com resposta de ausência de informação e sem acesso a rascunhos.