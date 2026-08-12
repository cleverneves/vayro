---
name: database-design
description: Projetar e modificar o schema PostgreSQL do Vayro usando migrations, relacionamentos, constraints e índices. Use quando criar entidades, alterar tabelas, definir relacionamentos ou otimizar consultas.
---

# Database Design Skill

## Workflow

1. Identificar a entidade.
2. Identificar seus atributos.
3. Identificar relacionamentos.
4. Definir constraints.
5. Definir índices necessários.
6. Criar migration.
7. Atualizar Model.
8. Atualizar Factory.
9. Criar/atualizar testes.
10. Verificar queries relevantes.

## Entidades

Para o domínio inicial do Vayro, considerar conceitos como:

- User
- Vehicle
- VehicleType
- Rental
- RentalItem, somente se necessário
- Pricing, somente se necessário

Não criar entidades antecipadamente.

## Vehicle

Veículos devem permitir representar diferentes categorias, incluindo:

- carro;
- moto.

A modelagem deve permitir expansão futura sem criar abstrações prematuras.

## Constraints

Usar constraints para garantir integridade.

Exemplos:

- placa única;
- foreign keys;
- campos obrigatórios.

## Índices

Adicionar somente índices que tenham justificativa baseada em:

- relacionamento;
- unicidade;
- filtros;
- consultas frequentes.

## Performance

Antes de otimizar:

1. identificar a query;
2. verificar o problema;
3. entender o plano;
4. aplicar a menor otimização necessária.

Não adicionar cache ou mecanismos complexos prematuramente.