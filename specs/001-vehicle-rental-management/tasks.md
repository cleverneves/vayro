# Tasks: Gerenciamento de Locação de Veículo

**Input**: Design documents from `/specs/001-vehicle-rental-management/`

**Prerequisites**: [plan.md](./plan.md), [spec.md](./spec.md), [research.md](./research.md), [data-model.md](./data-model.md), [contracts/](./contracts/), [quickstart.md](./quickstart.md)

**Tests**: Regras de negócio desta feature são cobertas por testes de unidade dos services em `tests/Unit/Services`, com `RefreshDatabase` e PostgreSQL `vayro_testing`. Sem Feature HTTP novo e sem E2E (exceção justificada no Complexity Tracking de [plan.md](./plan.md)). A suíte Feature de `/api/v1` permanece e deve continuar verde.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

## Path Conventions

- **Vayro API**: `app/Http/Controllers/`, `app/Http/Requests/`, `app/Http/Resources/`, `app/Services/`, `app/Repositories/`, `app/Models/`, `routes/api.php`, `tests/Unit/Services/`
- **Vayro UI**: `frontend/src/`
- Vocabulário interno em inglês (`Rental`, `renter`, `requested`); JSON público em português (`motivo`, `status`, `solicitada`) — ver [research.md](./research.md) e [contracts/comum.md](./contracts/comum.md)

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Diretórios e esqueleto da SPA. A API Laravel já existe; não reinstalar o framework.

- [X] T001 Create empty layer directories `app/Services/`, `app/Repositories/`, `app/Support/` and `tests/Unit/Services/`
- [X] T002 [P] Scaffold Vite + React + TypeScript + Material UI in `frontend/package.json`, `frontend/vite.config.ts` and `frontend/src/`
- [X] T003 [P] Add Compose service `frontend` on port 5173 in `docker-compose.yml`
- [X] T004 [P] Create operation timezone config (`America/Sao_Paulo`) in `config/rental.php`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Schema, models, autenticação aditiva, middleware de papel e grupo `/api/v2`. Bloqueia todas as histórias.

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [X] T005 [P] Add `users.role` (`renter`|`admin`, default `admin`) in `database/migrations/*_add_role_to_users_table.php`
- [X] T006 [P] Widen `clientes.nome` to 120 and add `user_id`, `email`, `phone` in `database/migrations/*_add_renter_account_columns_to_clientes_table.php`
- [X] T007 [P] Create `rentals` table with overlap index in `database/migrations/*_create_rentals_table.php`
- [X] T008 Update `User` fillable, casts and `cliente()` relation in `app/Models/User.php`
- [X] T009 [P] Update `Cliente` fillable, casts and `user()`/`rentals()` relations in `app/Models/Cliente.php`
- [X] T010 [P] Create `Rental` model with relations, casts and period helpers in `app/Models/Rental.php`
- [X] T011 [P] Add `rentals()` relation on `Carro` in `app/Models/Carro.php` without changing catalog fields
- [X] T012 Add `role` default `admin` and `renter()` state in `database/factories/UserFactory.php`
- [X] T013 [P] Add `user_id`, `email`, `phone` and widen `nome` in `database/factories/ClienteFactory.php`
- [X] T014 [P] Create `RentalFactory` with `requested` defaults in `database/factories/RentalFactory.php`
- [X] T015 Create `BusinessRuleException` with domain codes in `app/Exceptions/BusinessRuleException.php`
- [X] T016 [P] Create `EnsureUserRole` and register alias `role` in `app/Http/Middleware/EnsureUserRole.php` and `app/Http/Kernel.php`
- [X] T017 Add additive `papel` (`locatario`|`administrativo`) in `app/Http/Resources/UserResource.php` and login payload in `app/Http/Controllers/AuthController.php`
- [X] T018 [P] Create Portuguese/English vocabulary mapper in `app/Support/RentalVocabulary.php`
- [X] T019 Add empty `/api/v2` route group in `routes/api.php` without changing `/api/v1`

**Checkpoint**: Foundation ready — migrations apply, JWT login still works, `papel` is additive, `/api/v2` group exists

---

## Phase 3: User Story 1 - Locatário cadastra e atualiza o próprio perfil (Priority: P1) 🎯 MVP

