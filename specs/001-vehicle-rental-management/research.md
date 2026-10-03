# Research: Gerenciamento de Locação de Veículo

Decisões da Phase 0. Todas substituem o que seria `NEEDS CLARIFICATION` no plano.

## 1. Onde vive o contrato novo

**Decision**: O ciclo desta feature é publicado em `/api/v2`. `/api/v1` permanece com login, catálogo (`marcas`, `modelos`, `carros`), clientes e locações de diária/quilometragem.

**Rationale**: A constituição exige versão nova quando o contrato publicado muda de forma incompatível. `POST /api/v1/locacoes` hoje exige `valor_diaria`, `km_inicial` e `cliente_id`. A spec retira preço e quilometragem, faz o locatário nascer da própria conta e introduz motivo, status e observação. Os testes Feature atuais fixam esse JSON de v1. Login JWT (`POST /api/v1/login`, `GET /api/v1/me`, refresh e logout) continua o único autenticação; a resposta de login e o `UserResource` ganham o campo aditivo `papel`.

**Alternatives considered**: Evoluir `/api/v1/locacoes` no lugar foi rejeitado porque tornaria incompatíveis campos obrigatórios já cobertos por teste. Duplicar login em v2 foi rejeitado porque o guard `api` com `tymon/jwt-auth` já autentica `users`.

## 2. Tabela da locação nova

**Decision**: Nova tabela `rentals` e model `Rental`. A tabela `locacoes` não é alterada e não entra na disponibilidade nem nas listas de `/api/v2`.

**Rationale**: São dois registros diferentes. `locacoes` guarda período com hora, diária e quilometragem, e o controller legado grava Eloquent direto. Reusar essa tabela obrigaria colunas nulas, misturaria as duas formas na mesma listagem e, ao tocar o controller, a constituição puxaria esse CRUD legado para service e repository — incluindo preço e quilometragem, que a spec deixa de fora. Uma tabela nova localiza a feature.

**Alternatives considered**: Estender `locacoes` e esconder linhas novas do v1 com `whereNull('status')`. Protege o JSON antigo, mas altera o fluxo legado e ainda exige reinterpretar o fim do período (datetime inclusivo versus intervalo de datas). Fica de fora.

**Limitação consciente**: um cliente de `POST /api/v1/locacoes` ainda pode gravar uma locação legada para um automóvel que `rentals` reserva. Unificar as duas reservas reintroduziria diária e quilometragem nesta versão.

## 3. Conta do locatário

**Decision**: O locatário é um `users` com `role = renter` mais uma linha em `clientes` (`user_id`, `email`, `phone`, `nome`). O administrativo é um `users` com `role = admin` e sem linha de cliente. Usuários já existentes recebem `admin` na migration. Esta feature não cadastra administrativo.

**Rationale**: O JWT já autentica `User`. O cadastro público da spec precisa de senha e e-mail, que hoje estão em `users`, e de telefone, que o domínio de cliente passa a guardar. O locatário edita só o próprio perfil. O administrativo já existe, como a spec assume.

**Alternatives considered**: Guard JWT separado para `Cliente` duplicaria autenticação. Colocar telefone em `users` misturaria o funcionário administrativo com dados que a spec só atribui ao locatário.

## 4. Vocabulário interno e JSON

**Decision**: Colunas e enums no código ficam em inglês. A borda HTTP traduz.

| Código / coluna | JSON `/api/v2` |
|-----------------|----------------|
| `renter` | `locatario` |
| `admin` | `administrativo` |
| `trip` | `viagem` |
| `leisure` | `passeio` |
| `everyday` | `dia-a-dia` |
| `requested` | `solicitada` |
| `confirmed` | `confirmada` |
| `in_progress` | `em_andamento` |
| `completed` | `concluida` |
| `cancelled` | `cancelada` |

O service persiste o valor em inglês. Form Request aceita o valor em português e a tradução acontece antes do service. API Resource devolve português.

