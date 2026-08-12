---
name: api-design
description: Projetar endpoints REST da API Vayro, incluindo recursos, rotas, status HTTP, paginação, filtros, validação e formato de respostas. Use quando criar ou revisar contratos HTTP.
---

# API Design Skill

## Princípios

A API deve ser:

- RESTful;
- previsível;
- consistente;
- simples;
- versionada;
- fácil de consumir.

## Recursos

Modelar endpoints a partir de recursos.

Exemplo:

/api/v1/vehicles
/api/v1/rentals
/api/v1/users

## CRUD

Quando o recurso representar CRUD tradicional, utilizar:

GET
POST
GET /{id}
PUT/PATCH
DELETE

## Operações específicas

Quando uma operação representar uma ação de negócio, utilizar uma rota
explícita.

Exemplo:

POST /api/v1/rentals/{rental}/cancel

Somente criar endpoints de ação quando realmente necessário.

## Responses

Manter respostas consistentes.

Utilizar API Resources para serialização.

## Paginação

Listagens devem suportar paginação quando necessário.

## Erros

Utilizar HTTP status adequado e mensagens úteis.

## Compatibilidade

Evitar alterações breaking no contrato existente.

Quando uma mudança incompatível for necessária, avaliar criação de uma nova
versão da API.