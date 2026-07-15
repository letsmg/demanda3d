# Dicionário de Dados — Demanda3D

> Última atualização: 2026-07-15
> Base: 43 migrations em `database/migrations/`

---

## `users`
**Model:** `User` | **Propósito:** Autenticação do sistema (Laravel Fortify). Armazena credenciais e dados pessoais com criptografia LGPD.

| Coluna | Tipo | Constraints | Descrição |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT PK | auto | Identificador único |
| `email` | VARCHAR(255) | UNIQUE, NOT NULL | E-mail em texto puro para login |
| `first_name_encrypted` | TEXT | NOT NULL | Primeiro nome criptografado (AES-256-CBC) |
| `first_name_hash` | VARCHAR(64) | NOT NULL, INDEX | SHA-256 do primeiro nome para busca |
| `last_name_encrypted` | TEXT | NOT NULL | Sobrenome criptografado |
| `last_name_hash` | VARCHAR(64) | NOT NULL, INDEX | SHA-256 do sobrenome para busca |
| `display_name` | VARCHAR(255) | NULLABLE | Apelido ou primeiro nome para exibição pública |
| `birth_date` | DATE | NULLABLE | Data de nascimento |
| `email_verified_at` | TIMESTAMP | NULLABLE | Confirmação de e-mail (Fortify) |
| `password` | VARCHAR(255) | NOT NULL | Hash Argon2id da senha |
| `access_level` | TINYINT | DEFAULT 15, INDEX | 1=SELLER_1, 2=SELLER_2, 5=CARRIER_1, 6=CARRIER_2, 10=ADMIN, 11=ADMIN_2, 15=CUSTOMER |
| `is_active` | BOOLEAN | DEFAULT true, INDEX | Usuário ativo/inativo |
| `remember_token` | VARCHAR(100) | NULLABLE | Token "lembrar-me" (Fortify) |
| `created_at` | TIMESTAMP | — | Data de criação |
| `updated_at` | TIMESTAMP | — | Data de atualização |

---

## `tenants`
**Model:** `Tenant` | **Propósito:** Dados empresariais de cada conta multi-tenant (loja). Isolamento via TenantScope.

| Coluna | Tipo | Constraints | Descrição |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT PK | auto | Identificador único |
| `user_id` | BIGINT FK | `users.id` CASCADE | Seller 1 (dono da loja) |
| `company_name_encrypted` | TEXT | NULLABLE | Razão social criptografada |
| `company_name_hash` | VARCHAR(64) | NULLABLE, INDEX | SHA-256 da razão social |
| `legal_responsible_name` | VARCHAR(255) | NULLABLE | Nome do responsável legal |
| `fantasy_name` | VARCHAR(255) | NULLABLE | Nome fantasia da loja |
| `fantasy_slug` | VARCHAR(255) | UNIQUE | Slug amigável para URL pública |
| `document_type` | VARCHAR(4) | DEFAULT 'cnpj' | cnpj ou cpf |
| `document` | VARCHAR(18) | NULLABLE | CNPJ/CPF (texto puro — snapshot do cadastro) |
| `phone` | VARCHAR(20) | NULLABLE | Telefone comercial |
| `address` | VARCHAR(255) | NULLABLE | Endereço comercial |
| `number` | VARCHAR(20) | NULLABLE | Número do endereço |
| `district` | VARCHAR(100) | NULLABLE | Bairro |
| `city` | VARCHAR(100) | NULLABLE | Cidade |
| `state` | VARCHAR(2) | NULLABLE | UF |
| `zipcode` | VARCHAR(10) | NULLABLE | CEP |
| `logo_path` | VARCHAR(500) | NULLABLE | Caminho da logo no storage |
| `banner_path` | VARCHAR(500) | NULLABLE | Caminho do banner no storage |
| `active` | BOOLEAN | DEFAULT true, INDEX | Loja ativa |
| `is_profile_complete` | BOOLEAN | DEFAULT false | Cadastro completo |
| `rating_average` | DECIMAL(3,2) | DEFAULT 0 | Média de avaliações |
| `rating_count` | INTEGER | DEFAULT 0 | Quantidade de avaliações |
| `created_at` | TIMESTAMP | — | Data de criação |
| `updated_at` | TIMESTAMP | — | Data de atualização |

---

## `clients`
**Model:** `Client` | **Propósito:** Clientes finais (compradores). Não possui relação direta com `users` — a conexão ocorre via `orders`. SoftDeletes. Dados PII criptografados (LGPD).

