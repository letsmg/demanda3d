# Demanda3D

Sistema SaaS para gestão operacional, financeira e produtiva de negócios de impressão 3D.

## Sobre o Projeto

O Demanda3D está sendo desenvolvido como uma plataforma completa para empreendedores da área de impressão 3D, permitindo o controle de:

* Clientes
* Fornecedores
* Filamentos
* Custos de produção
* Consumo de energia
* Perdas de material
* Estoque
* Produtos
* Pedidos
* Assinaturas recorrentes

O sistema segue uma arquitetura multi-tenant, permitindo que múltiplos clientes utilizem a mesma aplicação com isolamento total de dados.

---

## Tecnologias Utilizadas

### Backend

* Laravel
* PostgreSQL
* Redis
* PHP 8+

### Frontend

* Vue 3
* TypeScript
* Inertia.js
* Tailwind CSS

### Infraestrutura

* Linux
* Docker
* Git
* CI/CD

### Pagamentos

* Stripe
* PIX
* Cartão de Crédito
* Boleto Bancário

---

## Arquitetura

O projeto foi desenvolvido seguindo boas práticas de engenharia de software:

* Clean Architecture
* SOLID
* DRY
* Separation of Concerns
* Multi-Tenant Architecture
* Service Layer Pattern
* Form Requests
* Policies e Authorization

---

## Destaques Técnicos

### Multi-Tenant

* Banco de dados compartilhado
* Isolamento completo entre clientes
* Controle de acesso por tenant

### Segurança

* Hashing Argon2id
* Validações Backend e Frontend
* Sanitização de entrada e saída
* Controle de permissões por perfil

### Performance

* PostgreSQL otimizado
* Redis para cache
* Eager Loading
* Paginação em listagens

### Qualidade

* Testes Unitários
* Feature Tests
* Cobertura de regras de negócio
* Integração contínua

---

## Perfis de Acesso

### Partner

Acesso administrativo global da plataforma.

### Admin

Gerenciamento operacional.

### Customer

Acesso exclusivo aos próprios dados.

---

## Objetivos do Projeto

Além de resolver problemas reais do mercado de impressão 3D, este projeto serve como demonstração prática de competências em:

* Desenvolvimento Full Stack
* Arquitetura de Software
* Sistemas SaaS
* Laravel
* Vue.js
* PostgreSQL
* Integrações de Pagamento
* Multi-Tenancy
* Engenharia de Software

---

## Status

🚧 Em desenvolvimento ativo

Novas funcionalidades estão sendo implementadas continuamente com foco em escalabilidade, segurança e qualidade de código.
