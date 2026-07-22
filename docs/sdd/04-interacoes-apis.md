# SDD 04 — Integrações e APIs (Stripe, Notificações, OpenSearch, Grafana)

> **Escopo:** Stripe (pagamentos + webhooks), microsserviço Go de notificações, OpenSearch (busca local), Grafana (observabilidade local) e APIs externas.
> **Atualizado:** 2026-07-21

---

## 1. Stripe — Pagamentos

### 1.1 Modelagem

**Tabela `stripe_webhook_logs`**

| Coluna | Tipo | Descrição |
| :--- | :--- | :--- |
| `id` | BIGINT PK | Identificador único |
| `type` | VARCHAR(255) INDEX | Tipo do evento (`checkout.session.completed`, etc.) |
| `stripe_event_id` | VARCHAR(255) INDEX | ID único do evento no Stripe |
| `payload` | JSON | Payload completo do webhook |
| `status` | VARCHAR(20) DEFAULT `received` | `received`, `processed`, `failed` |

### 1.2 Endpoints

| Método | Rota | Descrição |
| :--- | :--- | :--- |
| POST | `/stripe/webhook` | Webhook Stripe (público, validado por assinatura) |

> **CSRF:** Rota excluída da verificação CSRF no `bootstrap/app.php`.

### 1.3 Serviço `StripeService`

Gerencia:
- Criação de sessão de checkout (`CheckoutSession`).
- Criação de Payment Intent.
- Webhook handler:
  - `checkout.session.completed` → confirma pagamento, atualiza `orders.status`.
  - `payment_intent.succeeded` → dispara split de pagamento via `SplitPayService`.
- Repasses para sellers e carriers (Connect/Treasury).

### 1.4 Serviço `SplitPayService`

Responsável pelo split de pagamento entre:
1. **Plataforma** (`platform_fee_amount`) — taxa administrativa.
2. **Vendedor** (`seller_amount`) — repasse ao tenant vendedor.
3. **Transportadora** (`carrier_amount`) — repasse à carrier.

### 1.5 Fluxo de Pagamento

1. Cliente finaliza checkout → `CheckoutService` cria `Order`.
2. `StripeService` cria sessão Stripe com `stripe_session_id`.
3. Cliente paga no Stripe Checkout.
4. Webhook `checkout.session.completed` recebido:
   - `StripeWebhookLog` criado.
   - `Order.status` atualizado para `paid`.
   - `SplitPayService` processa divisão.
   - `Order.seller_amount`, `Order.carrier_amount`, `Order.platform_fee_amount` preenchidos.
5. Se falhar: `Order.payment_split_status` = `failed`, job de retry agendado.

### 1.6 Configuração Sandbox

| Variável | Descrição |
| :--- | :--- |
| `STRIPE_KEY` | Chave pública (pk_test_*) |
| `STRIPE_SECRET` | Chave secreta (sk_test_*) |
| `STRIPE_WEBHOOK_SECRET` | Segredo de assinatura de webhook |

---

## 2. Microsserviço Go — Notificações

### 2.1 Arquitetura

```
┌──────────────┐     RPUSH      ┌──────────────┐     BLPOP     ┌──────────────┐
│   Laravel    │ ──────────────→ │    Redis      │ ────────────→ │  Go Worker   │
│  (Producer)  │                │ notifications │              │  (Consumer)  │
└──────────────┘                │    _queue     │              └──────┬───────┘
                                └──────────────┘                     │
                                                              ┌──────▼───────┐
                                                              │   SMTP /     │
                                                              │  Push / SMS  │
                                                              └──────────────┘
```

### 2.2 Componentes

| Componente | Localização | Descrição |
| :--- | :--- | :--- |
| `SendNotification` Job | `app/Jobs/` | Publica mensagem via `RPUSH notifications_queue` |
| Go Worker | `go-service/main.go` | Consome fila via `BLPOP`, dispara notificações |
| Fila Redis | `notifications_queue` | Lista FIFO no Redis |

### 2.3 Payload da Mensagem (JSON)

```json
{
  "type": "email|push|sms",
  "recipient": "user@example.com",
  "subject": "Assunto",
  "body": "Conteúdo",
  "template": "order_confirmation",
  "data": {}
}
```

### 2.4 Dependências do Go Service

| Arquivo | Propósito |
| :--- | :--- |
| `go.mod` / `go.sum` | Dependências Go |
| `Dockerfile` | Containerização do worker |
| `main.go` | Loop principal: `BLPOP` → process → dispatch |

---

## 3. OpenSearch — Busca (Recurso Local Exclusivo)

