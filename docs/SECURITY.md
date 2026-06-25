DESENVOLVIMENTO & INFRAESTRUTURA
Este documento detalha o ambiente de desenvolvimento, a arquitetura técnica e os procedimentos operacionais para o projeto Demanda3D.

1. QUICK START
Passo 1: composer install && npm install
Passo 2: cp .env.example .env
Passo 3: php artisan key:generate
Passo 4: docker compose up -d
Passo 5: php artisan migrate

2. ARQUITETURA DE INFRAESTRUTURA
O projeto utiliza um ambiente conteinerizado para garantir isolamento:

Database: PostgreSQL 16 (Master/Replica).

Replicacao: Streaming Replication (Master para Escrita/Leitura, Replica para Leitura).

Seguranca: Comunicacao entre servicos via rede interna Docker.

Cache: Redis (persistencia de estado).

3. SEGURANCA EM NIVEL DE APLICACAO
O sistema foi desenhado seguindo principios de Security by Design:

Hashing: Argon2id (configuracao otimizada).

Autorizacao: Sistema baseado em Policies (granular) e Middlewares.

Integridade: Validacao de dados rigorosa via FormRequest.

4. ORGANIZACAO DO PROJETO
app/
├── Http/      # Controllers, Middlewares e FormRequests
├── Policies/  # Autorizacao (Client/Order)
├── Services/  # Logica de negocio
├── Enums/     # Contratos de niveis de acesso
└── Models/    # Eloquent Models

5. TESTES AUTOMATIZADOS
Para rodar a suite completa:
php artisan test

Para rodar com relatorio de cobertura:
php artisan test --coverage

6. TROUBLESHOOTING
SQLSTATE[42P01]: Rode "php artisan migrate".

403 Forbidden: Verifique se o access_level atende a Policy.

Lentidao Argon2id: Ajuste ARGON2ID_MEMORY no seu .env local.

7. ROADMAP
[ ] Rate limiting em endpoints de API.

[ ] Logging de auditoria para acoes sensiveis.

[ ] Implementacao de 2FA para contas administrativas.