# Tasks: Rearquitetura do Projeto

**Input**: Design documents from `/specs/002-project-rearchitecture/`

**Prerequisites**: [plan.md](./plan.md), [spec.md](./spec.md), [research.md](./research.md), [data-model.md](./data-model.md), [contracts/](./contracts/), [quickstart.md](./quickstart.md)

**Tests**: Sem PHPUnit novo de “existência de pasta” ([research.md](./research.md) §10). As regras de locação já têm testes; a US3 os executa em `apps/api` via `docker-compose exec app php artisan test`. Fronteiras e ambiente se validam por inspeção e pelo [quickstart.md](./quickstart.md). Specs históricas em `specs/001-vehicle-rental-management/` não são reescritas.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

## Path Conventions

- **Vayro UI**: `apps/web/` (hoje `frontend/`)
- **Vayro API**: `apps/api/` (hoje a raiz Laravel: `app/`, `routes/`, `tests/`)
- **Ambiente**: `infra/docker/`, `infra/postgres/`, `docker-compose.yml` na raiz
- **Documentação de produto**: `docs/`
- **Processo do time**: `.specify/`, `specs/`, `.cursor/` na raiz
- Contratos desta feature: [repository-layout.md](./contracts/repository-layout.md), [local-environment.md](./contracts/local-environment.md), [public-api-compatibility.md](./contracts/public-api-compatibility.md)

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Criar só o esqueleto vazio das áreas-alvo. Não instalar framework nem recriar a SPA.

- [X] T001 Create empty target directories `apps/web/`, `apps/api/`, `infra/docker/`, `infra/postgres/` and `docs/decisions/`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Mover as árvores existentes e apontar o Compose para os caminhos novos. Sem isso nenhuma história consegue ser validada no layout-alvo.

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

Não reescrever controllers, services nem repositories (FR-021). Usar `git mv` para preservar histórico.

- [X] T002 [P] Move the UI tree from `frontend/` to `apps/web/` with `git mv`
- [X] T003 [P] Move the Laravel tree into `apps/api/` with `git mv`: `app/`, `bootstrap/`, `config/`, `database/`, `lang/`, `public/`, `resources/`, `routes/`, `storage/`, `tests/`, `artisan`, `composer.json`, `composer.lock`, `phpunit.xml`, `webpack.mix.js`, `package.json`, `Dockerfile` and `.env.example`
- [X] T004 [P] Move shared Nginx config from `docker/nginx/` to `infra/docker/nginx/` with `git mv` of `docker/nginx/laravel.conf`
- [X] T005 Point Compose build and volumes at the new trees in `docker-compose.yml`: `app`/`queue` context and volume `./apps/api:/var/www`; `frontend` volume `./apps/web:/app`; `nginx` volumes `./apps/api:/var/www` and `./infra/docker/nginx/:/etc/nginx/conf.d/`. Keep service names `app` and `frontend` and ports `8989` and `5173`
- [X] T006 [P] Update ignore rules in `.gitignore` for `apps/api/vendor`, `apps/api/.env`, `apps/api/storage`, `apps/web/node_modules`, `apps/web/dist` and `apps/web/.env`
- [X] T007 [P] Update ignore rules in `.dockerignore` for `apps/api/vendor`, `apps/web/node_modules` and `apps/web/dist`

**Checkpoint**: As três árvores estão nos caminhos novos e o Compose referencia `apps/` e `infra/`. Histórias podem começar.

---

## Phase 3: User Story 1 - Time encontra cada responsabilidade em uma área clara (Priority: P1) 🎯 MVP

**Goal**: A raiz expõe `apps/`, `infra/` e `docs/`. Cada área contém só o tipo de conteúdo que lhe cabe. Caminhos antigos deixam de ser casa ativa.

**Independent Test**: A partir da raiz, indicar as quatro áreas (`apps/web`, `apps/api`, `infra`, `docs`) e confirmar que interface não tem regra/persistência, serviço não tem telas, ambiente não tem código de aplicação e documentação não tem runtime.

### Implementation for User Story 1