### 3.1 Regra Crítica

> **OpenSearch é EXCLUSIVO de ambiente local (DEV).** NÃO deve ter dependências ativas em produção.

| Flag | Ambiente | Comportamento |
| :--- | :--- | :--- |
| `OPENSEARCH_ENABLED=true` | Local (DEV) | Busca usa OpenSearch |
| `OPENSEARCH_ENABLED=false` | Produção | Fallback para PostgreSQL (Full-Text Search nativo) |

### 3.2 Fallback Obrigatório

Quando `OPENSEARCH_ENABLED=false`:
- Todas as buscas usam PostgreSQL (índices GIN, `tsvector`, `tsquery`).
- Nenhuma tentativa de conexão ao cluster OpenSearch.
- Nenhum código que dependa exclusivamente do OpenSearch pode ser mergeado sem fallback funcional.

### 3.3 Serviços de Busca

| Serviço | Responsabilidade |
| :--- | :--- |
| `MeilisearchService` | Índice de busca primário (Laravel Scout) |
| `DashboardSearchService` | Busca interna no painel staff |

### 3.4 Endpoints

| Método | Rota | Descrição |
| :--- | :--- | :--- |
| GET | `/api/search/suggestions` | Sugestões em tempo real (Redis + PostgreSQL) |

---

## 4. Grafana + Loki + Promtail — Observabilidade (Recurso Local Exclusivo)

### 4.1 Regra Crítica

> **Stack Grafana é EXCLUSIVA de ambiente local (DEV).** NÃO deve ter dependências ativas em produção.

| Flag | Ambiente | Comportamento |
| :--- | :--- | :--- |
| `GRAFANA_ENABLED=true` | Local (DEV) | Containers sobem com profile Docker |
| `GRAFANA_ENABLED=false` | Produção | Monitoramento delegado a serviços externos |

### 4.2 Stack

| Componente | Propósito |
| :--- | :--- |
| **Grafana** | Dashboards de telemetria e performance |
| **Loki** | Agregação de logs |
| **Promtail** | Coleta de logs dos containers |

### 4.3 Fila de Alertas

- `Exception Handler` do Laravel dispara job `SendCriticalErrorAlert` para fila Redis.
- `QUEUE_CONNECTION=redis` processa em segundo plano.
- Só ativo no ambiente configurado.

---

## 5. APIs Externas e de Suporte

### 5.1 BrasilAPI — CNPJ Lookup

| Método | Rota | Middleware |
| :--- | :--- | :--- |
| GET | `/api/brasilapi/cnpj` | `auth` |

**Serviço:** `BrasilApiService` — consulta dados de CNPJ na BrasilAPI.

### 5.2 CEP — Consulta de Estados

| Método | Rota | Descrição |
| :--- | :--- | :--- |
| GET | `/api/cep/{cep}` | Retorna estado baseado na faixa de CEP |

**Serviço:** `CepService` — consulta tabela `states` com faixas de CEP dos Correios.

### 5.3 Frontend Error Log

| Método | Rota | Descrição |
| :--- | :--- | :--- |
| POST | `/api/log-frontend-error` | Captura silenciosa de erros via `sendBeacon` |

**Controller:** `Api\FrontendErrorLogController`

---

## 6. Serviços Auxiliares

| Serviço | Responsabilidade |
| :--- | :--- |
| `ImageOptimizationService` | Conversão para WebP, geração de thumbnails |
| `ImageStorageService` | Armazenamento de imagens (local/S3) |
| `ImageModerationService` | Moderação via Google Cloud Vision SafeSearch |
| `DocumentValidationService` | Validação de CNPJ/CPF |
| `ReportService` | Geração de relatórios (inputs, produtos, vendas) |

---

## 7. Dependências

| Dependência | Propósito |
| :--- | :--- |
| `Stripe PHP SDK` | Integração Stripe |
| `go-service/` | Microsserviço Go para notificações assíncronas |
| `Redis` | Fila `notifications_queue` + cache + filas de jobs |
| `OpenSearch` (local) | Busca full-text (DEV apenas) |
| `Meilisearch` (Laravel Scout) | Índice de busca primário |
| `Grafana + Loki + Promtail` | Observabilidade (DEV apenas) |
| `BrasilAPI` | Consulta de CNPJ |
| `Google Cloud Vision` | Moderação de imagens (SafeSearch) |
| `App\Services\StripeService` | Abstração de pagamentos Stripe |
| `App\Services\SplitPayService` | Divisão de repasses |
| `App\Controllers\StripeWebhookController` | Handler de webhooks Stripe |