| Coluna | Tipo | Constraints | Descrição |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT PK | auto | Identificador único |
| `tenant_id` | BIGINT FK | `tenants.id` CASCADE | Tenant da loja onde o cliente se cadastrou |
| `display_name` | VARCHAR(255) | NULLABLE | Apelido/nome público |
| `data_nascimento` | DATE | NULLABLE | Data de nascimento |
| `email` | VARCHAR(255) | NULLABLE, UNIQUE | E-mail para login do cliente |
| `password` | VARCHAR(255) | NULLABLE | Hash Argon2id da senha |
| `doc_type` | VARCHAR(4) | DEFAULT 'CPF', INDEX | CPF ou CNPJ |
| `first_name_encrypted` | TEXT | NULLABLE | Primeiro nome criptografado |
| `first_name_hash` | VARCHAR(64) | NULLABLE, INDEX | SHA-256 do primeiro nome |
| `last_name_encrypted` | TEXT | NULLABLE | Sobrenome criptografado |
| `last_name_hash` | VARCHAR(64) | NULLABLE, INDEX | SHA-256 do sobrenome |
| `doc_encrypted` | TEXT | NULLABLE | CPF/CNPJ criptografado |
| `doc_hash` | VARCHAR(64) | NULLABLE, INDEX | SHA-256 do documento |
| `address_encrypted` | TEXT | NULLABLE | Endereço criptografado |
| `address_hash` | VARCHAR(64) | NULLABLE, INDEX | SHA-256 do endereço |
| `number_encrypted` | TEXT | NULLABLE | Número criptografado |
| `number_hash` | VARCHAR(64) | NULLABLE, INDEX | SHA-256 do número |
| `state_encrypted` | TEXT | NULLABLE | Estado criptografado |
| `state_hash` | VARCHAR(64) | NULLABLE, INDEX | SHA-256 do estado |
| `zipcode_encrypted` | TEXT | NULLABLE | CEP criptografado |
| `zipcode_hash` | VARCHAR(64) | NULLABLE, INDEX | SHA-256 do CEP |
| `city_encrypted` | TEXT | NULLABLE | Cidade criptografada |
| `city_hash` | VARCHAR(64) | NULLABLE, INDEX | SHA-256 da cidade |
| `state_id` | BIGINT FK | `states.id` NULL | FK para tabela de estados |
| `phone1_encrypted` | TEXT | NULLABLE | Telefone principal criptografado |
| `phone1_hash` | VARCHAR(64) | NULLABLE, INDEX | SHA-256 do telefone |
| `phone2_encrypted` | TEXT | NULLABLE | Telefone secundário criptografado |
| `phone2_hash` | VARCHAR(64) | NULLABLE, INDEX | SHA-256 do telefone 2 |
| `contact1_encrypted` | TEXT | NULLABLE | Contato adicional criptografado |
| `contact1_hash` | VARCHAR(64) | NULLABLE, INDEX | SHA-256 do contato 1 |
| `contact2_encrypted` | TEXT | NULLABLE | Contato adicional criptografado |
| `contact2_hash` | VARCHAR(64) | NULLABLE, INDEX | SHA-256 do contato 2 |
| `deleted_at` | TIMESTAMP | SoftDeletes | Exclusão lógica (LGPD) |
| `created_at` | TIMESTAMP | — | Data de criação |
| `updated_at` | TIMESTAMP | — | Data de atualização |

---

## `suppliers`
**Model:** `Supplier` | **Propósito:** Fornecedores de insumos de impressão 3D por tenant.

| Coluna | Tipo | Constraints | Descrição |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT PK | auto | Identificador único |
| `tenant_id` | BIGINT FK | `tenants.id` CASCADE, INDEX | Tenant proprietário |
| `name` | VARCHAR(255) | NOT NULL | Nome do fornecedor |
| `doc_type` | VARCHAR(4) | DEFAULT 'CNPJ', INDEX | CNPJ ou CPF |
| `ie` | VARCHAR(20) | NULLABLE | Inscrição estadual |
| `document_encrypted` | TEXT | NOT NULL | Documento criptografado |
| `document_hash` | VARCHAR(64) | NOT NULL, UNIQUE (com tenant_id) | SHA-256 do documento |
| `contact_encrypted` | TEXT | NOT NULL | Nome do contato criptografado |
| `address_encrypted` | TEXT | NULLABLE | Endereço criptografado |
| `address_hash` | VARCHAR(64) | NULLABLE | SHA-256 do endereço |
| `number_encrypted` | TEXT | NULLABLE | Número criptografado |
| `number_hash` | VARCHAR(64) | NULLABLE | SHA-256 do número |
| `district_encrypted` | TEXT | NULLABLE | Bairro criptografado |
| `district_hash` | VARCHAR(64) | NULLABLE | SHA-256 do bairro |
| `city_encrypted` | TEXT | NULLABLE | Cidade criptografada |
| `city_hash` | VARCHAR(64) | NULLABLE | SHA-256 da cidade |
| `state` | VARCHAR(2) | NULLABLE | UF |
| `zipcode` | VARCHAR(9) | NULLABLE | CEP |
| `state_id` | BIGINT FK | `states.id` NULL | FK para estados |
| `contact1_encrypted` | TEXT | NULLABLE | Contato adicional criptografado |
| `contact1_hash` | VARCHAR(64) | NULLABLE, INDEX | SHA-256 do contato 1 |
| `phone1_encrypted` | TEXT | NULLABLE | Telefone principal criptografado |
| `phone1_hash` | VARCHAR(64) | NULLABLE, INDEX | SHA-256 do telefone |
| `contact2_encrypted` | TEXT | NULLABLE | Contato adicional criptografado |
| `contact2_hash` | VARCHAR(64) | NULLABLE, INDEX | SHA-256 do contato 2 |
| `phone2_encrypted` | TEXT | NULLABLE | Telefone secundário criptografado |
| `phone2_hash` | VARCHAR(64) | NULLABLE, INDEX | SHA-256 do telefone 2 |
| `email` | VARCHAR(255) | NULLABLE | E-mail do fornecedor |
| `website` | VARCHAR(255) | NULLABLE | Site do fornecedor |
| `notes` | TEXT | NULLABLE | Observações internas |
| `is_active` | BOOLEAN | DEFAULT true, INDEX | Fornecedor ativo |
| `created_at` | TIMESTAMP | — | Data de criação |
| `updated_at` | TIMESTAMP | — | Data de atualização |