- [X] T008 [US1] Remove leftover active copies `frontend/` and `docker/` after the moves so they are not a second home for the same content
- [X] T009 [US1] Ensure the repository root no longer hosts Laravel application files (`artisan`, `composer.json`, `app/`, `bootstrap/`, `Dockerfile`, `phpunit.xml`)
- [X] T010 [P] [US1] Confirm `apps/web/` contains only UI assets (`src/`, `package.json`, `vite.config.ts`, `.env.example`) and no Laravel or shared Compose/Nginx files
- [X] T011 [P] [US1] Confirm `apps/api/` contains the Laravel application (`app/`, `routes/`, `tests/`, `Dockerfile`) and no React/Material UI/Vite tree
- [X] T012 [P] [US1] Confirm `infra/` contains only shared runtime and data config (`infra/docker/nginx/laravel.conf`, `infra/postgres/`) and no application source
- [X] T013 [US1] Write the area index in `docs/README.md` naming `apps/web`, `apps/api`, `infra` and `docs` and pointing to the structure, environment and decisions guides

**Checkpoint**: Uma pessoa localiza as quatro áreas sem perguntar. Guias completos entram nas histórias seguintes.

---

## Phase 4: User Story 2 - Ambiente local sobe de um único ponto (Priority: P1)

**Goal**: Interface, API e dados sobem pelo `docker-compose.yml` da raiz nos endereços já conhecidos.

**Independent Test**: Seguir `docs/environment.md` num checkout limpo e abrir `http://localhost:5173` e `http://localhost:8989` na primeira tentativa.

### Implementation for User Story 2

- [X] T014 [P] [US2] Create shared database env example in `infra/postgres/.env.example` with `POSTGRES_DB=vayro`, `POSTGRES_USER=vayro` and `POSTGRES_PASSWORD=secret`
- [X] T015 [P] [US2] Keep application env examples in `apps/api/.env.example` (Laravel, `DB_HOST=pgsql`) and `apps/web/.env.example` (`VITE_API_BASE_URL=http://localhost:8989`)
- [X] T016 [US2] Wire `pgsql` to `env_file: ./infra/postgres/.env` in `docker-compose.yml` and keep the data volume at `.docker/pgsql/dbdata`
- [X] T017 [US2] Write the local environment guide in `docs/environment.md` with the single root entry point `docker-compose up -d --build` and the commands in [local-environment.md](./contracts/local-environment.md)
- [X] T018 [US2] Bring the stack up from the repository root via `docker-compose.yml` and confirm `http://localhost:5173`, `http://localhost:8989` and SPA → API with `VITE_API_BASE_URL=http://localhost:8989`

**Checkpoint**: O ambiente sobe de um único ponto e os endereços antigos respondem.

---

## Phase 5: User Story 3 - Comportamento de locação permanece o mesmo (Priority: P1)

**Goal**: Cadastro, pedido, privacidade, listas administrativas, status e quantidade do dia continuam iguais. Contrato `/api/v1` e `/api/v2` intacto.

**Independent Test**: Percorrer cadastro de locatário, um pedido válido e a consulta administrativa; o resultado observável é o de `specs/001-vehicle-rental-management/spec.md`.

### Tests for User Story 3 (existing suite — do not add new PHPUnit)

- [X] T019 [US3] Run the existing suite with `docker-compose exec app php artisan test` (working_dir `/var/www` → `apps/api`) against PostgreSQL `vayro_testing` defined in `apps/api/phpunit.xml`

### Implementation for User Story 3

- [X] T020 [P] [US3] Confirm public route prefixes are unchanged in `apps/api/routes/api.php` (`v1` and `v2`) with no renamed Portuguese resources
- [X] T021 [P] [US3] Confirm JWT and rental services were moved, not rewritten, in `apps/api/app/Services/RenterAccountService.php` and `apps/api/app/Services/RentalService.php`
- [X] T022 [US3] Walk the rental flows listed in [quickstart.md](./quickstart.md) §4 (register/login, request, own list, admin lists and daily count) against `http://localhost:5173` and `http://localhost:8989`

**Checkpoint**: SC-004 e SC-005 valem: comportamento e suíte iguais aos de antes da reorganização.

