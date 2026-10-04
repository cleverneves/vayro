# Contract: Local Environment

**Feature**: `002-project-rearchitecture`
**Audience**: time que sobe o produto no ambiente local
**Entry point**: `docker-compose.yml` na raiz

## Entry point

O único ponto de partida documentado é a raiz do repositório.

```bash
docker-compose up -d --build
```

Não existe um segundo Compose dentro de `apps/web`, `apps/api` ou `infra/`
como forma oficial de subir o produto.

## Published addresses

| Surface | URL | Must remain |
|---|---|---|
| Interface | `http://localhost:5173` | Sim |
| Serviço de negócio (Nginx → PHP-FPM) | `http://localhost:8989` | Sim |
| PostgreSQL (apenas desenvolvimento) | `localhost:5432` | Sim |

A SPA continua chamando a API em `http://localhost:8989` com
`Authorization: Bearer`. `VITE_API_BASE_URL` não muda de valor padrão.

## Compose services

Os nomes dos serviços permanecem para não quebrar o hábito do time:

| Service | Container | Source path after move |
|---|---|---|
| `app` | `vayro-app` | build e volume `apps/api` |
| `frontend` | `vayro-frontend` | volume `apps/web` |
| `nginx` | `vayro-nginx` | config `infra/docker/nginx/` |
| `pgsql` | `vayro-pgsql` | env `infra/postgres/` |
| `redis` | `vayro-redis` | imagem oficial |
| `queue` | `vayro-queue` | mesma imagem e volume de `app` |

## Volume and build mapping

```yaml
app:
  build:
    context: ./apps/api
    dockerfile: Dockerfile
  volumes:
    - ./apps/api:/var/www
  working_dir: /var/www

nginx:
  ports:
    - "8989:80"
  volumes:
    - ./apps/api:/var/www
    - ./infra/docker/nginx/:/etc/nginx/conf.d/

frontend:
  ports:
    - "5173:5173"
  volumes:
    - ./apps/web:/app
  environment:
    - VITE_API_BASE_URL=http://localhost:8989

pgsql:
  env_file:
    - ./infra/postgres/.env
  volumes:
    - ./.docker/pgsql/dbdata:/var/lib/postgresql/data
```

O `root` do Nginx permanece `/var/www/public`.

## Environment files

| File | Responsibility |
|---|---|
| `apps/api/.env.example` | Laravel, JWT, `DB_HOST=pgsql` |
| `apps/web/.env.example` | `VITE_API_BASE_URL=http://localhost:8989` |
| `infra/postgres/.env.example` | `POSTGRES_DB`, `POSTGRES_USER`, `POSTGRES_PASSWORD` |

Defaults alinhados aos atuais: banco `vayro`, usuário `vayro`, senha
`secret`. Banco de teste `vayro_testing` continua criado no Postgres e
referenciado por `apps/api/phpunit.xml`.

## Developer commands (unchanged verbs)

Os verbos Compose permanecem; só o conteúdo montado no container muda.

```bash
docker-compose up -d --build
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan jwt:secret
docker-compose exec app php artisan migrate
docker-compose exec app php artisan storage:link
docker-compose exec app php artisan test
docker-compose exec pgsql psql -U vayro -d vayro -c "CREATE DATABASE vayro_testing;"
```

## Success signal

O ambiente cumpre este contrato quando, após o ponto único da raiz:

1. `http://localhost:5173` responde a interface.
2. `http://localhost:8989` responde o serviço de negócio.
3. A interface alcança o serviço no fluxo de cadastro ou login já existente.
4. `docker-compose exec app php artisan test` passa a suíte já publicada.