**Goal**: Pessoa cria conta de Cliente Locatário e, autenticada, altera o próprio nome, e-mail e telefone.

**Independent Test**: Cadastrar locatário válido, entrar com a conta, alterar os três dados e confirmar o perfil novo. E-mail repetido e edição de outro locatário são recusados.

### Tests for User Story 1 (REQUIRED for business rules) ⚠️

- [X] T020 [P] [US1] Unit tests for register, unique email, own profile update and foreign profile refusal in `tests/Unit/Services/RenterAccountServiceTest.php`

### Implementation for User Story 1

- [X] T021 [P] [US1] Implement `RenterRepository` create/find/update and unique-email lookup in `app/Repositories/RenterRepository.php`
- [X] T022 [US1] Implement `RenterAccountService` transactional register and self-update in `app/Services/RenterAccountService.php` (depends on T021)
- [X] T023 [P] [US1] Create `RegisterRenterRequest` (nome, email, telefone, senha) in `app/Http/Requests/RegisterRenterRequest.php`
- [X] T024 [P] [US1] Create `UpdateRenterProfileRequest` (nome, email, telefone; no senha/papel) in `app/Http/Requests/UpdateRenterProfileRequest.php`
- [X] T025 [P] [US1] Create `RenterResource` (`id`, `nome`, `email`, `telefone`) in `app/Http/Resources/RenterResource.php`
- [X] T026 [US1] Implement `store`, `me` and `updateMe` in `app/Http/Controllers/RenterController.php` and routes `POST /api/v2/locatarios`, `GET|PATCH /api/v2/locatarios/me` in `routes/api.php`
- [X] T027 [P] [US1] Create API client and JWT/`papel` session module in `frontend/src/api/client.ts` and `frontend/src/auth/session.ts`
- [X] T028 [US1] Create register, login and profile screens in `frontend/src/pages/RegisterPage.tsx`, `frontend/src/pages/LoginPage.tsx` and `frontend/src/pages/ProfilePage.tsx` (depends on T027)

**Checkpoint**: Cadastro, login v1, perfil próprio e recusa de e-mail/titularidade funcionam de forma independente

---

## Phase 4: User Story 2 - Locatário solicita uma locação (Priority: P1)

**Goal**: Locatário informa data de início e dias, vê só automóveis livres no período e solicita locação com motivo e comentário opcional em status `requested`.

**Independent Test**: Com locatário autenticado e catálogo existente, filtrar livres no período, gravar locação com motivo e confirmar status Solicitada na resposta `201`.

### Tests for User Story 2 (REQUIRED for business rules) ⚠️

- [X] T029 [P] [US2] Unit tests for available list, create `requested`, optional comment, invalid period/reason, overlap (1-day vs 2-day), cancelled/completed not reserving, and concurrent lock in `tests/Unit/Services/RentalServiceTest.php`

### Implementation for User Story 2

- [X] T030 [P] [US2] Implement `VehicleRepository` available-in-period query (ignore `carros.disponivel`) in `app/Repositories/VehicleRepository.php`
- [X] T031 [P] [US2] Implement `RentalRepository` create, overlapping-reserving query and `lockForUpdate` on `carros` in `app/Repositories/RentalRepository.php`
- [X] T032 [US2] Implement `listAvailableVehicles` and `createForRenter` in `app/Services/RentalService.php` (depends on T030, T031)
- [X] T033 [P] [US2] Create `ListAvailableVehiclesRequest` (`data_inicio`, `quantidade_dias`) in `app/Http/Requests/ListAvailableVehiclesRequest.php`
- [X] T034 [P] [US2] Create `StoreRentalRequest` (`automovel_id`, `data_inicio`, `quantidade_dias`, `motivo`, `comentario`) in `app/Http/Requests/StoreRentalRequest.php`
- [X] T035 [P] [US2] Create `AvailableVehicleResource` (placa, modelo, marca; no `disponivel`/km/preço) in `app/Http/Resources/AvailableVehicleResource.php`
- [X] T036 [P] [US2] Create `RentalResource` with período, motivo, status and observação in Portuguese in `app/Http/Resources/RentalResource.php`
- [X] T037 [US2] Implement `available` and `store` in `app/Http/Controllers/RentalController.php` and routes `GET /api/v2/carros/disponiveis`, `POST /api/v2/locacoes` in `routes/api.php`
- [X] T038 [US2] Create period + available vehicles + request form screens in `frontend/src/pages/AvailableVehiclesPage.tsx` and `frontend/src/pages/NewRentalPage.tsx`

