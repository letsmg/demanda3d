# SDD 06 — Moderação e Comunicação (Threads, Messages, Disputes)

> **Escopo:** Threads de conversa entre staff e clientes, mensagens criptografadas, disputas, regras de moderação de conteúdo e políticas de acesso.
> **Atualizado:** 2026-07-21

---

## 1. Modelagem de Dados

### 1.1 `threads`

| Coluna | Tipo | Descrição |
| :--- | :--- | :--- |
| `id` | BIGINT PK | Identificador único |
| `tenant_id` | BIGINT FK → `tenants.id` NULL | Loja (nullable) |
| `client_id` | BIGINT FK → `clients.id` CASCADE INDEX | Cliente |
| `order_id` | BIGINT FK → `orders.id` NULL | Pedido vinculado |
| `status` | VARCHAR(255) DEFAULT `open` | `open`, `closed`, `archived` |
| `created_at` / `updated_at` | TIMESTAMP | Datas |

**Model:** `App\Models\Thread`

### 1.2 `messages`

| Coluna | Tipo | Descrição |
| :--- | :--- | :--- |
| `id` | BIGINT PK | Identificador único |
| `thread_id` | BIGINT FK → `threads.id` CASCADE INDEX | Thread |
| `sender_type` | VARCHAR(255) NOT NULL | `client` ou `staff` |
| `sender_id` | BIGINT NOT NULL | ID do remetente |
| `content_encrypted` | TEXT NOT NULL | Conteúdo criptografado (AES-256-CBC) |
| `created_at` / `updated_at` | TIMESTAMP | Datas |

> **Índice composto:** `(sender_type, sender_id)`.

**Model:** `App\Models\Message` — conteúdo criptografado em repouso; descriptografado apenas em memória na exibição.

### 1.3 `disputes`

| Coluna | Tipo | Descrição |
| :--- | :--- | :--- |
| `id` | BIGINT PK | Identificador único |
| `tenant_id` | BIGINT FK → `tenants.id` CASCADE INDEX | Loja |
| `client_id` | BIGINT FK → `clients.id` CASCADE INDEX | Cliente reclamante |
| `order_id` | BIGINT FK → `orders.id` NULL | Pedido |
| `reason` | VARCHAR(255) NOT NULL INDEX | `fraud`, `fake_product`, `offensive`, `not_delivered` |
| `description_encrypted` | TEXT NOT NULL | Descrição criptografada |
| `status` | VARCHAR(255) DEFAULT `pending` INDEX | `pending`, `investigating`, `resolved`, `dismissed` |
| `admin_id` | BIGINT FK → `users.id` NULL INDEX | Admin responsável |
| `created_at` / `updated_at` | TIMESTAMP | Datas |

**Model:** `App\Models\Dispute` — descrição criptografada.

---

## 2. Endpoints e Rotas

### 2.1 Threads e Mensagens

| Método | Rota | Middleware | Descrição |
| :--- | :--- | :--- | :--- |
| GET | `/threads` | `auth` ou `auth:clients` | Lista threads do usuário/cliente |
| GET | `/threads/{thread}` | `auth` ou `auth:clients` | Exibe conversa |
| POST | `/threads/{thread}/messages` | `auth` ou `auth:clients` | Envia mensagem |
| PATCH | `/threads/{thread}/close` | `auth` | Fecha thread |
| PATCH | `/threads/{thread}/archive` | `auth` | Arquiva thread |

### 2.2 Disputas

| Método | Rota | Middleware | Descrição |
| :--- | :--- | :--- | :--- |
| GET | `/disputes` | `auth`, `verified`, `ensure.staff` | Lista disputas |
| GET | `/disputes/{dispute}` | `auth`, `verified`, `ensure.staff` | Detalhe da disputa |
| POST | `/orders/{order}/dispute` | `auth:clients` | Cliente abre disputa |
| PATCH | `/disputes/{dispute}/status` | `auth`, `verified`, `ensure.staff` | Atualiza status (admin) |

---

## 3. Regras de Moderação de Conteúdo

### 3.1 Validação Binária (Pré-Save)

Toda inserção em `messages` e `disputes` passa por **Custom Validation Rules** do Laravel. Se falhar, retorna **HTTP 422** imediatamente.

### 3.2 `NoContactDataRule`

**Objetivo:** Impedir compartilhamento de e-mails e telefones.

**Regex aplicado:**
- E-mails: padrão `xxx@xxx.xxx`
- Telefones brasileiros: `(XX) XXXXX-XXXX`, `(XX) XXXX-XXXX`, `XX XXXXXXXXX`, etc.

