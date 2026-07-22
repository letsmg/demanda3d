# SDD 02 — Banco de Dados e LGPD (Paridade de Dados)

> **Escopo:** Modelagem de dados sensíveis, criptografia simétrica, hashing determinístico, consentimento legal e direito ao esquecimento.
> **Atualizado:** 2026-07-21

---

## 1. Estratégia de Paridade de Dados

Todo dado pessoal identificável (PII) — exceto `email` da tabela `users` — utiliza **colunas duplas**:

| Coluna | Algoritmo | Propósito |
| :--- | :--- | :--- |
| `*_hash` | `hash('sha256', $valor)` | Índice único + busca rápida (WHERE) |
| `*_encrypted` | `Crypt::encryptString($valor)` (AES-256-CBC) | Armazenamento criptografado em repouso |

**Exceção:** `users.email` em texto puro (login Fortify).

---

## 2. Tabelas com Paridade LGPD

### 2.1 `users`

| Campos sensíveis | Hash | Encrypted |
| :--- | :--- | :--- |
| Primeiro nome | `first_name_hash` | `first_name_encrypted` |
| Sobrenome | `last_name_hash` | `last_name_encrypted` |
| E-mail (login) | — | `email` (texto puro, exceção) |

**Campo público seguro:** `display_name` (apelido ou primeiro nome).

### 2.2 `clients`

| Campos sensíveis | Hash | Encrypted |
| :--- | :--- | :--- |
| Primeiro nome | `first_name_hash` | `first_name_encrypted` |
| Sobrenome | `last_name_hash` | `last_name_encrypted` |
| Documento (CPF/CNPJ) | `doc_hash` | `doc_encrypted` |
| Endereço | `address_hash` | `address_encrypted` |
| Número | `number_hash` | `number_encrypted` |
| Estado | `state_hash` | `state_encrypted` |
| CEP | `zipcode_hash` | `zipcode_encrypted` |
| Cidade | `city_hash` | `city_encrypted` |
| Telefone 1 | `phone1_hash` | `phone1_encrypted` |
| Telefone 2 | `phone2_hash` | `phone2_encrypted` |
| Contato 1 | `contact1_hash` | `contact1_encrypted` |
| Contato 2 | `contact2_hash` | `contact2_encrypted` |

**SoftDeletes:** Exclusão lógica para preservar integridade referencial de `orders`.

### 2.3 `suppliers`

| Campos sensíveis | Hash | Encrypted |
| :--- | :--- | :--- |
| Documento (CNPJ/CPF) | `document_hash` (unique com tenant_id) | `document_encrypted` |
| Nome do contato | — | `contact_encrypted` |
| Endereço | `address_hash` | `address_encrypted` |
| Número | `number_hash` | `number_encrypted` |
| Bairro | `district_hash` | `district_encrypted` |
| Cidade | `city_hash` | `city_encrypted` |
| Contato 1 | `contact1_hash` | `contact1_encrypted` |
| Telefone 1 | `phone1_hash` | `phone1_encrypted` |
| Contato 2 | `contact2_hash` | `contact2_encrypted` |
| Telefone 2 | `phone2_hash` | `phone2_encrypted` |

### 2.4 `carriers`

| Campos sensíveis | Hash | Encrypted |
| :--- | :--- | :--- |
| Razão social | `company_name_hash` | `company_name_encrypted` |
| Documento (CNPJ/CPF) | `document_hash` | `document_encrypted` |
| Endereço | — | `address_encrypted` |
| Telefone | — | `phone_encrypted` |

### 2.5 `bank_details`

| Campos sensíveis | Hash | Encrypted |
| :--- | :--- | :--- |
| Agência | — | `routing_number_encrypted` |
| Conta | — | `account_number_encrypted` |
| Chave PIX | — | `bank_pix_key_encrypted` |
| Documento do titular | `account_holder_doc_hash` | `account_holder_doc_encrypted` |

### 2.6 `messages` e `disputes`

| Campos sensíveis | Hash | Encrypted |
| :--- | :--- | :--- |
| Conteúdo da mensagem | — | `content_encrypted` |
| Descrição da disputa | — | `description_encrypted` |
| Motivo da devolução | `reason_hash` | `reason_encrypted` |

### 2.7 `visitor_legal_consents`

| Campos sensíveis | Hash | Encrypted |
| :--- | :--- | :--- |
| IP do visitante | `ip_hash` | `ip_encrypted` |
| User agent | — | `user_agent` (texto puro) |

### 2.8 `tenants`

| Campos sensíveis | Hash | Encrypted |
| :--- | :--- | :--- |
| Razão social | `company_name_hash` | `company_name_encrypted` |
| Documento (CNPJ/CPF) | — | `document` (texto puro — snapshot cadastral) |

---

## 3. Serviço de Criptografia

**`App\Services\EncryptionService`**

