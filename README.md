# Vayro — API de Locação de Veículos

API REST para gerenciamento de locação de veículos, construída com **Laravel 11** e autenticada via **JWT**.

## Stack

- **PHP 8.2** (Alpine)
- **Laravel 11**
- **PostgreSQL 16**
- **Redis**
- **Nginx**
- **Docker / Docker Compose**

## Arquitetura

A API segue o fluxo convencional do Laravel, sem camadas extras (Repository, Service genérico, etc.):

```
Route → Controller → Form Request → Model (Eloquent) → API Resource → Response
```

- **Form Requests** (`app/Http/Requests`) concentram toda a validação de entrada.
- **API Resources** (`app/Http/Resources`) controlam o formato das respostas e nunca expõem Models diretamente.
- **Route Model Binding** é usado para buscar registros por ID, com resposta 404 padronizada quando o recurso não existe.

## Serviços Docker

| Container | Descrição | Porta |
|-----------|-----------|-------|
| `vayro-app` | Aplicação Laravel (PHP-FPM) | — |
| `vayro-nginx` | Servidor web Nginx | `8989` |
| `vayro-pgsql` | Banco de dados PostgreSQL 16 | `5432` |
| `vayro-redis` | Cache e filas Redis | — |
| `vayro-queue` | Worker de filas Laravel | — |

## Instalação

### 1. Clone o repositório e copie o `.env`

```bash
cp .env.example .env
```

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

A suíte usa um banco PostgreSQL isolado (`vayro_testing`), configurado em `phpunit.xml`. Antes de rodar os testes pela primeira vez, crie o banco:

```bash
docker-compose exec pgsql psql -U vayro -d vayro -c "CREATE DATABASE vayro_testing;"
```

Para executar a suíte:

```bash
docker-compose exec app php artisan test
```

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
