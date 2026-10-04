# Documentação do Vayro

O repositório se organiza em quatro áreas de responsabilidade. Cada uma
contém só o tipo de conteúdo que lhe cabe.

| Área | Responsabilidade |
|---|---|
| `apps/web` | Interface que a pessoa usa (páginas, estado do cliente, chamada HTTP) |
| `apps/api` | Serviço de negócio (autenticação, regras, persistência, testes) |
| `infra` | Ambiente compartilhado de execução e dados (Nginx, PostgreSQL, orquestração via Compose da raiz) |
| `docs` | Documentação de produto, ambiente e decisões |

O ponto único de subida do ambiente local é o `docker-compose.yml` da raiz.

## Guias

- [Estrutura do repositório](./structure.md) — onde colocar tela, operação, ambiente e decisão
- [Ambiente local](./environment.md) — como subir interface, API e dados de um único ponto
- [Fronteiras do monorepo](./decisions/001-monorepo-boundaries.md) — por que essas áreas existem e quando crescer
