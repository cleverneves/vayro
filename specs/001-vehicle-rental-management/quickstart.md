# Quickstart: Gerenciamento de Locação de Veículo

Guia de validação da feature. Os contratos estão em [contracts/](./contracts/). O modelo está em [data-model.md](./data-model.md). Este guia não traz implementação de controller, service, migration nem suíte pronta.

Não há testes E2E. A prova automatizada das regras é a suíte de unidade dos services.

## Pré-requisitos

- Docker Compose do repositório no ar (`app`, `nginx`, `pgsql`, `redis`), API em `http://localhost:8989`.
- Dependências PHP instaladas, `APP_KEY` e segredo JWT gerados, migrations aplicadas.
- Banco `vayro_testing` criado no PostgreSQL do Compose (a suíte usa `phpunit.xml`).
- Ao menos um automóvel no catálogo (`/api/v1/carros`), com modelo e marca.
- Ao menos um `users.role = admin`. O cadastro desta feature não cria administrativo. Usuários que já existiam antes da migration de `role` passam a `admin`.
- SPA em `http://localhost:5173` quando a validação manual incluir a interface. O serviço `frontend` do Compose sobe o Vite.

## Testes de unidade

Dentro do container da aplicação:

```bash
docker-compose exec app php artisan test --testsuite=Unit
```

Resultado esperado: os testes de `RenterAccountService` e `RentalService` passam contra `vayro_testing`. A suíte Feature já existente de `/api/v1` não é substituída por este comando; ela continua disponível com `php artisan test` e deve seguir verde porque `/api/v1` não muda de contrato.

### RenterAccountService

- Cadastro válido cria conta `renter` e o perfil com nome, e-mail e telefone.
- E-mail repetido no cadastro ou na troca do próprio e-mail é recusado e não grava a segunda conta.
- Atualização do próprio nome, e-mail e telefone reflete no perfil e mantém as locações no mesmo locatário.
- Atualização de outro locatário é recusada e o outro perfil permanece.
- Senha com menos de 8 caracteres, ou nome, e-mail e telefone ausentes ou e-mail inválido, não cria conta. Essas falhas de formato acontecem no Form Request; o service continua responsável pela unicidade do e-mail.

### RentalService

- Dado um período com automóveis livres e reservados, a lista traz só os livres.
- Período sem automóvel livre devolve lista vazia e não cria locação.
- Solicitação com motivo válido grava `requested`, a data de início, a quantidade de dias e o comentário. Sem comentário, o comentário fica vazio.
- Motivo ausente ou fora de `trip` / `leisure` / `everyday`, data de início passada, quantidade fora de 1..90, ou automóvel omitido, não grava locação.
- Automóvel com locação que reserva o período sobreposto é recusado. Locação de 1 dia em D não bloqueia outra em D+1. Locação de 2 dias em D bloqueia D+1.
- Locação `cancelled` ou `completed` não reserva.
- Dois pedidos simultâneos do mesmo automóvel e período: um é aceito e o outro é recusado por indisponibilidade.
- O locatário lista e abre só as próprias locações. Id de outro locatário não devolve conteúdo. Sem locações, a lista vem vazia.
- O administrativo lista todos os locatários (com e sem locação) e todas as locações, de qualquer status e data. Listas vazias quando não há registros. O locatário é recusado nessas listas e na quantidade do dia, sem receber o número.
- Transições: `requested` → `confirmed` ou `cancelled`; `confirmed` → `in_progress` ou `cancelled`; `in_progress` → `completed`. Fora disso, o status anterior permanece.
- `completed` e `cancelled` recusam status e observação.
- Observação pode ser gravada, trocada ou apagada com o status parado, enquanto a locação não estiver final. Status novo sem observação preserva o texto anterior. Pedido com transição ilegal não grava a observação enviada junto.
- Cancelar libera o automóvel naquele período.
- O dono, ao reler a locação, vê o status e a observação atuais.
- O locatário não altera status, observação, motivo, automóvel, data, quantidade nem comentário depois da criação.
- A quantidade do dia conta só `requested_on` de hoje, inclusive canceladas e com início futuro, e ignora pedidos de outros dias cujo período inclua hoje. Sem pedidos hoje, a quantidade é 0.

## Validação manual da API

Use os exemplos de [contracts/locatarios.md](./contracts/locatarios.md), [contracts/carros.md](./contracts/carros.md) e [contracts/locacoes.md](./contracts/locacoes.md).

1. `POST /api/v2/locatarios` com dados válidos retorna `201`. Login em `POST /api/v1/login` devolve token. `GET /api/v2/locatarios/me` mostra o perfil. `PATCH` no mesmo caminho altera nome, e-mail e telefone.
2. `GET /api/v2/carros/disponiveis` com data de início e quantidade de dias devolve só automóveis livres. `POST /api/v2/locacoes` cria status `solicitada`.
3. Com um segundo locatário, cada `GET /api/v2/locacoes` e cada detalhe mostram só as locações daquele token. O detalhe do outro volta `404` sem o conteúdo.
4. Com o token administrativo, as duas listas trazem todos os registros. `PATCH /api/v2/locacoes/{id}` segue a sequência de status. O token do dono, ao reler, mostra o mesmo status e a mesma observação.
5. `GET /api/v2/locacoes/quantidade-do-dia` com token administrativo devolve o número de solicitações de hoje. Com token de locatário, volta `403` sem o número.

`/api/v1/marcas`, `/api/v1/modelos`, `/api/v1/carros`, `/api/v1/clientes` e `/api/v1/locacoes` seguem respondendo como antes desta feature.

## Validação manual da interface

A SPA usa Material UI e o token da sessão.

- Locatário: cadastro, entrada, perfil, escolha de data e dias, lista de automóveis do período, solicitação com motivo e comentário opcional, lista e detalhe só das próprias locações.
- Administrativo: lista de locatários, detalhe com nome, e-mail e telefone, lista de locações, detalhe com status e observação editáveis na sequência permitida, e a quantidade do dia visível nessa área.
- Locatário não encontra navegação que abra a lista administrativa nem a quantidade do dia.

Não automatizar esse percurso em browser.
