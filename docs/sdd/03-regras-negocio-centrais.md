# SDD 03 — Regras de Negócio Centrais (Pedidos, Produtos, Checkout)

> **Escopo:** Produtos, carrinho, checkout, pedidos, snapshots imutáveis, etiquetas, devoluções, cupons e avaliações.
> **Atualizado:** 2026-07-21

---

## 1. Modelagem de Dados

### 1.1 `products`

| Coluna | Tipo | Descrição |
| :--- | :--- | :--- |
| `id` | BIGINT PK | Identificador único |
| `tenant_id` | BIGINT FK → `tenants.id` CASCADE | Loja proprietária (TenantScope) |
| `name` | VARCHAR(255) UNIQUE (com tenant_id) | Nome do produto |
| `slug` | VARCHAR(255) | Slug amigável |
| `description` | TEXT | Descrição |
| `height` / `width` | INTEGER | Dimensões em mm |
| `approximate_weight` | INTEGER | Peso final em gramas |
| `waste_weight` | INTEGER | Peso de purga/suportes em gramas |
| `material_type` | VARCHAR(255) | `filament` ou `resin` |
| `print_time` | INTEGER | Tempo de impressão em minutos |
| `pieces_produced` | INTEGER | Peças por fornada |
| `maintenance_fee` | DECIMAL(12,2) | Taxa de desgaste da máquina |
| `painting_time` | INTEGER | Tempo de pintura em minutos |
| `painting_material` | VARCHAR(255) | Materiais de pintura |
| `painting_cost` | DECIMAL(12,2) | Custo de materiais de pintura |
| `extras_cost` | DECIMAL(12,2) | Embalagem, LEDs, argolas etc. |
| `approximate_cost` | DECIMAL(12,2) | Custo total calculado |
| `sale_price` | DECIMAL(12,2) NOT NULL | Preço de venda |
| `is_active` | BOOLEAN DEFAULT true | Visível na vitrine |
| `moderation_status` | VARCHAR(255) DEFAULT `pending` | `pending`, `approved`, `rejected` |
| `adult_category` | INTEGER DEFAULT 0 | Flag +18 |
| `deleted_at` | TIMESTAMP (SoftDeletes) | Exclusão lógica |

**Model:** `App\Models\Product` — `TenantScope` global, `Searchable` (Meilisearch), `SoftDeletes`.

### 1.2 `product_images`

| Coluna | Tipo | Descrição |
| :--- | :--- | :--- |
| `path` | VARCHAR(255) | Imagem otimizada WebP |
| `original_path` | VARCHAR(255) | Original |
| `thumbnail_path` | VARCHAR(255) | Thumbnail |
| `order` | TINYINT DEFAULT 0 | Ordenação drag-and-drop (SortableJS) |

### 1.3 `cart_items`

| Coluna | Tipo | Descrição |
| :--- | :--- | :--- |
| `client_id` | BIGINT FK → `clients.id` CASCADE | Cliente |
| `product_id` | BIGINT FK → `products.id` CASCADE | Produto |

> **Unique:** `(client_id, product_id)` — um produto por cliente no carrinho.

### 1.4 `orders`

| Coluna | Tipo | Descrição |
| :--- | :--- | :--- |
| `id` | BIGINT PK | Identificador único |
| `tenant_id` | BIGINT FK → `tenants.id` CASCADE | Loja vendedora |
| `client_id` | BIGINT FK → `clients.id` CASCADE | Cliente comprador |
| `order_date` | DATE NOT NULL INDEX | Data do pedido |
| `delivery_date` | DATE NOT NULL | Previsão de entrega |
| `delivered_at` | TIMESTAMP NULLABLE INDEX | Data de entrega efetiva |
| `stripe_session_id` | VARCHAR(255) UNIQUE | ID da sessão Stripe |
| `stripe_payment_intent_id` | VARCHAR(255) | Payment Intent |
| `amount_total` | DECIMAL(12,2) | Valor total |
| `platform_fee_amount` | DECIMAL(12,2) | Taxa da plataforma |
| `seller_amount` | DECIMAL(12,2) | Repasse ao vendedor |
| `carrier_amount` | DECIMAL(12,2) | Repasse à transportadora |
| `payment_split_status` | VARCHAR(255) | Status do split de pagamento |
| `currency` | VARCHAR(3) DEFAULT `brl` | Moeda |
| `status` | VARCHAR(255) DEFAULT `pending` | `pending`, `paid`, `processing`, `confirmed`, `delivered`, `cancelled` |
| `snapshot_address` | TEXT | Endereço de entrega (snapshot imutável) |
| `snapshot_product_name` | VARCHAR(500) | Nome do produto no momento da compra |
| `snapshot_product_price` | DECIMAL(12,2) | Preço no momento da compra |

