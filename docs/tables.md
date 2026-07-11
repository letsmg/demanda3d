# Dicionário de Dados — Demanda3D

> Última atualização: 2026-07-11  
> Base: migrations presentes em `database/migrations/`

| Tabela | Model | Propósito |
| :--- | :--- | :--- |
| `users` | `User` | Autenticação do sistema (Fortify). Armazena e-mail (texto puro), first_name/last_name (hash + encrypted), display_name, access_level (enum), data_nascimento e is_active. Relacionamento 1:1 com `tenants`. |
| `tenants` | `Tenant` | Dados empresariais de cada conta multi-tenant: fantasy_name, slug, cnpj_encrypted, cnpj_hash, razão social, endereço, phone, logo, rating_average/rating_count. Isolamento via TenantScope. |
| `clients` | `Client` | Clientes finais (consumidores). Armazena first_name, last_name, email (hash+encrypted), display_name, phone, cep, endereço. Não possui relação direta com users — a conexão ocorre via orders. |
| `suppliers` | `Supplier` | Fornecedores de insumos: nome, contato, cnpj_encrypted/cnpj_hash. |
| `inputs` | `Input` | Insumos de impressão 3D: nome, tipo (filament, resin, other), quantidade, preço, tenant_id. |
| `products` | `Product` | Produtos impressos anunciados na plataforma: título, slug, descrição, preço, status, tenant_id, categoria adulta. |
| `product_images` | `ProductImage` | Imagens vinculadas a produtos: path original, path otimizada (WebP), thumbnail_path, ordem. |
| `categories` | `Category` | Categorias de produtos: nome, slug, ícone, category_type (product/adult). |
| `category_product` | — | Tabela pivot N:N entre categories e products. |
| `carts` / `cart_items` | `CartItem` | Carrinho de compras temporário antes da conversão em pedido. Vinculado a client_id e product_id. |
| `orders` | `Order` | Pedidos realizados: tenant_id, client_id, supplier_id, status, valores (numeric 12,2), tracking. |
| `carriers` | `Carrier` | Transportadoras cadastradas para cálculo de frete e tracking. |
| `freight_contracts` | `FreightContract` | Contratos de frete vinculando transportadora (carrier_id) a um pedido (order_id). |
| `carrier_state` | — | Pivot transportadora ↔ estados atendidos. |
| `vendor_carrier` | `VendorCarrier` | Pivot vinculando usuário staff (vendor) às transportadoras que gerencia. |
| `return_requests` | `ReturnRequest` | Solicitações de devolução de pedidos: motivo, status, tenant_id. |
| `coupons` | `Coupon` | Cupons de desconto: código, valor, validade, tenant_id. |
| `reviews` | `Review` | Avaliações de produtos/pedidos: rating, comentário, tenant_id. Dispara job RecalculateTenantRating. |
| `threads` | `Thread` | Conversas entre staff e clientes sobre pedidos: tenant_id, client_id, order_id, status (open/closed/archived). |
| `messages` | `Message` | Mensagens dentro de uma thread: thread_id, sender_type (staff/client), sender_id, content_encrypted (criptografado em repouso). Validação com NoContactDataRule + NoOffensiveContentRule. |
| `disputes` | `Dispute` | Disputas/contestações abertas por clientes: reporter_id, order_id, reason (fraud, fake_product, offensive, not_delivered), description_encrypted, status, admin_id. Validação com NoContactDataRule + NoOffensiveContentRule. |
| `accounts_payable` | `AccountPayable` | Contas a pagar do tenant: fornecedor, valor, vencimento, status. |
| `states` | `State` | Estados brasileiros (UF, nome) para uso em cálculos de frete e cadastros. |
| `seo_settings` | `SeoSetting` | Configurações de SEO por entidade pública: meta_title, meta_description, h1_text, schema_markup. |
| `legal_documents` | `LegalDocument` | Políticas de privacidade e termos de uso versionados: type, content, version, published_at. |
| `visitor_legal_consents` | `VisitorLegalConsent` | Registro de consentimentos LGPD: ip_hash, ip_encrypted, legal_document_id, user_agent, geolocation aproximada. |
| `security_logs` | `SecurityLog` | Log de auditoria de ações críticas: tenant_id, user_id, action, ip_address, user_agent, metadata (JSON). |
| `sessions` | — | Sessões de usuário gerenciadas pelo Laravel (driver Redis em produção). |
| `cache` / `cache_locks` | — | Cache atômico do Laravel (driver Redis). |
| `jobs` / `job_batches` / `failed_jobs` | — | Fila de jobs (QUEUE_CONNECTION=redis em dev local). |
| `password_reset_tokens` | — | Tokens de recuperação de senha (Fortify). |
| `migrations` | — | Controle de versionamento do schema (Laravel). |
| `notifications_queue` (Redis List) | — | Fila FIFO compartilhada entre Laravel (produtor, via `SendNotification` Job e RPUSH) e Go Notification Service (consumidor, via BLPOP). Não é uma tabela SQL — é uma lista atômica no Redis. |
