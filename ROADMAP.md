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
- [x] Criar testes de feature para fluxo de verificacao.

### 1.2 Gestao de Senhas (Reset Password)
- [x] Configurar fluxo completo de "Esqueci minha senha".
- [x] Configurar envio de e-mail para redefinicao.
- [x] Implementar tela/form de nova senha com token.
- [x] Criar testes de feature do fluxo completo.

### 1.3 Rate Limiting
- [x] Limitar criacao de URLs (exemplo: 5/min por usuario).
- [x] Proteger rota de redirecionamento `/r/{slug}` contra abuso/forca bruta.
- [x] Implementar throttle nas tentativas de login no `AuthController`.
- [x] Definir respostas padrao para limite excedido (mensagem + HTTP 429).

---

## Fase 2 - Analytics Detalhado (Inteligencia de Dados)
**Prioridade:** Alta

### 2.1 Processamento Assincrono
- [x] Mover captura de IP, `Location::get()` e criação de `UrlClick` para Job em background.
- [x] Garantir idempotência minima para evitar registros duplicados.
- [x] Criar testes de fila/feature para processamento assíncrono.

### 2.2 Geolocalização Avançada
- [x] Adicionar campos de latitude e longitude na tabela `url_clicks`.
- [x] Atualizar model e camada de persistência.
- [x] Criar migration com rollback seguro.

### 2.3 User-Agent Parsing
- [x] Extrair e persistir dispositivo (Mobile/Desktop).
- [x] Extrair e persistir navegador.
- [x] Extrair e persistir sistema operacional.
- [x] Padronizar valores desconhecidos para análise consistente.

### 2.4 Filtros de Data Dinâmicos no Dashboard
- [ ] Implementar seletores: Hoje, 7 dias, 30 dias e Total.
- [ ] Ajustar consultas e gráficos para respeitar o período selecionado.
- [ ] Persistir filtro selecionado na navegação da página.

### 2.5 Filtro de Bots
- [ ] Identificar cliques de crawlers/robos conhecidos.
- [ ] Marcar `is_bot` (ou ignorar no registro, conforme decisão de produto).
- [ ] Permitir alternar no dashboard entre "incluir bots" e "excluir bots".

---

## Fase 3 - Real-Time com Laravel Reverb (WebSockets)
**Prioridade:** Media

### 3.1 QR Code Feedback em Tempo Real
- [ ] Notificar frontend via WebSocket quando `GenerateQrCode` finalizar.
- [ ] Atualizar estado da tela sem refresh manual.
- [ ] Tratar reconexão e estado de erro no frontend.

### 3.2 Live Dashboard
- [ ] Atualizar contador de cliques em tempo real.
- [ ] Atualizar gráficos em tempo real ao registrar novo acesso.
- [ ] Garantir que eventos enviados respeitam permissao/escopo do usuário.

---

## Fase 4 - Arquitetura e Documentacao
**Prioridade:** Media

### 5.1 Refatoração para Actions
- [ ] Criar classes de ação (ex.: `CreateShortenUrlAction`).
- [ ] Reduzir responsabilidade dos controllers (`UrlController` mais enxuto).
- [ ] Cobrir actions com testes unitarios e de integracao.

### 5.2 API + Documentação
- [ ] Padronizar autenticação API com Sanctum.
- [ ] Integrar Scramble para documentacão automática.
- [ ] Publicar endpoint de docs e revisar exemplos de uso.

### 5.3 Novo README
- [ ] Substituir boilerplate por documentação real do projeto.
- [ ] Documentar stack, setup Docker, workflow de desenvolvimento e funcionalidades do Shortly.
- [ ] Incluir secoes de deploy, variaveis de ambiente e troubleshooting.

---

## Milestones sugeridas
- [x] **M1 (Seguranca):** concluir Fase 1
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

