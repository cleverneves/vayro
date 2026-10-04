# Estrutura do repositório

O Vayro é um monorepo por responsabilidade. A raiz expõe quatro áreas e o
ponto único de orquestração. Não se cria pasta de topo nomeada por
tecnologia nem pasta genérica (`misc`, `common`, `helpers`).

```text
vayro/
├── apps/
│   ├── web/
│   └── api/
├── infra/
│   ├── docker/
│   └── postgres/
├── docs/
├── docker-compose.yml
├── README.md
├── .specify/
├── .cursor/
└── specs/
```

## Onde colocar trabalho novo

| Pedido | Destino |
|---|---|
| Tela, página, componente, roteamento ou validação de formulário | `apps/web/src/` |
| Operação de negócio, autenticação, persistência ou teste PHPUnit | `apps/api/` |
| Ajuste de Nginx, porta local ou orquestração | `infra/docker/` e `docker-compose.yml` na raiz |
| Credencial ou configuração compartilhada de dados | `infra/postgres/` |
| Receita de construção da API | `apps/api/Dockerfile` |
| Receita de construção da interface | `apps/web/package.json` e `apps/web/vite.config.ts` |
| Decisão de arquitetura | `docs/decisions/` |
| Processo de especificação | `.specify/`, `specs/` e `.cursor/` na raiz |

A interface não recebe regra de locação nem acesso a PostgreSQL. O serviço
não recebe telas React, Material UI ou Vite. O ambiente não recebe código
de aplicação. A documentação não recebe runtime.

## Regra de decisão

Antes de criar um arquivo ou uma pasta, pergunte:

1. Faz parte de uma aplicação executável? Vai para `apps/`.
2. É configuração compartilhada de execução ou de dados? Vai para `infra/`.
3. É documentação de produto, ambiente ou decisão? Vai para `docs/`.
4. Não cabe com clareza em nenhuma área existente? **Não crie uma nova
   área de topo.** Reavalie a responsabilidade nas fronteiras já
   existentes.

`.specify/`, `specs/` e `.cursor/` permanecem na raiz. Não são aplicações.

## Crescimento

Uma aplicação executável nova só nasce sob `apps/`, com responsabilidade
de execução distinta das já existentes. `apps/web` e `apps/api` não se
movem para abrir espaço.

Um pacote compartilhado (`packages/`) só entra quando existirem pelo
menos dois consumidores reais. Com um único dono, o código permanece na
aplicação.

Não se criam `apps/admin`, `apps/worker`, `apps/cli` nem `packages/` por
antecipação. A próxima aplicação futura tem um lugar (`apps/`), mas não
foi criada agora.
