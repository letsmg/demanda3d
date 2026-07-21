# SDD 00 — Regras Críticas Globais e Protocolos de Segurança

> **OBRIGATÓRIO:** Este arquivo deve ser lido em **toda primeira requisição** ou quando o contexto for limpo. Contém regras inegociáveis que se sobrepõem a qualquer outra instrução.
> **Atualizado:** 2026-07-21

---

## 1. 🔴 REGRA ZERO — Git

> **É TERMINANTEMENTE PROIBIDO executar `git commit` ou `git push`.**
>
> Essa responsabilidade é **exclusiva do usuário**. O assistente pode preparar código, revisar, sugerir — mas nunca versionar ou enviar alterações ao repositório remoto.

---

## 2. 🔴 REGRA ZERO — Proteção de Dados Sensíveis (LGPD)

### 2.1 Proibição de Hardcoding

**NUNCA** expor senhas, chaves de API, chaves de criptografia, segredos de banco ou quaisquer credenciais diretamente em:
- Código-fonte
- Scripts
- Commits
- Arquivos de configuração (incluindo `docker-compose.yml`)

### 2.2 Uso Exclusivo do `.env`

Toda credencial deve ser extraída **exclusivamente** via `env('KEY')` do arquivo `.env`.

### 2.3 Versionamento

- `.env` → **obrigatoriamente** no `.gitignore`
- `.env.example` → **obrigatoriamente** mantido atualizado com a estrutura necessária (valores reais ocultos)

### 2.4 Paridade de Dados Pessoais

Para qualquer dado pessoal identificável (PII) — exceto `users.email`:
- `*_hash` = `hash('sha256', $valor)` → índice único + busca (WHERE)
- `*_encrypted` = `Crypt::encryptString($valor)` → AES-256-CBC em repouso

Ver **SDD 02** para modelagem completa.

---

## 3. 🔴 REGRA ZERO — Protocolo de Validação Pré-Execução

> **Se qualquer requisição do usuário parecer incorreta, perigosa ou fora dos padrões de engenharia/mercado, o assistente DEVE:**
>
> 1. **PARAR** imediatamente — não executar nada.
> 2. **QUESTIONAR** o usuário antes de qualquer ação.
> 3. **EXPLICAR** por que o pedido foi julgado incorreto.
> 4. **AGUARDAR** aprovação explícita antes de prosseguir.

**Exemplos de situações que exigem parada:**
- Remoção de criptografia ou hashing de dados sensíveis
- Exposição de credenciais em código
- Uso de `$guarded = []` em Models (deve usar `$fillable`)
- Uso de `float`/`double` para valores financeiros (deve usar `numeric(12,2)` ou inteiros)
- Uso de `fetch`/`axios` no frontend (deve usar recursos nativos do Inertia.js)
- Uso de `v-html` sem sanitização rigorosa
- Instalação de pacotes com menos de 15 dias de lançamento

---

## 4. Stack Obrigatória

| Camada | Tecnologia |
| :--- | :--- |
| **Backend** | Laravel (PHP 8.3+) |
| **Banco de Dados** | PostgreSQL (master/replica) |
| **Cache & Filas** | Redis |
| **Frontend** | Vue 3 (Composition API) |
| **Linguagem Frontend** | TypeScript |
| **Comunicação** | Inertia.js (proibido fetch/axios para dados internos) |
| **Estilo** | Tailwind CSS |

---

## 5. Idioma e Nomenclatura

| Contexto | Regra |
| :--- | :--- |
| Comunicação com o usuário | **Português** |
| Código, arquivos, classes, métodos, variáveis, tabelas, colunas | **Inglês estrito** |

---

## 6. Princípios de Engenharia

Todo código deve respeitar:

| Princípio | Descrição |
| :--- | :--- |
| **SOLID** | Single Responsibility, Open/Closed, Liskov, Interface Segregation, Dependency Inversion |
| **DRY** | Don't Repeat Yourself |
| **KISS** | Keep It Simple, Stupid |
| **Clean Architecture** | Separação em camadas (Controller → Service → Model) |
| **Separation of Concerns** | Cada classe/arquivo com responsabilidade única e bem definida |

---

## 7. Ordem de Implementação

Toda nova funcionalidade deve seguir **estritamente** esta ordem:

```
Migration → Model → Enum → FormRequest → Service → Controller → Policy → Vue Page → Components → Tests
```

> Nenhum passo pode ser pulado. Nenhuma funcionalidade com transações, criptografia ou permissões de faturamento está concluída sem cobertura de testes.

---

## 8. Testes Obrigatórios

### 8.1 Pest Framework (Backend)

Obrigatório para testes de Unidade e Integração (Feature). Nenhuma funcionalidade que envolva:
- Transações financeiras
- Paridade de dados (criptografia)
- Permissões de faturamento

pode ser considerada concluída sem cobertura completa de testes.

### 8.2 Playwright (E2E Frontend)