---

## Phase 6: User Story 4 - Novato sabe onde colocar trabalho novo (Priority: P2)

**Goal**: O guia de estrutura e os templates da próxima feature descrevem o layout novo. Specs de `001` permanecem como foram escritas.

**Independent Test**: Diante de tela, operação, ambiente e decisão, a pessoa aponta `apps/web`, `apps/api`, `infra`/`docker-compose.yml` e `docs/` sem criar pasta genérica no topo.

### Implementation for User Story 4

- [X] T023 [US4] Write the structure guide in `docs/structure.md` covering where to put a screen, a business operation, an environment change, an app build recipe and an architecture decision, plus the rule “do not create a new top-level area before re-evaluating responsibility”
- [X] T024 [US4] Update the root `README.md` to describe `apps/web`, `apps/api`, `infra`, `docs`, the Compose entry point and the ports `5173` / `8989`
- [X] T025 [P] [US4] Replace default layout paths in `.specify/templates/plan-template.md` so the next feature plan uses `apps/api/` and `apps/web/` instead of root `app/` and `frontend/`
- [X] T026 [P] [US4] Replace path conventions in `.specify/templates/tasks-template.md` so the next feature tasks use `apps/api/` and `apps/web/` instead of `frontend/src/`
- [X] T027 [P] [US4] Align process guidance in `.cursor/skills/testing/SKILL.md` with `docker-compose exec app php artisan test` running inside `apps/api`

**Checkpoint**: Um novato com `docs/structure.md` acerta 4 de 4 destinos. `specs/001-vehicle-rental-management/` não foi editada.

---

## Phase 7: User Story 5 - Produto cresce sem nova reorganização ampla (Priority: P3)

**Goal**: O critério de app nova e de código compartilhado está escrito. A estrutura atual não tem pastas especulativas.

**Independent Test**: Ler o critério no guia e confirmar que não existem `packages/` nem uma terceira pasta sob `apps/`.

### Implementation for User Story 5

- [X] T028 [US5] Write the boundary decision in `docs/decisions/001-monorepo-boundaries.md` explaining why `apps/web`, `apps/api`, `infra` and `docs` exist and why `packages/` and extra apps are deferred
- [X] T029 [US5] Add growth rules to `docs/structure.md`: new executable app only under `apps/` without moving `web`/`api`; shared package only with two real consumers
- [X] T030 [US5] Confirm the delivered tree has exactly `apps/web` and `apps/api`, no `packages/` directory and no speculative `apps/admin`, `apps/worker` or `apps/cli`

**Checkpoint**: SC-007 vale. A próxima aplicação futura tem um lugar, mas não foi criada agora.

---

## Phase 8: Polish & Cross-Cutting Concerns

**Purpose**: Fechar a entrega ponta a ponta sem reabrir regras de locação.

- [X] T031 [P] Point `README.md` to `docs/structure.md`, `docs/environment.md` and `docs/decisions/001-monorepo-boundaries.md`
- [X] T032 [P] Leave historical rental specs untouched in `specs/001-vehicle-rental-management/spec.md`, `plan.md`, `tasks.md` and `contracts/`
- [X] T033 Run the full validation in [quickstart.md](./quickstart.md): inspect four areas, start Compose from the root, hit `5173`/`8989` and keep `docker-compose exec app php artisan test` green

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: Sem dependências — começa imediatamente
- **Foundational (Phase 2)**: Depende do Setup — BLOQUEIA todas as histórias
- **User Story 1 (Phase 3)**: Depende do Foundational
- **User Story 2 (Phase 4)**: Depende do Foundational; na prática sequencia após US1 para não validar ambiente com cópias antigas ainda ativas
- **User Story 3 (Phase 5)**: Depende do ambiente da US2 (senão a suíte não sobe no layout novo)
- **User Story 4 (Phase 6)**: Depende das áreas da US1; pode começar depois da US1 se o guia citar o ambiente ainda em construção
- **User Story 5 (Phase 7)**: Depende do guia da US4 (`docs/structure.md`)
- **Polish (Phase 8)**: Depende das histórias que serão entregues