---

## `inputs`
**Model:** `Input` | **Propósito:** Insumos de impressão 3D (filamentos, resinas) vinculados a fornecedores.

| Coluna | Tipo | Constraints | Descrição |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT PK | auto | Identificador único |
| `tenant_id` | BIGINT FK | `tenants.id` CASCADE, INDEX | Tenant proprietário |
| `supplier_id` | BIGINT FK | `suppliers.id` CASCADE, INDEX | Fornecedor do insumo |
| `description` | VARCHAR(255) | NOT NULL | Descrição do insumo |
| `brand` | VARCHAR(255) | NOT NULL | Marca do insumo |
| `quantity` | INTEGER | NOT NULL | Quantidade em gramas ou unidades |
| `shipping_cost` | DECIMAL(12,2) | NOT NULL | Valor do frete rateado |
| `cost_value` | DECIMAL(12,2) | NOT NULL | Valor pago no insumo |
| `created_at` | TIMESTAMP | — | Data de criação |
| `updated_at` | TIMESTAMP | — | Data de atualização |

---

## `products`
**Model:** `Product` | **Propósito:** Produtos impressos anunciados na plataforma. SoftDeletes preserva integridade referencial. Tenant-scoped.

| Coluna | Tipo | Constraints | Descrição |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT PK | auto | Identificador único |
| `tenant_id` | BIGINT FK | `tenants.id` CASCADE, INDEX | Loja proprietária |
| `name` | VARCHAR(255) | NOT NULL, UNIQUE (com tenant_id) | Nome do produto |
| `slug` | VARCHAR(255) | NOT NULL | Slug amigável para URL |
| `description` | TEXT | NULLABLE | Descrição do produto |
| `height` | INTEGER | NULLABLE | Altura em mm |
| `width` | INTEGER | NULLABLE | Largura em mm |
| `approximate_weight` | INTEGER | NULLABLE | Peso final da peça em gramas |
| `waste_weight` | INTEGER | NULLABLE | Peso de purga/suportes em gramas |
| `material_type` | VARCHAR(255) | NULLABLE | filament ou resin |
| `print_time` | INTEGER | NULLABLE | Tempo de impressão em minutos |
| `pieces_produced` | INTEGER | NULLABLE | Peças por fornada |
| `maintenance_fee` | DECIMAL(12,2) | NULLABLE | Taxa de desgaste da máquina |
| `painting_time` | INTEGER | NULLABLE | Tempo de pintura em minutos |
| `painting_material` | VARCHAR(255) | NULLABLE | Materiais usados na pintura |
| `painting_cost` | DECIMAL(12,2) | NULLABLE | Custo de materiais de pintura |
| `extras_cost` | DECIMAL(12,2) | NULLABLE | Embalagem, LEDs, argolas etc. |
| `approximate_cost` | DECIMAL(12,2) | NULLABLE | Custo total calculado |
| `sale_price` | DECIMAL(12,2) | NOT NULL | Preço de venda |
| `is_active` | BOOLEAN | DEFAULT true | Visível na vitrine |
| `moderation_status` | VARCHAR(255) | DEFAULT 'pending' | pending, approved, rejected |
| `moderation_notes` | TEXT | NULLABLE | Observações da moderação |
| `adult_category` | INTEGER | DEFAULT 0 | 0=normal, 1=+18 |
| `deleted_at` | TIMESTAMP | SoftDeletes | Exclusão lógica |
| `created_at` | TIMESTAMP | — | Data de criação |
| `updated_at` | TIMESTAMP | — | Data de atualização |

---

## `product_images`
**Model:** `ProductImage` | **Propósito:** Imagens vinculadas a produtos com versionamento otimizado (original, WebP, thumbnail).

| Coluna | Tipo | Constraints | Descrição |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT PK | auto | Identificador único |
| `product_id` | BIGINT FK | `products.id` CASCADE, INDEX | Produto vinculado |
| `path` | VARCHAR(255) | NOT NULL | Caminho da imagem otimizada (WebP) |
| `original_path` | VARCHAR(255) | NULLABLE | Caminho da imagem original |
| `thumbnail_path` | VARCHAR(255) | NULLABLE | Caminho do thumbnail |
| `order` | TINYINT | DEFAULT 0 | Ordem de exibição (drag-and-drop) |
| `created_at` | TIMESTAMP | — | Data de criação |
| `updated_at` | TIMESTAMP | — | Data de atualização |

---

## `categories`
**Model:** `Category` | **Propósito:** Categorias de produtos com flag de conteúdo adulto.

| Coluna | Tipo | Constraints | Descrição |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT PK | auto | Identificador único |
| `name` | VARCHAR(255) | NOT NULL | Nome da categoria |
| `slug` | VARCHAR(255) | UNIQUE | Slug amigável |
| `is_adult` | BOOLEAN | DEFAULT false | Categoria +18 |
| `created_at` | TIMESTAMP | — | Data de criação |
| `updated_at` | TIMESTAMP | — | Data de atualização |

---

## `category_product`
**Pivot table** | **Propósito:** Relacionamento N:N entre categorias e produtos.

