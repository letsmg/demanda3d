# SDD 05 — Infraestrutura e Multi-Tenant

> **Escopo:** Arquitetura multi-tenant, isolamento via TenantScope, Docker, PostgreSQL master/replica, Redis, CI/CD e auditoria.
> **Atualizado:** 2026-07-21

---

## 1. Arquitetura Multi-Tenant (Lógico)

### 1.1 Modelo

| Característica | Detalhe |
| :--- | :--- |
| Tipo | Multi-tenant lógico (single database, shared schema) |
| Isolamento | `tenant_id` em todas as tabelas de negócio |
| Mecanismo | `TenantScope` (Global Scope) injetado automaticamente |
| Injeção | `auth()->user()->tenant_id` em todos os Services |

### 1.2 Tabelas com `tenant_id`

| Tabela | Descrição |
| :--- | :--- |
| `clients` | Clientes vinculados ao tenant da loja |
| `suppliers` | Fornecedores de insumos |
| `inputs` | Insumos de impressão 3D |
| `products` | Produtos anunciados |
| `orders` | Pedidos |
| `order_labels` | Etiquetas de envio |
| `carrier_tenant_agreements` | Contratos tenant ↔ carrier |
| `freight_contracts` | Contratos de frete |
| `coupons` | Cupons (nullable = global) |
| `reviews` | Avaliações |
| `threads` | Conversas |
| `disputes` | Disputas |
| `accounts_payable` | Contas a pagar |
| `bank_details` | Dados bancários |
| `activity_logs` | Logs de auditoria |
| `security_logs` | Logs de segurança |

### 1.3 TenantScope

**`App\Scopes\TenantScope`**

```php
// Aplicado como Global Scope em Models com tenant_id
// Exceção: ADMIN (access_level 10) ignora o escopo via Policy
// Nunca remover o escopo globalmente — tratar via exceção explícita
```

**Modelos com TenantScope aplicado:**
- `Product` — `static::addGlobalScope(new TenantScope)`
- `Client`, `Supplier`, `Input`, `Order`, `Coupon`, etc.

### 1.4 Relacionamento Principal

```
users (auth) 1:1 tenants (dados empresariais)
     │
     └── access_level → define o papel (SELLER_1, SELLER_2, ADMIN, etc.)
```

### 1.5 Regras de Isolamento

| Regra | Descrição |
| :--- | :--- |
| Vendedores (SELLER_1, SELLER_2) | Só acessam dados do próprio `tenant_id` |
| Transportadoras (CARRIER_1, CARRIER_2) | Acessam dados de tenants com contrato ativo |
| ADMIN (10, 11) | Acesso cross-tenant via exceção explícita na Policy |
| Clientes (CUSTOMER) | Filtram por `tenant_id` da loja que estão navegando |

---

## 2. Infraestrutura Docker

### 2.1 Containers (Ambiente DEV)

| Container | Porta | Descrição |
| :--- | :--- | :--- |
| `demanda-psql-dev` (master) | 5434 | PostgreSQL master — `demanda_db_dev` |
| `demanda-psql-rep-dev` (réplica) | 5435 | PostgreSQL réplica — `demanda_db_dev_repl` |
| `redis-dev` | 6379 | Redis — cache + filas + `notifications_queue` |
| `opensearch-dev` | 9200 | OpenSearch (DEV apenas, `OPENSEARCH_ENABLED=true`) |
| `grafana-dev` | 3000 | Grafana (DEV apenas, `GRAFANA_ENABLED=true`) |
| `loki-dev` | 3100 | Loki — agregação de logs |
| `promtail-dev` | — | Coleta de logs |

### 2.2 Arquivos Docker

| Arquivo | Propósito |
| :--- | :--- |
| `docker-compose.yml` | Serviços essenciais (PostgreSQL, Redis) |
| `docker-compose-hom.yml` | Homologação |
| `docker-compose-prod.yml` | Produção (leve, sem OpenSearch/Grafana) |
| `docker/` | Configurações auxiliares dos containers |

### 2.3 Profiles Docker

| Profile | Serviços inclusos |
| :--- | :--- |
| (default) | PostgreSQL master + Redis |
| `replication` | + PostgreSQL réplica |
| `search` | + OpenSearch |
| `monitoring` | + Grafana, Loki, Promtail |

---

## 3. PostgreSQL Master/Replica (DEV)

### 3.1 Arquitetura

| Instância | Container | Porta | Banco |
| :--- | :--- | :--- | :--- |
| **Master** | `demanda-psql-dev` | 5434 | `demanda_db_dev` |
| **Replica** | `demanda-psql-rep-dev` | 5435 | `demanda_db_dev_repl` |

### 3.2 Configuração no Laravel

```php
// config/database.php
'pgsql' => [
    'read' => [
        'host' => env('DB_REPLICA_HOST', '127.0.0.1'),
        'port' => env('DB_REPLICA_PORT', '5435'),
    ],
    'write' => [
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '5434'),
    ],
],
```

### 3.3 Variáveis de Controle

| Variável | Default | Descrição |
| :--- | :--- | :--- |
| `DB_CONNECTION` | `pgsql` | Driver PostgreSQL |
| `DB_HOST` | `127.0.0.1` | Master host |
| `DB_PORT` | `5434` | Master porta |
| `DB_DATABASE` | `demanda_db_dev` | Master banco |
| `DB_READ_WRITE_SPLIT` | `false` | Habilita split de leitura/escrita |
| `DB_REPLICA_HOST` | `127.0.0.1` | Réplica host |
| `DB_REPLICA_PORT` | `5435` | Réplica porta |
| `DB_REPLICA_DATABASE` | `demanda_db_dev_repl` | Réplica banco |

