# Vayro — Locação de Veículos

Produto de locação de veículos em monorepo: interface em **React**, serviço em **Laravel 13** com **JWT**, dados em **PostgreSQL**.

## Layout

| Área | Responsabilidade |
|---|---|
| `apps/web` | Interface (React, TypeScript, Material UI) |
| `apps/api` | Serviço de negócio (Laravel, regras, persistência, testes) |
| `infra` | Ambiente compartilhado (Nginx, PostgreSQL) |
| `docs` | Estrutura, ambiente e decisões |

O ponto único de subida é o `docker-compose.yml` da raiz.

Guias: [estrutura](docs/structure.md), [ambiente](docs/environment.md) e [fronteiras do monorepo](docs/decisions/001-monorepo-boundaries.md).

## Stack

- **PHP 8.3** (Alpine)
- **Laravel 13**
- **PostgreSQL 16**
- **Redis**
- **Nginx**
- **Docker / Docker Compose**
- **Interface**: Vite 7, React 19, TypeScript e Material UI 7 (`apps/web`, porta `5173`)

## Arquitetura

A constituição (`.specify/memory/constitution.md`) define as regras do projeto.
O fluxo do serviço em `apps/api` separa HTTP, regra de negócio e persistência:

```
Route → Controller → Form Request → Service → Repository → Model (Eloquent)
                                 ↘ API Resource → Response
```

- **Controllers** (`apps/api/app/Http/Controllers`) orquestram o HTTP. Não contêm regra de negócio nem consulta direta.
- **Form Requests** (`apps/api/app/Http/Requests`) validam a entrada.
- **Services** (`apps/api/app/Services`) concentram as regras de negócio.
- **Repositories** (`apps/api/app/Repositories`) concentram o acesso a dados.
- **API Resources** (`apps/api/app/Http/Resources`) definem o JSON público e não expõem Models diretamente.

Controllers que ainda persistem via Eloquent são legado. Ao alterar esse fluxo, a mudança passa a seguir as camadas acima.

## Serviços Docker

| Container | Descrição | Porta |
|-----------|-----------|-------|
| `vayro-app` | Aplicação Laravel (PHP-FPM) | — |
| `vayro-nginx` | Servidor web Nginx | `8989` |
| `vayro-pgsql` | Banco de dados PostgreSQL 16 | `5432` |
| `vayro-redis` | Cache e filas Redis | — |
| `vayro-queue` | Worker de filas Laravel | — |
| `vayro-frontend` | SPA Vite (React + Material UI) | `5173` |

## Instalação

### 1. Clone o repositório e copie os arquivos de ambiente

```bash
cp apps/api/.env.example apps/api/.env
cp apps/web/.env.example apps/web/.env
cp infra/postgres/.env.example infra/postgres/.env
```

O detalhe completo está em [docs/environment.md](docs/environment.md).

### 2. Suba os containers

```bash
docker-compose up -d --build
```

> O serviço `app` aguarda o PostgreSQL estar saudável antes de iniciar.

### 3. Instale as dependências PHP

```bash
docker-compose exec app composer install
```

### 4. Gere as chaves da aplicação

```bash
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan jwt:secret
```

### 5. Execute as migrations

```bash
docker-compose exec app php artisan migrate
```

### 6. Crie o link de armazenamento público (necessário para as imagens de marcas/modelos)

```bash
docker-compose exec app php artisan storage:link
```