| Coluna | Tipo | Constraints | Descrição |
| :--- | :--- | :--- | :--- |
| `product_id` | BIGINT FK | `products.id` CASCADE | Produto |
| `category_id` | BIGINT FK | `categories.id` CASCADE | Categoria |

---

## `carts` / `cart_items`
**Propósito:** Carrinho de compras temporário antes da conversão em pedido.

| Coluna | Tipo | Constraints | Descrição |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT PK | auto | Identificador único |
| `client_id` | BIGINT FK | `clients.id` CASCADE, INDEX | Cliente |
| `product_id` | BIGINT FK | `products.id` CASCADE | Produto |
| `created_at` | TIMESTAMP | — | Data de criação |
| `updated_at` | TIMESTAMP | — | Data de atualização |

> **Unique:** `(client_id, product_id)` — um produto por cliente no carrinho.

---

## `orders`
**Model:** `Order` | **Propósito:** Pedidos realizados com snapshot imutável dos dados no momento da compra.

| Coluna | Tipo | Constraints | Descrição |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT PK | auto | Identificador único |
| `tenant_id` | BIGINT FK | `tenants.id` CASCADE | Loja vendedora |
| `client_id` | BIGINT FK | `clients.id` CASCADE | Cliente comprador |
| `order_date` | DATE | NOT NULL, INDEX | Data do pedido |
| `delivery_date` | DATE | NOT NULL | Previsão de entrega |
| `stripe_session_id` | VARCHAR(255) | NULLABLE, UNIQUE | ID da sessão Stripe |
| `amount_total` | DECIMAL(12,2) | NULLABLE | Valor total da compra |
| `currency` | VARCHAR(3) | DEFAULT 'brl' | Moeda |
| `status` | VARCHAR(255) | DEFAULT 'pending' | pending, in_progress, delivered, cancelled |
| `delivered_at` | TIMESTAMP | NULLABLE, INDEX | Data de entrega efetiva |
| `snapshot_address` | TEXT | NULLABLE | Endereço de entrega (snapshot imutável) |
| `snapshot_product_name` | VARCHAR(500) | NULLABLE | Nome do produto no momento da compra |
| `snapshot_product_price` | DECIMAL(12,2) | NULLABLE | Preço do produto no momento da compra |
| `created_at` | TIMESTAMP | — | Data de criação |
| `updated_at` | TIMESTAMP | — | Data de atualização |

> **Índices:** `(tenant_id, client_id)`, `order_date`.

---

## `order_items`
**Model:** `OrderItem` | **Propósito:** Snapshots imutáveis de cada item do pedido. product_id é FK nullable (sobrevive a soft deletes).

| Coluna | Tipo | Constraints | Descrição |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT PK | auto | Identificador único |
| `order_id` | BIGINT FK | `orders.id` CASCADE, INDEX | Pedido |
| `product_id` | BIGINT FK | `products.id` NULL, INDEX | Produto original (nullable) |
| `snapshot_product_name` | VARCHAR(500) | NOT NULL | Nome do produto no momento da compra |
| `snapshot_product_price` | DECIMAL(12,2) | NOT NULL | Preço no momento da compra |
| `created_at` | TIMESTAMP | — | Data de criação |
| `updated_at` | TIMESTAMP | — | Data de atualização |

---

## `order_labels`
**Model:** `OrderLabel` | **Propósito:** Etiquetas de envio com snapshot imutável do destinatário. Uso exclusivo de staff.

| Coluna | Tipo | Constraints | Descrição |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT PK | auto | Identificador único |
| `order_id` | BIGINT FK | `orders.id` CASCADE, INDEX | Pedido |
| `carrier_id` | BIGINT FK | `carriers.id` NULL | Transportadora |
| `tenant_id` | BIGINT FK | `tenants.id` CASCADE | Loja |
| `tracking_code` | VARCHAR(255) | NULLABLE | Código de rastreio |
| `label_url` | VARCHAR(255) | NULLABLE | URL do PDF/HTML da etiqueta |
| `status` | VARCHAR(255) | DEFAULT 'pending' | pending, generated, shipped, delivered |
| `recipient_name` | VARCHAR(255) | NOT NULL | Nome do destinatário (display_name) |
| `recipient_address` | TEXT | NOT NULL | Endereço completo de entrega |
| `created_at` | TIMESTAMP | — | Data de criação |
| `updated_at` | TIMESTAMP | — | Data de atualização |

> **Índices:** `tenant_id`, `(tenant_id, status)`, `order_id`.

---

## `carriers`
**Model:** `Carrier` | **Propósito:** Transportadoras cadastradas (B2B). Estrutura similar a tenants com dados LGPD criptografados. Autenticação delegada à tabela `users` (access_level 5/6).

