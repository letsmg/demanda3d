# SDD 01 — Autenticação e Níveis de Acesso

> **Escopo:** Autenticação, guards, níveis de acesso (Enums), middlewares, políticas de senha e fluxo de recuperação.
> **Atualizado:** 2026-07-21

---

## 1. Modelagem de Dados

### 1.1 Tabela `users`

| Coluna | Tipo | Descrição |
| :--- | :--- | :--- |
| `id` | BIGINT PK | Identificador único |
| `email` | VARCHAR(255) UNIQUE | E-mail em texto puro para login (exceção LGPD) |
| `first_name_encrypted` | TEXT | Primeiro nome criptografado (AES-256-CBC) |
| `first_name_hash` | VARCHAR(64) INDEX | SHA-256 do primeiro nome |
| `last_name_encrypted` | TEXT | Sobrenome criptografado |
| `last_name_hash` | VARCHAR(64) INDEX | SHA-256 do sobrenome |
| `display_name` | VARCHAR(255) | Apelido/nome público seguro para listagens |
| `birth_date` | DATE | Data de nascimento (validação +18) |
| `password` | VARCHAR(255) | Hash Argon2id (64MB / 3 iterações / 2 threads) |
| `access_level` | TINYINT INDEX | Enum `UserAccessLevel` (1-15) |
| `is_active` | BOOLEAN DEFAULT true | Bloqueio administrativo |
| `email_verified_at` | TIMESTAMP | Confirmação Fortify |
| `remember_token` | VARCHAR(100) | Token "lembrar-me" |

**Model:** `App\Models\User` — Observer `UserObserver` aplica hash/encrypt automaticamente.

---

## 2. Hierarquia de Acesso

### 2.1 `UserAccessLevel` (int enum)

| Valor | Constante | Grupo | Descrição |
| :--- | :--- | :--- | :--- |
| 1 | `SELLER_1` | sellers | Vendedor Master — finanças, gerência, exclusões |
| 2 | `SELLER_2` | sellers | Vendedor Operacional — apenas catálogo/produtos |
| 5 | `CARRIER_1` | carriers | Transportador Admin — painel logístico total |
| 6 | `CARRIER_2` | carriers | Transportador Colaborador — acesso operacional limitado |
| 10 | `ADMIN` | platform_admin | Administrador Geral da Plataforma |
| 11 | `ADMIN_2` | platform_admin | Administrador de Suporte/Operações |
| 15 | `CUSTOMER` | customer | Cliente final / Comprador |

### 2.2 `UserAccessGroup` (string enum)

| Grupo | Membros | Portal de Redirecionamento |
| :--- | :--- | :--- |
| `platform_admin` | ADMIN, ADMIN_2 | Dashboard staff |
| `sellers` | SELLER_1, SELLER_2 | Dashboard staff |
| `carriers` | CARRIER_1, CARRIER_2 | `/carrier/dashboard` |
| `customer` | CUSTOMER | `/home` (loja pública) |

### 2.3 Métodos de Permissão (centralizados no Enum)

| Método | Quem tem acesso |
| :--- | :--- |
| `canAccessFinancials()` | SELLER_1, ADMIN |
| `canManageTenant()` | SELLER_1, ADMIN |
| `canAccessAdultContent()` | sellers, admins |
| `canManageTeam()` | SELLER_1, SELLER_2 |
| `canToggleUsers()` | ADMIN, ADMIN_2 |
| `canResetPasswords()` | ADMIN, ADMIN_2 |

---

## 3. Guards e Autenticação

| Guard | Tabela/Model | Uso |
| :--- | :--- | :--- |
| `web` (default) | `users` | Staff (sellers, admins) |
| `clients` | `clients` | Clientes finais (CUSTOMER) |
| `carriers` | `users` (access_level 5/6) | Transportadoras |

**Particularidade do CUSTOMER:** Clientes autenticam via tabela `clients` com guard `clients`, separado do guard `web` usado por staff.

---

## 4. Endpoints e Rotas

### 4.1 Staff (sellers + admins) — Guard `web`