**Checkpoint**: Solicitação válida nasce `solicitada`; período inválido ou automóvel reservado não grava linha

---

## Phase 5: User Story 3 - Locatário vê somente as próprias locações (Priority: P1)

**Goal**: Locatário lista e abre só as próprias locações. Id de outro locatário ou inexistente vira `not_found` sem conteúdo.

**Independent Test**: Criar locações para dois locatários e confirmar que cada um vê apenas as suas na lista e recebe `not_found` no detalhe da outra.

### Tests for User Story 3 (REQUIRED for business rules) ⚠️

- [X] T039 [US3] Unit tests for owner list, owner show, foreign/missing id as `not_found`, and empty list in `tests/Unit/Services/RentalServiceTest.php`

### Implementation for User Story 3

- [X] T040 [US3] Implement `listForRenter` and `showForRenter` (filter `renter_id`, never leak fields) in `app/Services/RentalService.php` and `app/Repositories/RentalRepository.php`
- [X] T041 [US3] Expose `GET /api/v2/locacoes` and `GET /api/v2/locacoes/{locacao}` for renter in `app/Http/Controllers/RentalController.php` and `routes/api.php`
- [X] T042 [US3] Create renter rental list and detail screens in `frontend/src/pages/MyRentalsPage.tsx` and `frontend/src/pages/RentalDetailPage.tsx`

**Checkpoint**: Isolamento entre locatários vale na lista e no detalhe; lista vazia não mostra locação alheia

---

## Phase 6: User Story 4 - Administrativo consulta locatário e locação (Priority: P2)

**Goal**: Administrativo vê todos os locatários e todas as locações (qualquer status/data), abre detalhe de cada um. Locatário recebe `forbidden` nessas listas administrativas.

**Independent Test**: Com vários locatários (com e sem locação) e locações de status/datas diferentes, as duas listas trazem todos os itens e o detalhe mostra os campos do contrato. Sem registros, lista vazia.

### Tests for User Story 4 (REQUIRED for business rules) ⚠️

- [X] T043 [US4] Unit tests for admin renter list/show, admin rental list/show, empty collections and renter `forbidden` in `tests/Unit/Services/RenterAccountServiceTest.php` and `tests/Unit/Services/RentalServiceTest.php`

### Implementation for User Story 4

- [X] T044 [US4] Implement admin `listRenters` and `showRenter` in `app/Services/RenterAccountService.php` and `app/Repositories/RenterRepository.php`
- [X] T045 [US4] Implement admin `listAll` and `showForAdmin` in `app/Services/RentalService.php` and `app/Repositories/RentalRepository.php`
- [X] T046 [US4] Expose `GET /api/v2/locatarios` and `GET /api/v2/locatarios/{locatario}` (register `me` before `{locatario}`) in `app/Http/Controllers/RenterController.php` and `routes/api.php`
- [X] T047 [US4] Branch `index`/`show` of `GET /api/v2/locacoes` by role in `app/Http/Controllers/RentalController.php`
- [X] T048 [US4] Create admin renter and rental list/detail screens in `frontend/src/pages/AdminRentersPage.tsx`, `frontend/src/pages/AdminRenterDetailPage.tsx` and `frontend/src/pages/AdminRentalsPage.tsx`

**Checkpoint**: Administrativo enxerga o conjunto completo; locatário é recusado nas listas administrativas

---

## Phase 7: User Story 5 - Administrativo atualiza status e observação (Priority: P2)

**Goal**: Administrativo avança status na sequência fixa e grava observação. Locatário dono vê o resultado; locatário não altera a locação depois de criada.

**Independent Test**: Partindo de Solicitada, confirmar, gravar observação e reler como dono com o mesmo status e texto. Transição ilegal e status final recusam a operação inteira.

### Tests for User Story 5 (REQUIRED for business rules) ⚠️

