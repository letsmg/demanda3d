# 🛠️ Development & Infrastructure Guide

Este documento detalha o ambiente de desenvolvimento, a arquitetura técnica e os procedimentos operacionais para o projeto **Demanda3D**.

## 🚀 Quick Start

```bash
# 1. Instalação de dependências
composer install && npm install

# 2. Preparação do ambiente
cp .env.example .env
php artisan key:generate

# 3. Subir infraestrutura Docker (PostgreSQL Master/Replica + Redis)
docker compose up -d

# 4. Executar migrações
php artisan migrate
🏗️ Arquitetura de InfraestruturaO projeto utiliza um ambiente conteinerizado para garantir isolamento e paridade entre ambientes:Database: PostgreSQL 16 (Master/Replica).Replicação: Streaming Replication (Master para Escrita/Leitura, Replica para Leitura).Segurança: Comunicação entre serviços via rede interna Docker.Cache: Redis (persistência de estado).Nota: Para detalhes técnicos sobre replicação e procedimentos de desastre (failover), consulte docs/INFRA.md.🔐 Segurança em Nível de AplicaçãoO sistema foi desenhado seguindo princípios de Security by Design:Hashing: Argon2id (configuração otimizada para resistência a ataques de GPU).Autorização: Sistema baseado em Policies (granular) e Middlewares customizados.Integridade: Validação de dados rigorosa via FormRequest com mensagens de erro contextualizadas.📂 Organização do ProjetoO sistema segue padrões PSR e Design Patterns de Laravel, mantendo uma estrutura desacoplada:Plaintextapp/
├── Http/      # Controllers, Middlewares e FormRequests
├── Policies/  # Autorização (Client/Order)
├── Services/  # Lógica de negócio (Desacoplamento)
├── Enums/     # Contratos de níveis de acesso
└── Models/    # Eloquent Models
🧪 Testes AutomatizadosO projeto prioriza a qualidade através de testes de Feature:Bash# Executar suíte completa
php artisan test

# Executar com relatório de cobertura
php artisan test --coverage
🔧 Troubleshooting & TipsProblemaSoluçãoSQLSTATE[42P01]Rode php artisan migrate.403 ForbiddenVerifique se o access_level atende à Policy.Lentidão Argon2idAjuste ARGON2ID_MEMORY no seu .env local.📋 Roadmap[ ] Rate limiting em endpoints de API.[ ] Logging de auditoria para ações sensíveis.[ ] Implementação de 2FA para contas administrativas.