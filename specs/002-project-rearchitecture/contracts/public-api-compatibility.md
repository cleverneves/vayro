# Contract: Public API Compatibility

**Feature**: `002-project-rearchitecture`
**Audience**: clientes HTTP já integrados e a SPA
**Change**: none

Esta feature não publica endpoint novo e não altera endpoint existente.
O contrato vigente continua o de `specs/001-vehicle-rental-management/contracts/`.

## Unchanged surfaces

| Area | Reference | This feature |
|---|---|---|
| Autenticação JWT e envelope comum | [001 comum](../../001-vehicle-rental-management/contracts/comum.md) | Sem mudança |
| Locatários `/api/v2/locatarios` | [001 locatarios](../../001-vehicle-rental-management/contracts/locatarios.md) | Sem mudança |
| Locações `/api/v2/locacoes` | [001 locacoes](../../001-vehicle-rental-management/contracts/locacoes.md) | Sem mudança |
| Carros disponíveis `/api/v2/carros` | [001 carros](../../001-vehicle-rental-management/contracts/carros.md) | Sem mudança |
| Recursos `/api/v1` (marcas, modelos, carros, clientes, locacoes, login) | README da raiz e Feature Tests atuais | Sem mudança |

## Invariants

- Base de desenvolvimento: `http://localhost:8989`.
- Header: `Authorization: Bearer {token}`.
- Vocabulário público em português (`marcas`, `locacoes`, `nome`, `papel`).
- Status já usados: `401`, `404`, `409`, `422` e os de sucesso atuais.
- Campo aditivo `papel` em login e `GET /api/v1/me` permanece.
- Cadastro de locatário em `POST /api/v2/locatarios` continua público.

## Observable rental flows

Os fluxos abaixo devem produzir o mesmo resultado de antes da reorganização:

1. Cadastro de locatário e login.
2. Pedido de locação com período e automóvel livre.
3. Locatário vê somente as próprias locações.
4. Administrativo consulta locatários, locações e quantidade do dia.
5. Mudança de status e recusas de acesso já especificadas.

Qualquer passo, endereço ou resultado diferente é regressão e impede a
conclusão desta feature.

## Out of scope

- Nova versão de API.
- Renomear recursos para inglês nas URLs.
- Alterar Form Requests, API Resources, services ou repositories, salvo
  ajuste de caminho de arquivo exigido pelo movimento.
