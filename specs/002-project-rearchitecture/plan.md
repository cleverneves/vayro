# Implementation Plan: Rearquitetura do Projeto

**Branch**: `002-project-rearchitecture` | **Date**: 2026-10-03 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/002-project-rearchitecture/spec.md`

**Note**: This template is filled in by the `/speckit-plan` command; its definition describes the execution workflow.

## Summary

Reorganizar o repositório Vayro em fronteiras de responsabilidade, sem mudar
regras de locação nem o contrato HTTP já publicado. A interface vai para
`apps/web`, o Laravel vai para `apps/api`, a configuração compartilhada de
execução e dados vai para `infra/`, a documentação nova vai para `docs/`, e
o `docker-compose.yml` permanece o ponto único de subida na raiz. Endereços
`http://localhost:5173` e `http://localhost:8989` e os vocabulários `/api/v1`
e `/api/v2` permanecem. A divisão interna do serviço (controllers, services,
repositories) não é reescrita nesta entrega.

## Technical Context

**Language/Version**: PHP 8.3 (API), TypeScript ~5.9 (web), Node 22 (imagem da SPA)

**Primary Dependencies**: Laravel 13, React 19, Material UI 7, Vite 7, Docker Compose, Nginx Alpine, Redis Alpine

**Storage**: PostgreSQL 16 (volume local `.docker/pgsql/dbdata`; migrations continuam em `apps/api/database/`)

**Testing**: PHPUnit 12 via `docker-compose exec app php artisan test`, banco `vayro_testing`. Validação estrutural e de ambiente pelo [quickstart.md](./quickstart.md).

**Target Platform**: Ambiente local Docker (Windows, Linux e macOS). SPA no navegador.

**Project Type**: Monorepo com duas aplicações executáveis (`apps/web`, `apps/api`), infraestrutura e documentação

**Performance Goals**: Sem SLA novo. O ambiente sobe pelo Compose da raiz e a suíte atual permanece verde.

**Constraints**: Portas 8989 e 5173 estáveis. Contrato `/api/v1` e `/api/v2` compatível. Sem `packages/`. Sem terceira aplicação. Sem pasta de topo nomeada por tecnologia. Sem reescrita das camadas internas do Laravel. Processo Speckit permanece na raiz.

**Scale/Scope**: Mover as duas aplicações existentes, o Nginx, a documentação de ambiente e os guias de processo da próxima feature. Specs históricas de locação não são reescritas.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

Gates from `.specify/memory/constitution.md` (Vayro v1.0.0). A gate that fails
without a Complexity Tracking row blocks the plan.

- **Simplicity**: PASS — o desenho só move árvores existentes e atualiza
  caminhos. Não cria `packages/`, apps futuras, Dockerfile extra da web,
  scripts de init do Postgres nem nova camada Laravel.
- **Layers**: PASS — a divisão HTTP / serviço / repositório do Laravel não
  muda (FR-021). Controllers legados que ainda falam com Eloquent permanecem
  legado; esta feature não os altera.
- **REST**: PASS — nenhum recurso, método, status ou campo público muda.
  Vocabulário em português de `/api/v1` e `/api/v2` permanece.
- **Tests**: PASS — as regras de locação já têm testes; eles devem continuar
  passando (SC-005). As regras novas são de fronteira de repositório e se
  validam pelo quickstart e pela subida do ambiente, não por PHPUnit de pasta.
- **Language**: PASS — pastas novas usam inglês (`apps`, `web`, `api`,
  `infra`, `docs`). Textos de documentação em português do Brasil.
- **Stack**: PASS — Laravel, React + TypeScript + Material UI, PostgreSQL,
  Docker. Nenhuma biblioteca nova.
- **SOLID**: PASS — nenhuma interface ou indireção extra.

**Post-design re-check**: PASS. Os contratos e o data-model descrevem
fronteiras e compatibilidade; não introduzem camada, framework ou
reescrita de domínio. Complexity Tracking permanece vazio.

## Project Structure

### Documentation (this feature)

```text
specs/002-project-rearchitecture/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── repository-layout.md
│   ├── local-environment.md
│   └── public-api-compatibility.md
└── tasks.md              # Phase 2 (/speckit-tasks) — ainda não criado
```

### Source Code (repository root)

```text
apps/
├── web/                          # interface (hoje frontend/)
│   ├── src/
│   │   ├── api/
│   │   ├── auth/
│   │   ├── pages/
│   │   └── theme/
│   ├── index.html
│   ├── package.json
│   ├── vite.config.ts
│   └── .env.example
└── api/                          # serviço Laravel (hoje a raiz)
    ├── app/
    │   ├── Http/
    │   │   ├── Controllers/
    │   │   ├── Requests/
    │   │   └── Resources/
    │   ├── Services/
    │   ├── Repositories/
    │   └── Models/
    ├── bootstrap/
    ├── config/
    ├── database/
    ├── public/
    ├── routes/
    ├── storage/
    ├── tests/
    │   ├── Feature/
    │   └── Unit/
    ├── artisan
    ├── composer.json
    ├── phpunit.xml
    ├── Dockerfile
    └── .env.example

infra/
├── docker/
│   └── nginx/
│       └── laravel.conf          # hoje docker/nginx/laravel.conf
└── postgres/
    └── .env.example              # credenciais do serviço pgsql

docs/
├── structure.md
├── environment.md
└── decisions/
    └── 001-monorepo-boundaries.md

docker-compose.yml                # orquestração única na raiz
README.md
.specify/                         # processo Speckit (não é aplicação)
.cursor/                          # skills e regras do time
specs/                            # histórico de features
```

**Structure Decision**: Monorepo da skill `monorepo-structure`. Duas
aplicações executáveis apenas (`web`, `api`). Infraestrutura compartilhada
em `infra/`. Documentação de produto em `docs/`. Orquestração local no
Compose da raiz. Processo Speckit e constituição permanecem na raiz.
Não se cria `packages/` nesta versão.

## Complexity Tracking

> Nenhuma violação da constituição. Tabela intencionalmente vazia.

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| — | — | — |