**Model:** `App\Models\Order` — método `canBeCancelled()` implementa regra CDC (7 dias).

### 1.5 `order_items`

| Coluna | Tipo | Descrição |
| :--- | :--- | :--- |
| `order_id` | BIGINT FK → `orders.id` CASCADE INDEX | Pedido |
| `product_id` | BIGINT FK → `products.id` NULL INDEX | Produto original (nullable) |
| `snapshot_product_name` | VARCHAR(500) NOT NULL | Nome (snapshot) |
| `snapshot_product_price` | DECIMAL(12,2) NOT NULL | Preço (snapshot) |

### 1.6 `order_labels`

| Coluna | Tipo | Descrição |
| :--- | :--- | :--- |
| `order_id` | BIGINT FK → `orders.id` CASCADE | Pedido |
| `carrier_id` | BIGINT FK → `carriers.id` NULL | Transportadora |
| `tenant_id` | BIGINT FK → `tenants.id` CASCADE | Loja |
| `tracking_code` | VARCHAR(255) | Código de rastreio |
| `label_url` | VARCHAR(255) | URL do PDF/HTML da etiqueta |
| `status` | VARCHAR(255) DEFAULT `pending` | `pending`, `generated`, `shipped`, `delivered` |
| `recipient_name` | VARCHAR(255) NOT NULL | Nome do destinatário (display_name) |
| `recipient_address` | TEXT NOT NULL | Endereço completo de entrega |

### 1.7 `return_requests`

| Coluna | Tipo | Descrição |
| :--- | :--- | :--- |
| `order_id` | BIGINT FK UNIQUE | Um pedido = uma devolução ativa |
| `client_id` | BIGINT FK INDEX | Cliente |
| `status` | VARCHAR(20) DEFAULT `requested` | `requested`, `shipped_back`, `approved`, `rejected` |
| `reason_encrypted` / `reason_hash` | TEXT / VARCHAR(64) | Motivo criptografado |

### 1.8 `coupons`

| Coluna | Tipo | Descrição |
| :--- | :--- | :--- |
| `code` | VARCHAR(50) UNIQUE INDEX | Código do cupom |
| `tenant_id` | BIGINT FK NULL | null = global |
| `category_id` | BIGINT FK NULL | Categoria específica |
| `type` | VARCHAR(20) DEFAULT `percentage` | `percentage` ou `fixed` |
| `value` | DECIMAL(12,2) NOT NULL | Valor (percentual ou fixo) |
| `min_order_value` | DECIMAL(12,2) | Valor mínimo do pedido |
| `max_uses` | INTEGER | Limite de usos |
| `used_count` | INTEGER DEFAULT 0 | Usos consumidos |
| `starts_at` / `expires_at` | TIMESTAMP | Vigência |

### 1.9 `reviews`

| Coluna | Tipo | Descrição |
| :--- | :--- | :--- |
| `tenant_id` | BIGINT FK → `tenants.id` CASCADE | Loja avaliada |
| `client_id` | BIGINT FK → `clients.id` CASCADE | Cliente |
| `order_id` | BIGINT FK → `orders.id` CASCADE | Pedido |
| `rating` | INTEGER NOT NULL | Nota (1-5) |
| `comment` | TEXT | Comentário |

---

## 2. Endpoints e Rotas

### 2.1 Loja Pública e Produtos