| Coluna | Tipo | Constraints | Descrição |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT PK | auto | Identificador único |
| `user_id` | BIGINT FK | `users.id` NULL, INDEX | Usuário CARRIER_1 vinculado |
| `company_name_encrypted` | TEXT | NULLABLE | Razão social criptografada |
| `company_name_hash` | VARCHAR(64) | NULLABLE, INDEX | SHA-256 da razão social |
| `fantasy_name` | VARCHAR(255) | NOT NULL, INDEX | Nome fantasia |
| `slug` | VARCHAR(255) | NULLABLE, UNIQUE, INDEX | Slug amigável |
| `document_type` | VARCHAR(4) | DEFAULT 'cnpj', INDEX | cnpj ou cpf |
| `document_encrypted` | TEXT | NOT NULL | Documento criptografado |
| `document_hash` | VARCHAR(64) | NOT NULL, INDEX | SHA-256 do documento |
| `address_encrypted` | TEXT | NULLABLE | Endereço criptografado |
| `phone_encrypted` | TEXT | NULLABLE | Telefone criptografado |
| `logo_path` | VARCHAR(500) | NULLABLE | Logo da transportadora |
| `website_url` | VARCHAR(500) | NULLABLE | Site |
| `is_profile_complete` | BOOLEAN | DEFAULT false | Cadastro completo |
| `rating_average` | DECIMAL(3,2) | DEFAULT 0, INDEX | Média de avaliações |
| `rating_count` | INTEGER | DEFAULT 0 | Quantidade de avaliações |
| `is_active` | BOOLEAN | DEFAULT true, INDEX | Ativa |
| `created_at` | TIMESTAMP | — | Data de criação |
| `updated_at` | TIMESTAMP | — | Data de atualização |

---

## `carrier_tenant_agreements`
**Model:** `CarrierTenantAgreement` | **Propósito:** Contratos entre tenants (vendedores) e carriers (transportadoras).

| Coluna | Tipo | Constraints | Descrição |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT PK | auto | Identificador único |
| `tenant_id` | BIGINT FK | `tenants.id` CASCADE | Vendedor |
| `carrier_id` | BIGINT FK | `carriers.id` CASCADE | Transportadora |
| `status` | VARCHAR(20) | DEFAULT 'pending_carrier', INDEX | pending_carrier, pending_tenant, active, rejected |
| `blocked_by` | VARCHAR(20) | NULLABLE | Quem bloqueou (carrier/tenant) |
| `created_at` | TIMESTAMP | — | Data de criação |
| `updated_at` | TIMESTAMP | — | Data de atualização |

> **Unique:** `(tenant_id, carrier_id)`.

---

## `carrier_coverage_ranges`
**Model:** `CarrierCoverageRange` | **Propósito:** Faixas de CEP de cobertura de entrega por transportadora.

| Coluna | Tipo | Constraints | Descrição |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT PK | auto | Identificador único |
| `carrier_id` | BIGINT FK | `carriers.id` CASCADE, INDEX | Transportadora |
| `title` | VARCHAR(255) | NOT NULL | Nome da região |
| `cep_start` | VARCHAR(8) | NOT NULL, INDEX (com cep_end) | CEP inicial |
| `cep_end` | VARCHAR(8) | NOT NULL, INDEX (com cep_start) | CEP final |
| `created_at` | TIMESTAMP | — | Data de criação |
| `updated_at` | TIMESTAMP | — | Data de atualização |

---

## `carrier_banned_tenants`
**Model:** `CarrierBannedTenant` | **Propósito:** Blacklist de tenants banidos por transportadoras.

| Coluna | Tipo | Constraints | Descrição |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT PK | auto | Identificador único |
| `carrier_id` | BIGINT FK | `carriers.id` CASCADE, INDEX | Transportadora |
| `tenant_id` | BIGINT FK | `tenants.id` CASCADE, INDEX | Tenant banido |
| `created_at` | TIMESTAMP | — | Data de criação |
| `updated_at` | TIMESTAMP | — | Data de atualização |

> **Unique:** `(carrier_id, tenant_id)`.

---

## `carrier_state`
**[LEGADO]** | Pivot transportadora ↔ estados atendidos. Substituída por `carrier_coverage_ranges`.

| Coluna | Tipo | Constraints | Descrição |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT PK | auto | Identificador único |
| `carrier_id` | BIGINT FK | `carriers.id` CASCADE | Transportadora |
| `state_id` | BIGINT FK | `states.id` CASCADE | Estado |

> **Unique:** `(carrier_id, state_id)`.

---

## `vendor_carrier`
**[LEGADO]** | Pivot vinculando usuário staff (vendor) às transportadoras. Substituído por `carrier_tenant_agreements`.

| Coluna | Tipo | Constraints | Descrição |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT PK | auto | Identificador único |
| `user_id` | BIGINT FK | `users.id` CASCADE | Staff |
| `carrier_id` | BIGINT FK | `carriers.id` CASCADE | Transportadora |
| `status` | VARCHAR(20) | DEFAULT 'pending', INDEX | pending, approved, rejected |
| `notes` | TEXT | NULLABLE | Observações |
| `responded_at` | TIMESTAMP | NULLABLE | Data da resposta |
| `created_at` | TIMESTAMP | — | Data de criação |
| `updated_at` | TIMESTAMP | — | Data de atualização |

> **Unique:** `(user_id, carrier_id)`.

---

## `freight_contracts`
**Model:** `FreightContract` | **Propósito:** Contratos de frete vinculando transportadora a um pedido.

