# 🚀 Setup e Deployment

## Pré-requisitos
- PHP 8.2+
- PostgreSQL 14+
- Node.js 18+
- Composer

## Instalação Local

### 1. Clonar e Instalar Dependências
```bash
cd /var/www/demanda3d
composer install
npm install
```

### 2. Configurar Ambiente
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Configurar Banco de Dados PostgreSQL
```bash
# No PostgreSQL (substitua <senha> por uma senha real)
CREATE USER interview_user WITH PASSWORD '<senha>';
CREATE DATABASE interview_db OWNER interview_user;
GRANT ALL PRIVILEGES ON DATABASE interview_db TO interview_user;

# No .env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=interview_db
DB_USERNAME=interview_user
DB_PASSWORD=<senha>
```

### 4. Executar Migrations
```bash
php artisan migrate
```

### 5. Criar Usuários de Teste
```bash
# Admin
php artisan user:create-admin "Admin User" "admin@demanda3d.com" --password=senha123

# Staff
php artisan user:create-staff "Staff User" "staff@demanda3d.com" --password=senha123
```

### 6. Compilar Assets Frontend
```bash
npm run dev    # Desenvolvimento
npm run build  # Produção
```

### 7. Iniciar Servidor
```bash
# Terminal 1: Laravel
php artisan serve --port=8000

# Terminal 2: Vite (HMR)
npm run dev
```

Acesse: http://localhost:8000

---

## Segurança Aplicada

### ✅ Argon2id Hashing
- Memory: 64MB (65536 bytes)
- Time: 4 iterations
- Threads: 4
- Configurável por `.env`

### ✅ Controle de Acesso (ENUM)
- **ADMIN (1)**: Acesso total - CRUD completo
- **STAFF (0)**: Acesso gerencial - Create/Read/Update
- **CUSTOMER (9)**: Acesso limitado - Visualização apenas

### ✅ Policies
- ClientPolicy: Autorização granular
- OrderPolicy: Autorização granular

### ✅ Middlewares
- `admin.only`: Bloqueia não-admin
- `staff.only`: Bloqueia customer
- `access.level:X`: Verifica níveis específicos

### ✅ Request Validation
- Autorização em cada FormRequest
- Mensagens de erro personalizadas em PT-BR
- Validação de emails únicos
- CEP validado com regex

---

## Testing

### Rodar Testes
```bash
# Todos os testes
php artisan test

# Apenas testes de autorização
php artisan test tests/Feature/AuthorizationTest.php

# Com coverage
php artisan test --coverage
```

### Testes Implementados
- ✅ Admin pode criar clients
- ✅ Staff pode criar clients
- ✅ Customer não pode criar clients
- ✅ Admin pode deletar clients
- ✅ Staff não pode deletar clients
- ✅ Autenticação requerida
- ✅ Argon2id hash verification

---

## Estrutura de Pastas

```
app/
├── Enums/
│   └── UserAccessLevel.php        # ENUM com níveis
├── Http/
│   ├── Controllers/
│   │   ├── ClientController.php   # CRUD com autorização
│   │   └── OrderController.php    # CRUD com autorização
│   ├── Middleware/
│   │   ├── AdminOnly.php
│   │   ├── StaffOnly.php
│   │   └── CheckAccessLevel.php
│   └── Requests/
│       ├── Store/UpdateClientRequest.php
│       └── Store/UpdateOrderRequest.php
├── Models/
│   ├── User.php                   # Com access_level ENUM
│   ├── Client.php
│   └── Order.php
├── Policies/
│   ├── ClientPolicy.php           # Autorização
│   └── OrderPolicy.php            # Autorização
├── Services/
│   ├── ClientService.php          # Lógica de negócio
│   ├── OrderService.php           # Lógica de negócio
│   └── PasswordHashService.php    # Hash seguro
├── Providers/
│   ├── AppServiceProvider.php
│   ├── AuthServiceProvider.php    # Registra policies
│   └── FortifyServiceProvider.php
└── Console/Commands/
    ├── CreateAdminUser.php
    └── CreateStaffUser.php

config/
├── app.php
├── hashing.php                    # Argon2id config
└── enums.php

database/
├── migrations/
│   ├── 0001_01_01_000000_create_users_table.php      (updated)
│   ├── 2026_06_10_000000_create_clients_table.php
│   └── 2026_06_10_000001_create_orders_table.php
└── factories/
    └── UserFactory.php

routes/
├── api.php                        # REST API com middleware
└── web.php                        # Inertia pages

resources/js/
├── pages/
│   ├── Clients/Index.vue
│   ├── Clients/Create.vue
│   ├── Clients/Edit.vue
│   ├── Orders/Index.vue
│   ├── Orders/Create.vue
│   └── Orders/Edit.vue
└── types/
    └── models.ts                  # TypeScript types

tests/Feature/
└── AuthorizationTest.php          # Testes de segurança

docs/
└── SECURITY.md                    # Guia de segurança
```

---

## API Endpoints

### Clientes
```
GET    /api/clients              # Lista (Staff+)
POST   /api/clients              # Criar (Staff+)
GET    /api/clients/{id}         # Detalhes (Staff+)
PUT    /api/clients/{id}         # Atualizar (Staff+)
DELETE /api/clients/{id}         # Deletar (Admin only)
```

### Pedidos
```
GET    /api/orders               # Lista (Staff+)
POST   /api/orders               # Criar (Staff+)
GET    /api/orders/{id}          # Detalhes (Staff+)
PUT    /api/orders/{id}          # Atualizar (Staff+)
DELETE /api/orders/{id}          # Deletar (Admin only)
GET    /api/clients/{id}/orders  # Pedidos por cliente (Staff+)
```

---

## Variáveis de Ambiente Importantes

```env
# Argon2id Security
ARGON2ID_MEMORY=65536    # 64MB
ARGON2ID_TIME=4          # iterations
ARGON2ID_THREADS=4       # parallelism

# Password Stretching (opcional, para críticos)
PASSWORD_STRETCHING=false
```

---

## Troubleshooting

### Error: "SQLSTATE[42P01]"
Significa table não existe. Execute:
```bash
php artisan migrate
```

### Error: "Unauthorized" (401)
Falta token de autenticação. Use Fortify/Sanctum:
```bash
php artisan sanctum:install
```

### Error: "Forbidden" (403)
Usuário não tem permissão (access_level insuficiente).
Verifique: `SELECT access_level FROM users WHERE email='your@email.com';`

### Performance do Argon2id lenta
Normal em dev. Para testar, reduza em `.env`:
```env
ARGON2ID_MEMORY=32768
ARGON2ID_TIME=2
```
(Aumentar para produção)

---

## Próximas Melhorias

- [ ] Rate limiting em endpoints críticos
- [ ] Audit log de todas as ações
- [ ] 2FA para admin accounts
- [ ] Password rotation policy (90 days)
- [ ] Session timeout management
- [ ] Email notifications para ações críticas
- [ ] API versioning (v1, v2...)
- [ ] GraphQL support
- [ ] WebSocket real-time updates
