# Dicionário de Dados — Demanda3D

> Última atualização: 2026-07-13  
> Base: migrations presentes em `database/migrations/`

| Tabela | Model | Propósito |
| :--- | :--- | :--- |
| `users` | `User` | Autenticação do sistema (Fortify). Armazena e-mail (texto puro), first_name/last_name (hash + encrypted), display_name, access_level (UserAccessLevel enum), birth_date e is_active. Níveis: 1=SELLER_1, 2=SELLER_2, 5=CARRIER_1, 6=CARRIER_2, 10=ADMIN, 15=CUSTOMER. Relacionamento 1:1 com `tenants` e 1:1 com `carriers`. |
| `tenants` | `Tenant` | Dados empresariais de cada conta multi-tenant: user_id, legal_name (encrypted + hash), fantasy_name, fantasy_slug, document_type (cnpj/cpf), document (encrypted + hash), phone, address, logo_path, banner_path, rating_average/rating_count. Isolamento via TenantScope. |
| `clients` | `Client` | Clientes finais (consumidores). Armazena first_name, last_name, email (hash+encrypted), display_name, phone, cep, endereço. Não possui relação direta com users — a conexão ocorre via orders. |
| `suppliers` | `Supplier` | Fornecedores de insumos: nome, contato, document_encrypted/document_hash. |
| `inputs` | `Input` | Insumos de impressão 3D: nome, tipo (filament, resin, other), quantidade, preço, tenant_id. |
| `products` | `Product` | Produtos impressos anunciados na plataforma: título, slug, descrição, preço, status, tenant_id, categoria adulta. **SoftDeletes ativado** — exclusões não quebram integridade referencial em order_items. |
| `product_images` | `ProductImage` | Imagens vinculadas a produtos: path original, path otimizada (WebP), thumbnail_path, ordem. |
| `categories` | `Category` | Categorias de produtos: nome, slug, ícone, category_type (product/adult). |
| `category_product` | — | Tabela pivot N:N entre categories e products. |
| `carts` / `cart_items` | `CartItem` | Carrinho de compras temporário antes da conversão em pedido. Vinculado a client_id e product_id. |
| `orders` | `Order` | Pedidos realizados: tenant_id, client_id, order_date, delivery_date, stripe_session_id, amount_total, currency, status. Não armazena product_id/price — os itens estão em `order_items` com snapshots imutáveis. |
| `order_items` | `OrderItem` | Snapshots imutáveis de cada item do pedido: snapshot_product_name, snapshot_product_price, quantity. product_id é FK nullable (sobrevive a soft deletes). |
| `order_labels` | `OrderLabel` | Etiquetas de envio com snapshot imutável do destinatário: recipient_name e recipient_address (JSON). A reimpressão sempre lê destes campos. |
| `carriers` | `Carrier` | Transportadoras cadastradas (B2B). Espelha estrutura de tenants. Relacionamento 1:1 com `users` via `user_id`. Colunas públicas: fantasy_name, slug, document_type, logo_path, website_url, rating_*. Colunas LGPD (encrypted+hash): legal_name, document, address, phone. Autenticação delegada à tabela `users` (access_level=5 CARRIER_1 ou 6 CARRIER_2). |
| `carrier_tenant_agreements` | `CarrierTenantAgreement` | Acordos comerciais entre tenants (vendedores) e carriers (transportadoras). Status: pending_tenant, pending_carrier, active, rejected. Unique (tenant_id, carrier_id). |
| `carrier_coverage_ranges` | `CarrierCoverageRange` | Faixas de CEP de cobertura de entrega por transportadora. Colunas: title, cep_start, cep_end (8 chars). Query: WHERE cep_start <= :cep AND cep_end >= :cep. |
| `carrier_state` | — | **[LEGADO]** Pivot transportadora ↔ estados atendidos. Substituída por `carrier_coverage_ranges`. |
| `freight_contracts` | `FreightContract` | Contratos de frete vinculando transportadora (carrier_id) a um pedido (order_id). |
| `vendor_carrier` | `VendorCarrier` | **[LEGADO]** Pivot vinculando usuário staff (vendor) às transportadoras. Substituído por `carrier_tenant_agreements`. |
| `return_requests` | `ReturnRequest` | Solicitações de devolução de pedidos: motivo, status, tenant_id. |
| `coupons` | `Coupon` | Cupons de desconto: código, valor, validade, tenant_id. |
| `reviews` | `Review` | Avaliações de produtos/pedidos: rating, comentário, tenant_id. Dispara job RecalculateTenantRating. |
| `threads` | `Thread` | Conversas entre staff e clientes sobre pedidos: tenant_id, client_id, order_id, status (open/closed/archived). |
| `messages` | `Message` | Mensagens dentro de uma thread: thread_id, sender_type, sender_id, content_encrypted (criptografado em repouso). Validação com NoContactDataRule + NoOffensiveContentRule. |
| `disputes` | `Dispute` | Disputas/contestações abertas por clientes: reporter_id, order_id, reason, description_encrypted, status, admin_id. |
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