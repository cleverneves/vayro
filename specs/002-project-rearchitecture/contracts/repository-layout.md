# Contract: Repository Layout

**Feature**: `002-project-rearchitecture`
**Audience**: time que navega ou altera o repositório
**Status**: target after this feature

Este contrato descreve a interface de organização do repositório. Não é um
contrato HTTP. O contrato HTTP permanece o de
`specs/001-vehicle-rental-management/contracts/` — ver
[public-api-compatibility.md](./public-api-compatibility.md).

## Top-level tree

```text
<repo>/
├── apps/
│   ├── web/
│   └── api/
├── infra/
│   ├── docker/
│   └── postgres/
├── docs/
├── docker-compose.yml
├── README.md
├── .specify/
├── .cursor/
└── specs/
```

## Ownership

| Path | Owns | Must not contain |
|---|---|---|
| `apps/web/` | UI React + TypeScript + Material UI, cliente HTTP, `.env.example` da SPA | Laravel, SQL, Nginx compartilhado, Compose |
| `apps/api/` | Laravel, regras, persistência, testes PHPUnit, `Dockerfile` da API | React, Material UI, Vite, Nginx compartilhado, Compose |
| `infra/docker/` | Nginx e config compartilhada de execução | `app/`, `src/` das aplicações, Dockerfile da API |
| `infra/postgres/` | Credenciais e config compartilhada de dados | Migrations, models, repositories Laravel |
| `docs/` | Estrutura, ambiente, decisões | Código executável |
| `/docker-compose.yml` | Orquestração local | Receita de construção de uma aplicação só |
| `.specify/`, `specs/`, `.cursor/` | Processo do time | Runtime de produto |

## Naming

- Diretórios de topo e de aplicação: minúsculas, responsabilidade, kebab-case
  se houver mais de uma palavra.
- Permitido: `apps`, `web`, `api`, `infra`, `docs`.
- Proibido no topo: `frontend`, `react`, `laravel`, `typescript`, `postgres`
  como nome de aplicação, `docker` como pasta de aplicação, `misc`, `common`,
  `helpers`.

`infra/postgres` descreve a responsabilidade “dados persistidos do ambiente”,
não a aplicação de negócio.

## Growth rules

```text
POST /apps/{name}     → só se a unidade tiver runtime próprio e distinto
POST /packages/{name} → só se existirem ≥ 2 consumidores reais
POST /{top-level}     → só depois de avaliar apps, infra e docs
```

Respostas esperadas para pedidos comuns:

| Pedido | Destino |
|---|---|
| Tela nova | `apps/web/src/` |
| Operação de negócio nova | `apps/api/` (camadas já usadas pela constituição) |
| Ajuste de Nginx ou porta local | `infra/docker/` + `docker-compose.yml` |
| Credencial do banco local | `infra/postgres/` |
| Decisão de arquitetura | `docs/decisions/` |
| Receita de build da API | `apps/api/Dockerfile` |
| Receita de build da web | `apps/web/package.json` |

## Compatibility with old paths

Depois da entrega, estes caminhos deixam de ser casa ativa do conteúdo:

| Old path | New path |
|---|---|
| `frontend/` | `apps/web/` |
| `app/`, `routes/`, `tests/`, `composer.json`, `artisan`, `Dockerfile` na raiz | `apps/api/` |
| `docker/nginx/` | `infra/docker/nginx/` |

O guia `docs/structure.md` aponta o destino. Não se mantém atalho nem cópia
no caminho antigo.

Specs históricas em `specs/001-vehicle-rental-management/` podem continuar
citando `frontend/` e a raiz Laravel. Elas são registro da época, não o
contrato corrente.
