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