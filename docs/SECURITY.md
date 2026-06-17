# 🔐 Segurança - Guia de Implementação

## Argon2id Hash Configuration

### O que é Argon2id?
Argon2id é um algoritmo de hashing de senha vencedor do Password Hashing Competition (2015). É resistente a ataques GPU, ASIC e side-channel.

### Configuração Atual (Production Ready)
```php
ARGON2ID_MEMORY=65536  // 64MB - Alto nível de proteção contra ataques GPU
ARGON2ID_TIME=4        // Minimum iterations (aumentar para 5 em operações críticas)
ARGON2ID_THREADS=4     // Paralelismo para melhorar performance sem sacrificar segurança
```

### Proteção contra Ataques

#### 1. Ataques de Força Bruta
- **Memory Cost (65536)**: Força o atacante a alocar 64MB por tentativa, impedindo paralelização eficiente
- **Time Cost (4)**: Cada hash leva tempo computacional significativo
- **Threads (4)**: Aproveita múltiplos núcleos, tornando ataques paralelos mais custosos

#### 2. Ataques GPU/ASIC
- Argon2id é específicamente designado para resistir a ataques com hardware especializado
- Não pode ser acelerado significativamente em GPUs como MD5 ou SHA1

#### 3. Side-Channel Attacks
- Hash verification é feita com `Hash::check()` que compara de forma segura (timing-safe)
- Uso de `Hash::needsRehash()` para migração gradual de parâmetros

### Variações Seguras por Caso de Uso

#### Registros (Menos frequentes - máxima segurança)
```php
// .env
PASSWORD_STRETCHING=true
```
Aumenta para time_cost=5, memory_cost=131072 (128MB)

#### Login (Frequente - balanço segurança/performance)
Usa configuração padrão (time_cost=4, memory_cost=65536)

#### Senhas Críticas (Admin, alteração)
Use `PasswordHashService::hash($password, stretched: true)`

---

## Access Control com ENUM

### Níveis de Acesso (UserAccessLevel)
```php
enum UserAccessLevel: int {
    case STAFF = 0;      // Funcionário com acesso limitado
    case ADMIN = 1;      // Administrador com acesso total
    case CUSTOMER = 9;   // Cliente externo (acesso muito limitado)
}
```

### Políticas de Autorização (Policies)

#### ClientPolicy
```
viewAny:   ADMIN, STAFF
view:      ADMIN, STAFF
create:    ADMIN, STAFF
update:    ADMIN, STAFF
delete:    ADMIN only
forceDelete: ADMIN only
```

#### OrderPolicy
Mesma estrutura que ClientPolicy

### Middlewares

#### AdminOnly
- Bloqueia qualquer coisa que não seja ADMIN
- Retorna 403 Forbidden

#### StaffOnly
- Permite ADMIN e STAFF
- Bloqueia CUSTOMER e usuários não autenticados

#### CheckAccessLevel
- Verifica um ou mais níveis específicos
- Uso: `Route::middleware('access.level:' . UserAccessLevel::ADMIN->value)`

---

## Como Usar

### Criar Usuários com Access Levels

#### Admin
```bash
php artisan user:create-admin "John Admin" "john@admin.com" --password=securepass
```

#### Staff
```bash
php artisan user:create-staff "Jane Staff" "jane@staff.com"
```

#### Customer (Via formulário/API)
```php
User::create([
    'name' => 'Customer Name',
    'email' => 'customer@example.com',
    'password' => Hash::make('password'),
    'access_level' => UserAccessLevel::CUSTOMER,
]);
```

### Verificar Access Level no Controller

```php
// Controller
if ($user->isAdmin()) {
    // Admin actions
}

if ($user->isStaff()) {
    // Staff actions
}

// Ou com Policies
$this->authorize('create', Client::class);
```

### Verificar em Request

```php
// FormRequest
public function authorize(): bool {
    return $this->user()->isAdmin() || $this->user()->isStaff();
}
```

---

## Testes de Segurança

Execute os testes de autorização:
```bash
php artisan test tests/Feature/AuthorizationTest.php
```

O teste valida:
- ✅ ADMIN pode CRUD completo
- ✅ STAFF pode Create/Read/Update
- ✅ CUSTOMER é bloqueado
- ✅ Hash Argon2id funciona corretamente

---

## Variáveis Sensíveis

Jamais commitar:
- `.env` com credenciais reais
- Senhas hardcoded
- Tokens de API

Use `.env.example` como template.

---

## Monitoramento e Auditoria

### Para Produção
1. **Rate Limiting**: Adicionar em endpoints críticos (login, password reset)
2. **Audit Logs**: Registrar todas as ações de ADMIN/STAFF
3. **2FA**: Considerar para accounts ADMIN
4. **Password Rotation**: Forçar mudança periódica (90 dias para ADMIN)
5. **Session Management**: Limpar sessions inativas

### Exemplo Rate Limiting
```php
Route::middleware(['throttle:60,1'])->group(function () {
    Route::post('/api/clients', [ClientController::class, 'store']);
});
```

---

## Referências

- [OWASP Password Storage Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Password_Storage_Cheat_Sheet.html)
- [Argon2 Official](https://github.com/P-H-C/phc-winner-argon2)
- [Laravel Authentication](https://laravel.com/docs/authentication)
- [Laravel Authorization](https://laravel.com/docs/authorization)