### User Story Dependencies

- **User Story 1 (P1)**: Depois do Foundational. Independente para inspeção de pastas.
- **User Story 2 (P1)**: Depois do Foundational. Precisa das árvores já movidas. Independente para “sobe e abre as URLs”.
- **User Story 3 (P1)**: Depois da US2. Independente como regressão de locação; não muda regra.
- **User Story 4 (P2)**: Depois da US1. Independente como guia de destinos.
- **User Story 5 (P3)**: Depois da US4. Independente como critério de crescimento.

### Within Each User Story

- Movimentos de arquivo (`git mv`) antes de limpar caminhos antigos
- Compose com caminhos novos antes de `docker-compose up`
- Env de `infra/postgres` antes de validar o `pgsql`
- Suíte existente depois do ambiente no ar
- Guias de processo depois das pastas reais existirem
- História completa antes de avançar a prioridade seguinte quando houver o mesmo arquivo

### Parallel Opportunities

- T002, T003 e T004 podem correr em paralelo (árvores distintas)
- T006 e T007 podem correr em paralelo depois de T005
- T010, T011 e T012 podem correr em paralelo (inspeções de árvores distintas)
- T014 e T015 podem correr em paralelo
- T020 e T021 podem correr em paralelo
- T025, T026 e T027 podem correr em paralelo
- T031 e T032 podem correr em paralelo
- US4 pode avançar em paralelo com US3 depois da US1, se o time estiver dividido

---

## Parallel Example: Foundational moves

```bash
# Três árvores distintas, sem overlap de arquivos:
Task: "Move the UI tree from frontend/ to apps/web/"
Task: "Move the Laravel tree into apps/api/"
Task: "Move shared Nginx config from docker/nginx/ to infra/docker/nginx/"
```

## Parallel Example: User Story 1 inspections

```bash
Task: "Confirm apps/web/ contains only UI assets"
Task: "Confirm apps/api/ contains the Laravel application"
Task: "Confirm infra/ contains only shared runtime and data config"
```

## Parallel Example: User Story 4 process guides

```bash
Task: "Replace default layout paths in .specify/templates/plan-template.md"
Task: "Replace path conventions in .specify/templates/tasks-template.md"
Task: "Align process guidance in .cursor/skills/testing/SKILL.md"
```

---

## Implementation Strategy

### MVP First (User Story 1)

1. Completar Phase 1: Setup
2. Completar Phase 2: Foundational (CRÍTICO — bloqueia as histórias)
3. Completar Phase 3: User Story 1
4. **PARAR e VALIDAR**: as quatro áreas estão visíveis e sem mistura
5. Demo interna do layout

Para o time usar o produto de novo, US2 entra no mesmo corte. A feature só pode ser chamada pronta depois da US3 (regressão). Reorganização pela metade não é entrega.

### Incremental Delivery

1. Setup + Foundational → árvores no lugar certo
2. US1 → fronteiras visíveis (MVP de organização)
3. US2 → ambiente sobe nos endereços antigos
4. US3 → locação e suíte iguais às de antes
5. US4 → novato e próxima feature usam o layout novo
6. US5 → critério de crescimento sem pastas futuras
7. Polish → quickstart completo

### Parallel Team Strategy

Com mais de uma pessoa, depois do Foundational:

1. Pessoa A: US1 (limpeza e inspeção de fronteiras)
2. Pessoa B: rascunho de `docs/environment.md` e env de `infra/postgres` (US2), integrando depois que A remover cópias antigas
3. US3 espera o Compose no ar
4. US4 e US3 podem seguir em paralelo depois da US1
5. US5 espera `docs/structure.md`

---

## Notes

- [P] = arquivos diferentes, sem depender de tarefa incompleta
- [US] liga a tarefa à história
- Não criar `packages/`, `apps/admin`, `apps/worker` nem `apps/cli`
- Não reescrever `specs/001-vehicle-rental-management/`
- Não reescrever a divisão interna do Laravel
- Commit depois de cada grupo lógico (`git mv`, Compose, docs)
- Validar em cada checkpoint; se o ambiente ou a suíte quebrar, a feature não avança
