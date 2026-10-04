# Ambiente local

O único ponto de partida do produto é a raiz do repositório. Interface, API
e dados sobem juntos pelo `docker-compose.yml`.

Não existe um segundo Compose dentro de `apps/web`, `apps/api` ou `infra/`
como forma oficial de subir o ambiente.

## Endereços

| Superfície | URL |
|---|---|
| Interface | http://localhost:5173 |
| Serviço de negócio | http://localhost:8989 |
| PostgreSQL (apenas desenvolvimento) | localhost:5432 |

A interface chama a API em `http://localhost:8989` com
`Authorization: Bearer`. O valor padrão de `VITE_API_BASE_URL` não muda.

## Preparar os arquivos de ambiente

Na raiz, copie os exemplos. Cada um guarda só o que é da sua área:

```bash
cp apps/api/.env.example apps/api/.env
cp apps/web/.env.example apps/web/.env
cp infra/postgres/.env.example infra/postgres/.env
```

| Arquivo | Responsabilidade |
|---|---|
| `apps/api/.env` | Laravel, JWT, `DB_HOST=pgsql` |
| `apps/web/.env` | `VITE_API_BASE_URL=http://localhost:8989` |
| `infra/postgres/.env` | `POSTGRES_DB`, `POSTGRES_USER`, `POSTGRES_PASSWORD` |

Defaults: banco `vayro`, usuário `vayro`, senha `secret`. O banco de teste
`vayro_testing` continua no Postgres e é referenciado por
`apps/api/phpunit.xml`.

## Subir o ambiente

Na raiz:

```bash
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

Para rodar a suíte já publicada:

```bash
docker-compose exec app php artisan test
```

O `working_dir` do container `app` é `/var/www`, montado em `apps/api`.

## Serviços Compose

Os nomes dos serviços permanecem. Só o conteúdo montado muda.

| Serviço | Container | Origem |
|---|---|---|
| `app` | `vayro-app` | build e volume `apps/api` |
| `frontend` | `vayro-frontend` | volume `apps/web` |
| `nginx` | `vayro-nginx` | config `infra/docker/nginx/` |
| `pgsql` | `vayro-pgsql` | env `infra/postgres/` |
| `redis` | `vayro-redis` | imagem oficial |
| `queue` | `vayro-queue` | mesma imagem e volume de `app` |

## Sinal de sucesso

Depois do ponto único da raiz:

1. http://localhost:5173 responde a interface.
2. http://localhost:8989 responde o serviço de negócio.
3. A interface alcança o serviço no fluxo de cadastro ou login já existente.
4. `docker-compose exec app php artisan test` passa a suíte já publicada.
