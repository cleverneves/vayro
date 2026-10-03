# Implementation Plan: Gerenciamento de Locação de Veículo

**Branch**: `001-vehicle-rental-management` | **Date**: 2026-10-03 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/001-vehicle-rental-management/spec.md`

## Summary

O locatário cadastra a própria conta, atualiza nome, e-mail e telefone, escolhe data de início e quantidade de dias, vê só os automóveis livres nesse período e solicita a locação com motivo e comentário opcional. O administrativo lista todos os locatários e todas as locações, altera o status na sequência fixa, registra observação e consulta quantas locações foram solicitadas no dia corrente. O locatário só enxerga as próprias locações.

A implementação acrescenta esse ciclo em `/api/v2`, com JWT já usado em `/api/v1`, regras nos services, persistência nos repositories e interface React + TypeScript + Material UI. O contrato publicado de `/api/v1` (catálogo, clientes legados e locações com diária e quilometragem) permanece. As regras de negócio desta feature são cobertas por testes de unidade dos services em PostgreSQL. Testes E2E ficam fora do escopo.

## Technical Context

**Language/Version**: PHP 8.3, Laravel 13, TypeScript no frontend

**Primary Dependencies**: Laravel, `tymon/jwt-auth` 2.x, React, Material UI, Vite

**Storage**: PostgreSQL 16. Schema novo por migration. Ciclo desta feature na tabela `rentals`. Tabelas `clientes` e `users` recebem colunas aditivas. `locacoes` não muda.

**Testing**: PHPUnit 12. Testes de unidade dos services em `tests/Unit`, com PostgreSQL `vayro_testing` no Docker. Sem testes E2E.

**Target Platform**: Ambiente local Docker Compose (PHP-FPM, Nginx, PostgreSQL, Redis, SPA)

**Project Type**: Aplicação web — API REST e SPA

**Performance Goals**: Cadastro válido em menos de 3 minutos, solicitação válida em menos de 2 minutos e consulta administrativa de locatário e locação em menos de 1 minuto (SC-001, SC-002, SC-007). Sem meta de throughput.

**Constraints**: JWT nas rotas autenticadas. Dias de 1 a 90. Comentário e observação até 500 caracteres. Data de início no dia corrente ou futura, no fuso `America/Sao_Paulo`. Listas desta versão sem busca e sem paginação. Preço, caução, quilometragem e devolução antecipada fora deste contrato. Sem testes E2E.

**Scale/Scope**: Uma operação de locadora. Dois papéis (locatário e administrativo). Catálogo de automóveis, marcas e modelos já existe e não é mantido aqui. Seis histórias de usuário.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

Gates from `.specify/memory/constitution.md` (Vayro v1.0.0). A gate that fails
without a Complexity Tracking row blocks the plan.

- **Simplicity**: O desenho cobre o ciclo da spec e preserva o CRUD legado. Não há eventos de domínio, CQRS, repositório genérico nem estado global especulativo.
- **Layers**: Rotas e controllers novos só recebem a requisição, chamam o service e devolvem API Resource ou erro. Form Requests validam formato. Services guardam as invariantes. Repositories acessam o banco. Controllers novos não consultam Eloquent nem decidem o resultado de negócio.
- **REST**: Recursos substantivos em português, sob `/api/v2`. `/api/v1` permanece compatível. Status HTTP: `422` validação, `404` recurso ausente ou locação de outro locatário, `409` conflito de domínio, `403` operação de outro papel.
- **Tests**: Cada regra de negócio deste plano tem teste de unidade do service, exercendo o resultado observável em PostgreSQL. Testes E2E não entram. A suíte Feature HTTP já existente de `/api/v1` permanece e não é o veículo das regras novas.
- **Language**: Identificadores novos em inglês (`Rental`, `RenterAccountService`). O JSON público permanece em português (`motivo`, `status`, `quantidade_dias`).
- **Stack**: Laravel, React + TypeScript + Material UI, PostgreSQL por migration, Docker Compose, JWT.
- **SOLID**: Services e repositories concretos, sem interface de implementação única.

**Gate antes da pesquisa**: aprovado. O contexto técnico não deixa `NEEDS CLARIFICATION`. A escolha de testes de unidade, em vez do padrão de Feature HTTP, está justificada no Complexity Tracking.

**Gate depois do desenho**: aprovado. `research.md`, `data-model.md`, `contracts/` e `quickstart.md` mantêm as camadas, o contrato `/api/v1` e o vocabulário português na borda nova. A mesma exceção de testes continua justificada; o desenho não introduziu outra.

## Project Structure

### Documentation (this feature)

```text
specs/001-vehicle-rental-management/
├── plan.md              # This file (/speckit-plan command output)
├── research.md          # Phase 0 output (/speckit-plan command)
├── data-model.md        # Phase 1 output (/speckit-plan command)
├── quickstart.md        # Phase 1 output (/speckit-plan command)
├── contracts/           # Phase 1 output (/speckit-plan command)
│   ├── comum.md
│   ├── locatarios.md
│   ├── carros.md
│   └── locacoes.md
└── tasks.md             # Phase 2 output (/speckit-tasks command - NOT created by /speckit-plan)
```

### Source Code (repository root)

```text
app/
├── Exceptions/
│   └── BusinessRuleException.php
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php          # login passa a incluir papel
│   │   ├── RenterController.php
│   │   └── RentalController.php
│   ├── Middleware/
│   │   └── EnsureUserRole.php
│   ├── Requests/
│   │   ├── RegisterRenterRequest.php
│   │   ├── UpdateRenterProfileRequest.php
│   │   ├── ListAvailableVehiclesRequest.php
│   │   ├── StoreRentalRequest.php
│   │   └── UpdateRentalRequest.php
│   └── Resources/
│       ├── UserResource.php            # /api/v1/me passa a incluir papel
│       ├── RenterResource.php
│       ├── AvailableVehicleResource.php
│       └── RentalResource.php
├── Models/
│   └── Rental.php
├── Repositories/
│   ├── RenterRepository.php
│   ├── VehicleRepository.php
│   └── RentalRepository.php
└── Services/
    ├── RenterAccountService.php
    └── RentalService.php
