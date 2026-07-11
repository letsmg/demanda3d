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
* **Busca Otimizada:** OpenSearch integrado localmente para indexação e consultas de alto desempenho.

## 🛠️ Stack Tecnológica

| Camada | Tecnologias |
| :--- | :--- |
| **Backend** | Laravel 11, PHP 8.3, Go 1.25 (notificações), PostgreSQL, Redis |
| **Frontend** | Vue 3, TypeScript, Inertia.js, Tailwind CSS |
| **DevOps** | Docker, Kubernetes, CI/CD Pipeline, OpenSearch |
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

## 🔍 OpenSearch (Busca Local Otimizada)

O projeto utiliza **OpenSearch** como motor de busca complementar ao PostgreSQL, fornecendo buscas full-text rápidas e tolerantes a erros tipográficos (fuzzy search), indexação de documentos e sugestões de autocomplete.

> ⚠️ **Recurso estritamente local:** O OpenSearch está configurado para funcionar **apenas em ambiente de desenvolvimento local** via Docker. Em produção na **Oracle Free Tier**, o container é desabilitado (`OPENSEARCH_ENABLED=false`) e as buscas utilizam o PostgreSQL nativamente, pois a instância gratuita da Oracle possui limitações de hardware (1 GB de RAM, 1 OCPU) que não suportam o OpenSearch. O container usa `profiles: [opensearch]` e não é iniciado por padrão.

| Variável | Valor Local | Produção |
| :--- | :--- | :--- |
| `OPENSEARCH_ENABLED` | `true` | `false` |
| `OPENSEARCH_HOST` | `127.0.0.1` | — |
| `OPENSEARCH_PORT` | `9201` | — |

### Container Docker

```bash
# Iniciar OpenSearch via profile dedicado
docker compose --profile opensearch up -d

# Verificar saúde do cluster
curl http://localhost:9201/_cluster/health
```

---

## 📊 Grafana + Loki + Promtail (Monitoramento Local)

Stack de observabilidade 100% local para desenvolvimento:
- **Loki**: agregação de logs (datasource principal).
- **Promtail**: agente que coleta logs do Laravel (`storage/logs/*.log`) e envia para o Loki.
- **Grafana**: dashboards interativos (erros, severidade, live tail) + **Grafana Alerting** nativo para disparar e-mails de alerta em anomalias locais.

> ⚠️ **Recurso estritamente local:** O Grafana e o pipeline Loki+Promtail são exclusivos do ambiente de desenvolvimento local. Em produção na Oracle Free Tier, esses containers **não sobem** (`GRAFANA_ENABLED=false`). O monitoramento de produção é delegado ao Sentry e a serviços externos. Os containers usam `profiles: [grafana]` e não são iniciados por padrão.

| Variável | Valor Local | Produção |
| :--- | :--- | :--- |
| `GRAFANA_ENABLED` | `true` | `false` |

### Containers Docker

```bash
# Iniciar stack Grafana via profile dedicado
docker compose --profile grafana up -d

# Acessar dashboards
open http://localhost:3001   # admin / admin

# Verificar saúde
curl http://localhost:3001/api/health
curl http://localhost:3101/ready
```

### Dashboard Padrão

O dashboard **"Demanda3D — Logs & Erros"** é provisionado automaticamente e inclui:
- Contador de erros críticos (últimos 5 min)
- Gráfico de logs por severidade (error, warning, critical)
- Stream de erros em tempo real (Live Tail)

---

## 🗄️ PostgreSQL — Estratégia Unificada (DEV)

Em desenvolvimento local, usamos um **único container PostgreSQL** (`demanda-psql-dev`). As conexões de leitura (`read`) e escrita (`write`) do Laravel apontam para o mesmo host, controladas pela flag `DB_READ_WRITE_SPLIT=false`.

> ℹ️ Em produção e homologação, a réplica hot standby é mantida nos respectivos `docker-compose-prod.yml` e `docker-compose-hom.yml`, com `DB_READ_WRITE_SPLIT=true`.

| Variável | DEV | PROD / HOM |
| :--- | :--- | :--- |
| `DB_READ_WRITE_SPLIT` | `false` | `true` |
| `DB_HOST` | `127.0.0.1` | master host |
| `DB_REPLICA_HOST` | (ignorado) | replica host |

---

## 🛡️ Moderação de Conteúdo — Mensagens e Disputas

O sistema aplica validação automática e dedutiva em **messages** e **disputes** antes de qualquer inserção no banco de dados, utilizando Custom Validation Rules do Laravel.

