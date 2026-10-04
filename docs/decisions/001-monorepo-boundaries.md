# 001 — Fronteiras do monorepo

**Status**: aceita  
**Data**: 2026-10-03

## Contexto

O produto vivia com a interface em `frontend/`, o Laravel na raiz e o
Nginx em `docker/`. Essa mistura obrigava a pessoa a perguntar onde
colocar tela, operação, ambiente e documentação. A reorganização precisava
separar responsabilidades sem mudar regras de locação nem o contrato HTTP
já publicado.

## Decisão

O repositório passa a ter quatro áreas:

| Área | Por que existe |
|---|---|
| `apps/web` | Interface executável. A pessoa usa esta aplicação. |
| `apps/api` | Serviço executável. Autentica, aplica regras e persiste. |
| `infra` | Ambiente compartilhado. Faz o produto subir e guardar dados. |
| `docs` | Documentação de produto, ambiente e decisões. Sem runtime. |

O `docker-compose.yml` permanece na raiz como único ponto de subida.
O processo Speckit (`.specify/`, `specs/`, `.cursor/`) permanece na raiz
e não é tratado como aplicação.

`packages/` e aplicações extras (`apps/admin`, `apps/worker`, `apps/cli`)
ficam adiadas. Não há dois consumidores reais de código compartilhado e
não há uma terceira unidade com runtime próprio.

## Consequências

- Trabalho novo tem destino previsível sem criar pasta genérica no topo.
- Uma aplicação futura entra em `apps/` sem mover `web` nem `api`.
- Um pacote compartilhado só nasce com dois consumidores reais.
- Specs históricas de locação em `specs/001-vehicle-rental-management/`
  permanecem como registro da época e não são reescritas.