**Rationale**: A constituição pede identificadores novos em inglês e preserva o vocabulário português do contrato (`nome`, recursos no plural).

**Alternatives considered**: Gravar `viagem` e `solicitada` no banco acopla a regra ao texto da API. Renomear models legados (`Carro` → `Vehicle`) redesenharia o catálogo que esta feature não mantém.

## 5. Período e disponibilidade

**Decision**: O período é o intervalo meio-aberto `[starts_on, starts_on + day_count)`. O último dia reservado é o dia anterior a `starts_on + day_count`. Dois períodos se sobrepõem quando `start_a < end_b` e `start_b < end_a`.

Reservam o automóvel os status `requested`, `confirmed` e `in_progress`. `completed` e `cancelled` não reservam (FR-020). A frase da spec que diz “locação não cancelada” é lida junto com FR-020: locação concluída também libera o automóvel.

A lista do período devolve automóveis de `carros` sem reserva sobreposta em `rentals`. A coluna `carros.disponivel` não participa dessa regra; ela continua sendo o filtro legado `GET /api/v1/carros?disponivel=1`.

“Hoje” e a data da solicitação usam o fuso `America/Sao_Paulo`, configurado em `config/rental.php`. `config/app.php` permanece `UTC`, para não deslocar os datetimes já gravados em `locacoes`. `requested_on` é uma `date` gravada pelo service nesse fuso. O cliente não envia essa data.

**Rationale**: O caso de borda da spec exige que 1 dia a partir de D aceite outra locação em D+1, e que 2 dias a partir de D recusem D+1. O fuso da operação precisa ser explícito: às 22h em São Paulo o dia corrente ainda é o dia local, mesmo que UTC já tenha virado.

**Alternatives considered**: Tratar `data_final` como fim inclusivo em datetime, no estilo de `locacoes`, falha o caso de 1 dia encostado no dia seguinte. Usar `APP_TIMEZONE=America/Sao_Paulo` alteraria timestamps legados. Exclusion constraint com `btree_gist` foi deixada de lado: o volume é o de uma locadora e o travamento da linha do automóvel cobre a corrida com menos extensão do PostgreSQL.

## 6. Corrida por e-mail e por automóvel

**Decision**: O service grava o e-mail em minúsculas. O índice único de `users.email` e de `clientes.email` rejeita a segunda escrita; o service traduz a violação para a regra “e-mail já está em uso” (`422`). Na solicitação, a transação faz `lockForUpdate` na linha de `carros` e só então relê as reservas. A segunda transação do mesmo automóvel espera e recebe indisponibilidade (`409`).

**Rationale**: Os dois casos de borda da spec (cadastros simultâneos e dois locatários no mesmo automóvel) precisam de uma garantia no banco, não só de uma checagem antes do insert.

**Alternatives considered**: Confiar apenas na validação `unique` do Form Request perde a corrida entre duas requisições que passaram na validação juntas. Um constraint de exclusão de datas seria mais forte e também bloquearia inserções fora do service, ao custo de uma extensão e de mais uma invariante na migration. O lock na linha do automóvel é suficiente para o único caminho que cria `rentals`.

## 7. Status, observação e autorização

**Decision**: Transições permitidas, e nenhuma outra:

- `requested` → `confirmed` ou `cancelled`
- `confirmed` → `in_progress` ou `cancelled`
- `in_progress` → `completed`
- `completed` e `cancelled` são finais: status e observação não mudam

A atualização é atômica. Transição inválida não grava a observação enviada no mesmo pedido. `observacao` omitida preserva o texto; `observacao` presente e vazia apaga o texto, enquanto o status não for final. Mudança de status sem observação preserva a observação anterior.

O locatário não tem operação de alteração da locação depois de criada. Locação de outro locatário resulta em `404` sem corpo da locação. Operação exclusiva do administrativo resulta em `403` sem o dado protegido (listas administrativas e quantidade do dia).

**Rationale**: FR-017, FR-018, FR-014 e FR-024. `404` na locação alheia evita confirmar que o registro existe. `403` nas rotas administrativas recusa o acesso sem devolver a contagem nem a lista.