| Método | Descrição |
| :--- | :--- |
| `encrypt(?string $value): ?string` | `Crypt::encryptString()` — AES-256-CBC |
| `decrypt(?string $value): ?string` | `Crypt::decryptString()` — descriptografia em memória |
| `hash(?string $value): ?string` | `hash('sha256', $value)` — determinístico |
| `hashAndEncrypt(?string $value): array` | Retorna `['hash' => ..., 'encrypted' => ...]` |

**Observers automáticos:**
- `UserObserver` — aplica hash/encrypt em `first_name`, `last_name` no `saving`.

---

## 4. Consentimento Legal

### 4.1 Tabela `legal_documents`

| Coluna | Descrição |
| :--- | :--- |
| `type` | `terms_of_service` ou `privacy_policy` |
| `title` | Título do documento |
| `content_html` | Conteúdo completo em HTML |
| `version` | Versão incremental (inteiro) |
| `grace_period_days` | Prazo de carência (default 7) para aceite obrigatório |
| `published_at` | Data de publicação |

### 4.2 Tabela `visitor_legal_consents`

Registra cada aceite de termos com:
- `legal_document_id` — documento aceito
- `client_id` / `user_id` — quem aceitou (nullable para visitantes)
- `ip_hash` + `ip_encrypted` — paridade LGPD do IP
- `user_agent` — navegador (texto puro)
- `status` — `accepted` ou `revoked`

### 4.3 Endpoints de Consentimento

| Método | Rota | Descrição |
| :--- | :--- | :--- |
| GET | `/legal/{type}` | Exibe termos (`terms` ou `privacy`) dinamicamente |
| POST | `/legal/accept` | Aceite de um documento específico |
| POST | `/legal/decline` | Recusa |
| POST | `/legal/accept-both` | Aceite simultâneo de termos + privacidade |
| POST | `/consent/accept` | Consentimento pós-login |
| POST | `/consent/dismiss` | Dispensa do banner |

---

## 5. Direito ao Esquecimento (Exclusão de Contas)

### 5.1 Política

1. Dados financeiros, fiscais e de vendas **não podem ser apagados fisicamente** (Hard Delete proibido).
2. SoftDeletes nas tabelas permitidas (`clients`).
3. Anonimização: substituir campos `*_encrypted` pelo valor literal `[ANONYMIZED]`.
4. `display_name` alterado para `Usuário Anonimizado`.

### 5.2 Tabelas afetadas

| Tabela | Ação |
| :--- | :--- |
| `clients` | SoftDelete + anonimização de PII |
| `orders` | Mantido (dados fiscais) — snapshot já imutável |
| `order_items` | Mantido (dados fiscais) |
| `reviews` | Mantido (avaliações são conteúdo público) |
| `visitor_legal_consents` | Mantido (auditoria legal) |

---

## 6. Fluxos Críticos

### 6.1 Cadastro com Dados Sensíveis

1. `FormRequest` aplica `trim()` e `strip_tags()` em todos os inputs.
2. Service chama `EncryptionService::hashAndEncrypt($value)` para cada campo PII.
3. Persiste `*_hash` e `*_encrypted` nas respectivas colunas.
4. `display_name` é gerado a partir do primeiro nome (ou fornecido pelo usuário).

### 6.2 Busca por Dado Sensível

1. Input do usuário → `hash('sha256', $input)`.
2. Query: `WHERE *_hash = $hash`.
3. Resultado: descriptografa `*_encrypted` em memória para exibição.

### 6.3 Exclusão de Conta (Cliente)

1. Cliente solicita exclusão via perfil.
2. `DB::transaction()`:
   - SoftDelete em `clients`.
   - Substitui todos os `*_encrypted` por `[ANONYMIZED]`.
   - `display_name` → `Usuário Anonimizado`.
   - `email` → `anonymous_{id}@deleted.demanda3d.com`.
3. `orders` e dados fiscais permanecem intactos.

---

## 7. Sanitização de Entradas

| Regra | Aplicação |
| :--- | :--- |
| `trim()` | Todos os campos string em `FormRequest` |
| `strip_tags()` | Todos os campos string em `FormRequest` |
| `NoContactDataRule` | Bloqueia e-mails/telefones em `messages` e `disputes` |
| `NoOffensiveContentRule` | Bloqueia termos ofensivos (leet-speak, espaços artificiais) |

---

## 8. Dependências

| Dependência | Propósito |
| :--- | :--- |
| `App\Services\EncryptionService` | Criptografia simétrica + hash determinístico |
| `App\Observers\UserObserver` | Observer automático de hash/encrypt |
| `App\Services\LegalConsentService` | Gestão de consentimentos |
| `App\Rules\NoContactDataRule` | Validação de dados de contato em mensagens |
| `App\Rules\NoOffensiveContentRule` | Validação de conteúdo ofensivo |
| `Illuminate\Support\Facades\Crypt` | Criptografia nativa do Laravel |
| `Illuminate\Support\Facades\DB` | Transações atômicas |