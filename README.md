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

### 📦 Dependências do Sistema (apt)

Os seguintes binários são necessários para o pipeline de otimização de imagens:

```bash
sudo apt-get update && sudo apt-get install -y \
    jpegoptim \
    optipng \
    pngquant \
    webp \
    gifsicle
```

O pacote `spatie/image-optimizer` detecta automaticamente esses binários quando instalados.

### 🖼️ Pipeline de Otimização de Imagens

O projeto inclui um pipeline automático de otimização de imagens com `intervention/image` (redimensionamento/conversão) e `spatie/image-optimizer` (compressão sem perda).

**Estrutura de diretórios:**
- `storage/app/public/imgs/originais/` — Imagens brutas (originais), **não versionadas no Git**.
- `storage/app/public/imgs/home/` — Imagens otimizadas (geradas), **não versionadas no Git**.

**Comando de processamento em lote:**
```bash
# Processa todas as imagens de originais/ → home/
php artisan images:optimize-batch

# Força reprocessamento de todos os arquivos
php artisan images:optimize-batch --force
```

> ⚠️ **Importante:** Execute `php artisan images:optimize-batch` como passo de setup inicial e após restaurar backups. As pastas `originais/` e `home/` são ignoradas pelo Git — apenas os `.gitkeep` são versionados para manter a estrutura.

**Fluxo de upload de produtos:**
Toda imagem enviada via formulário de produto é automaticamente:
1. Validada (máx. 2MB, formatos JPG/PNG/WEBP — backend + frontend)
2. Salva como original em `imgs/originais/`
3. Redimensionada (máx. 1600px largura, sem upscale) e convertida para WebP
4. Comprimida sem perda visual via `spatie/image-optimizer`
5. Salva como otimizada em `imgs/home/`
6. Persistida no banco com path relativo da versão otimizada + referência ao original

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

### 📦 System Dependencies (apt)

The following binaries are required for the image optimization pipeline:

```bash
sudo apt-get update && sudo apt-get install -y \
    jpegoptim \
    optipng \
    pngquant \
    webp \
    gifsicle
```

The `spatie/image-optimizer` package auto-detects these binaries when installed.

### 🖼️ Image Optimization Pipeline

The project includes an automatic image optimization pipeline using `intervention/image` (resize/conversion) and `spatie/image-optimizer` (lossless compression).

**Directory structure:**
- `storage/app/public/imgs/originais/` — Raw (original) images, **not versioned in Git**.
- `storage/app/public/imgs/home/` — Optimized images (generated), **not versioned in Git**.

**Batch processing command:**
```bash
# Process all images from originais/ → home/
php artisan images:optimize-batch

# Force reprocess all files
php artisan images:optimize-batch --force
```

> ⚠️ **Important:** Run `php artisan images:optimize-batch` as an initial setup step and after restoring backups. The `originais/` and `home/` folders are Git-ignored — only `.gitkeep` files are versioned to preserve the directory structure.

---

## 📝 Roadmap
- [ ] Advanced Rate Limiting for API endpoints.
- [ ] Audit Logs for critical actions.
- [ ] 2FA support for administrative accounts.

---

## ✒️ Author
**Luiz** — Fullstack PHP/Laravel Developer.
[LinkedIn](https://linkedin.com/in/seu-perfil) | [Portfolio](https://www.hierarca.com)