| Coluna | Tipo | Constraints | Descrição |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT PK | auto | Identificador único |
| `tenant_id` | BIGINT FK | `tenants.id` CASCADE, INDEX | Loja |
| `carrier_id` | BIGINT FK | `carriers.id` CASCADE, INDEX | Transportadora |
| `order_id` | BIGINT FK | `orders.id` NULL, INDEX | Pedido |
| `pickup_location` | VARCHAR(500) | NOT NULL | Local de coleta |
| `delivery_location` | VARCHAR(500) | NOT NULL | Local de entrega |
| `cargo_description` | VARCHAR(500) | NOT NULL | Descrição da carga |
| `pickup_date` | DATE | NOT NULL, INDEX | Data de coleta |
| `estimated_delivery_date` | DATE | NOT NULL, INDEX | Previsão de entrega |
| `delivered_date` | DATE | NULLABLE | Data de entrega real |
| `freight_paid` | BOOLEAN | DEFAULT false | Frete pago |
| `freight_value` | DECIMAL(12,2) | DEFAULT 0 | Valor do frete |
| `status` | VARCHAR(20) | DEFAULT 'pending', INDEX | pending, in_transit, delivered, cancelled |
| `notes` | TEXT | NULLABLE | Observações |
| `created_at` | TIMESTAMP | — | Data de criação |
| `updated_at` | TIMESTAMP | — | Data de atualização |

---

## `return_requests`
**Model:** `ReturnRequest` | **Propósito:** Solicitações de devolução de pedidos (direito de arrependimento).

| Coluna | Tipo | Constraints | Descrição |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT PK | auto | Identificador único |
| `order_id` | BIGINT FK | `orders.id` CASCADE, UNIQUE | Pedido |
| `client_id` | BIGINT FK | `clients.id` CASCADE, INDEX | Cliente |
| `status` | VARCHAR(20) | DEFAULT 'requested', INDEX | requested, shipped_back, approved, rejected |
| `reason_encrypted` | TEXT | NULLABLE | Motivo criptografado |
| `reason_hash` | VARCHAR(64) | NULLABLE | SHA-256 do motivo |
| `requested_at` | TIMESTAMP | NULLABLE | Data da solicitação |
| `shipped_back_at` | TIMESTAMP | NULLABLE | Data de devolução |
| `approved_at` | TIMESTAMP | NULLABLE | Data de aprovação |
| `rejected_at` | TIMESTAMP | NULLABLE | Data de rejeição |
| `rejection_reason_encrypted` | TEXT | NULLABLE | Motivo da rejeição criptografado |
| `created_at` | TIMESTAMP | — | Data de criação |
| `updated_at` | TIMESTAMP | — | Data de atualização |

> **Unique:** `order_id` — um pedido só pode ter uma devolução ativa.

---

## `coupons`
**Model:** `Coupon` | **Propósito:** Cupons de desconto (globais ou por tenant/categoria).

| Coluna | Tipo | Constraints | Descrição |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT PK | auto | Identificador único |
| `tenant_id` | BIGINT FK | `tenants.id` NULL, INDEX | Loja (null = global) |
| `category_id` | BIGINT FK | `categories.id` NULL | Categoria específica |
| `code` | VARCHAR(50) | UNIQUE, INDEX | Código do cupom |
| `type` | VARCHAR(20) | DEFAULT 'percentage' | percentage ou fixed |
| `value` | DECIMAL(12,2) | NOT NULL | Valor (percentual ou fixo) |
| `min_order_value` | DECIMAL(12,2) | NULLABLE | Valor mínimo do pedido |
| `max_uses` | INTEGER | NULLABLE | Limite de usos |
| `used_count` | INTEGER | DEFAULT 0 | Usos consumidos |
| `starts_at` | TIMESTAMP | NULLABLE | Data de início |
| `expires_at` | TIMESTAMP | NULLABLE | Data de expiração |
| `is_active` | BOOLEAN | DEFAULT true, INDEX | Cupom ativo |
| `created_at` | TIMESTAMP | — | Data de criação |
| `updated_at` | TIMESTAMP | — | Data de atualização |

---

## `reviews`
**Model:** `Review` | **Propósito:** Avaliações de produtos. Dispara job `RecalculateTenantRating`.

| Coluna | Tipo | Constraints | Descrição |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT PK | auto | Identificador único |
| `tenant_id` | BIGINT FK | `tenants.id` CASCADE | Loja avaliada |
| `client_id` | BIGINT FK | `clients.id` CASCADE | Cliente avaliador |
| `order_id` | BIGINT FK | `orders.id` CASCADE | Pedido avaliado |
| `rating` | INTEGER | NOT NULL | Nota (1-5) |
| `comment` | TEXT | NULLABLE | Comentário |
| `created_at` | TIMESTAMP | — | Data de criação |
| `updated_at` | TIMESTAMP | — | Data de atualização |

---

## `threads`
**Model:** `Thread` | **Propósito:** Conversas entre staff e clientes sobre pedidos.

| Coluna | Tipo | Constraints | Descrição |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT PK | auto | Identificador único |
| `tenant_id` | BIGINT FK | `tenants.id` NULL | Loja |
| `client_id` | BIGINT FK | `clients.id` CASCADE, INDEX | Cliente |
| `order_id` | BIGINT FK | `orders.id` NULL | Pedido vinculado |
| `status` | VARCHAR(255) | DEFAULT 'open' | open, closed, archived |
| `created_at` | TIMESTAMP | — | Data de criação |
| `updated_at` | TIMESTAMP | — | Data de atualização |

---

## `messages`
**Model:** `Message` | **Propósito:** Mensagens dentro de uma thread. Conteúdo criptografado em repouso. Validação: NoContactDataRule + NoOffensiveContentRule.