**Alternatives considered**: `403` na locação de outro locatário revelaria a existência do id. Deixar o locatário cancelar a própria locação contradiz a spec: cancelamento é status feito pelo administrativo.

## 8. Listas

**Decision**: `GET` de locatários, locações e automóveis disponíveis em `/api/v2` devolve a coleção inteira em `{ "data": [ ... ] }`, sem `links` nem `meta`. Ordem das locações: `requested_on` decrescente, depois `id` decrescente. Automóveis disponíveis: `placa` crescente. Não há busca por nome, e-mail ou automóvel.

**Rationale**: A spec exige que o administrativo veja todos os registros e declara que esta versão não tem busca. A paginação de 15 itens do v1 esconderia registros e falharia essa leitura. O volume assumido é o de uma operação só.

**Alternatives considered**: Reusar a paginação do v1. Atende o padrão atual da API e deixa de atender “vê todos” sem o cliente percorrer páginas, algo que a spec não descreve.

## 9. Testes

**Decision**: Regras de negócio desta feature têm testes de unidade em `tests/Unit/Services`, estendendo `Tests\TestCase`, usando `RefreshDatabase` e o PostgreSQL `vayro_testing`. O teste instancia o service com os repositories reais e afirma o resultado. Não há suíte de browser, Playwright ou Cypress. Não se acrescentam Feature tests HTTP para `/api/v2`.

O que o teste cobre está em [quickstart.md](./quickstart.md). Formato de e-mail, tamanho de senha e teto de 500 caracteres também são regras do Form Request; o service recusa de novo quantidade de dias, data passada, motivo, transição, sobreposição, titularidade e contagem do dia, que são as invariantes sob teste de unidade.

**Rationale**: O pedido fixa testes de unidade das regras e exclui E2E. A constituição exige que a suíte rode em PostgreSQL no Docker e que o teste exerça o comportamento. Chamar o service contra o banco faz as duas coisas. Mock de repository não provaria a sobreposição nem o índice único.

**Alternatives considered**: Feature HTTP como padrão da constituição. Fica registrado como exceção justificada em `plan.md`. Teste de unidade puro, sem banco, não observa a corrida nem o intervalo de datas.

## 10. Frontend e CORS

**Decision**: SPA em `frontend/` com Vite, React, TypeScript e Material UI. Sessão mínima: o módulo de auth guarda o JWT e o `papel` devolvido por `GET /api/v1/me`. Sem Redux nem outra store global. Compose ganha o serviço `frontend` na porta 5173. CORS atual (`config/cors.php`, `paths: api/*`, origens `*`) permanece; o token vai no header `Authorization`, não em cookie.

**Rationale**: A constituição exige essa stack quando a feature tem interface, e proíbe estado global especulativo. O token é um problema presente: toda tela autenticada precisa dele. CORS já libera a API para outra origem.

**Alternatives considered**: Servir o build pelo Nginx atual acoplaria o ciclo de frontend ao container PHP nesta fase. Outra biblioteca de UI exigiria emenda da constituição.

## 11. Camadas e o que não muda

**Decision**: Controllers novos dependem de services concretos. Repositories concretos, sem interface. O controller traduz `BusinessRuleException` no status HTTP. `Marca`, `Modelo`, `Carro`, `Cliente` e `Locacao` legados ficam como estão.

Mapeamento de exceção:

| Código interno | HTTP |
|----------------|------|
| `email_taken` | 422 |
| `vehicle_unavailable` | 409 |
| `invalid_transition` | 409 |
| `rental_closed` | 409 |
| `not_found` | 404 |
| `forbidden` | 403 |

**Rationale**: Uma classe de exceção carrega o código que o controller traduz. Várias classes ou uma interface de repository seriam indireção com uma implementação só.

**Alternatives considered**: Devolver `JsonResponse` a partir do service vazaria HTTP para a regra. Reescrever os controllers legados para as camadas novas amplia a mudança além da spec.
