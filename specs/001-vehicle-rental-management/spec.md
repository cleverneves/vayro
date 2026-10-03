# Feature Specification: Gerenciamento de Locação de Veículo

**Feature Branch**: `001-vehicle-rental-management`

**Created**: 2026-10-03

**Status**: Draft

**Input**: User description: "Crie uma funcionalidade de gerenciamento de locação de veículo para um sistema de locação de automóvel. Existem dois tipos de usuário: Cliente Locatário e Usuário Administrativo. O Locatário deve conseguir se cadastrar, editar as suas informações, informar o motivo da locação (viagem, passeio, dia-a-dia), escolher o automóvel, escolher a quantidade de dias e deixar um comentário. O funcionário administrativo deve conseguir visualizar os dados do locatário, visualizar os detalhes da locação, alterar o status da locação, informar uma observação e visualizar a quantidade de locações do dia. O locatário não pode visualizar locação de outros locatários."

**Constitution**: `.specify/memory/constitution.md`. This spec stays
technology-agnostic. Business rules stated here MUST be observable so they can
be tested. Stack, layers, and language rules are not restated in the spec.

## Clarifications

### Session 2026-10-03

- Q: Qual conjunto de status da locação e qual sequência o administrativo pode usar? → A: Cinco status com sequência fixa: Solicitada segue para Confirmada ou Cancelada; Confirmada segue para Em andamento ou Cancelada; Em andamento segue para Concluída. Concluída e Cancelada não mudam mais.
- Q: Quando começa o período da locação? → A: O locatário escolhe a data de início, hoje ou uma data futura, e a quantidade de dias.
- Q: O que entra na quantidade de locações do dia? → A: Todas as locações solicitadas no dia corrente, inclusive as de início futuro e as já canceladas.
- Q: Como o administrativo encontra locatários e locações? → A: Vê a lista de todos os locatários e a lista de todas as locações, e abre o detalhe a partir da lista.
- Q: Como o locatário escolhe o automóvel? → A: Informa a data de início e a quantidade de dias e então vê somente os automóveis disponíveis nesse período.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Locatário cadastra e atualiza o próprio perfil (Priority: P1)

Uma pessoa que deseja alugar um automóvel cria a própria conta de Cliente
Locatário e, depois de entrar no sistema, corrige ou atualiza nome, e-mail e
telefone.

**Why this priority**: Sem uma conta própria, o locatário não pode solicitar
locação nem ser identificado nas consultas do administrativo.

**Independent Test**: Cadastrar um locatário com nome, e-mail e telefone,
entrar com essa conta, alterar os três dados e confirmar que a consulta do
próprio perfil mostra os valores novos.

**Acceptance Scenarios**:

1. **Given** que não existe locatário com o e-mail informado, **When** a pessoa envia nome, e-mail, telefone e senha válidos, **Then** a conta de Cliente Locatário é criada e ela consegue acessar o sistema com esse e-mail e essa senha.
2. **Given** que o locatário está autenticado, **When** ele altera o próprio nome, e-mail ou telefone para valores válidos, **Then** o perfil passa a exibir os novos dados e as locações já existentes continuam vinculadas a ele.
3. **Given** que já existe um locatário com determinado e-mail, **When** outra pessoa tenta se cadastrar ou alterar o próprio e-mail para esse mesmo valor, **Then** o sistema recusa a operação e informa que o e-mail já está em uso.
4. **Given** que um locatário está autenticado, **When** ele tenta alterar o perfil de outro locatário, **Then** o sistema recusa a operação e o perfil do outro locatário permanece inalterado.

---

### User Story 2 - Locatário solicita uma locação (Priority: P1)

O locatário autenticado informa a data de início (hoje ou uma data futura) e
por quantos dias pretende ficar com o automóvel. Em seguida vê somente os
automóveis disponíveis nesse período, escolhe um, indica o motivo (viagem,
passeio ou dia-a-dia) e pode deixar um comentário. A solicitação nasce com
status Solicitada.