### Regras Aplicadas

| Regra | Classe | Comportamento |
| :--- | :--- | :--- |
| **Dados de Contato** | `NoContactDataRule` | Bloqueia e-mails e telefones (formato brasileiro com/sem DDD). Exibe o termo suspeito detectado. Retorna HTTP 422. |
| **Palavras Ofensivas** | `NoOffensiveContentRule` | Utiliza `snipe/banbuilder` + algoritmo próprio de normalização dedutiva. Bloqueia completamente (sem censura ou salvamento). Lista os termos detectados. |

### Algoritmo de Dedução (Anti-Ofuscação)

A regra `NoOffensiveContentRule` aplica um pipeline de normalização que deduz palavrões mesmo quando o usuário tenta burlar o filtro:

| Técnica de Ofuscação | Exemplo | Resultado Deduzido |
| :--- | :--- | :--- |
| Letras espaçadas | `c a r a l h o` | `caralho` |
| Letras repetidas/esticadas | `caralhoooo` | `caralho` |
| Substituição por números/símbolos (leet) | `c4r4lh0` / `c@r@lho` | `caralho` |
| Variações de gênero/sufixo | `caralha` / `caralhas` | `caralho` |

### Permissões de Administrador

Os usuários com nível **Admin** (`access_level = 10`) têm permissão total de visualização em todas as conversas (`threads`/`messages`) e disputas (`disputes`) para fins de moderação, conforme definido nas Policies:

- `MessagePolicy` — Admin acessa todas as mensagens
- `ThreadPolicy` — Admin acessa todas as threads
- `DisputePolicy` — Admin acessa todas as disputas

Staff (Management e Operational) também possui acesso de leitura, mas clientes só visualizam seus próprios registros.

---

## 📋 Dicionário de Dados

O arquivo **[docs/tables.md](docs/tables.md)** contém um dicionário completo de todas as tabelas do sistema, baseado nas migrations existentes. Consulte-o para entender o propósito e as relações de cada entidade.

---

## 🔔 Notificações — Microsserviço Go (Arquitetura Híbrida)

O sistema utiliza uma arquitetura **híbrida Laravel + Go** para processamento assíncrono de notificações:

```
┌──────────────┐     RPUSH      ┌──────────────┐     BLPOP      ┌──────────────────────┐
│   Laravel    │ ──────────────> │    Redis     │ ─────────────> │  Go Notification      │
│  (Core)      │  notifications │  (Queue)     │  notifications │  Service (Worker)     │
│              │  _queue        │              │  _queue        │                       │
│ SendNotifi-  │                │              │                │ Goroutines → Mock      │
│ cation Job   │                │              │                │ (push/email/sms)       │
└──────────────┘                └──────────────┘                └──────────────────────┘
```

### Motivação
- **Economia de RAM**: O container Go compilado (`scratch`) ocupa ~8 MB em memória, contra 40-80 MB de um worker PHP adicional.
- **Concorrência nativa**: Goroutines processam múltiplas notificações simultaneamente sem overhead de processo.
- **Separação de responsabilidades**: O Laravel publica o payload e continua executando; o Go consome e dispara push, e-mail e SMS de forma isolada.

### Fluxo de Uso

```php
// No Laravel: dispatch o job e o Go processa em background
SendNotification::dispatch(
    userId: '42',
    title: 'Novo pedido recebido!',
    message: 'Seu pedido #1234 foi confirmado.',
    channel: 'push',
    tenantId: '1',
);
```

### Container Docker

```bash
# O container sobe automaticamente com docker compose up
docker compose up -d go-notification-service

# Verificar logs
docker logs -f go-notification-service
```

| Componente | Tecnologia | Propósito |
| :--- | :--- | :--- |
| `go-service/main.go` | Go 1.25 + go-redis | Worker que escuta `notifications_queue` via BLPOP com 5 goroutines |
| `app/Jobs/SendNotification.php` | Laravel | Job que serializa o payload e publica via RPUSH no Redis |
| `notifications_queue` | Redis List | Fila FIFO compartilhada entre Laravel (produtor) e Go (consumidor) |

---

## 📝 Roadmap
- [ ] Advanced Rate Limiting for API endpoints.
- [ ] Audit Logs for critical actions.
- [ ] 2FA support for administrative accounts.

---

## ✒️ Author
**Luiz** — Fullstack PHP/Laravel Developer.
[LinkedIn](https://linkedin.com/in/seu-perfil) | [Portfolio](https://www.hierarca.com)