---
name: testing
description: Criar e executar testes para a API Laravel do Vayro, especialmente Feature Tests HTTP, validações, persistência PostgreSQL e regras de negócio. Use após implementar ou modificar funcionalidades.
---

# Testing Skill

## Workflow

1. Identificar comportamento alterado.
2. Encontrar testes existentes relacionados em `apps/api/tests/`.
3. Criar ou atualizar testes.
4. Executar o teste específico.
5. Corrigir falhas.
6. Executar testes relacionados.
7. Executar suíte completa quando apropriado.

A suíte roda no container `app`, cujo `working_dir` é `/var/www` montado em
`apps/api`:

```bash
docker-compose exec app php artisan test
```

Um arquivo específico:

```bash
docker-compose exec app php artisan test --filter NomeDoTeste
```

## Feature Tests

Para endpoints HTTP, preferir Feature Tests.

Validar:

- status;
- JSON;
- banco;
- validação;
- autorização;
- regras de negócio.

## Unit Tests

Usar Unit Tests para lógica realmente isolada.

Não transformar cada método trivial em Unit Test.

## Factories

Utilizar factories para dados de teste.

Evitar duplicar grandes arrays de fixtures.

## Falhas

Quando um teste falhar:

1. identificar a causa;
2. verificar se o teste está correto;
3. verificar se a implementação está correta;
4. corrigir a causa;
5. executar novamente.

Nunca alterar um teste apenas para fazê-lo passar sem entender o comportamento
esperado.
