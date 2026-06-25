# [Português](#português) | [English](#english)

<a id="português"></a>
# 🚀 Demanda3D
*Sistema SaaS especializado na gestão operacional, financeira e produtiva para negócios de impressão 3D.*

[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=flat&logo=php)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=flat&logo=laravel)](https://laravel.com)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-336791?style=flat&logo=postgresql)](https://postgresql.org)
[![Docker](https://img.shields.io/badge/Docker-Enabled-2496ED?style=flat&logo=docker)](https://docker.com)

---

## 🎯 Sobre o Projeto
O Demanda3D é uma plataforma SaaS robusta desenvolvida para a gestão de ponta a ponta de negócios de impressão 3D. O sistema gerencia desde o controle de insumos e custos de produção até assinaturas recorrentes e entrega final, utilizando uma arquitetura **multi-tenant** com isolamento estrito de dados.

## 🏗️ Diferenciais de Engenharia
* **Infraestrutura Escalável:** Projetado com containers Docker e preparado para orquestração via Kubernetes.
* **Alta Disponibilidade:** Estratégia de replicação PostgreSQL (Master/Replica) para garantir resiliência e performance em leitura.
* **Segurança por Design:** Conformidade com LGPD através de criptografia em repouso (`AES-256`) e hashing de senhas com `Argon2id`.
* **Performance:** Camada de cache e filas distribuídas via Redis.

## 🛠️ Stack Tecnológica

| Camada | Tecnologias |
| :--- | :--- |
| **Backend** | Laravel 11, PHP 8.3, PostgreSQL, Redis |
| **Frontend** | Vue 3, TypeScript, Inertia.js, Tailwind CSS |
| **DevOps** | Docker, Kubernetes, CI/CD Pipeline |
| **Payments** | Stripe API, Pix, Crédito/Débito |

---

## 🚀 Setup e Instalação
O projeto conta com um guia detalhado de infraestrutura.
> 📖 **[Clique aqui para acessar o Guia de Setup Detalhado (docs/SETUP.md)](docs/SETUP.md)**

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

## 🛠️ Tech Stack

| Layer | Technologies |
| :--- | :--- |
| **Backend** | Laravel 11, PHP 8.3, PostgreSQL, Redis |
| **Frontend** | Vue 3, TypeScript, Inertia.js, Tailwind CSS |
| **DevOps** | Docker, Kubernetes, CI/CD Pipeline |
| **Payments** | Stripe API, Pix, Credit/Debit Cards |

## 🚀 Setup and Installation
The project includes a detailed infrastructure guide.
> 📖 **[Click here to access the Detailed Setup Guide (docs/SETUP.md)](docs/SETUP.md)**

---

## 📝 Roadmap
- [ ] Advanced Rate Limiting for API endpoints.
- [ ] Audit Logs for critical actions.
- [ ] 2FA support for administrative accounts.

---

## ✒️ Author
**Luiz** — Fullstack PHP/Laravel Developer.
[LinkedIn](https://linkedin.com/in/seu-perfil) | [Portfolio](https://www.hierarca.com)