### 3.4 Regras de Uso

| Operação | Destino |
| :--- | :--- |
| `INSERT`, `UPDATE`, `DELETE` | Master (write) |
| `SELECT` (com `DB_READ_WRITE_SPLIT=true`) | Replica (read) |
| `SELECT` (com `DB_READ_WRITE_SPLIT=false`) | Master (read + write) |
| Migrations | Exclusivamente no Master |

---

## 4. Redis — Cache e Filas

### 4.1 Estrutura de Dados no Redis

| Chave | Tipo | Propósito |
| :--- | :--- | :--- |
| `notifications_queue` | List (FIFO) | Fila de notificações (Laravel → Go) |
| `cache:*` | String/Hash | Cache de aplicação |
| `horizon:*` | Vários | Monitoramento de filas (Laravel Horizon) |
| `sessions:*` | String | Sessões de usuário |

### 4.2 Configuração

| Variável | Default |
| :--- | :--- |
| `REDIS_HOST` | `127.0.0.1` |
| `REDIS_PASSWORD` | `null` |
| `REDIS_PORT` | `6379` |
| `QUEUE_CONNECTION` | `redis` |

---

## 5. Auditoria e Logs

### 5.1 `activity_logs`

Modelo polimórfico imutável (sem `updated_at` efetivo — registros nunca são alterados).

| Coluna | Descrição |
| :--- | :--- |
| `tenant_id` | Tenant (nulo para ações de admin global) |
| `causer_type` / `causer_id` | Quem executou (User ou Client) |
| `event` | Tipo da ação (ex: "Criou Produto") |
| `subject_type` / `subject_id` | Recurso afetado |
| `description` | Descrição legível |
| `properties` | JSONB com `old` e `attributes` |
| `created_at` | Data imutável da ação |

**Índices:** `(tenant_id, created_at)`, `(causer_type, causer_id, created_at)`, `(subject_type, subject_id)`.

**Endpoint:** `GET /audit-logs` — sellers veem apenas do seu tenant; admins veem todos.

### 5.2 `security_logs`

Registra violações de conteúdo (Google Cloud Vision SafeSearch):

| Coluna | Descrição |
| :--- | :--- |
| `violation_type` | `ADULT`, `VIOLENCE`, `RACY`, `MEDICAL` |
| `raw_response` | JSON com resposta completa da API Google Vision |

### 5.3 `user_status_logs`

Histórico de bloqueios/desbloqueios:

| Coluna | Descrição |
| :--- | :--- |
| `user_id` | Usuário afetado |
| `author_id` | Admin que executou |
| `action` | `blocked` ou `unblocked` |
| `reason` | Motivo textual |

---

## 6. CI/CD — Deploy Atômico

### 6.1 Estrutura de Diretórios

```
/var/www/demanda3d/
├── current/      → link simbólico para a release ativa
├── releases/     → versões numeradas/datadas
│   ├── 2026-07-21_01/
│   └── 2026-07-20_01/
└── shared/       → arquivos persistentes
    ├── .env
    ├── .env.docker
    ├── docker-compose.yml
    └── storage/
        └── app/
            └── public/  → uploads dinâmicos
```

### 6.2 Pipeline (GitHub Actions)

| Arquivo | Propósito |
| :--- | :--- |
| `.github/workflows/deploy.yml` | Pipeline de CI/CD |

### 6.3 Regra do Storage Link

```bash
# Link simbólico absoluto (não usar php artisan storage:link)
ln -sfn /var/www/demanda3d/shared/storage/app/public \
        /var/www/demanda3d/releases/{nova_release}/public/storage
```

---

## 7. SEO e Crawlers

### 7.1 Tabelas

| Tabela | Propósito |
| :--- | :--- |
| `seo_settings` | Configurações globais de SEO (meta tags, schema, social) |
| `seo_metadatas` (polimórfica) | SEO por entidade pública (produtos, lojas) |

### 7.2 Sitemap e Robots

| Recurso | Descrição |
| :--- | :--- |
| `sitemap.xml` | Gerado dinamicamente via `POST /tools/sitemap` |
| `robots.txt` | Bloqueia `/admin/*`, `/checkout/*` com `noindex, nofollow` |

### 7.3 SEO por Produto (Accessors)

| Accessor | Fonte |
| :--- | :--- |
| `meta_title` | `product.name` (máx. 120 chars) |
| `meta_description` | `product.description` (strip_tags, máx. 320 chars) |
| `meta_keywords` | name + categorias + termos nicho |
| `canonical_url` | `route('store.detail', slug)` |
| `og_image` | Primeira imagem ou fallback |
| `schema_markup` | JSON-LD Product via `ProductService` |
| `google_tag_manager` | Script GTM + dataLayer via `ProductService` |

---

## 8. Dependências

| Dependência | Propósito |
| :--- | :--- |
| `App\Scopes\TenantScope` | Global Scope de isolamento multi-tenant |
| `App\Models\Tenant` | Model central de dados empresariais |
| `Docker` + `docker-compose` | Orquestração de containers locais |
| `PostgreSQL 16` | Banco de dados principal |
| `Redis 7` | Cache, filas, sessões, notifications_queue |
| `Laravel Horizon` | Monitoramento de filas Redis |
| `GitHub Actions` | Pipeline CI/CD |
| `App\Models\ActivityLog` | Auditoria polimórfica imutável |
| `App\Models\SecurityLog` | Log de violações de conteúdo |
| `App\Models\SeoSetting` | Configurações de SEO globais |