### 3.3 `NoOffensiveContentRule`

**Objetivo:** Bloquear termos ofensivos com pipeline de normalização:

| Etapa | Exemplo |
| :--- | :--- |
| 1. Leet-speak → texto normal | `c4r4lh0` → `caralho` |
| 2. Remove espaços artificiais | `c a r a l h o` → `caralho` |
| 3. Colapsa letras repetidas | `caralhoooo` → `caralho` |
| 4. Normaliza gêneros | `caralha` → detectado como variante |

### 3.4 Sanção

- É **proibido** mascarar caracteres com asteriscos (`c*****o`).
- A mensagem é **rejeitada integralmente**.
- O erro informa ao usuário **qual termo proibido** ele deve remover.
- **Sem silent fail** — o usuário sempre sabe o motivo da rejeição.

### 3.5 Exceção Admin

Usuários **ADMIN** e **ADMIN_2** (access_level 10 e 11) **ignoram** as regras de moderação ao enviar mensagens no chat.
Possuem acesso total de auditoria das conversas.

---

## 4. Políticas de Acesso (Policies)

### 4.1 `MessagePolicy`

| Ação | Regra |
| :--- | :--- |
| `viewAny` | Staff do tenant ou cliente dono da thread |
| `view` | Participante da thread |
| `create` | Participante da thread (staff ou cliente) |
| `delete` | ADMIN apenas |

### 4.2 `ThreadPolicy`

| Ação | Regra |
| :--- | :--- |
| `view` | Staff do tenant ou cliente dono |
| `update` | Staff do tenant |
| `close` | Staff do tenant |
| `archive` | Staff do tenant |

### 4.3 `DisputePolicy`

| Ação | Regra |
| :--- | :--- |
| `viewAny` | Staff do tenant, ADMIN cross-tenant |
| `view` | Staff do tenant ou cliente reclamante |
| `create` | Cliente (dono do pedido) |
| `updateStatus` | ADMIN, ADMIN_2, SELLER_1 |

---

## 5. Fluxos Críticos

### 5.1 Envio de Mensagem com Moderação

1. Usuário (staff ou cliente) envia mensagem via POST.
2. `FormRequest` aplica `trim()` e `strip_tags()` no conteúdo.
3. `NoContactDataRule` verifica e-mails/telefones.
4. `NoOffensiveContentRule` verifica termos ofensivos com pipeline de normalização.
5. Se passar em ambas: conteúdo é criptografado (`content_encrypted`) e persistido.
6. Se falhar: HTTP 422 com mensagem indicando o termo proibido.

### 5.2 Exibição de Mensagem

1. `content_encrypted` é lido do banco.
2. Descriptografado em memória via `EncryptionService::decrypt()`.
3. Exibido no frontend — nunca persiste descriptografado.

### 5.3 Abertura de Disputa

1. Cliente acessa pedido no perfil.
2. POST `/orders/{order}/dispute` com `reason` e `description`.
3. `NoContactDataRule` e `NoOffensiveContentRule` aplicados na descrição.
4. `description_encrypted` persistido.
5. Disputa com status `pending`.
6. Admin notificado para investigação.

### 5.4 Resolução de Disputa

1. Admin acessa disputa.
2. Atualiza status: `investigating` → `resolved` ou `dismissed`.
3. `admin_id` registrado como responsável.
4. Cliente notificado da resolução.

---

## 6. Retenção de Dados

| Tabela | Período mínimo de retenção |
| :--- | :--- |
| `threads` | 2 anos após fechamento |
| `messages` | 2 anos (logs de comunicação) |
| `disputes` | 5 anos (dados legais/fiscais) |

> **Nota:** Mensagens e disputas podem conter dados relevantes para auditoria legal. Soft delete ou anonimização só após o período mínimo.

---

## 7. Dependências

| Dependência | Propósito |
| :--- | :--- |
| `App\Services\EncryptionService` | Criptografia de conteúdo de mensagens e disputas |
| `App\Services\MessageSanitizer` | Pipeline de normalização de texto ofensivo |
| `App\Rules\NoContactDataRule` | Bloqueio de e-mails/telefones |
| `App\Rules\NoOffensiveContentRule` | Bloqueio de termos ofensivos |
| `App\Policies\MessagePolicy` | Autorização de mensagens |
| `App\Policies\ThreadPolicy` | Autorização de threads |
| `App\Policies\DisputePolicy` | Autorização de disputas |
| `App\Models\Thread` | Model de conversas |
| `App\Models\Message` | Model de mensagens criptografadas |
| `App\Models\Dispute` | Model de disputas |