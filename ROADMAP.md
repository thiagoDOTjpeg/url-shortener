# Roadmap - Shortly

## Objetivo
Este roadmap organiza as proximas evolucoes do projeto em blocos incrementais, priorizando seguranca, confiabilidade, observabilidade e performance.

## Como usar
- Marque os itens com `[x]` conforme forem entregues.
- Para cada item concluido, referencie PR, migration e teste relacionado.
- Priorize entregas pequenas e incrementais por sprint.

---

## Fase 1 - Autenticacao e Seguranca (Core Profissional)
**Prioridade:** Alta

### 1.1 Confirmacao de E-mail
- [x] Implementar `implements MustVerifyEmail` no model `User`.
- [x] Proteger areas autenticadas para usuarios com e-mail verificado.
- [x] Adicionar aviso na UI para reenvio de confirmacao.
- [ ] Criar testes de feature para fluxo de verificacao.

### 1.2 Gestao de Senhas (Reset Password)
- [x] Configurar fluxo completo de "Esqueci minha senha".
- [x] Configurar envio de e-mail para redefinicao.
- [x] Implementar tela/form de nova senha com token.
- [ ] Criar testes de feature do fluxo completo.

### 1.3 Rate Limiting
- [x] Limitar criacao de URLs (exemplo: 5/min por usuario).
- [x] Proteger rota de redirecionamento `/r/{slug}` contra abuso/forca bruta.
- [x] Implementar throttle nas tentativas de login no `AuthController`.
- [] Definir respostas padrao para limite excedido (mensagem + HTTP 429).

---

## Fase 2 - Analytics Detalhado (Inteligencia de Dados)
**Prioridade:** Alta

### 2.1 Processamento Assincrono
- [ ] Mover captura de IP, `Location::get()` e criacao de `UrlClick` para Job em background.
- [ ] Garantir idempotencia minima para evitar registros duplicados.
- [ ] Criar testes de fila/feature para processamento assincrono.

### 2.2 Geolocalizacao Avancada
- [ ] Adicionar campos de latitude e longitude na tabela `url_clicks`.
- [ ] Atualizar model e camada de persistencia.
- [ ] Criar migration com rollback seguro.

### 2.3 User-Agent Parsing
- [ ] Extrair e persistir dispositivo (Mobile/Desktop).
- [ ] Extrair e persistir navegador.
- [ ] Extrair e persistir sistema operacional.
- [ ] Padronizar valores desconhecidos para analise consistente.

### 2.4 Filtros de Data Dinamicos no Dashboard
- [ ] Implementar seletores: Hoje, 7 dias, 30 dias e Total.
- [ ] Ajustar consultas e graficos para respeitar o periodo selecionado.
- [ ] Persistir filtro selecionado na navegacao da pagina.

### 2.5 Filtro de Bots
- [ ] Identificar cliques de crawlers/robos conhecidos.
- [ ] Marcar `is_bot` (ou ignorar no registro, conforme decisao de produto).
- [ ] Permitir alternar no dashboard entre "incluir bots" e "excluir bots".

---

## Fase 3 - Real-Time com Laravel Reverb (WebSockets)
**Prioridade:** Media

### 3.1 QR Code Feedback em Tempo Real
- [ ] Notificar frontend via WebSocket quando `GenerateQrCode` finalizar.
- [ ] Atualizar estado da tela sem refresh manual.
- [ ] Tratar reconexao e estado de erro no frontend.

### 3.2 Live Dashboard
- [ ] Atualizar contador de cliques em tempo real.
- [ ] Atualizar graficos em tempo real ao registrar novo acesso.
- [ ] Garantir que eventos enviados respeitam permissao/escopo do usuario.

---

## Fase 4 - Arquitetura e Documentacao
**Prioridade:** Media

### 5.1 Refatoracao para Actions
- [ ] Criar classes de acao (ex.: `CreateShortenUrlAction`).
- [ ] Reduzir responsabilidade dos controllers (`UrlController` mais enxuto).
- [ ] Cobrir actions com testes unitarios e de integracao.

### 5.2 API + Documentacao
- [ ] Padronizar autenticacao API com Sanctum.
- [ ] Integrar Scramble para documentacao automatica.
- [ ] Publicar endpoint de docs e revisar exemplos de uso.

### 5.3 Novo README
- [ ] Substituir boilerplate por documentacao real do projeto.
- [ ] Documentar stack, setup Docker, workflow de desenvolvimento e funcionalidades do Shortly.
- [ ] Incluir secoes de deploy, variaveis de ambiente e troubleshooting.

---

## Milestones sugeridas
- [ ] **M1 (Seguranca):** concluir Fase 1
- [ ] **M2 (Dados):** concluir Fase 2
- [ ] **M3 (Realtime):** concluir Fase 3
- [ ] **M4 (Infra):** concluir Fase 4
- [ ] **M5 (Arquitetura + Docs):** concluir Fase 5

## Definicao de pronto (DoD)
- [ ] Feature com testes automatizados.
- [ ] Migration revisada e com rollback.
- [ ] Logs e monitoramento basico para erro.
- [ ] Documentacao atualizada (`README.md` e/ou docs internas).
- [ ] PR revisado e aprovado.