**Why this priority**: Solicitar a locação é o resultado de negócio principal
do sistema.

**Independent Test**: Com um locatário autenticado, informar data de início e
quantidade de dias, confirmar que a lista mostra só automóveis livres nesse
período, registrar a locação com motivo e confirmar o status Solicitada.

**Acceptance Scenarios**:

1. **Given** que o locatário está autenticado e, para a data de início e a quantidade de dias informadas, existem automóveis livres e outros reservados, **When** ele informa essa data e essa quantidade, **Then** vê somente os automóveis livres nesse período.
2. **Given** que o locatário vê um automóvel livre no período, **When** ele o escolhe, informa um motivo válido e um comentário, **Then** a locação é registrada em nome dele, com status Solicitada, começando na data de início escolhida e durando a quantidade de dias informada.
3. **Given** que o locatário vê um automóvel livre no período, **When** ele o escolhe e informa um motivo válido sem comentário, **Then** a locação é registrada da mesma forma e o comentário fica vazio.
4. **Given** que o locatário está autenticado, **When** ele omite o motivo, a data de início ou o automóvel, escolhe um motivo fora de viagem, passeio e dia-a-dia, informa data de início anterior a hoje, ou informa quantidade de dias menor que 1 ou maior que 90, **Then** a locação não é registrada e o sistema indica o que precisa ser corrigido.
5. **Given** que o automóvel não aparece na lista porque já possui locação não cancelada com período sobreposto, **When** o locatário ainda tenta registrá-lo para esse período, **Then** a locação não é registrada e o sistema informa que o automóvel não está disponível.
6. **Given** que nenhum automóvel está livre no período informado, **When** o locatário consulta a lista, **Then** a lista fica vazia e nenhuma locação é registrada.
7. **Given** que a locação foi registrada, **When** o locatário consulta essa locação, **Then** ele vê automóvel, data de início, motivo, quantidade de dias, comentário, status e data da solicitação.

---

### User Story 3 - Locatário vê somente as próprias locações (Priority: P1)

O locatário consulta a lista e o detalhe das locações feitas por ele. Locações
de outros locatários não aparecem e não podem ser abertas por ele.

**Why this priority**: A confidencialidade entre locatários é uma restrição
explícita do negócio e precisa valer assim que a locação existir.

**Independent Test**: Criar locações para dois locatários e confirmar que cada
um vê apenas as suas, tanto na lista quanto na tentativa de abrir o detalhe
da outra.

**Acceptance Scenarios**:

1. **Given** que o locatário possui uma ou mais locações, **When** ele consulta suas locações, **Then** o sistema lista somente as locações vinculadas a ele.
2. **Given** que existe uma locação de outro locatário, **When** o locatário autenticado tenta abri-la, **Then** o sistema não exibe automóvel, motivo, dias, comentário, status nem observação dessa locação.
3. **Given** que o locatário não possui locações, **When** ele consulta suas locações, **Then** o sistema mostra uma lista vazia, sem dados de outros locatários.

---

### User Story 4 - Administrativo consulta locatário e locação (Priority: P2)

O usuário administrativo vê a lista de todos os locatários e a lista de todas
as locações. A partir de cada lista, abre o cadastro do locatário ou o detalhe
da locação, inclusive comentário, motivo, automóvel, data de início,
quantidade de dias, status e observação. As listas reúnem todos os registros,
de qualquer data e, nas locações, de qualquer status. Esta versão não inclui
busca por nome, e-mail ou automóvel.

**Why this priority**: A operação da locadora depende de enxergar quem alugou
e o que foi pedido antes de decidir o andamento.

**Independent Test**: Com vários locatários e locações de status e datas
diferentes, abrir cada lista, conferir que todos aparecem e abrir um item de
cada lista para ver o detalhe.