A API estará disponível em **[http://localhost:8989](http://localhost:8989)**.

A interface de desenvolvimento sobe no serviço `frontend` e fica em **[http://localhost:5173](http://localhost:5173)**. Ela chama a API com `Authorization: Bearer` e `VITE_API_BASE_URL=http://localhost:8989`.

```bash
docker-compose up -d frontend
```

---

## Autenticação

A API usa **JWT** como mecanismo de autenticação. Todas as rotas sob `/api/v1` (exceto `login`) exigem o token no header:

```
Authorization: Bearer {token}
```

### Endpoints de autenticação

| Método | Rota | Autenticação | Descrição |
|--------|------|--------------|-----------|
| `POST` | `/api/v1/login` | Não | Autentica e retorna o token JWT (401 se inválido) |
| `GET` | `/api/v1/me` | Sim | Retorna o usuário autenticado |
| `POST` | `/api/v1/refresh` | Sim | Renova o token |
| `POST` | `/api/v1/logout` | Sim | Invalida o token |

---

## Endpoints da API

Todas as rotas abaixo exigem autenticação JWT e seguem o padrão REST (recursos no plural, em português, alinhados às tabelas do domínio).

### Marcas — `/api/v1/marcas`

| Método | Rota | Descrição |
|--------|------|-----------|
| `GET` | `/api/v1/marcas` | Lista marcas paginadas (com modelos) |
| `POST` | `/api/v1/marcas` | Cadastra uma marca (imagem PNG obrigatória) |
| `GET` | `/api/v1/marcas/{marca}` | Exibe uma marca com seus modelos |
| `PUT/PATCH` | `/api/v1/marcas/{marca}` | Atualiza uma marca |
| `DELETE` | `/api/v1/marcas/{marca}` | Remove uma marca (409 se possuir modelos) |

### Modelos — `/api/v1/modelos`

| Método | Rota | Descrição |
|--------|------|-----------|
| `GET` | `/api/v1/modelos` | Lista modelos paginados (com marca) |
| `POST` | `/api/v1/modelos` | Cadastra um modelo (imagem PNG/JPEG obrigatória) |
| `GET` | `/api/v1/modelos/{modelo}` | Exibe um modelo com sua marca |
| `PUT/PATCH` | `/api/v1/modelos/{modelo}` | Atualiza um modelo |
| `DELETE` | `/api/v1/modelos/{modelo}` | Remove um modelo (409 se possuir carros) |

### Carros — `/api/v1/carros`

| Método | Rota | Descrição |
|--------|------|-----------|
| `GET` | `/api/v1/carros` | Lista carros paginados (aceita `?disponivel=1`) |
| `POST` | `/api/v1/carros` | Cadastra um carro |
| `GET` | `/api/v1/carros/{carro}` | Exibe um carro com seu modelo |
| `PUT/PATCH` | `/api/v1/carros/{carro}` | Atualiza um carro |
| `DELETE` | `/api/v1/carros/{carro}` | Remove um carro (409 se possuir locações) |

### Clientes — `/api/v1/clientes`

| Método | Rota | Descrição |
|--------|------|-----------|
| `GET` | `/api/v1/clientes` | Lista clientes paginados |
| `POST` | `/api/v1/clientes` | Cadastra um cliente |
| `GET` | `/api/v1/clientes/{cliente}` | Exibe um cliente |
| `PUT/PATCH` | `/api/v1/clientes/{cliente}` | Atualiza um cliente |
| `DELETE` | `/api/v1/clientes/{cliente}` | Remove um cliente (409 se possuir locações) |

### Locações — `/api/v1/locacoes`

| Método | Rota | Descrição |
|--------|------|-----------|
| `GET` | `/api/v1/locacoes` | Lista locações paginadas (aceita `?cliente_id=` e `?carro_id=`) |
| `POST` | `/api/v1/locacoes` | Registra uma locação |
| `GET` | `/api/v1/locacoes/{locacao}` | Exibe uma locação |
| `PUT/PATCH` | `/api/v1/locacoes/{locacao}` | Atualiza uma locação (ex.: finalização com km/data) |
| `DELETE` | `/api/v1/locacoes/{locacao}` | Remove uma locação |

---

## Formato das respostas

Recursos únicos e coleções seguem o padrão de API Resources do Laravel:

```json
{
  "data": {
    "id": 1,
    "nome": "Honda"
  }
}
```

Listagens são paginadas e incluem `links` e `meta`. Erros de validação retornam `422` com a estrutura:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "nome": ["O nome da marca já existe."]
  }
}
```

---

## Testes

A suíte usa um banco PostgreSQL isolado (`vayro_testing`), configurado em `apps/api/phpunit.xml`. O comando roda no container `app`, cujo `working_dir` aponta para `apps/api`. Antes de rodar os testes pela primeira vez, crie o banco:

```bash
docker-compose exec pgsql psql -U vayro -d vayro -c "CREATE DATABASE vayro_testing;"
```

Para executar a suíte:

```bash
docker-compose exec app php artisan test
```

As regras de negócio de `/api/v2` (services `RenterAccountService` e `RentalService`) são a suíte Unit, contra o PostgreSQL `vayro_testing`:

```bash
docker-compose exec app php artisan test --testsuite=Unit
```

A suíte Feature de `/api/v1` permanece e deve continuar verde. O contrato publicado de marcas, modelos, carros, clientes e locações de v1 não muda.

---

## Comandos úteis

```bash
# Acessar o container da aplicação
docker-compose exec app sh

# Ver logs da aplicação
docker-compose logs -f app

# Parar os containers
docker-compose down

# Parar e remover os volumes
docker-compose down -v
```
