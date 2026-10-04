# Quickstart: Rearquitetura do Projeto

**Branch**: `002-project-rearchitecture`
**Goal**: provar que as quatro áreas existem, o ambiente sobe no ponto único
da raiz e o comportamento de locação não mudou.

Detalhes de pastas: [contracts/repository-layout.md](./contracts/repository-layout.md).
Detalhes de Compose e portas: [contracts/local-environment.md](./contracts/local-environment.md).
Contrato HTTP: [contracts/public-api-compatibility.md](./contracts/public-api-compatibility.md).

## Prerequisites

- Docker e Docker Compose
- Repositório na branch `002-project-rearchitecture` depois da implementação
- Portas `5173`, `8989` e `5432` livres

## 1. Inspecionar as fronteiras

Na raiz, as áreas abaixo devem existir e as casas antigas não devem mais
ser a casa ativa do mesmo conteúdo:

```text
apps/web/
apps/api/
infra/docker/
infra/postgres/
docs/
docker-compose.yml
```

Confirme também:

- Não existe `frontend/` ativo.
- Não existem `artisan`, `composer.json` nem `app/` na raiz.
- Não existe `packages/`.
- Não existe terceira pasta sob `apps/` além de `web` e `api`.
- `docs/structure.md` e `docs/environment.md` existem.
- README da raiz descreve o layout novo.

Pedido hipotético (US4): tela nova → `apps/web`; operação nova → `apps/api`;
ambiente → `infra` + Compose da raiz; decisão → `docs/decisions`.

## 2. Subir o ambiente

Na raiz:

```bash
cp apps/api/.env.example apps/api/.env
cp apps/web/.env.example apps/web/.env
cp infra/postgres/.env.example infra/postgres/.env

docker-compose up -d --build

docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan jwt:secret
docker-compose exec app php artisan migrate
docker-compose exec app php artisan storage:link
```

Se o banco de testes ainda não existir:

```bash
docker-compose exec pgsql psql -U vayro -d vayro -c "CREATE DATABASE vayro_testing;"
```

## 3. Endereços conhecidos

| Superfície | URL | Resultado esperado |
|---|---|---|
| Interface | http://localhost:5173 | SPA responde |
| Serviço | http://localhost:8989 | API responde |
| SPA → API | `VITE_API_BASE_URL=http://localhost:8989` | Login ou cadastro já existente alcança o serviço |

Nenhum endereço novo é exigido do usuário de desenvolvimento.

## 4. Regressão de locação

Rodar a suíte já publicada, agora com o `working_dir` do container `app`
apontando para `apps/api`:

```bash
docker-compose exec app php artisan test
```

Esperado: 100% dos testes atuais de `/api/v1` e das regras de `/api/v2`
passam (SC-005).

Percurso manual mínimo (SC-004):

1. Cadastrar locatário e entrar.
2. Informar período, escolher automóvel livre e solicitar locação.
3. Confirmar que o locatário vê só as próprias locações.
4. Entrar como administrativo e consultar listas e quantidade do dia.

O resultado observável deve ser o mesmo de antes da reorganização.

## 5. Falhas que impedem a conclusão

- Qualquer uma das quatro áreas ausente ou misturada.
- Ambiente que não sobe pelo Compose da raiz.
- Interface ou API em porta diferente de `5173` / `8989`.
- Teste de locação vermelho.
- Usuário final com passo ou resultado diferente no fluxo de locação.
- Cópia ativa no caminho antigo (`frontend/`, Laravel na raiz, `docker/nginx/`).
