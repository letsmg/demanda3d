# [Português](#português) | [English](#english)

<a id="português"></a>
# Demanda3D

Sistema SaaS para gestão operacional, financeira e produtiva de negócios de impressão 3D.

## Sobre o Projeto

O Demanda3D está sendo desenvolvido como uma plataforma completa para empreendedores da área de impressão 3D, permitindo o controle de clientes, fornecedores, filamentos, custos de produção, consumo de energia, perdas de material, estoque, produtos, pedidos e assinaturas recorrentes.

O sistema segue uma arquitetura multi-tenant com isolamento total de dados entre clientes.

## Arquitetura e Infraestrutura

Para assegurar performance e prontidão para escala horizontal, o sistema utiliza **Kubernetes** para orquestração de containers, garantindo resiliência e alta disponibilidade.

### Persistência e Performance
* **PostgreSQL:** Banco de dados relacional com estratégia de replicação de dados, preparado para suportar crescimento horizontal e alta demanda de escrita/leitura.
* **Redis:** Camada de alta performance utilizada para cache e gerenciamento de filas em tarefas recorrentes de pesquisa de dados, otimizando o tempo de resposta.

---

## Stack Tecnológica

### Backend
- **Laravel** — Framework PHP para desenvolvimento web
- **PostgreSQL** — Banco de dados relacional com replicação
- **Redis** — Cache e filas para processamento recorrente
- **PHP 8.3+** — Linguagem de programação

### Frontend
- **Vue 3** — Framework JavaScript progressivo
- **TypeScript** — Tipagem estática
- **Inertia.js** — Conector monolítico entre backend e frontend
- **Tailwind CSS** — Estilização utilitária

### Infraestrutura
- **Kubernetes** | **Docker** | **Linux** | **Git** | **CI/CD**

### Pagamentos
- Stripe | PIX | Cartão de Crédito | Boleto Bancário

---

## Segurança e LGPD

Este projeto adota padrões rigorosos de segurança e criptografia em conformidade com a **Lei Geral de Proteção de Dados (LGPD)**:

- **Estrutura de Paridade:** Todos os dados pessoais (nomes, e-mails, documentos, telefones e endereços) são armazenados com  **hash determinístico (`sha256`)** para buscas e **criptografia em repouso (`Crypt::encryptString`)** para exibição segura
- **Senhas:** Hash Argon2id com configuração otimizada (Memory cost: 64MB, Time cost: 4, Threads: 2)
- **Sanitização:** Entrada e saída de dados sanitizadas para prevenir XSS e SQL Injection
- **Mass Assignment:** Proteção via `$fillable` em todos os models
- **Rate Limiting:** Limites de requisições em endpoints críticos
- **Multi-Tenant:** Isolamento completo por `tenant_id` com Global Scopes

---

## Instalação / Setup

```bash
# Clone o repositório
git clone [https://github.com/letsmg/demanda3d.git](https://github.com/letsmg/demanda3d.git)
cd demanda3d

# Instale as dependências do backend
composer install

# Instale as dependências do frontend
npm install

# Configure o ambiente
cp .env.example .env
php artisan key:generate

# Execute as migrações e seeders
php artisan migrate --seed

# Compile os assets
npm run build

# Inicie o servidor de desenvolvimento
php artisan serve