Testes ponta a ponta obrigatórios para fluxos críticos:
- Login de múltiplos perfis (SELLER_1, CUSTOMER, CARRIER_1)
- Registro progressivo
- Checkout completo
- Restrições de permissões

**Dependência local:** Containers Docker (PostgreSQL e Redis) devem estar ativos para os testes Playwright rodarem.

---

## 9. Proteção de Mass-Assignment

> **PROIBIDO** usar `protected $guarded = [];` nos Models.

Usar **estritamente** `protected $fillable = [...]` especificando campo por campo que pode ser escrito.

**Exemplo correto:**
```php
#[Fillable([
    'email',
    'display_name',
    'first_name_encrypted',
    'first_name_hash',
    // ... cada campo explicitamente
])]
class User extends Authenticatable
```

---

## 10. Precisão Financeira

> **PROIBIDO** usar `float` ou `double` para valores monetários.

Usar **exclusivamente** `DECIMAL(12,2)` no banco de dados ou inteiros (centavos). Cálculos financeiros devem ser feitos **estritamente** dentro de classes de Service.

---

## 11. Proibições no Frontend (Vue 3 + Inertia.js)

| Proibido | Alternativa |
| :--- | :--- |
| `fetch()` ou `axios` para dados internos | Recursos nativos do Inertia.js (`router.get`, `router.post`, etc.) |
| Montagem manual de URLs no frontend | Rotas nomeadas do Laravel (`route('name')`) |
| `v-html` sem sanitização | Sanitização rigorosa obrigatória antes de usar `v-html` |

**Fluxo de dados correto:** Controller → Service → `Inertia::render()` → Props → Vue Component.

---

## 12. Estabilidade de Pacotes

> **NUNCA** instalar ou atualizar dependências/pacotes com **menos de 15 dias** de lançamento.

Garantia mínima de estabilidade antes da adoção.

---

## 13. Seeders de Teste

Para cada tabela criada ou modificada:
- Criar ou atualizar o Seeder respectivo
- Gerar **pelo menos 5 registros** de teste
- Utilizar **estritamente** `updateOrCreate` do Laravel

---

## 14. Idempotência

Todos os scripts de deploy, migrations e comandos auxiliares devem ser **estritamente idempotentes** (executáveis múltiplas vezes sem quebrar o sistema ou duplicar dados).

---

## 15. Marcas Forenses e Licenciamento

### 15.1 Arquivo LICENSE

Projeto licenciado sob **CC BY-NC-SA 4.0**. O arquivo `LICENSE` deve existir na raiz.

### 15.2 Footer de Direitos Autorais

O rodapé do painel público e privado (`Footer.vue`) deve conter exatamente:

> Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

### 15.3 Marcas Forenses no Código

Inserir a linha de comentário abaixo no cabeçalho ou rodapé de **pelo menos dois arquivos críticos** de backend:

```php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.
```

O assistente deve **verificar e preservar** essa linha em cada alteração desses arquivos.

**Arquivos atualmente marcados:**
- `routes/web.php`
- `app/Models/User.php`
- `app/Models/Order.php` (duas marcas — topo e rodapé)

---

## 16. Rate Limiting em Autenticação

Configurar limitadores de requisições **extremamente agressivos** em todas as rotas de:
- Login
- Registro
- Recuperação de senha
- Verificação de e-mail

Mitigação de ataques de força bruta e timing attacks.

---

## 17. Documentação Viva

### 17.1 README.md

Atualizar sempre que adicionar nova dependência, fluxo ou estrutura técnica. Deve conter:
- Pré-requisitos
- Processos de instalação locais
- Comandos para iniciar testes Playwright
- Orientações de inicialização dos containers Docker

### 17.2 docs/tables.md

Dicionário de dados oficial do sistema. Atualizar sempre que uma migration for adicionada ou tabela alterada.

### 17.3 docs/sdd/

Documentos de Design de Software modulares — este diretório.

---

## 18. Mapeamento Rápido para SDDs Específicos

| Escopo da Solicitação | Arquivo SDD |
| :--- | :--- |
| Login, senhas, níveis de acesso, guards, bloqueios | `docs/sdd/01-autenticacao-niveis-acesso.md` |
| Criptografia, LGPD, dados sensíveis, consentimento, exclusão | `docs/sdd/02-banco-dados-lgpd.md` |
| Produtos, carrinho, checkout, pedidos, etiquetas, cupons | `docs/sdd/03-regras-negocio-centrais.md` |
| Stripe, notificações, OpenSearch, Grafana, APIs externas | `docs/sdd/04-integracao-apis.md` |
| Multi-tenant, Docker, PostgreSQL, Redis, CI/CD, auditoria, SEO | `docs/sdd/05-infraestrutura-multi-tenant.md` |
| Threads, mensagens, disputas, moderação de conteúdo | `docs/sdd/06-moderacao-comunicacao.md` |
| Regras críticas globais (este arquivo) | `docs/sdd/00-core-rules-and-safety.md` |