**Acceptance Scenarios**:

1. **Given** que o administrativo está autenticado e existem locatários com e sem locação, **When** ele abre a lista de locatários, **Then** vê todos eles, com nome, e-mail e telefone.
2. **Given** que o administrativo abre um locatário a partir da lista, **When** o cadastro é exibido, **Then** vê nome, e-mail e telefone atuais.
3. **Given** que existem locações de todos os status e de datas de solicitação diferentes, **When** o administrativo abre a lista de locações, **Then** vê todas elas.
4. **Given** que o administrativo abre uma locação a partir da lista, **When** o detalhe é exibido, **Then** vê o locatário, o automóvel, a data de início, o motivo, a quantidade de dias, o período, o comentário, o status, a observação administrativa e a data da solicitação.
5. **Given** que não existe locatário ou não existe locação, **When** o administrativo abre a lista correspondente, **Then** a lista fica vazia.
6. **Given** que o locatário está autenticado, **When** ele tenta abrir a lista de locatários ou a lista de todas as locações, **Then** o sistema recusa o acesso.

---

### User Story 5 - Administrativo atualiza status e observação (Priority: P2)

O administrativo acompanha a locação alterando o status dentro da sequência
permitida e registrando uma observação operacional. O locatário dono da
locação passa a ver o status e a observação atualizados.

**Why this priority**: Sem a mudança de status, a solicitação não se transforma
em locação acompanhada pela locadora.

**Independent Test**: Partindo de uma locação Solicitada, o administrativo
confirma a locação, registra uma observação e o locatário dono vê o novo
status e o texto da observação.

**Acceptance Scenarios**:

1. **Given** que a locação está Solicitada, **When** o administrativo muda o status para Confirmada e informa uma observação, **Then** o detalhe da locação passa a mostrar Confirmada e a observação informada.
2. **Given** que a locação está Confirmada, **When** o administrativo muda o status para Em andamento, **Then** o status exibido é Em andamento.
3. **Given** que a locação está Em andamento, **When** o administrativo muda o status para Concluída, **Then** o status exibido é Concluída e não aceita nova mudança de status.
4. **Given** que a locação está Solicitada ou Confirmada, **When** o administrativo muda o status para Cancelada, **Then** o status exibido é Cancelada, o automóvel deixa de ficar reservado por essa locação e o status não aceita nova mudança.
5. **Given** que a locação está Em andamento, Concluída ou Cancelada, **When** o administrativo tenta um status que não é o próximo permitido, **Then** o sistema recusa a mudança e mantém o status anterior.
6. **Given** que o administrativo informa uma observação com texto válido, **When** ele salva sem alterar o status, **Then** a observação é atualizada e o status permanece o mesmo.
7. **Given** que o locatário dono consulta a própria locação depois da atualização, **When** a tela de detalhe é exibida, **Then** ele vê o status e a observação atuais.

---

### User Story 6 - Administrativo vê quantas locações houve no dia (Priority: P3)

O administrativo consulta um único número: quantas locações foram solicitadas
no dia corrente. A contagem usa a data da solicitação, inclui início futuro e
qualquer status, inclusive Cancelada.

**Why this priority**: É um indicador operacional do dia, útil depois que as
locações já podem ser registradas e consultadas.

**Independent Test**: Registrar no dia corrente uma locação cancelada e outra
com início futuro, registrar em um dia anterior uma locação que começa hoje, e
confirmar que a quantidade do dia conta só as duas solicitadas hoje.

**Acceptance Scenarios**:

