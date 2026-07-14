# Regras de Negócio — Demanda3D

## PostgreSQL — Master/Replica com Replicação Lógica (DEV)

O ambiente de desenvolvimento possui **dois containers PostgreSQL com replicação lógica nativa** (Publication/Subscription). O `wal_level=logical` é ativado automaticamente no master via script de inicialização.

### Como funciona
- **Master** (porta 5434): `wal_level=logical`, publication `demanda_pub` cobre todas as tabelas
- **Réplica** (porta 5435): subscription `demanda_sub` recebe dados do master em **tempo real**
- Não é necessário `pg_dump` manual — a replicação lógica mantém os bancos sincronizados automaticamente

### Procedimento padrão de desenvolvimento

```bash
# 1. Recriar banco com seed (roda apenas no master)
php artisan migrate:fresh --seed

# 2. Configurar replicação lógica (publication + subscription)
php artisan db:sync-replica

# 3. A replicação permanece ativa — alterações no master refletem na réplica automaticamente
```

### Repetir `db:sync-replica` após `migrate:fresh`

O `migrate:fresh` dropa e recria todas as tabelas, o que invalida a subscription. Após cada `migrate:fresh`, execute `php artisan db:sync-replica` para recriar publication + subscription.

### Estrutura

| Container | Porta | Banco | Função |
| :--- | :--- | :--- | :--- |
| `demanda-psql-dev` | `5434` | `demanda_db_dev` | Master (escrita + publicação) |
| `demanda-psql-rep-dev` | `5435` | `demanda_db_dev_repl` | Réplica (subscriber) |

### Configuração no .env

```env
DB_READ_WRITE_SPLIT=true  # DEV: ativa split de leitura/escrita
DB_HOST=127.0.0.1
DB_PORT=5434
DB_DATABASE=demanda_db_dev
DB_REPLICA_HOST=127.0.0.1
DB_REPLICA_PORT=5435
DB_REPLICA_DATABASE=demanda_db_dev_repl
```

---

## Regras de Exibição de Produtos na Vitrine (Store)

Um produto SÓ deve ser exibido na loja geral (`/store`) ou na página do vendedor (`/tenant/{fantasy_slug}`) se cumprir TODOS os seguintes requisitos cumulativos:

1. **Produto ativo:** `products.is_active = true`
2. **Vendedor ativo:** `tenants.active = true`
3. **Transportadora ativa vinculada:** Deve existir pelo menos uma transportadora (`carriers.is_active = true`) com contrato ativo (`carrier_tenant_agreements.status = 'active'`) vinculada ao tenant do produto.
4. **E-mail do vendedor verificado:** `users.email_verified_at IS NOT NULL` para o usuário dono do tenant.
5. **E-mail da transportadora verificado:** Pelo menos uma das transportadoras ativas vinculadas deve ter `users.email_verified_at IS NOT NULL`.

Se qualquer critério falhar, o produto NÃO é exibido.

**Implementação:** Scope global `scopeAvailableForSale()` no modelo `Product`.

---

## Tratamento de Erros, Logs e Mensagens na Interface

1. **Logs detalhados no backend:**
   - Sempre que uma consulta de produtos retornar **zero resultados**, o sistema deve executar consultas de diagnóstico e registrar `Log::warning()` com o motivo específico (ex: "Vendedor ID X está com e-mail pendente", "Vendedor ID Y não possui transportadora ativa", etc.).
   - Os logs são capturados pelo Promtail e enviados ao Loki/Grafana no ambiente local.

2. **Mensagem genérica no frontend:**
   - Na interface pública da loja, se nenhum produto for carregado, exibir APENAS:
     *"Nenhum produto disponível no momento. Se você é o administrador do sistema, verifique os logs do sistema para mais detalhes."*
   - **Nunca** expor detalhes técnicos, nomes de tabelas, SQL ou mensagens de erro do sistema ao cliente final.

---

## Verificação de E-mail (MustVerifyEmail)

1. **Model `User`:** Implementa `Illuminate\Contracts\Auth\MustVerifyEmail`.
2. **Seeders:** Todos os seeders devem marcar `email_verified_at = now()` para contas de teste.
3. **Registro:** Novas contas de vendedor/transportadora devem validar e-mail antes do login (middleware `verified`).
4. **Alteração de e-mail:** O `UserObserver` detecta mudanças no campo `email` e automaticamente define `email_verified_at = null`, dispara novo e-mail de verificação e força re-autenticação.

---

## Ambiente Local — Grafana + Loki + Promtail

- `GRAFANA_ENABLED=true` no `.env` local habilita os containers via Docker Compose profiles.
- Promtail coleta logs de `storage/logs/laravel.log` e envia para Loki.
- Grafana acessível em `http://localhost:3000` (admin/admin).
- Em produção, manter `GRAFANA_ENABLED=false`.