| Coluna | Tipo | Constraints | Descrição |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT PK | auto | Identificador único |
| `thread_id` | BIGINT FK | `threads.id` CASCADE, INDEX | Thread |
| `sender_type` | VARCHAR(255) | NOT NULL | client ou staff |
| `sender_id` | BIGINT | NOT NULL, INDEX (com sender_type) | ID do remetente |
| `content_encrypted` | TEXT | NOT NULL | Conteúdo criptografado (AES-256-CBC) |
| `created_at` | TIMESTAMP | — | Data de criação |
| `updated_at` | TIMESTAMP | — | Data de atualização |

> **Índice composto:** `(sender_type, sender_id)`.

---

## `disputes`
**Model:** `Dispute` | **Propósito:** Disputas/contestações abertas por clientes contra pedidos. Conteúdo criptografado.

| Coluna | Tipo | Constraints | Descrição |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT PK | auto | Identificador único |
| `tenant_id` | BIGINT FK | `tenants.id` CASCADE, INDEX | Loja |
| `client_id` | BIGINT FK | `clients.id` CASCADE, INDEX | Cliente reclamante |
| `order_id` | BIGINT FK | `orders.id` NULL | Pedido |
| `reason` | VARCHAR(255) | NOT NULL, INDEX | fraud, fake_product, offensive, not_delivered |
| `description_encrypted` | TEXT | NOT NULL | Descrição criptografada |
| `status` | VARCHAR(255) | DEFAULT 'pending', INDEX | pending, investigating, resolved, dismissed |
| `admin_id` | BIGINT FK | `users.id` NULL, INDEX | Admin responsável |
| `created_at` | TIMESTAMP | — | Data de criação |
| `updated_at` | TIMESTAMP | — | Data de atualização |

---

## `accounts_payable`
**Model:** `AccountPayable` | **Propósito:** Contas a pagar do tenant (fornecedores, insumos).

| Coluna | Tipo | Constraints | Descrição |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT PK | auto | Identificador único |
| `tenant_id` | BIGINT FK | `tenants.id` CASCADE, INDEX | Loja |
| `supplier_id` | BIGINT FK | `suppliers.id` CASCADE, INDEX | Fornecedor |
| `input_id` | BIGINT FK | `inputs.id` NULL, INDEX | Insumo vinculado |
| `description` | VARCHAR(255) | NOT NULL | Descrição da conta |
| `purchase_date` | DATE | NOT NULL | Data da compra |
| `due_date` | DATE | NOT NULL, INDEX | Data de vencimento |
| `amount` | DECIMAL(12,2) | NOT NULL | Valor total |
| `paid_amount` | DECIMAL(12,2) | DEFAULT 0 | Valor pago |
| `status` | VARCHAR(20) | DEFAULT 'pending', INDEX | pending, paid, overdue, cancelled |
| `notes` | TEXT | NULLABLE | Observações |
| `paid_at` | TIMESTAMP | NULLABLE | Data de pagamento |
| `created_at` | TIMESTAMP | — | Data de criação |
| `updated_at` | TIMESTAMP | — | Data de atualização |

---

## `bank_details`
**Model:** `BankDetail` | **Propósito:** Dados bancários de tenants e carriers com consentimento LGPD.

| Coluna | Tipo | Constraints | Descrição |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT PK | auto | Identificador único |
| `tenant_id` | BIGINT FK | `tenants.id` CASCADE, INDEX | Tenant (nullable se carrier) |
| `carrier_id` | BIGINT FK | `carriers.id` NULL | Carrier (nullable se tenant) |
| `bank_name` | VARCHAR(100) | NULLABLE | Nome do banco (texto puro) |
| `routing_number_encrypted` | TEXT | NULLABLE | Agência criptografada |
| `account_number_encrypted` | TEXT | NULLABLE | Conta criptografada |
| `bank_pix_key_encrypted` | TEXT | NULLABLE | Chave PIX criptografada |
| `account_holder_name` | VARCHAR(255) | NULLABLE | Nome do titular |
| `account_holder_doc_encrypted` | TEXT | NULLABLE | Documento do titular criptografado |
| `account_holder_doc_hash` | VARCHAR(64) | NULLABLE, INDEX | SHA-256 do documento |
| `consented` | BOOLEAN | DEFAULT false | Consentimento LGPD |
| `consented_at` | TIMESTAMP | NULLABLE | Data do consentimento |
| `consent_ip` | VARCHAR(45) | NULLABLE | IP do consentimento |
| `consent_term_version` | VARCHAR(20) | DEFAULT '1.0' | Versão dos termos |
| `pending_token` | VARCHAR(64) | NULLABLE | Token de confirmação pendente |
| `pending_data` | TEXT | NULLABLE | Dados pendentes de confirmação |
| `pending_at` | TIMESTAMP | NULLABLE | Data da pendência |
| `created_at` | TIMESTAMP | — | Data de criação |
| `updated_at` | TIMESTAMP | — | Data de atualização |

---

## `states`
**Model:** `State` | **Propósito:** Estados brasileiros com faixas de CEP oficiais dos Correios.