1. **Given** que três locações foram solicitadas no dia corrente e uma delas foi cancelada, **When** o administrativo consulta a quantidade do dia, **Then** o sistema mostra 3.
2. **Given** que nenhuma locação foi solicitada no dia corrente, **When** o administrativo consulta a quantidade do dia, **Then** o sistema mostra 0.
3. **Given** que existem locações solicitadas em dias anteriores, mesmo que o período delas inclua o dia corrente, **When** o administrativo consulta a quantidade do dia corrente, **Then** essas locações não entram na contagem.
4. **Given** que uma locação foi solicitada no dia corrente com data de início futura, **When** o administrativo consulta a quantidade do dia, **Then** essa locação entra na contagem.
5. **Given** que um locatário está autenticado, **When** ele tenta consultar a quantidade de locações do dia, **Then** o sistema recusa o acesso.

---

### Edge Cases

- Cadastro ou edição sem nome, sem e-mail, sem telefone ou com e-mail em formato inválido: a conta não é criada nem alterada, e o sistema aponta os campos inválidos.
- Senha ausente ou com menos de 8 caracteres no cadastro: a conta não é criada.
- Dois cadastros simultâneos com o mesmo e-mail: apenas um é aceito.
- Dois locatários escolhem o mesmo automóvel para períodos sobrepostos ao mesmo tempo: apenas uma locação é aceita; a outra é recusada por indisponibilidade.
- Automóvel inexistente ou indisponível para o período: a locação não é registrada, e esse automóvel não aparece na lista do período.
- Nenhum automóvel livre no período informado: a lista fica vazia e nenhuma locação é registrada.
- Data de início ausente ou anterior ao dia corrente: a locação não é registrada.
- Locação de 1 dia com início em uma data e outra locação do mesmo automóvel com início no dia seguinte: a segunda é aceita. Se a primeira durar 2 dias, a segunda é recusada por sobreposição.
- Comentário ou observação acima de 500 caracteres: o texto não é salvo e o sistema pede um texto mais curto.
- Locação inexistente: o administrativo não consegue abrir detalhe, mudar status nem gravar observação.
- Mudança de status sem observação: é permitida; a observação anterior, se houver, permanece.
- Observação vazia para limpar o texto: é permitida enquanto a locação não estiver Concluída ou Cancelada.
- Locação Concluída ou Cancelada: status e observação não podem mais ser alterados.
- Locatário tenta mudar status, gravar observação administrativa ou alterar data de início, quantidade de dias, motivo, automóvel ou comentário depois que a locação já foi criada: o sistema recusa.
- Lista administrativa sem locatários ou sem locações: a lista correspondente fica vazia.
- Consulta da quantidade do dia por alguém que não é administrativo: acesso recusado, sem revelar o número.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: O sistema MUST permitir que uma pessoa se cadastre como Cliente Locatário informando nome, e-mail, telefone e senha.
- **FR-002**: O sistema MUST recusar cadastro ou troca de e-mail quando o e-mail já pertencer a outro locatário.
- **FR-003**: O sistema MUST exigir nome, e-mail em formato válido, telefone e, no cadastro, senha com no mínimo 8 caracteres.
- **FR-004**: O locatário autenticado MUST conseguir alterar o próprio nome, e-mail e telefone.
- **FR-005**: O locatário MUST NOT conseguir alterar o cadastro de outro locatário.
- **FR-006**: O locatário autenticado MUST informar primeiro uma data de início e uma quantidade de dias válidas e, em seguida, ver somente os automóveis disponíveis nesse período. A partir dessa lista, MUST escolher um automóvel e um motivo, com comentário opcional, para solicitar a locação.
- **FR-007**: O motivo MUST ser exatamente um entre: viagem, passeio e dia-a-dia.
- **FR-008**: A quantidade de dias MUST ser um número inteiro de 1 a 90.
- **FR-009**: O locatário MUST informar a data de início. Essa data MUST ser o dia corrente ou uma data futura, sem limite máximo de antecedência. O período MUST reservar essa data e os dias consecutivos seguintes até completar a quantidade de dias. O último dia reservado é o dia anterior à data de início somada à quantidade de dias. Data de início ausente ou passada MUST ser recusada.
- **FR-010**: O sistema MUST omitir da lista do período o automóvel inexistente ou já reservado por outra locação não cancelada com período sobreposto. Se mesmo assim a locação for pedida para esse automóvel, o sistema MUST recusá-la.
- **FR-011**: Toda locação nova MUST nascer com status Solicitada.
- **FR-012**: O comentário do locatário MUST ser opcional, ter no máximo 500 caracteres e permanecer o texto informado na solicitação.
- **FR-013**: O locatário MUST conseguir consultar somente as próprias locações e o detalhe de cada uma delas.
- **FR-014**: O locatário MUST NOT visualizar lista nem detalhe de locação de outro locatário, e a recusa MUST NOT revelar o conteúdo dessa locação.
- **FR-015**: O usuário administrativo autenticado MUST ver a lista de todos os locatários e, a partir dela, nome, e-mail e telefone de qualquer um, inclusive de quem não tem locação. Quando não houver locatários, a lista MUST estar vazia.
- **FR-016**: O usuário administrativo autenticado MUST ver a lista de todas as locações, de qualquer status e de qualquer data de solicitação, e a partir dela o detalhe de qualquer uma, incluindo locatário, automóvel, data de início, motivo, quantidade de dias, período, comentário, status, observação e data da solicitação. Quando não houver locações, a lista MUST estar vazia. Esta versão não inclui busca por nome, e-mail ou automóvel.
- **FR-017**: O usuário administrativo MUST conseguir alterar o status somente nesta sequência: Solicitada para Confirmada ou Cancelada; Confirmada para Em andamento ou Cancelada; Em andamento para Concluída.
- **FR-018**: Concluída e Cancelada MUST ser status finais, sem mudança posterior de status nem de observação.
- **FR-019**: O usuário administrativo MUST conseguir registrar, alterar ou apagar a observação da locação enquanto ela não estiver Concluída ou Cancelada. A observação MUST ter no máximo 500 caracteres e MAY ser vazia.
- **FR-020**: Uma locação Cancelada MUST deixar de reservar o automóvel. Locações Solicitada, Confirmada e Em andamento MUST continuar reservando o automóvel no período da locação.
- **FR-021**: O locatário dono da locação MUST ver o status e a observação administrativa atuais ao consultar a própria locação.
- **FR-022**: O locatário MUST NOT alterar status, observação, motivo, automóvel, data de início, quantidade de dias ou comentário depois que a locação for criada.
- **FR-023**: O usuário administrativo MUST conseguir ver a quantidade de locações cuja data de solicitação é o dia corrente do calendário. A contagem MUST incluir todos os status, inclusive Cancelada, e locações com data de início futura. Locações solicitadas em outros dias MUST ficar de fora, mesmo quando o período reservado inclui o dia corrente. Quando não houver nenhuma, a quantidade MUST ser 0.
- **FR-024**: O locatário MUST NOT consultar a quantidade de locações do dia, MUST NOT abrir a lista administrativa de locatários nem a lista de todas as locações, e MUST NOT alterar status ou observação.
- **FR-025**: O sistema MUST distinguir Cliente Locatário de Usuário Administrativo e aplicar a cada um apenas as capacidades descritas nesta especificação.

