# [Português](#português) | [English](#english)

<a id="português"></a>
# 🚀 Demanda3D
*Sistema SaaS especializado na gestão operacional, financeira e produtiva para negócios de impressão 3D.*

[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=flat&logo=php)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=flat&logo=laravel)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=flat&logo=vuedotjs)](https://vuejs.org)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-336791?style=flat&logo=postgresql)](https://postgresql.org)
[![Docker](https://img.shields.io/badge/Docker-Enabled-2496ED?style=flat&logo=docker)](https://docker.com)
[![Redis](https://img.shields.io/badge/Redis-Cache-DC382D?style=flat&logo=redis)](https://redis.io)
[![RabbitMQ](https://img.shields.io/badge/RabbitMQ-Broker-FF6600?style=flat&logo=rabbitmq)](https://www.rabbitmq.com)
[![Go](https://img.shields.io/badge/Go-1.25-00ADD8?style=flat&logo=go)](https://golang.org)
[![Meilisearch](https://img.shields.io/badge/Meilisearch-Search-FF5722?style=flat&logo=meilisearch)](https://www.meilisearch.com)
[![Grafana](https://img.shields.io/badge/Grafana-Observability-F46800?style=flat&logo=grafana)](https://grafana.com)

---

## 🎯 Sobre o Projeto
O Demanda3D é uma plataforma SaaS robusta desenvolvida para a gestão de ponta a ponta de negócios de impressão 3D. O sistema gerencia desde o controle de insumos e custos de produção até assinaturas recorrentes e entrega final, utilizando uma arquitetura **multi-tenant** com isolamento estrito de dados.

## 🏗️ Diferenciais de Engenharia
* **Infraestrutura Escalável:** Projetado com containers Docker e preparado para orquestração via Kubernetes.
* **Alta Disponibilidade:** Estratégia de replicação PostgreSQL (Master/Replica) para garantir resiliência e performance em leitura.
* **Segurança por Design:** Conformidade com LGPD através de criptografia em repouso (`AES-256`) e hashing de senhas com `Argon2id`.
* **Performance e Cache:** Camada de cache de dados, sessões e filas distribuídas via Redis.
* **Busca Otimizada Híbrida:** Integração com **Meilisearch** para buscas textuais de alta performance e tolerância a erros (fuzzy search), combinada com estratégia de cache em **Redis** com fallback para o PostgreSQL.

## 🛠️ Stack Tecnológica

| Camada | Tecnologias |
| :--- | :--- |
| **Backend** | [![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=flat&logo=laravel&logoColor=white)](https://laravel.com) [![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat&logo=php&logoColor=white)](https://php.net) [![Go](https://img.shields.io/badge/Go-00ADD8?style=flat&logo=go&logoColor=white)](https://golang.org) [![PostgreSQL](https://img.shields.io/badge/PostgreSQL-336791?style=flat&logo=postgresql&logoColor=white)](https://postgresql.org) [![Redis](https://img.shields.io/badge/Redis-DC382D?style=flat&logo=redis&logoColor=white)](https://redis.io) [![RabbitMQ](https://img.shields.io/badge/RabbitMQ-FF6600?style=flat&logo=rabbitmq&logoColor=white)](https://www.rabbitmq.com) |
| **Frontend** | [![Vue.js](https://img.shields.io/badge/Vue.js-4FC08D?style=flat&logo=vuedotjs&logoColor=white)](https://vuejs.org) [![TypeScript](https://img.shields.io/badge/TypeScript-3178C6?style=flat&logo=typescript&logoColor=white)](https://www.typescriptlang.org) Tailwind CSS, Inertia.js |
| **DevOps** | [![Docker](https://img.shields.io/badge/Docker-2496ED?style=flat&logo=docker&logoColor=white)](https://docker.com) Kubernetes, [![Grafana](https://img.shields.io/badge/Grafana-F46800?style=flat&logo=grafana&logoColor=white)](https://grafana.com), Loki, Promtail, [![Meilisearch](https://img.shields.io/badge/Meilisearch-FF5722?style=flat&logo=meilisearch&logoColor=white)](https://www.meilisearch.com) |
| **Payments** | Stripe API, Pix, Crédito/Débito |

---

## 🚀 Setup e Instalação
O projeto conta com um guia detalhado de infraestrutura.
> 📖 **[Clique aqui para acessar o Guia de Setup Detalhado (docs/SETUP.md)](docs/SETUP.md)**

### 📦 Dependências do Sistema (apt)
```bash
sudo apt-get update && sudo apt-get install -y jpegoptim optipng pngquant webp gifsicle
```

### 🖼️ Pipeline de Otimização de Imagens
```bash
# Processa todas as imagens de originais/ → home/
php artisan images:optimize-batch

# Força reprocessamento de todos os arquivos
php artisan images:optimize-batch --force
```

---

<a id="english"></a>
# 🚀 Demanda3D (English Version)
*SaaS platform specialized in operational, financial, and production management for 3D printing businesses.*

## 🎯 About the Project
Demanda3D is a robust SaaS platform built for end-to-end management of 3D printing businesses. The system handles everything from raw material inventory and production costs to recurring subscriptions and final delivery, utilizing a **multi-tenant** architecture with strict data isolation.

## 🏗️ Engineering Highlights
* **Scalable Infrastructure:** Designed with Docker containers and ready for Kubernetes orchestration.
* **High Availability:** PostgreSQL Master/Replica replication strategy to ensure resilience and read performance.
* **Security by Design:** Compliance with data protection standards through encryption-at-rest (`AES-256`) and `Argon2id` password hashing.
* **Performance:** High-performance caching and distributed queues via Redis.
* **Optimized Search:** **Meilisearch** integrated for high-performance full-text search, typo tolerance, and instant autocomplete.

## 🛠️ Tech Stack

| Layer | Technologies |
| :--- | :--- |
| **Backend** | [![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=flat&logo=laravel&logoColor=white)](https://laravel.com) [![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat&logo=php&logoColor=white)](https://php.net) [![Go](https://img.shields.io/badge/Go-00ADD8?style=flat&logo=go&logoColor=white)](https://golang.org) [![PostgreSQL](https://img.shields.io/badge/PostgreSQL-336791?style=flat&logo=postgresql&logoColor=white)](https://postgresql.org) [![Redis](https://img.shields.io/badge/Redis-DC382D?style=flat&logo=redis&logoColor=white)](https://redis.io) [![RabbitMQ](https://img.shields.io/badge/RabbitMQ-FF6600?style=flat&logo=rabbitmq&logoColor=white)](https://www.rabbitmq.com) |
| **Frontend** | [![Vue.js](https://img.shields.io/badge/Vue.js-4FC08D?style=flat&logo=vuedotjs&logoColor=white)](https://vuejs.org) [![TypeScript](https://img.shields.io/badge/TypeScript-3178C6?style=flat&logo=typescript&logoColor=white)](https://www.typescriptlang.org) Tailwind CSS, Inertia.js |
| **DevOps** | [![Docker](https://img.shields.io/badge/Docker-2496ED?style=flat&logo=docker&logoColor=white)](https://docker.com) Kubernetes, [![Grafana](https://img.shields.io/badge/Grafana-F46800?style=flat&logo=grafana&logoColor=white)](https://grafana.com), Loki, Promtail, [![Meilisearch](https://img.shields.io/badge/Meilisearch-FF5722?style=flat&logo=meilisearch&logoColor=white)](https://www.meilisearch.com) |
| **Payments** | Stripe API, Pix, Credit/Debit Cards |

---

## 🚀 Setup and Installation
The project includes a detailed infrastructure guide.
> 📖 **[Click here to access the Detailed Setup Guide (docs/SETUP.md)](docs/SETUP.md)**

### 📦 System Dependencies (apt)
```bash
sudo apt-get update && sudo apt-get install -y jpegoptim optipng pngquant webp gifsicle
```

### 🖼️ Image Optimization Pipeline
```bash
# Process all images from originais/ → home/
php artisan images:optimize-batch

# Force reprocess all files
php artisan images:optimize-batch --force
```

---

## 🔍 Meilisearch & Estratégia de Busca Híbrida

O projeto utiliza o **Meilisearch** como motor de busca principal para catálogos, produtos e termos textuais, oferecendo alta velocidade e tolerância a erros (fuzzy search).

### Fluxo de Busca Inteligente (Redis + PostgreSQL + Meilisearch)
1. **Cache em Redis**: As consultas de produtos verificam primeiramente o Redis.
2. **Fallback para PostgreSQL**: Se não encontrado no Redis, a busca recorre ao PostgreSQL e o resultado é escrito no Redis para otimizar acessos futuros.
3. **Indexação**: O Meilisearch atua em paralelo mantendo o catálogo indexado para buscas complexas de texto completo.

| Variável | Valor Local | Descrição |
| :--- | :--- | :--- |
| `MEILISEARCH_HOST` | `http://127.0.0.1:7700` | URL da API do Meilisearch |
| `MEILISEARCH_KEY` | `masterKey` | Chave mestre de autenticação |

### Container Docker
```bash
# Iniciar Meilisearch via Docker Compose
docker compose up -d demanda-meilisearch

# Verificar saúde do serviço
curl http://localhost:7700/health
```

---

## 📊 Grafana + Loki + Promtail (Monitoramento Local)

Stack de observabilidade 100% local para desenvolvimento:
- **Loki**: agregação de logs (datasource principal).
- **Promtail**: agente que coleta logs do Laravel (`storage/logs/*.log`) e envia para o Loki.
- **Grafana**: dashboards interativos (erros, severidade, live tail) + **Grafana Alerting** nativo.

---

## 🗄️ PostgreSQL — Master/Replica (DEV)

O ambiente de desenvolvimento simula a arquitetura real de produção com dois containers PostgreSQL independentes:

| Container | Porta | Função |
| :--- | :--- | :--- |
| `demanda-psql-dev` | `5434` | Master (escrita + leitura) |
| `demanda-psql-rep-dev` | `5435` | Réplica (leitura dedicada) |

---

## 🐇 RabbitMQ — Message Broker Assíncrono

Message broker utilizado para processamento assíncrono de jobs pesados e filas robustas, totalmente independente do Redis.

```bash
# Iniciar RabbitMQ
docker compose up -d demanda-rabbitmq-dev
```

---

## 🔔 Notificações — Microsserviço Go (Arquitetura Híbrida)

Sistema híbrido Laravel + Go para processamento assíncrono de notificações via Redis List (`notifications_queue`) consumidas por workers em Go utilizando Goroutines para máxima performance e baixo consumo de RAM.

---

## 📝 Roadmap
- [ ] Advanced Rate Limiting for API endpoints.
- [x] RabbitMQ — Message Broker para processamento assíncrono.
- [x] Audit Logs — Sistema de logs de auditoria polimórfico e multi-tenant.
- [x] Meilisearch — Motor de busca full-text integrado.

---

## ✒️ Author
**Luiz** — Fullstack PHP/Laravel Developer.
[LinkedIn](https://www.linkedin.com/in/letsmg/) | [Portfolio](https://www.hierarca.com)