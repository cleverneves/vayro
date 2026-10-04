# Research: Rearquitetura do Projeto

**Branch**: `002-project-rearchitecture` | **Date**: 2026-10-03

Todas as decisões abaixo resolvem o Technical Context e alinham a spec
`002-project-rearchitecture` à skill `.cursor/skills/monorepo-structure`.
Nenhum item permanece como NEEDS CLARIFICATION.

## 1. Layout-alvo do repositório

**Decision**: Adotar o monorepo por responsabilidade, sem pastas nomeadas pela
tecnologia:

```text
vayro/
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

**Rationale**: A spec exige áreas distintas para aplicações, ambiente e
documentação, com nomes curtos em minúsculas que descrevem responsabilidade.
A skill do monorepo já fixou esses nomes. Um único repositório evita a
tentação de separar frontend, backend e infra em repositórios distintos.

**Alternatives considered**:

- Manter Laravel na raiz e só mover o frontend (`frontend/` → `apps/web`):
  deixa o serviço de negócio misturado à raiz e falha FR-005, FR-018 e FR-020.
- Nomear `apps/react` e `apps/laravel`: descreve tecnologia, proibido pela
  spec e pela skill.
- Criar repositórios separados: contradiz FR-001 e a estratégia de monorepo.

## 2. Mapeamento spec → pastas

**Decision**: Traduzir as entidades da spec para os nomes da skill, sem criar
sinônimos extras no disco.

| Entidade da spec | Pasta |
|---|---|
| Área de aplicações executáveis | `apps/` |
| Interface com o usuário | `apps/web/` |
| Serviço de negócio | `apps/api/` |
| Ambiente de execução | `infra/` |
| Configuração compartilhada de execução | `infra/docker/` |
| Configuração compartilhada de dados | `infra/postgres/` |
| Documentação do produto | `docs/` |
| Ponto único de orquestração | `docker-compose.yml` na raiz |
| Receita de construção da API | `apps/api/Dockerfile` |
| Receita de construção da interface | `apps/web/package.json` e `apps/web/vite.config.ts` |
| Processo de especificação | `.specify/`, `specs/`, `.cursor/` na raiz |

**Rationale**: A spec é agnóstica a pastas; o plano precisa de nomes concretos.
Usar os nomes já acordados na skill evita um segundo vocabulário.

**Alternatives considered**:

- `apps/frontend` e `apps/backend`: ainda descrevem papel técnico, não o
  vocabulário estável `web` / `api`.
- Colocar `.specify/` dentro de `docs/`: a spec manda o processo permanecer
  na raiz e não ser tratado como aplicação (FR-019).

## 3. Movimentação do Laravel sem reescrita interna

**Decision**: Mover a árvore Laravel atual para `apps/api/` com `git mv`,
preservando a divisão interna existente (`app/`, `bootstrap/`, `config/`,
`database/`, `routes/`, `tests/`, `composer.json`, `phpunit.xml`, `artisan`).
Não reescrever controllers, services nem repositories nesta feature.

Arquivos Laravel hoje na raiz que acompanham a API:

- `app/`, `bootstrap/`, `config/`, `database/`, `lang/`, `public/`,
  `resources/`, `routes/`, `storage/`, `tests/`
- `artisan`, `composer.json`, `composer.lock`, `phpunit.xml`
- `webpack.mix.js`, `package.json` da raiz (pipeline Mix legado da API)
- `Dockerfile` (receita de construção da API)
- `.env.example` da aplicação Laravel
- `.styleci.yml` permanece na raiz como configuração de processo do
  repositório, não como código da API

**Rationale**: FR-021 proíbe mudar a divisão interna do serviço. A constituição
trata controllers que ainda falam com Eloquent como legado: só se alinha o
fluxo quando ele for alterado por outra feature. Esta entrega só muda o
lugar dos arquivos.

**Alternatives considered**:

- Aproveitar a mudança para extrair services/repositories nos controllers
  legados: viola FR-021 e o princípio de menor mudança.
- Deixar `composer.json` e `artisan` na raiz com path mapping: mantém o
  serviço misturado à orquestração e falha FR-018.

## 4. Movimentação da interface

**Decision**: Mover `frontend/` para `apps/web/` com `git mv`, sem alterar
páginas, tema, cliente HTTP nem sessão JWT. Atualizar apenas caminhos de
volume no Compose, `.gitignore` e `.dockerignore`.

Não criar `apps/web/Dockerfile` nesta entrega. A receita de construção
continua sendo Vite + `package.json`; o Compose segue usando a imagem
`node:22-alpine` com `npm run dev`.

**Rationale**: A interface já existe e a spec manda recolocá-la, não
recreá-la. Um Dockerfile só para espelhar o `npm run dev` atual é
abstração sem problema presente.

**Alternatives considered**:

- Recriar a SPA do zero em `apps/web/`: custo sem ganho observável.
- Adicionar Dockerfile da web “para o futuro”: overengineering; a skill
  permite Dockerfile por aplicação só quando ele for acoplado de fato.

## 5. Orquestração Docker e fronteira de `infra/`

**Decision**:

- `docker-compose.yml` permanece na raiz (FR-022, skill).
- Nomes dos serviços Compose (`app`, `frontend`, `nginx`, `pgsql`, `redis`,
  `queue`) e dos containers (`vayro-app`, `vayro-frontend`, …) permanecem.
- Portas publicadas permanecem `8989` (API via Nginx) e `5173` (SPA).
- Volume da API: `./apps/api:/var/www`. `working_dir` continua `/var/www`.
- Volume da web: `./apps/web:/app`.
- Nginx: config em `infra/docker/nginx/laravel.conf`; `root` continua
  `/var/www/public` porque o volume da API já aponta para `apps/api`.
- Build da API: `context: ./apps/api`, `dockerfile: Dockerfile`.
- Volume de dados do PostgreSQL permanece em `.docker/pgsql/dbdata` (já
  ignorado pelo Git) para não forçar migração de dados locais.
- `infra/postgres/.env.example` documenta `POSTGRES_DB`, `POSTGRES_USER` e
  `POSTGRES_PASSWORD`. O serviço `pgsql` lê esse arquivo via `env_file`
  depois da cópia para `infra/postgres/.env`.
- Não criar scripts de init do PostgreSQL que hoje não existem.

**Rationale**: FR-011 e a US2 exigem os mesmos endereços. FR-008 exige que
execução e dados compartilhados morem em `infra/`, não em `apps/*`. Manter
os nomes dos serviços Compose evita reescrever o hábito `docker-compose exec
app` além do ajuste de caminhos. Preservar o volume `.docker/` evita perda
de banco local.

**Alternatives considered**:

- Renomear serviços Compose para `api` e `web`: alinhamento cosmético com
  custo em todo comando documentado, sem mudar endereço de usuário.
- Colocar o Compose em `infra/docker-compose.yml`: a spec manda o ponto
  único na raiz.
- Mover o Dockerfile da API para `infra/docker/`: a skill manda receita
  acoplada à aplicação ficar com a aplicação.
- Bind-mount do repositório inteiro em `/var/www` como hoje: depois da
  mudança, o `public/` do Laravel deixaria de estar em `/var/www/public`.

## 6. Variáveis de ambiente

**Decision**: Separar três casas, sem `.env` compartilhado “único para tudo”:

1. `apps/api/.env.example` — Laravel (`APP_KEY`, JWT, `DB_HOST=pgsql`,
   `DB_*` com os mesmos defaults).
2. `apps/web/.env.example` — `VITE_API_BASE_URL=http://localhost:8989`.
3. `infra/postgres/.env.example` — credenciais do serviço `pgsql`.

O Compose na raiz orquestra; não guarda receita de construção. O `.env` da
raiz deixa de ser o `.env` do Laravel.

**Rationale**: FR-022 e a clarificação da spec: cada aplicação guarda só o
que é dela; dados compartilhados ficam no ambiente.

**Alternatives considered**:

- Continuar com um único `.env` na raiz para Laravel e Compose: mistura
  configuração da API com orquestração e falha FR-018.
- Fazer o `pgsql` ler `apps/api/.env`: coloca configuração de dados dentro
  da aplicação e falha FR-008.

## 7. Código compartilhado e aplicações futuras

**Decision**: Não criar `packages/`. Não criar `apps/admin`, `apps/worker`
nem `apps/cli`. O guia em `docs/` registra o critério: nova aplicação só
com responsabilidade de execução distinta; pacote compartilhado só com
dois consumidores reais.

**Rationale**: FR-016, FR-017, SC-007 e a skill (“do not create `packages/`
preemptively”).

**Alternatives considered**:

- Extrair tipos ou cliente HTTP para `packages/` agora: um único consumidor
  real (a web). A skill manda o código permanecer na aplicação dona.

## 8. Documentação e guias de processo

**Decision**: Criar documentação nova em `docs/` e atualizar os guias que a
próxima feature usa. Não reescrever `specs/001-vehicle-rental-management/`.

Documentos novos:

- `docs/structure.md` — fronteiras, regra de decisão, critério de app nova
  e de código compartilhado.
- `docs/environment.md` — como subir o ambiente pelo Compose da raiz.
- `docs/decisions/001-monorepo-boundaries.md` — por que essas fronteiras.

Atualizar:

- `README.md` da raiz (layout, caminhos, comandos).
- `.specify/templates/plan-template.md` e
  `.specify/templates/tasks-template.md` (trocar `frontend/` e `app/` na
  raiz por `apps/web/` e `apps/api/`).
- `.cursor/skills/testing/SKILL.md` se citar caminhos da raiz.
- `.gitignore` e `.dockerignore`.

**Rationale**: FR-014 e a clarificação da sessão: README, docs novas e guias
da próxima feature acompanham o layout; specs históricas de locação ficam
como registro da época.

**Alternatives considered**:

- Reescrever o plano e as tasks de `001` para os caminhos novos: apaga o
  histórico e viola a clarificação.
- Deixar só o README: a US4 exige um guia de estrutura que um novato
  consiga seguir sem perguntar.

## 9. Contrato público e comportamento de locação

**Decision**: Nenhum endpoint, campo, status HTTP ou vocabulário público
muda. A SPA continua em `http://localhost:5173` e chama
`http://localhost:8989` com `Authorization: Bearer`. A suíte PHPUnit
existente (Feature de `/api/v1` e Unit das regras de `/api/v2`) deve
permanecer verde após o movimento.

**Rationale**: FR-011, FR-012, FR-013, SC-004, SC-005 e o princípio REST da
constituição.

**Alternatives considered**:

- Aproveitar para versionar de novo ou renomear recursos para inglês:
  incompatível com o contrato já publicado.

## 10. Estratégia de teste desta feature

**Decision**: Não inventar suíte nova de “teste de pastas”. A prova
automatizada desta feature é a suíte já existente rodando de dentro do
container `app` (cujo `working_dir` passa a ser `apps/api`). A prova
estrutural e de ambiente é o `quickstart.md`: inspeção das quatro áreas,
`docker-compose up` na raiz e checagem dos endereços `8989` e `5173`.

**Rationale**: A constituição pede teste da regra de negócio observável.
As regras novas são de fronteira de repositório, não de locação. Recriar
PHPUnit só para assertar existência de diretório é teste de detalhe
interno. As regras de locação já têm testes; quebrá-las falha SC-005.

**Alternatives considered**:

- Script na raiz que lista pastas e falha se `packages/` existir: útil, mas
  é ferramenta extra sem consumidor além desta feature. O quickstart cobre
  o mesmo critério com menos superfície.

## 11. Idioma e stack

**Decision**: Identificadores novos de arquivo e de pasta de código em
inglês (`apps`, `web`, `api`, `infra`, `docs`). Textos de `docs/` e README
em português do Brasil. Nenhuma biblioteca, framework ou banco novo.

**Rationale**: Constituição V (idioma) e seção de stack. A skill já usa
esses nomes em inglês porque descrevem responsabilidade estável.

**Alternatives considered**:

- `aplicacoes/interface` e `aplicacoes/servico`: descrevem responsabilidade
  em português, mas quebram a convenção já ratificada na skill e tornam o
  layout diferente de qualquer guia futuro do time.