### Key Entities

- **Cliente Locatário**: Pessoa que aluga automóveis. Possui nome, e-mail único, telefone e credencial de acesso. Cadastra a si mesma e só edita o próprio perfil.
- **Usuário Administrativo**: Funcionário da locadora. Vê a lista de todos os locatários e a lista de todas as locações, abre o detalhe de cada item, atualiza status e observação e vê a quantidade de locações do dia. Não se cadastra por esta funcionalidade. Esta versão não inclui busca.
- **Automóvel**: Veículo do catálogo já existente. O locatário só o vê na lista depois de informar data de início e quantidade de dias, e somente quando nenhuma locação não cancelada reserva esse veículo no período. A manutenção do catálogo fica fora desta funcionalidade.
- **Locação**: Pedido de um locatário para ficar com um automóvel a partir de uma data de início, por uma quantidade de dias. Guarda data de início, motivo, comentário opcional, período, status, observação administrativa e a data da solicitação. A data de início e a data da solicitação são datas distintas. Pertence a um único locatário e a um único automóvel.
- **Motivo da locação**: Finalidade declarada pelo locatário. Valores permitidos: viagem, passeio e dia-a-dia.
- **Status da locação**: Situação operacional. Valores: Solicitada, Confirmada, Em andamento, Concluída e Cancelada. Transições permitidas: Solicitada para Confirmada ou Cancelada; Confirmada para Em andamento ou Cancelada; Em andamento para Concluída. Concluída e Cancelada são finais.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Um novo locatário conclui o próprio cadastro em menos de 3 minutos quando informa dados válidos.
- **SC-002**: Um locatário já cadastrado registra uma locação válida (data de início, motivo, automóvel, quantidade de dias e comentário opcional) em menos de 2 minutos.
- **SC-003**: Pelo menos 95% dos locatários que informam data de início e dias válidos e escolhem um automóvel livre nesse período concluem a solicitação na primeira tentativa.
- **SC-004**: Em 100% das tentativas, um locatário não consegue ver o conteúdo de uma locação de outro locatário.
- **SC-005**: Em 100% das mudanças de status aceitas, o locatário dono e o administrativo veem o mesmo status ao consultar a locação.
- **SC-006**: A quantidade exibida ao administrativo coincide exatamente com o número de locações solicitadas no dia corrente, inclusive quando esse número é zero, quando alguma locação do dia já foi cancelada e quando a data de início é futura. Locações pedidas em outros dias ficam de fora.
- **SC-007**: Um administrativo localiza os dados do locatário e o detalhe de uma locação já solicitada em menos de 1 minuto.

