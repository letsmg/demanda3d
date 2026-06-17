# 🖨️ Demanda 3D - Sistema de Gerenciamento de Impressão 3D

Sistema web completo para gerenciamento de produção e vendas de impressões 3D. Permite cadastrar clientes, gerenciar pedidos, controlar insumos (filamentos, energia, custos) e acompanhar métricas do negócio através de um dashboard interativo.

## 🚀 Tecnologias Utilizadas

- **Backend:** Laravel 13 + PHP 8.3+
- **Frontend:** Vue 3 + TypeScript + Inertia.js + Tailwind CSS
- **Banco de Dados:** PostgreSQL (recomendado) ou MariaDB
- **Cache/Fila:** Redis
- **Autenticação:** Laravel Fortify + sessão web
- **Hash:** Argon2id (alta segurança)

## 📋 Funcionalidades

- **Dashboard** com métricas em tempo real (totais, receitas, pedidos pendentes)
- **Clientes** - Cadastro completo com CPF/CNPJ, documentos, contatos e endereços
- **Pedidos** - Controle de datas, valores, descrição do serviço e status de entrega
- **Insumos** - Registro de filamentos, custos de compra, energia e purga
- **Níveis de Acesso:**
  - `0` = Staff (funcionário)
  - `1` = Admin (administrador)
  - `9` = Customer (cliente)

## 📦 Pré-requisitos

Antes de começar, você precisa ter instalado em sua máquina:

1. **PHP 8.3 ou superior** (puro, XAMPP, WAMP, Laragon, etc.)
   - Extensões obrigatórias: `pdo_pgsql` ou `pdo_mysql`, `bcmath`, `ctype`, `json`, `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`, `redis`
2. **Composer** (gerenciador de dependências PHP)
3. **Node.js 20+** e **NPM**
4. **PostgreSQL 15+** (recomendado) ou **MariaDB 10+**
5. **Redis** (para cache e filas)

## 🔧 Instalação Passo a Passo

### 1. Clone o repositório

```bash
git clone https://github.com/letsmg/demanda3d.git
cd demanda3d
```

### 2. Configure o arquivo .env

Copie o arquivo de exemplo e edite com suas configurações:

```bash
cp .env.example .env
```

Edite o arquivo `.env` com suas configurações de banco de dados:

```env
DB_CONNECTION=pgsql        # ou "mysql" para MariaDB
DB_HOST=127.0.0.1
DB_PORT=5432               # 3306 para MariaDB
DB_DATABASE=demanda_db
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

SESSION_DRIVER=database
```

### 3. Instale as dependências do PHP

```bash
composer install
```

### 4. Instale as dependências do Node.js

```bash
npm install
```

### 5. Crie o banco de dados

**PostgreSQL:**

```bash
sudo -u postgres psql
CREATE DATABASE demanda_db;
CREATE USER demanda_user WITH PASSWORD 'sua_senha';
GRANT ALL PRIVILEGES ON DATABASE demanda_db TO demanda_user;
\c demanda_db;
GRANT ALL ON SCHEMA public TO demanda_user;
\q
```

**MariaDB:**

```bash
mysql -u root -p
CREATE DATABASE demanda_db;
CREATE USER 'demanda_user'@'localhost' IDENTIFIED BY 'sua_senha';
GRANT ALL PRIVILEGES ON demanda_db.* TO 'demanda_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 6. Gere a chave da aplicação e execute as migrations

```bash
php artisan key:generate
php artisan migrate
```

### 7. (Opcional) Popule o banco com dados de teste

```bash
php artisan db:seed
```

### 8. Compile os assets do frontend

```bash
npm run build
```

### 9. Inicie o servidor de desenvolvimento

```bash
# Terminal 1: Servidor Laravel
php artisan serve

# Terminal 2: Servidor Vite (para hot-reload em desenvolvimento)
npm run dev
```

### 10. Acesse o sistema

Abra o navegador em: **http://localhost:8000**

Para criar um usuário administrador, registre-se normalmente e depois altere o `access_level` no banco:

```sql
UPDATE users SET access_level = 1 WHERE email = 'seu@email.com';
```

## 🏗️ Estrutura do Projeto

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Inertia/          # Controllers para renderização Inertia
│   │   │   ├── ClientController.php
│   │   │   ├── OrderController.php
│   │   │   └── InputController.php
│   │   ├── DashboardController.php
│   │   ├── ClientController.php   # API REST
│   │   ├── OrderController.php    # API REST
│   │   └── InputController.php    # API REST
│   └── Requests/              # Validação de formulários
├── Models/                    # Modelos Eloquent
├── Policies/                  # Regras de autorização
└── Services/                  # Lógica de negócio

resources/
└── js/
    ├── pages/                 # Páginas Vue (Dashboard, Clients, Orders, Inputs)
    ├── components/            # Componentes reutilizáveis
    └── routes/                # Definições de rotas geradas pelo Wayfinder

routes/
├── web.php                   # Rotas web (Inertia)
└── api.php                   # Rotas da API REST
```

## 🔐 Níveis de Acesso

| Código | Nível    | Permissões                                    |
|--------|----------|-----------------------------------------------|
| 0      | Staff    | Visualizar e editar registros                 |
| 1      | Admin    | Acesso total, pode excluir registros          |
| 9      | Customer | Acesso limitado a consultas                   |

## 📄 Licença

Este projeto está sob a licença MIT.