| Método | Rota | Middleware |
| :--- | :--- | :--- |
| GET | `/login` | `redirect_if_authenticated` |
| POST | `/login` | `throttle:6,1` |
| POST | `/logout` | `auth` |
| GET | `/forgot-password` | guest |
| POST | `/forgot-password` | `throttle:6,1` |
| GET | `/reset-password/{token}` | guest |
| POST | `/reset-password` | guest |
| GET | `/verify-email` | `auth` |
| POST | `/verify-email` | `auth`, `throttle:6,1` |

### 4.2 Clientes — Guard `clients`

| Método | Rota | Middleware |
| :--- | :--- | :--- |
| GET | `/login_cli` | `redirect_if_authenticated` |
| POST | `/login_cli` | `redirect_if_authenticated` |
| GET | `/register_cli` | `redirect_if_authenticated` |
| POST | `/register_cli` | `redirect_if_authenticated` |
| POST | `/logout_cli` | — |

### 4.3 Transportadoras — Guard `carriers`

| Método | Rota | Middleware |
| :--- | :--- | :--- |
| GET | `/login_carrier` | `redirect_if_authenticated:carriers` |
| POST | `/login_carrier` | `redirect_if_authenticated:carriers` |
| GET | `/register_carrier` | `redirect_if_authenticated:carriers` |
| POST | `/register_carrier` | `redirect_if_authenticated:carriers` |
| POST | `/logout_carrier` | — |

### 4.4 Configurações do Usuário (staff)

| Método | Rota | Middleware |
| :--- | :--- | :--- |
| GET/PATCH | `/settings/profile` | `auth` |
| DELETE | `/settings/profile` | `auth`, `verified` |
| GET | `/settings/security` | `auth`, `verified`, `RequirePassword` |
| PUT | `/settings/password` | `auth`, `verified`, `throttle:6,1` |

### 4.5 Gestão Administrativa de Usuários

| Método | Rota | Middleware |
| :--- | :--- | :--- |
| GET | `/admin/users` | `auth`, `verified`, `ensure.staff` |
| PUT | `/admin/users/{user}` | `auth`, `verified`, `ensure.staff` |
| PATCH | `/admin/users/{user}/toggle` | `canToggleUsers` |
| POST | `/admin/users/{user}/reset-password` | `canResetPasswords` |

---

## 5. Fluxos Críticos

### 5.1 Recuperação de Senha (Zero E-mail Plaintext)

1. Usuário informa e-mail no formulário.
2. Sistema aplica `hash('sha256', $email)` e busca por `email` (texto puro).
3. Se encontrado, `email_encrypted` é descriptografado **em memória** para disparar o e-mail.
4. Nenhum e-mail descriptografado é persistido em log ou requisição.

### 5.2 Bloqueio/Desbloqueio de Usuários

1. ADMIN ou ADMIN_2 acessa `/admin/users`.
2. Ação de toggle altera `is_active`.
3. Registro em `user_status_logs` com `(user_id, author_id, action, reason)`.

### 5.3 Verificação de Idade (+18)

1. `User::is18Plus()` calcula idade a partir de `birth_date`.
2. Middleware `check.age` nas rotas de produto e loja com conteúdo adulto.
3. Staff (sellers/admins) ignora verificação — acesso irrestrito a conteúdo adulto.

---

## 6. Dependências

| Dependência | Propósito |
| :--- | :--- |
| `Laravel Fortify` | Autenticação, verificação de e-mail, reset de senha |
| `App\Enums\UserAccessLevel` | Enum central de níveis de acesso |
| `App\Enums\UserAccessGroup` | Agrupamento para redirecionamento de portais |
| `App\Services\EncryptionService` | Criptografia/descriptografia de dados PII |
| `App\Services\PasswordHashService` | Configuração Argon2id |
| `App\Observers\UserObserver` | Hash automático de `first_name`, `last_name` |
| `App\Policies\*` | Verificações de autorização por recurso |
| `App\Http\Middleware\CheckAccessLevel` | Middleware que recebe níveis permitidos (ex: `:10,1`) |
| `App\Http\Middleware\EnsureStaff` | Bloqueia CUSTOMER do painel administrativo |
| `App\Http\Middleware\RedirectIfAuthenticated` | Redireciona usuários já logados |