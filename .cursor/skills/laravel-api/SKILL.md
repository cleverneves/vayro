---
name: laravel-api
description: Desenvolver e modificar APIs REST usando Laravel 13, seguindo as convenções do framework, Eloquent, Form Requests, API Resources, Policies e testes. Use quando implementar endpoints, controllers, requests, resources ou regras de negócio da API Vayro.
---

# Laravel API Skill

## Objetivo

Implementar funcionalidades da API Vayro utilizando Laravel 13 de forma
idiomática, simples e testável.

## Workflow

1. Entender o requisito.
2. Identificar o recurso afetado.
3. Verificar Models existentes.
4. Verificar migrations existentes.
5. Verificar rotas existentes.
6. Implementar somente as mudanças necessárias.
7. Criar/alterar Form Requests.
8. Criar/alterar Controller.
9. Criar/alterar API Resource.
10. Criar testes.
11. Executar os testes.
12. Revisar a implementação procurando over engineering.

## Implementação de endpoint

Para um novo recurso:

- migration;
- model;
- factory;
- request;
- resource;
- controller;
- route;
- feature tests.

Não criar todos esses arquivos se o requisito não precisar deles.

## Controller

Manter controllers pequenos.

Exemplo conceitual:

public function store(StoreVehicleRequest $request)
{
    $vehicle = Vehicle::create($request->validated());

    return new VehicleResource($vehicle);
}

## Validação

Usar Form Request para validações relevantes.

Sempre trabalhar com dados validados:

$request->validated();

Nunca persistir diretamente `$request->all()`.

## Resource

Utilizar API Resources para definir o contrato público da API.

## Testes

Para endpoints, preferir Feature Tests.

Validar:

- status HTTP;
- JSON;
- persistência;
- validação;
- comportamento de negócio.

## Regra

Não criar uma camada adicional apenas para seguir um padrão arquitetural.