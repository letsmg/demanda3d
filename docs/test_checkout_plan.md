# Plano de Ação — Checkout e Segurança

**Data:** 2026-07-15

## 1. Análise de Lacunas (Gaps)

### 1.1 Migration & Model
- [x] Coluna `delivered_at` — migration `000005` já existe
- [x] Model `Order` atualizado com `delivered_at`, `snapshot_address`, `snapshot_product_name`, `snapshot_product_price` no `$fillable`
- [x] `snapshot_address` salvo no `CheckoutService` na tabela `orders`

### 1.2 Cancelamento (CDC 7 dias)
- [x] Rota `POST /perfil/pedidos/{order}/devolucao` já existe no `ClientProfileController`
- [x] `Order::canBeCancelled()` adicionado ao Model
- [x] `ClientProfileController::requestReturn` usa `canBeCancelled()` (regra unificada)

### 1.3 Imutabilidade pós-compra
- [x] `OrderItems` já salva `snapshot_product_name` e `snapshot_product_price`
- [x] `snapshot_address` salvo na tabela `orders` via `CheckoutService`
- [x] Cliente não pode editar pedidos (apenas devolução/cancelamento)

### 1.4 Segurança de API (Bypass)
- [x] `canAccessFinancials()` cobre SELLER_2 e CARRIER_2
- [x] `ClientProfileController::requestReturn` verifica `client_id` antes de autorizar
- [x] `CheckLegalConsent` corrigido: usa `User::find()` em vez de `auth()->user()` para evitar erro com guard `clients`

### 1.5 Testes
- [x] `checkout.spec.ts` — reescrito com locators modernos (11 testes)
- [x] `CheckoutServiceTest.php` — 6 testes existentes
- [x] `OrderCancellationTest.php` — 5 testes (PASS)
- [x] Teste de bypass de API incluído no `OrderCancellationTest.php`

## 2. Correções Aplicadas

| Problema | Arquivo | Solução |
|---|---|---|
| `Call to a member function isAdmin() on null` | `CheckLegalConsent.php` | `User::find($userId)` em vez de `auth()->user()` |
| `$legalData` não definido | `RegisterCarrierController.php` | Adicionado ao `use` do closure |
| Cancelamento usava `delivery_date` | `ClientProfileController.php` | Usa `canBeCancelled()` via Model |
| `snapshot_address` não salvo | `CheckoutService.php` | Salvo na criação da Order |
| Model sem `delivered_at` | `Order.php` | Adicionado ao `$fillable` + `canBeCancelled()` |

## 3. Resultado dos Testes

### Pest
```
✓ CheckoutServiceTest: 6 passed
✓ RegistrationTest: 6 passed
✓ OrderCancellationTest: 5 passed (12 assertions)
```

### Playwright
```
✓ login.spec.ts: 15 testes (3 describes)
✓ register.spec.ts: 9 testes (3 describes)
✓ store.spec.ts: 9 testes (3 describes)
✓ checkout.spec.ts: 11 testes (3 describes)