# Data Model: Rearquitetura do Projeto

**Branch**: `002-project-rearchitecture` | **Date**: 2026-10-03

Esta feature não cria tabelas nem altera o modelo de locação. As entidades
abaixo são as fronteiras do repositório. Regras de validação vêm dos
FR-001 a FR-022. Persistência de negócio continua em PostgreSQL, via
migrations já existentes em `apps/api/database/` depois da mudança.

O modelo de locação (locatário, locação, automóvel, status, papéis) não é
redesenhado. Ver `specs/001-vehicle-rental-management/data-model.md`.

## Entities

### ProductRepository

Casa única do produto.

| Field | Rule |
|---|---|
| Root areas | Deve expor `apps/`, `infra/`, `docs/` e o ponto de orquestração `docker-compose.yml` |
| Process areas | `.specify/`, `specs/` e `.cursor/` permanecem na raiz e não são aplicações |
| Top-level names | Curto, minúsculas, responsabilidade — nunca tecnologia |
| Generic top-level | Proibido: `misc`, `stuff`, `temp`, `common`, `helpers`, `others`, `new`, `old` |
| Completeness | Incompleto enquanto interface, serviço, ambiente ou documentação estiver ausente ou misturada |

**Relationships**: contém exatamente duas `ExecutableApplication` nesta
versão; contém um `ExecutionEnvironment`; contém um `ProductDocumentation`.

### ExecutableApplication

Unidade com responsabilidade de execução própria.

| Field | Rule |
|---|---|
| Count now | Exatamente duas: `web` e `api` |
| Location | Sempre sob `apps/` |
| Own build recipe | MAY viver na aplicação (`Dockerfile` da API; `package.json` / Vite da web) |
| Own env example | MAY viver na aplicação (somente o que ela exige) |
| New application | Só nasce com responsabilidade de execução distinta das já existentes |

**Relationships**: `web` e `api` são as únicas instâncias atuais.
Não aponta para `packages/`.

### UserInterface (`apps/web`)

Aplicação que a pessoa usa para ver e operar o produto.

| Field | Allowed | Forbidden |
|---|---|---|
| Contents | Páginas, componentes, roteamento cliente, estado cliente, cliente HTTP, validação de formulário, `package.json`, Vite, `.env.example` com `VITE_API_BASE_URL` | Regras de locação, acesso a PostgreSQL, migrations, Nginx compartilhado, Compose, credenciais do banco |
| Public address | `http://localhost:5173` | Trocar porta ou exigir URL nova do usuário |

**State**: não possui máquina de estados própria nesta feature. A sessão JWT
já existente permanece no cliente.

### BusinessService (`apps/api`)

Aplicação que autentica, aplica regras e persiste dados.

| Field | Allowed | Forbidden |
|---|---|---|
| Contents | HTTP Laravel, services, repositories, models, migrations, testes PHPUnit, `composer.json`, `Dockerfile`, `.env.example` Laravel | Páginas React, Material UI, Vite, Nginx compartilhado, Compose, volume do Postgres |
| Internal layers | Permanecem como estão; esta feature não as redesenha | Reescrever fluxo para outra separação interna |
| Public address | `http://localhost:8989` | Trocar porta |

**State**: nenhum estado de domínio novo. Status de locação continua o da
feature 001.

### ExecutionEnvironment (`infra/`)

Configuração compartilhada que faz o produto subir e persistir dados.

| Field | Location | Rule |
|---|---|---|
| Shared runtime config | `infra/docker/` | Nginx e afins. Sem código de `web` ou `api` |
| Shared data config | `infra/postgres/` | Credenciais do serviço `pgsql`. Sem migrations Laravel |
| Orchestration | `docker-compose.yml` na raiz | Único ponto de partida |
| Runtime data volume | `.docker/pgsql/dbdata` | Estado local, fora do Git |
| App-only Dockerfile | — | Não vive em `infra/` |

**Relationships**: orquestra `web`, `api`, PostgreSQL, Redis, Nginx e queue.
Não é dono do código das aplicações.

### BuildRecipe

Instruções para preparar uma aplicação para executar.

| Owner | Recipe | Must not live in |
|---|---|---|
| `apps/api` | `Dockerfile` PHP 8.3-FPM Alpine | `infra/` |
| `apps/web` | `package.json`, Vite, TSConfig | `infra/` |

Se duas aplicações passarem a usar a mesma receita, aí se avalia extração.
Hoje não há esse caso.

### ProductDocumentation (`docs/`)

Textos de estrutura, ambiente e decisões. Sem código executável.

| Document | Purpose |
|---|---|
| `docs/structure.md` | Onde colocar tela, operação, ambiente e decisão |
| `docs/environment.md` | Como subir pelo Compose da raiz |
| `docs/decisions/001-monorepo-boundaries.md` | Por que essas fronteiras |

README da raiz aponta para esses guias. Specs de `001` não são reescritas.

### PublishedContract

Operações e vocabulário que clientes já usam. Esta feature não o altera.

| Surface | Compatibility rule |
|---|---|
| `/api/v1/*` | Caminhos, campos e status permanecem |
| `/api/v2/*` | Caminhos, campos e status permanecem |
| JWT `Authorization: Bearer` | Permanece |
| SPA → API | `VITE_API_BASE_URL=http://localhost:8989` |

## Validation rules (cross-entity)

1. Não pode existir segunda cópia ativa no caminho antigo (`frontend/`,
   Laravel na raiz, `docker/nginx/` depois da mudança).
2. `packages/` não existe enquanto houver um único consumidor.
3. Aplicação nova sem runtime próprio não é criada.
4. Reorganização pela metade (só web ou só api) não é conclusão.
5. Fluxo de locação com passo, endereço ou resultado diferente é regressão.

## State transitions

Não há máquina de estados de domínio. O único “estado” desta feature é o
do repositório:

```text
raiz misturada  →  movimento em andamento  →  quatro áreas distintas
                                              (web, api, infra, docs)
```

Transição incompleta não é estado válido de entrega.