## Assumptions

- A frase do pedido que cita "locador" que não pode ver locação de outros locatários refere-se ao Cliente Locatário. O Usuário Administrativo continua autorizado a ver todas as locações.
- O cadastro do locatário contém nome, e-mail e telefone. Documento de identidade, carteira de habilitação, endereço e data de nascimento ficam fora desta versão.
- O e-mail identifica o locatário e serve para ele acessar o sistema. A senha é definida no cadastro. Recuperação de senha fica fora desta versão.
- Usuários administrativos já existem e não são criados por esta funcionalidade.
- O administrativo encontra locatários e locações em listas completas, sem busca por nome, e-mail ou automóvel nesta versão.
- O catálogo de automóveis já existe. Esta funcionalidade não inclui cadastrar, editar ou remover automóveis, marcas ou modelos.
- O locatário informa primeiro a data de início, no dia corrente ou em uma data futura, e a quantidade de dias. Só então vê os automóveis livres nesse período. Não há prazo máximo de antecedência. Uma data passada é recusada. A data da solicitação permanece a data em que o pedido foi feito.
- A duração máxima de 90 dias e o limite de 500 caracteres para comentário e observação são limites de negócio desta versão.
- O locatário pode ter mais de uma locação, inclusive em períodos sobrepostos, desde que cada automóvel esteja disponível.
- O locatário não edita nem cancela a locação depois de criada. Cancelamento é feito pelo administrativo, pelo status Cancelada.
- A observação administrativa é visível para o locatário dono da locação e para o administrativo. Não é visível para outros locatários.
- A quantidade do dia conta solicitações feitas no dia corrente do calendário da operação, de qualquer status e com qualquer data de início. Uma locação pedida ontem e iniciada hoje fica de fora. Não é um histórico de outros dias nem um total de automóveis em uso.
- Pagamento, preço da diária, caução, quilometragem, multa e devolução antecipada ficam fora desta versão.
- O status segue a sequência definida em FR-017. Não há status intermediários além dos cinco listados.