- [X] T049 [US5] Unit tests for allowed transitions, cancelled releasing the vehicle, note-only update, empty note, illegal transition rolling back note, closed rental, and renter cannot update in `tests/Unit/Services/RentalServiceTest.php`

### Implementation for User Story 5

- [X] T050 [US5] Implement atomic `updateByAdmin` (status sequence + `admin_note` rules) in `app/Services/RentalService.php` and `app/Repositories/RentalRepository.php`
- [X] T051 [P] [US5] Create `UpdateRentalRequest` (`status` and/or `observacao`, at least one required, max 500) in `app/Http/Requests/UpdateRentalRequest.php`
- [X] T052 [US5] Expose `PATCH /api/v2/locacoes/{locacao}` (admin only, `403` for renter) in `app/Http/Controllers/RentalController.php` and `routes/api.php`
- [X] T053 [US5] Add status/observation form on admin rental detail in `frontend/src/pages/AdminRentalDetailPage.tsx`

**Checkpoint**: Sequência de status e observação funcionam; `completed`/`cancelled` são finais; dono vê o mesmo estado

---

## Phase 8: User Story 6 - Administrativo vê quantas locações houve no dia (Priority: P3)

**Goal**: Um número: quantas linhas têm `requested_on` igual ao dia corrente em `America/Sao_Paulo`, qualquer status e qualquer `starts_on`.

**Independent Test**: Solicitar hoje uma cancelada e uma com início futuro; solicitar ontem uma que começa hoje; a quantidade do dia conta só as duas de hoje.

### Tests for User Story 6 (REQUIRED for business rules) ⚠️

- [X] T054 [US6] Unit tests for today's count including cancelled and future start, excluding other days, zero, and renter `forbidden` in `tests/Unit/Services/RentalServiceTest.php`

### Implementation for User Story 6

- [X] T055 [US6] Implement `countRequestedOnCurrentDay` using `config/rental.php` timezone in `app/Services/RentalService.php` and `app/Repositories/RentalRepository.php`
- [X] T056 [US6] Expose `GET /api/v2/locacoes/quantidade-do-dia` before `/{locacao}` in `app/Http/Controllers/RentalController.php` and `routes/api.php`
- [X] T057 [US6] Show daily count in the admin area in `frontend/src/pages/AdminDailyCountPage.tsx`

**Checkpoint**: Contagem do dia coincide com `requested_on` de hoje; locatário recebe `403` sem o número

---

## Phase 9: Polish & Cross-Cutting Concerns

**Purpose**: Navegação por papel, regressão de `/api/v1` e validação do quickstart

- [ ] T058 [P] Add MUI theme and role-based navigation (hide admin routes from renter) in `frontend/src/theme/index.ts` and `frontend/src/App.tsx`
- [ ] T059 Confirm existing `/api/v1` Feature suite stays green in `tests/Feature/`
- [ ] T060 Run `docker-compose exec app php artisan test --testsuite=Unit` per `specs/001-vehicle-rental-management/quickstart.md`
- [ ] T061 [P] Document frontend port 5173, Compose service and unit-test command in `README.md`
- [ ] T062 Walk through manual API checks in `specs/001-vehicle-rental-management/quickstart.md` against `specs/001-vehicle-rental-management/contracts/`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — can start immediately
- **Foundational (Phase 2)**: Depends on Setup — BLOCKS all user stories
- **User Stories (Phase 3+)**: All depend on Foundational
  - US1 (P1) and US2 (P1) can proceed in parallel after Phase 2 (services and files distintos)
  - US3 depends on US2 create (`rentals` com `renter_id`)
  - US4 depends on US1 (locatários) and US2 (locações) for a meaningful independent test
  - US5 depends on US2 (locação existente) and should reuse US3/US4 show
  - US6 depends on US2 (`requested_on`)
- **Polish (Phase 9)**: Depends on the stories chosen for delivery

### User Story Dependencies

- **User Story 1 (P1)**: After Phase 2 — no dependency on other stories
- **User Story 2 (P1)**: After Phase 2 — uses catálogo legado (`carros`/`modelos`/`marcas`); não depende de US1 no service se o ator `renter` já existir via factory
- **User Story 3 (P1)**: After US2 — isolation só é observável com locações criadas
- **User Story 4 (P2)**: After US1 + US2 — listas administrativas leem as mesmas tabelas
- **User Story 5 (P2)**: After US2 — transições partem de uma locação `requested`
- **User Story 6 (P3)**: After US2 — contagem lê `requested_on`