| Coluna | Tipo | Constraints | Descrição |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT PK | auto | Identificador único |
| `uf` | VARCHAR(2) | NOT NULL, INDEX | Sigla do estado |
| `name` | VARCHAR(100) | NOT NULL | Nome do estado |
| `cep_start` | VARCHAR(9) | NOT NULL, INDEX (com cep_end) | CEP inicial da faixa |
| `cep_end` | VARCHAR(9) | NOT NULL, INDEX (com cep_start) | CEP final da faixa |
| `created_at` | TIMESTAMP | — | Data de criação |
| `updated_at` | TIMESTAMP | — | Data de atualização |

---

## `seo_settings`
**Model:** `SeoSetting` | **Propósito:** Configurações de SEO globais e por entidade pública (meta tags, schema markup).

| Coluna | Tipo | Constraints | Descrição |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT PK | auto | Identificador único |
| `key` | VARCHAR(255) | UNIQUE | Chave da configuração |
| `value` | TEXT | NULLABLE | Valor da configuração |
| `group` | VARCHAR(255) | DEFAULT 'general' | Agrupamento (general, social, product) |
| `created_at` | TIMESTAMP | — | Data de criação |
| `updated_at` | TIMESTAMP | — | Data de atualização |

---

## `legal_documents`
**Model:** `LegalDocument` | **Propósito:** Políticas de privacidade e termos de uso versionados com período de carência para aceite.

| Coluna | Tipo | Constraints | Descrição |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT PK | auto | Identificador único |
| `type` | VARCHAR(255) | NOT NULL | terms_of_service, privacy_policy |
| `title` | VARCHAR(255) | NOT NULL | Título do documento |
| `content_html` | TEXT | NOT NULL | Conteúdo em HTML |
| `version` | INTEGER | NOT NULL | Versão do documento |
| `grace_period_days` | INTEGER | DEFAULT 7 | Prazo de carência (dias) para aceite obrigatório |
| `published_at` | TIMESTAMP | NULLABLE | Data de publicação |
| `created_at` | TIMESTAMP | — | Data de criação |
| `updated_at` | TIMESTAMP | — | Data de atualização |

---

## `visitor_legal_consents`
**Model:** `VisitorLegalConsent` | **Propósito:** Registro de consentimentos LGPD com IP hash + encrypted.

| Coluna | Tipo | Constraints | Descrição |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT PK | auto | Identificador único |
| `legal_document_id` | BIGINT FK | `legal_documents.id` CASCADE | Documento aceito |
| `client_id` | BIGINT FK | `clients.id` NULL, INDEX | Cliente (se autenticado) |
| `user_id` | BIGINT FK | `users.id` NULL, INDEX | Usuário staff (se autenticado) |
| `ip_hash` | VARCHAR(64) | NOT NULL, INDEX | SHA-256 do IP |
| `ip_encrypted` | TEXT | NOT NULL | IP criptografado |
| `user_agent` | TEXT | NULLABLE | User agent do navegador |
| `status` | VARCHAR(20) | DEFAULT 'accepted', INDEX | accepted, revoked |
| `created_at` | TIMESTAMP | — | Data do consentimento |
| `updated_at` | TIMESTAMP | — | Data de atualização |

---

## `security_logs`
**Model:** `SecurityLog` | **Propósito:** Auditoria de violações de conteúdo (Google Cloud Vision SafeSearch).

| Coluna | Tipo | Constraints | Descrição |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT PK | auto | Identificador único |
| `tenant_id` | BIGINT FK | `tenants.id` NULL | Tenant |
| `user_id` | BIGINT FK | `users.id` NULL | Usuário que enviou conteúdo |
| `attempted_at` | TIMESTAMP | NOT NULL, INDEX | Data da tentativa |
| `violation_type` | VARCHAR(255) | NOT NULL, INDEX | ADULT, VIOLENCE, RACY, MEDICAL |
| `raw_response` | JSON | NULLABLE | Resposta completa da API Google Vision |
| `created_at` | TIMESTAMP | — | Data de criação |
| `updated_at` | TIMESTAMP | — | Data de atualização |

---

## `user_status_logs`
**Model:** `UserStatusLog` | **Propósito:** Histórico de bloqueios/desbloqueios de usuários (auditoria administrativa).

| Coluna | Tipo | Constraints | Descrição |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT PK | auto | Identificador único |
| `user_id` | BIGINT FK | `users.id` CASCADE, INDEX | Usuário afetado |
| `author_id` | BIGINT FK | `users.id` CASCADE, INDEX | Admin que executou a ação |
| `action` | VARCHAR(20) | NOT NULL, INDEX | blocked, unblocked |
| `reason` | TEXT | NOT NULL | Motivo da ação |
| `created_at` | TIMESTAMP | INDEX | Data da ação |
| `updated_at` | TIMESTAMP | — | Data de atualização |

---

## Tabelas de Infraestrutura (Laravel)

| Tabela | Propósito |
| :--- | :--- |
| `sessions` | Sessões de usuário (driver Redis em produção) |
| `cache` / `cache_locks` | Cache atômico do Laravel (driver Redis) |
| `jobs` / `job_batches` / `failed_jobs` | Fila de jobs (QUEUE_CONNECTION=redis) |
| `password_reset_tokens` | Tokens de recuperação de senha (Fortify) |
| `migrations` | Controle de versionamento do schema |

---

## Infraestrutura Redis (não-SQL)

| Chave | Tipo | Propósito |
| :--- | :--- | :--- |
| `notifications_queue` | List (FIFO) | Fila compartilhada Laravel (produtor RPUSH) ↔ Go Notification Service (consumidor BLPOP) |