config/
└── rental.php                 # fuso do dia corrente da operação
database/migrations/          # users.role, clientes aditivos, tabela rentals
routes/api.php                # grupo /api/v2; rotas /api/v1 já publicadas permanecem
tests/Unit/Services/
├── RenterAccountServiceTest.php
└── RentalServiceTest.php
frontend/
├── package.json
├── vite.config.ts
└── src/
    ├── api/
    ├── auth/
    ├── pages/
    └── theme/
docker-compose.yml            # serviço frontend, porta 5173
```

**Structure Decision**: A API continua na raiz Laravel. O ciclo novo entra pelas camadas já definidas na constituição, em classes com nome em inglês, sem reescrever `MarcaController`, `ModeloController`, `CarroController`, `ClienteController` nem `LocacaoController`. A SPA fica em `frontend/` e sobe no Compose ao lado de Nginx, PostgreSQL e Redis. O Nginx existente segue servindo a API em `localhost:8989`. A SPA em desenvolvimento escuta `localhost:5173` e chama a API com `Authorization: Bearer`.

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| As regras novas são verificadas por testes de unidade dos services, e esta feature não ganha Feature HTTP nem suíte E2E. A constituição indica Feature HTTP como padrão quando a regra é observável pela API. | O pedido deste plano exige testes de unidade das regras de negócio e exclui testes E2E. O teste chama o service, persiste em PostgreSQL e afirma o resultado (status, disponibilidade, contagem, recusa). | Feature HTTP repetiria a mesma regra através do controller. E2E atravessaria o browser, o que este plano exclui. O comportamento continua testado fora de métodos triviais. |