### Within Each User Story

- Business-rule unit tests MUST exist before the story is complete
- Models and migrations (Phase 2) before repositories
- Repositories before services
- Services before controllers and routes
- Form Requests traduzem português → inglês; Resources traduzem inglês → português
- Controllers traduzem `BusinessRuleException` (`email_taken`→422, `vehicle_unavailable`/`invalid_transition`/`rental_closed`→409, `not_found`→404, `forbidden`→403)
- Frontend de uma história pode acompanhar o contrato HTTP dessa história

### Parallel Opportunities

- T002, T003, T004 in Setup
- T005–T007 migrations; T009–T011 models; T013–T014 factories; T016 and T018 in Foundational
- After Phase 2: US1 (T020–T028) and US2 (T029–T038) on different files
- T023–T025 in parallel; T033–T036 in parallel
- Do **not** edit `tests/Unit/Services/RentalServiceTest.php` in parallel across US2–US6
- Do **not** edit `app/Services/RentalService.php`, `app/Repositories/RentalRepository.php`, `app/Http/Controllers/RentalController.php` or `routes/api.php` in parallel across US2–US6

---

## Parallel Example: User Story 1

```bash
# Testes de regra (arquivo próprio):
Task: "T020 Unit tests for RenterAccountService in tests/Unit/Services/RenterAccountServiceTest.php"

# HTTP de borda em paralelo (arquivos distintos):
Task: "T023 RegisterRenterRequest in app/Http/Requests/RegisterRenterRequest.php"
Task: "T024 UpdateRenterProfileRequest in app/Http/Requests/UpdateRenterProfileRequest.php"
Task: "T025 RenterResource in app/Http/Resources/RenterResource.php"

# Persistência em paralelo ao HTTP:
Task: "T021 RenterRepository in app/Repositories/RenterRepository.php"
```

## Parallel Example: User Story 2

```bash
# Repositories e Form Requests/Resources em paralelo:
Task: "T030 VehicleRepository in app/Repositories/VehicleRepository.php"
Task: "T031 RentalRepository in app/Repositories/RentalRepository.php"
Task: "T033 ListAvailableVehiclesRequest in app/Http/Requests/ListAvailableVehiclesRequest.php"
Task: "T034 StoreRentalRequest in app/Http/Requests/StoreRentalRequest.php"
Task: "T035 AvailableVehicleResource in app/Http/Resources/AvailableVehicleResource.php"
Task: "T036 RentalResource in app/Http/Resources/RentalResource.php"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup
2. Complete Phase 2: Foundational (CRITICAL — blocks all stories)
3. Complete Phase 3: User Story 1
4. **STOP and VALIDATE**: `RenterAccountServiceTest` verde; cadastro + `PATCH /locatarios/me` + login `/api/v1`
5. Deploy/demo if ready

### Incremental Delivery

1. Setup + Foundational → foundation ready
2. US1 → conta do locatário (MVP)
3. US2 → solicitação de locação
4. US3 → isolamento entre locatários
5. US4 → consulta administrativa
6. US5 → ciclo de status
7. US6 → quantidade do dia
8. Cada história acrescenta valor sem reabrir o contrato de `/api/v1`

### Parallel Team Strategy

With multiple developers:

1. Team completes Setup + Foundational together
2. Once Foundational is done:
   - Developer A: User Story 1 (depois US4 renters)
   - Developer B: User Story 2 (depois US3, US5, US6 no mesmo `RentalService`)
3. Integrar em `routes/api.php` e `RentalController` de forma sequencial para evitar conflito de arquivo

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story for traceability
- Each user story should be independently completable and testable
- Business-rule unit tests are required before the story is done
- Commit after each task or logical group
- Stop at any checkpoint to validate story independently
- Do not rewrite `MarcaController`, `ModeloController`, `CarroController`, `ClienteController`, `LocacaoController` or table `locacoes`
- Avoid: Feature HTTP novo para `/api/v2`, testes E2E, busca/paginação nesta versão, preço/caução/km
