# Monorepo Structure & Naming

## Purpose

Define the standard monorepo structure and naming conventions for the project.

The goal is to keep responsibilities clearly separated, maintain a simple architecture, and allow the project to evolve without unnecessary restructuring.

## Technology Stack

The project uses:

* React
* TypeScript
* Material UI
* Laravel
* PostgreSQL
* Docker

Technology-specific implementation details MUST remain inside their respective application boundaries.

## Monorepo Structure

The project MUST follow this structure:

```text
<project>/
├── apps/
│   ├── web/
│   └── api/
├── infra/
├── docs/
├── docker-compose.yml
└── README.md
```

## Application Boundaries

### `apps/web`

Contains the frontend application.

Technology:

* React
* TypeScript
* Material UI

Responsibilities:

* User interface.
* Client-side routing.
* Client-side state management.
* API communication.
* Form handling.
* Frontend validation.
* UI components.

The frontend MUST NOT contain:

* Laravel code.
* Database access.
* Docker configuration.
* Backend business logic.

The directory MUST be named `web`.

### `apps/api`

Contains the backend application.

Technology:

* Laravel
* PHP

Responsibilities:

* HTTP API.
* Authentication and authorization.
* Business rules.
* Request validation.
* Application services.
* Persistence orchestration.
* Integration with external services.

The API MUST NOT contain:

* React code.
* Material UI code.
* Frontend-specific logic.
* Docker infrastructure configuration.

The directory MUST be named `api`.

### `infra`

Contains infrastructure-related configuration.

Responsibilities may include:

* Docker configuration.
* Dockerfiles.
* Container configuration.
* PostgreSQL configuration.
* Database initialization scripts.
* Deployment configuration.
* Infrastructure-specific scripts.

Infrastructure configuration MUST NOT be mixed with application source code.

### `docs`

Contains project documentation.

Examples:

* Architecture documentation.
* Business rules.
* API documentation.
* Technical decisions.
* Development guides.
* Setup instructions.

## Naming Principles

Directory names MUST represent their responsibility rather than their implementation technology.

Preferred:

```text
apps/web
apps/api
infra
docs
```

Avoid:

```text
apps/react
apps/laravel
apps/typescript
apps/postgres
apps/docker
```

The technology may change while the responsibility remains the same.

## Naming Rules

Use:

* lowercase names;
* short and descriptive names;
* responsibility-oriented names;
* kebab-case for multi-word names.

Examples:

```text
apps/web
apps/api
infra
docs
```

Multi-word directories:

```text
database-migrations
user-management
payment-integration
```

Avoid generic or ambiguous names such as:

```text
misc
stuff
temp
common
helpers
others
new
old
```

unless there is a clear and justified responsibility.

## Repository Strategy

The project is a monorepo.

The default repository MUST contain the frontend, backend, infrastructure, and documentation.

Do NOT create separate repositories for:

```text
frontend
backend
infra
database
```

unless there is an explicit architectural or organizational requirement.

The preferred structure is:

```text
<project>/
├── apps/
│   ├── web/
│   └── api/
├── infra/
└── docs/
```

## Infrastructure

Docker is the standard containerization technology.

Application-specific Dockerfiles MAY be located inside their respective applications when they are tightly coupled to that application.

Example:

```text
apps/
├── web/
│   └── Dockerfile
│
└── api/
    └── Dockerfile
```

Shared infrastructure configuration belongs in `infra/`.

Example:

```text
infra/
├── docker/
└── postgres/
```

The root `docker-compose.yml` is responsible for orchestrating the development environment.

Example:

```text
<project>/
├── apps/
│   ├── web/
│   └── api/
├── infra/
├── docker-compose.yml
└── docs/
```

## Database

PostgreSQL is the project's database.

Database-specific infrastructure configuration belongs in:

```text
infra/postgres/
```

Laravel database migrations, models, repositories, and persistence-related application code MUST remain inside:

```text
apps/api/
```

Do NOT place Laravel application code inside `infra/`.

## Future Applications

If the project introduces another independently executable application, it SHOULD be placed under `apps/`.

Example:

```text
apps/
├── web/
├── api/
└── worker/
```

Possible applications include:

```text
apps/web
apps/api
apps/worker
apps/admin
apps/cli
```

Only create a new application when it has a distinct runtime responsibility.

Do not create directories merely to organize source files.

## Shared Code

If multiple applications eventually require genuinely shared code, a `packages/` directory MAY be introduced.

Example:

```text
<project>/
├── apps/
│   ├── web/
│   └── api/
├── packages/
├── infra/
└── docs/
```

Do NOT create `packages/` preemptively.

Shared code MUST have at least two real consumers before introducing a shared package, unless there is another concrete architectural reason.

## Architectural Boundaries

Each top-level directory has a defined responsibility:

```text
apps/      → executable applications
packages/  → shared code
infra/     → infrastructure
docs/      → documentation
```

Responsibilities MUST NOT be mixed between these boundaries.

Examples:

```text
React code
→ apps/web

Laravel code
→ apps/api

PostgreSQL infrastructure
→ infra/postgres

Docker infrastructure
→ infra/docker

Shared application library
→ packages/

Architecture documentation
→ docs/
```

## Avoid Overengineering

The monorepo MUST evolve according to actual project requirements.

Do not introduce directories, applications, packages, or abstractions based only on possible future requirements.

Prefer:

```text
apps/
├── web/
└── api/
```

over prematurely introducing:

```text
apps/
├── web/
├── api/
├── admin/
├── worker/
└── cli/

packages/
├── shared/
├── types/
├── config/
├── ui/
└── utils/
```

Only introduce additional boundaries when there is a concrete need.

## Decision Rule

When deciding where a new file or directory belongs, ask:

1. Is it part of an executable application?

   * Place it under `apps/`.

2. Is it shared by multiple applications?

   * Consider `packages/`.

3. Is it infrastructure or environment configuration?

   * Place it under `infra/`.

4. Is it documentation?

   * Place it under `docs/`.

5. Does it not clearly fit any existing boundary?

   * Do NOT immediately create a new top-level directory.
   * Evaluate the responsibility and existing architectural boundaries first.

## Agent Behavior

When creating or moving files, the agent MUST:

1. Follow the directory responsibilities defined in this Skill.
2. Preserve existing architectural decisions.
3. Avoid renaming directories without a concrete reason.
4. Avoid creating new top-level directories without evaluating existing boundaries.
5. Prefer the simplest structure that correctly represents the responsibility.
6. Avoid introducing abstractions for hypothetical future requirements.
7. Keep frontend, backend, database infrastructure, and documentation responsibilities separated.
8. Identify architectural conflicts before implementing a requested change.
9. Prefer consistency with the existing monorepo structure over introducing alternative conventions.
