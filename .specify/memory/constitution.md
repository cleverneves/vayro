<!--
Sync Impact Report
- Version change: unversioned template → 1.0.0
- Modified principles:
  - [PRINCIPLE_1_NAME] → I. Simplicidade e Manutenibilidade
  - [PRINCIPLE_2_NAME] → II. Arquitetura em Camadas
  - [PRINCIPLE_3_NAME] → III. API REST
  - [PRINCIPLE_4_NAME] → IV. Testes de Regras de Negócio
  - [PRINCIPLE_5_NAME] → V. Idioma do Código
- Added sections:
  - Stack Tecnológica
  - Padrões de Desenvolvimento
- Removed sections: none (placeholders replaced)
- Templates:
  - .specify/templates/plan-template.md ✅ updated
  - .specify/templates/spec-template.md ✅ updated
  - .specify/templates/tasks-template.md ✅ updated
  - .cursor/skills/speckit-*/SKILL.md ✅ reviewed (no outdated agent-specific names)
  - README.md ✅ updated
  - .cursor/skills/laravel-api/SKILL.md ✅ updated
- Deferred TODOs:
  - TODO(LEGACY_LAYERS): controllers atuais persistem via Eloquent direto.
    Alinhar o fluxo às camadas quando esse código for alterado.
  - TODO(FRONTEND): a aplicação React + TypeScript + Material UI ainda não
    existe no repositório.
-->

# Vayro Constitution

## Core Principles

### I. Simplicidade e Manutenibilidade

O código MUST ser simples, claro e fácil de manter. A solução MUST ser a menor
que atenda ao requisito atual.

Overengineering é proibido. Abstração, camada extra, padrão ou generalização
MUST existir somente para resolver um problema presente.

SOLID MUST ser aplicado quando tornar mais clara uma responsabilidade que já
existe. SOLID MUST NOT justificar interface, classe base ou indireção com uma
única implementação e sem necessidade atual.

Complexidade além destas regras MUST ser justificada no Complexity Tracking do
plano da feature.

**Racional**: simplicidade e clareza arquitetural reduzem o custo de mudança.

### II. Arquitetura em Camadas

O backend MUST separar três responsabilidades:

- **HTTP**: rotas e controllers. O controller recebe a requisição, chama um
  service e devolve um API Resource ou uma resposta de erro. O controller
  MUST NOT conter regra de negócio nem consulta de persistência.
- **Serviço**: regras de negócio. Toda invariante de domínio MUST viver em
  `app/Services`.
- **Repositório**: acesso a dados. O repositório persiste e consulta via
  Eloquent em `app/Repositories`. O repositório MUST NOT decidir resultado de
  negócio.

Form Requests validam formato e presença da entrada. API Resources definem o
JSON público e MUST NOT expor Models diretamente. Models Eloquent mapeiam
tabelas e relacionamentos; não substituem services nem repositories.

Camadas além dessas (eventos de domínio, CQRS, repository genérico,
specification) MUST NOT ser adicionadas sem justificativa no Complexity
Tracking.

Código anterior a esta constituição que chama Eloquent no controller é legado.
Quando esse fluxo for alterado, a mudança MUST passar a cumprir esta separação.

**Racional**: a regra de negócio fica em um único lugar, testável, fora do HTTP
e fora do banco.

### III. API REST

A API MUST seguir REST. Recursos são substantivos. Rotas são estáveis. Métodos
HTTP correspondem à ação (`GET`, `POST`, `PUT`/`PATCH`, `DELETE`).

O status HTTP MUST representar o resultado, incluindo `422` para validação,
`404` para recurso ausente e `409` para conflito de domínio.

O contrato público MUST permanecer compatível. Mudança incompatível MUST ser
tratada como nova versão da API.

O contrato já publicado usa vocabulário em português (`/api/v1/marcas`, campo
`nome`). Esse vocabulário MUST ser preservado. Código interno novo usa inglês
e mapeia esse contrato na borda HTTP.

**Racional**: clientes dependem de um contrato previsível. A língua da URL não
é a língua do código-fonte.

### IV. Testes de Regras de Negócio

Toda regra de negócio MUST ter teste automatizado que falhe se a regra for
quebrada. O teste MUST exercer o comportamento, não o detalhe interno.

Feature test HTTP é o padrão quando a regra é observável pela API. Teste de
unidade fica restrito a lógica isolada que o teste HTTP não expressa com
clareza. Método trivial MUST NOT receber teste próprio.

A suíte MUST rodar em PostgreSQL, no ambiente Docker do projeto.

**Racional**: disponibilidade, locação e integridade do domínio precisam de
regressão automática.

### V. Idioma do Código

Identificadores MUST estar em inglês: arquivos, classes, métodos, variáveis,
testes e módulos.

Comentários MUST ser evitados. O código MUST se explicar pelo nome e pela
estrutura. Quando um comentário for indispensável para registrar uma restrição
não óbvia, ele MUST ser escrito em português do Brasil e MUST explicar o
porquê.

Mensagens de validação e de erro voltadas ao usuário podem permanecer em
português.

**Racional**: o código permanece pesquisável em inglês. Comentários em
português só entram quando o código sozinho não registra a restrição.

## Stack Tecnológica

- **Frontend**: React, TypeScript e Material UI.
- **Backend**: Laravel em PHP 8.3, com Form Requests e API Resources na borda
  HTTP.
- **Banco de dados**: PostgreSQL. Mudança de schema MUST ocorrer por migration.
- **Ambiente local**: Docker e Docker Compose. Aplicação, Nginx, PostgreSQL e
  Redis sobem por esse ambiente.
- **Autenticação**: JWT nas rotas `/api/v1`.

Outro framework, banco ou biblioteca de UI MUST NOT ser introduzido sem emenda
a esta constituição.

## Padrões de Desenvolvimento

Uma feature MUST cumprir os princípios acima antes de ser considerada pronta.

Validação de entrada fica no Form Request. Invariante de negócio fica no
service. Persistência fica no repository.

A alteração MUST ser localizada no fluxo existente. Redesenho amplo MUST ser
justificado.

Nomes de domínio no código usam inglês (`Vehicle`, `Rental`), mesmo quando o
contrato HTTP correspondente está em português (`carros`, `locacoes`).

Interface incluída na feature MUST usar React, TypeScript e Material UI, com
componentes diretos e sem estado global especulativo.

## Governance

Esta constituição prevalece sobre práticas conflitantes em README, skills e
planos. O documento conflitante MUST ser alinhado a este arquivo.

Emenda exige:

1. Descrição da mudança e do motivo.
2. Incremento de versão semântica neste arquivo.
3. Atualização do Sync Impact Report no topo deste arquivo.
4. Revisão dos templates em `.specify/templates/` e dos guias que citam o
   princípio alterado.

Versionamento:

- **MAJOR**: remoção ou redefinição incompatível de um princípio.
- **MINOR**: princípio ou seção nova, ou expansão material de uma regra.
- **PATCH**: clarificação, correção ou ajuste de redação sem mudança de regra.

Plano de feature MUST passar no Constitution Check antes da pesquisa e de novo
após o desenho. Violação sem justificativa bloqueia a implementação. A
justificativa fica na tabela Complexity Tracking.

Revisão de conformidade MUST verificar camadas, localização da regra de
negócio, teste dessa regra, idioma do código e aderência REST.

**Version**: 1.0.0 | **Ratified**: 2026-10-03 | **Last Amended**: 2026-10-03