| Método | Rota | Descrição |
| :--- | :--- | :--- |
| GET | `/store` | Listagem de produtos (loja agregada) |
| GET | `/api/store/products` | Lazy-loading "Mostrar mais" |
| GET | `/store/{slug}` | Detalhe do produto (+ middleware `check.age`) |
| GET | `/tenants` | Listagem de vendedores |
| GET | `/tenant/{fantasy_slug}` | Perfil público do vendedor |
| GET | `/api/tenant/{fantasy_slug}/products` | Lazy-loading produtos do vendedor |
| GET | `/api/produtos` | API pública de produtos |
| GET | `/api/produtos/{slug}` | API pública detalhe (+ `check.age`) |

### 2.2 Carrinho e Checkout

| Método | Rota | Descrição |
| :--- | :--- | :--- |
| GET | `/cart` | Exibe carrinho |
| GET | `/cart/items` | Lista itens (API) |
| POST | `/cart` | Adiciona item |
| PUT | `/cart/{cartItem}` | Atualiza quantidade |
| DELETE | `/cart/{cartItem}` | Remove item |
| POST | `/cart/clear` | Limpa carrinho |
| GET | `/checkout` | Página de checkout |
| POST | `/checkout` | Finaliza pedido |
| GET | `/checkout/success` | Confirmação pós-pagamento |
| GET | `/checkout/cancel` | Cancelamento |

### 2.3 Gestão de Pedidos (Staff)

| Método | Rota | Middleware |
| :--- | :--- | :--- |
| GET | `/orders` | `auth`, `verified`, `ensure.staff` |
| GET/POST | `/orders/create` | `auth`, `verified`, `ensure.staff` |
| GET/PUT | `/orders/{order}/edit` | `auth`, `verified`, `ensure.staff` |
| DELETE | `/orders/{order}` | `auth`, `verified`, `ensure.staff` |
| GET | `/api/orders` | API Resource |
| GET | `/api/clients/{clientId}/orders` | Pedidos por cliente |

### 2.4 Gestão de Produtos (Staff)

| Método | Rota | Middleware |
| :--- | :--- | :--- |
| GET | `/products` | `auth`, `verified`, `ensure.staff` |
| GET/POST | `/products/create` | `auth`, `verified`, `ensure.staff` |
| GET/PUT | `/products/{product}/edit` | `auth`, `verified`, `ensure.staff` |
| DELETE | `/products/{product}` | `auth`, `verified`, `ensure.staff` |

### 2.5 Etiquetas (Order Labels)

| Método | Rota | Middleware |
| :--- | :--- | :--- |
| GET | `/admin/orders/{order}/label` | `auth`, `verified`, `ensure.staff` |

> **Acesso:** Exclusivo para SELLER_1, SELLER_2, CARRIER_1, CARRIER_2, ADMIN, ADMIN_2.

### 2.6 Devoluções (Clientes)

| Método | Rota | Middleware |
| :--- | :--- | :--- |
| POST | `/perfil/pedidos/{order}/devolucao` | `auth:clients` |

### 2.7 Cupons

| Método | Rota | Middleware |
| :--- | :--- | :--- |
| POST | `/api/coupons/validate` | `auth:clients` |

### 2.8 Perfil do Cliente

| Método | Rota | Middleware |
| :--- | :--- | :--- |
| GET | `/perfil` | `auth:clients` |
| PUT | `/perfil` | `auth:clients` |
| GET | `/perfil/enderecos` | `auth:clients` |
| PUT | `/perfil/enderecos` | `auth:clients` |
| GET | `/perfil/pedidos` | `auth:clients` |

---

## 3. Fluxos Críticos

### 3.1 Checkout e Snapshot Imutável

1. Cliente adiciona produtos ao carrinho (`cart_items`).
2. No checkout, `CheckoutService` inicia `DB::transaction()`.
3. Cria `Order` com **snapshot estático** de:
   - Endereço de entrega completo (`snapshot_address`).
   - Nome e preço do produto (`snapshot_product_name`, `snapshot_product_price`).
   - Cupom aplicado (valor e regras).
4. Cria `OrderItem` para cada produto com os mesmos snapshots.
5. `product_id` no `OrderItem` é FK **nullable** — sobrevive a soft-deletes.
6. Cria sessão Stripe via `StripeService`.
7. `stripe_session_id` é vinculado ao pedido.
8. Carrinho é limpo após conversão.

### 3.2 Cancelamento de Pedido (CDC)

1. `Order::canBeCancelled()`:
   - Só pedidos com status `paid`, `processing` ou `confirmed`.
   - Se `delivered_at` for null → pode cancelar.
   - Se entregue: 7 dias corridos após `delivered_at` (CDC).
2. Cliente solicita via perfil ou staff via painel.
3. Status alterado para `cancelled`.
4. Estorno processado via Stripe (se aplicável).

### 3.3 Privacidade no Pós-Compra

- **Vendedores NÃO têm acesso** a dados brutos de contato do cliente (telefone, e-mail pessoal).
- Na página de pedidos, o lojista vê apenas `display_name` e dados de entrega física.
- Etiquetas de postagem são geradas automaticamente com dados estritamente necessários.
- `client.email` e `client.phone*` são criptografados e inacessíveis ao seller.

### 3.4 Geração de Etiquetas (Order Labels)

1. Staff acessa `/admin/orders/{order}/label`.
2. Sistema gera etiqueta com:
   - `recipient_name` = `client.display_name`.
   - `recipient_address` = snapshot do endereço de entrega.
   - `carrier_id` e `tracking_code`.
3. Label é persistida em `order_labels` com status `pending`.
4. Atualização de status: `generated` → `shipped` → `delivered`.

### 3.5 Validação de Cupom

1. Cliente autenticado (`auth:clients`) envia código.
2. `POST /api/coupons/validate`:
   - Busca por `code`.
   - Verifica `is_active`, `starts_at`, `expires_at`.
   - Verifica `max_uses` vs `used_count`.
   - Verifica `min_order_value` contra total do carrinho.
   - Verifica `tenant_id` (global ou da loja) e `category_id`.
3. Retorna desconto calculado.

### 3.6 Disponibilidade para Venda (`scopeAvailableForSale`)

Produto só aparece na vitrine se TODOS os critérios forem atendidos:
1. `is_active = true`
2. `tenant.active = true`
3. `tenant.user.email_verified_at IS NOT NULL`
4. Pelo menos 1 `carrier_tenant_agreement` com `status = active`
5. Carrier vinculado com `is_active = true` e `email_verified_at IS NOT NULL`

### 3.7 Avaliações (Reviews)

1. Cliente avalia pedido entregue (nota 1-5).
2. Review dispara job `RecalculateTenantRating`.
3. `tenants.rating_average` e `tenants.rating_count` são recalculados.

---

## 4. Serviços

| Serviço | Responsabilidade |
| :--- | :--- |
| `ProductService` | CRUD, SEO metadata, schema markup, GTM |
| `CheckoutService` | Transação de checkout, snapshot, criação de Order + OrderItems |
| `OrderService` | Gestão de pedidos, cancelamento, status |
| `SplitPayService` | Divisão de pagamento entre plataforma, seller e carrier |
| `ReviewService` | Criação de avaliações, recálculo de rating |

---

## 5. Dependências

| Dependência | Propósito |
| :--- | :--- |
| `App\Scopes\TenantScope` | Isolamento multi-tenant em products |
| `App\Services\StripeService` | Integração Stripe (sessão + webhook) |
| `App\Services\SplitPayService` | Divisão de repasses |
| `App\Services\EncryptionService` | Criptografia de dados sensíveis |
| `App\Services\ImageOptimizationService` | Otimização WebP + thumbnail |
| `App\Services\ImageStorageService` | Armazenamento de imagens |
| `SortableJS` | Ordenação drag-and-drop de product_images |
| `Laravel Scout` | Índice de busca (Meilisearch) |
| `App\Policies\OrderPolicy` | Autorização de ações sobre pedidos |