# Feature Specification: Rearquitetura do Projeto

**Feature Branch**: `002-project-rearchitecture`

**Created**: 2026-10-03

**Status**: Draft

**Input**: User description: "Crie uma funcionalidade para rearquitetura do projeto"

**Constitution**: `.specify/memory/constitution.md`. This spec stays
technology-agnostic. Business rules stated here MUST be observable so they can
be tested. Stack, layers, and language rules are not restated in the spec.

## Clarifications

### Session 2026-10-03

- Q: A rearquitetura inclui só as fronteiras do repositório, ou também a limpeza interna do serviço de negócio? → A: Só reorganizar o repositório. Sem mudar a divisão interna do serviço de negócio.
- Q: Quais documentos existentes precisam acompanhar o layout novo? → A: Atualizar README, nova documentação e guias usados na próxima feature. Specs históricas da locação ficam como foram escritas.
- Q: Onde fica a configuração de execução depois da reorganização? → A: Cada aplicação pode guardar só a receita de construção que é dela. Configuração compartilhada de execução e de dados fica na área de ambiente. O ponto único de orquestração permanece na raiz.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Time encontra cada responsabilidade em uma área clara (Priority: P1)

Uma pessoa do time que vai alterar o produto precisa saber, sem perguntar a
outra pessoa, onde fica a interface com o usuário, onde ficam as operações de
negócio, onde fica a configuração do ambiente e onde fica a documentação. Cada
área tem um nome que descreve a responsabilidade, não a tecnologia usada.

**Why this priority**: Sem fronteiras visíveis, qualquer mudança mistura
telas, regras e ambiente no mesmo lugar e o custo de evolução cresce a cada
feature.

**Independent Test**: Pedir a uma pessoa do time que indique, a partir da
raiz do repositório, as quatro áreas e confirme que cada uma contém somente o
tipo de conteúdo que lhe cabe.

**Acceptance Scenarios**:

1. **Given** que o repositório já foi reorganizado, **When** a pessoa abre a raiz, **Then** ela vê áreas distintas para aplicações executáveis, ambiente de execução e documentação, todas no mesmo repositório.
2. **Given** que a pessoa precisa alterar uma tela, **When** ela entra na área da interface com o usuário, **Then** encontra somente o que o usuário vê e opera, sem regras de negócio, sem acesso a dados persistidos e sem configuração compartilhada de ambiente. A receita de construção da própria interface pode estar ali.
3. **Given** que a pessoa precisa alterar uma regra de locação já existente, **When** ela entra na área do serviço de negócio, **Then** encontra as operações, as regras e o acesso aos dados, sem telas e sem configuração compartilhada de ambiente. A receita de construção do próprio serviço pode estar ali.
4. **Given** que a pessoa precisa ajustar como o produto sobe no ambiente local, **When** ela entra na área de ambiente, **Then** encontra somente a configuração compartilhada de execução e de dados, sem código das aplicações e sem a receita de construção que pertence a uma aplicação só.
5. **Given** que a pessoa precisa entender a organização do produto, **When** ela entra na área de documentação, **Then** encontra o guia de estrutura, o guia de ambiente e as decisões que justificam as fronteiras.

---

### User Story 2 - Ambiente local sobe de um único ponto (Priority: P1)

Uma pessoa que acaba de obter o repositório sobe interface, serviço de
negócio e dados persistidos a partir de um único ponto de partida na raiz.
Os endereços que o time e os usuários de desenvolvimento já usam para abrir
a interface e o serviço continuam os mesmos.

**Why this priority**: Se a reorganização quebrar o ambiente, o time não
consegue validar o produto e a rearquitetura não entrega valor.

**Independent Test**: Seguir o guia de ambiente a partir de um repositório
limpo e confirmar que a interface e o serviço de negócio respondem nos
mesmos endereços de antes, na primeira tentativa.

**Acceptance Scenarios**:

1. **Given** que a pessoa tem o repositório e o guia de ambiente, **When** ela executa o ponto de partida único documentado na raiz, **Then** a interface, o serviço de negócio e os dados persistidos ficam disponíveis sem passos extras espalhados por várias áreas.
2. **Given** que o ambiente subiu, **When** a pessoa abre o endereço já conhecido da interface e o endereço já conhecido do serviço de negócio, **Then** ambos respondem como antes da reorganização.
3. **Given** que a interface precisa falar com o serviço de negócio, **When** a pessoa usa o fluxo de cadastro ou de entrada já existente, **Then** a interface continua alcançando o serviço sem que o usuário final mude hábito ou endereço.

---

### User Story 3 - Comportamento de locação permanece o mesmo (Priority: P1)

Um Cliente Locatário e um Usuário Administrativo usam o produto depois da
reorganização. Cadastro, pedido de locação, consulta das próprias locações,
listas administrativas, mudança de status e quantidade do dia funcionam
como na especificação de gerenciamento de locação já publicada. Nenhum
passo novo é exigido do usuário final.

**Why this priority**: Rearquitetura sem preservação do comportamento é
regressão. O valor desta feature é organizar o produto, não mudar o
negócio.

**Independent Test**: Percorrer o cadastro de locatário, um pedido de
locação válido e a consulta administrativa de uma locação já existente, e
confirmar o mesmo resultado observável de antes.

**Acceptance Scenarios**:

1. **Given** que um locatário válido se cadastra e entra no produto, **When** ele informa período, escolhe um automóvel livre e solicita a locação, **Then** a locação nasce com o mesmo status inicial e os mesmos dados que a regra atual exige.
2. **Given** que existem locações de dois locatários, **When** um deles consulta a lista, **Then** ele vê somente as próprias locações.
3. **Given** que um administrativo autenticado abre as listas, **When** ele consulta locatários, locações e a quantidade do dia, **Then** os mesmos dados e as mesmas recusas de acesso continuam válidos.
4. **Given** que o contrato público de operações já publicado está em uso, **When** um cliente existente chama essas operações, **Then** caminhos, nomes de recursos e resultados observáveis permanecem compatíveis.

---

### User Story 4 - Novato sabe onde colocar trabalho novo (Priority: P2)

Uma pessoa que entra no time pela primeira vez lê a documentação de
estrutura e consegue decidir sozinha onde criar uma tela, onde criar uma
operação de negócio, onde ajustar o ambiente e onde registrar uma decisão.
Ela não cria uma área nova no topo do repositório só para “organizar
arquivos”.

**Why this priority**: A rearquitetura só se sustenta se a próxima feature
respeitar as mesmas fronteiras.

**Independent Test**: Dar à pessoa um pedido hipotético de cada tipo
(tela, regra, ambiente, texto de decisão) e verificar que ela aponta a
área correta sem criar pasta genérica no topo.

**Acceptance Scenarios**:

1. **Given** que a documentação de estrutura está publicada, **When** a pessoa precisa criar uma tela nova, **Then** ela a coloca na aplicação de interface já existente, não no serviço de negócio nem no ambiente.
2. **Given** que a pessoa precisa criar uma operação de negócio nova, **When** ela segue o guia, **Then** ela a coloca no serviço de negócio já existente, não na interface nem no ambiente.
3. **Given** que a pessoa precisa registrar uma decisão de arquitetura, **When** ela segue o guia, **Then** o texto vai para a área de documentação.
4. **Given** que o pedido não se encaixa de imediato em nenhuma área, **When** a pessoa avalia o guia, **Then** ela não cria uma área de topo nova; primeiro reavalia a responsabilidade.
5. **Given** que a pessoa precisa alterar a receita de construção de uma aplicação, **When** ela segue o guia, **Then** ela a altera nessa aplicação, não na área compartilhada de ambiente.

---

### User Story 5 - Produto cresce sem nova reorganização ampla (Priority: P3)

O time precisa, no futuro, de outra aplicação executável com
responsabilidade de execução própria. Essa aplicação entra na área de
aplicações sem mover a interface atual, o serviço de negócio atual, o
ambiente ou a documentação. Código compartilhado só nasce quando pelo
menos dois consumidores reais existirem.

**Why this priority**: Evita outra rearquitetura cedo e impede pastas
criadas “para o futuro”.

**Independent Test**: Descrever no guia o critério para uma aplicação
nova e o critério para código compartilhado, e confirmar que a estrutura
atual não inclui aplicações nem compartilhamentos especulativos.

**Acceptance Scenarios**:

1. **Given** que surge uma aplicação com execução própria e distinta, **When** o time a adiciona, **Then** ela entra na área de aplicações sem exigir mover as aplicações já existentes.
2. **Given** que só uma aplicação usa determinado código, **When** o time avalia extrair um compartilhamento, **Then** o código permanece dentro da aplicação dona.
3. **Given** que a estrutura atual é inspecionada, **When** alguém procura aplicações ou compartilhamentos criados só por hipótese, **Then** não encontra nenhum.

---

### Edge Cases

- Reorganização pela metade: interface movida e serviço ainda misturado ao
  ambiente, ou o contrário, não é considerada concluída.
- Pessoa do time procura o caminho antigo da interface ou do serviço: o
  guia de estrutura indica o destino novo; os caminhos antigos não
  permanecem como segunda casa do mesmo conteúdo.
- Ferramentas de especificação, constituição e histórico de features do
  produto ficam na raiz do repositório como processo do time, não como
  aplicação executável.
- O ponto de orquestração do ambiente local permanece na raiz. A
  configuração compartilhada de execução e de dados fica na área de
  ambiente. A receita de construção que serve a uma aplicação só fica
  nessa aplicação.
- Aplicação nova sem responsabilidade de execução própria não é criada.
- Área de código compartilhado sem dois consumidores reais não é criada.
- Área de topo com nome genérico (miscelânea, temporário, comum, helpers)
  não é criada.
- Nome de área que descreve a tecnologia em vez da responsabilidade não é
  usado.
- Falha ao subir o ambiente depois da reorganização: o time não declara a
  feature pronta até interface, serviço e dados voltarem a responder nos
  endereços já conhecidos.
- Usuário final percebe passo, endereço ou resultado diferente no fluxo de
  locação: a mudança é regressão e deve ser revertida ou corrigida antes
  da conclusão.
- Fluxo antigo do serviço que ainda mistura responsabilidades internas:
  permanece como está; esta feature só o move de lugar.
- Documento histórico da feature de locação que ainda cita o layout
  antigo: permanece como registro da época; o guia novo é a referência
  corrente.
- Receita de construção de uma aplicação colocada na área compartilhada
  de ambiente: deve voltar para a aplicação dona, a menos que duas
  aplicações a usem de fato.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: O produto MUST viver em um único repositório que contém a
  interface com o usuário, o serviço de negócio, o ambiente de execução e
  a documentação.
- **FR-002**: A raiz do repositório MUST expor áreas distintas para
  aplicações executáveis, ambiente de execução e documentação.
- **FR-003**: Cada área de topo MUST ter um nome curto, em minúsculas, que
  descreve a responsabilidade, não a tecnologia.
- **FR-004**: A interface com o usuário MUST residir em uma aplicação
  executável própria dentro da área de aplicações.
- **FR-005**: O serviço de negócio MUST residir em uma aplicação
  executável própria, distinta da interface, dentro da área de
  aplicações.
- **FR-006**: A interface MUST NOT conter regras de negócio, acesso a
  dados persistidos nem configuração compartilhada de ambiente. MAY
  conter a própria receita de construção e o exemplo das configurações
  que só ela exige.
- **FR-007**: O serviço de negócio MUST NOT conter telas, componentes de
  interface nem configuração compartilhada de ambiente. MAY conter a
  própria receita de construção e o exemplo das configurações que só
  ele exige.
- **FR-008**: A área de ambiente MUST conter a configuração compartilhada
  de execução e de dados persistidos. MUST NOT conter código das
  aplicações nem a receita de construção que pertence a uma aplicação
  só.
- **FR-009**: A área de documentação MUST conter o guia da estrutura, o
  guia de ambiente local e as decisões que justificam as fronteiras.
- **FR-010**: O time MUST conseguir subir interface, serviço de negócio e
  dados persistidos a partir de um único ponto de partida na raiz.
- **FR-011**: Os endereços já usados em desenvolvimento para abrir a
  interface e o serviço de negócio MUST permanecer os mesmos.
- **FR-012**: O contrato público de operações já publicado MUST
  permanecer compatível: caminhos, vocabulário exposto ao cliente e
  resultados observáveis não mudam por causa desta reorganização.
- **FR-013**: Os fluxos já especificados de cadastro de locatário, pedido
  de locação, privacidade entre locatários, listas administrativas,
  mudança de status e quantidade do dia MUST continuar válidos sem passo
  novo para o usuário final.
- **FR-014**: A documentação MUST dizer onde colocar tela nova, operação
  de negócio nova, ajuste de ambiente e decisão de arquitetura. O README
  da raiz e os guias de processo usados na próxima feature MUST
  descrever o layout novo. Specs, planos e tarefas históricas da
  feature de locação MUST permanecer como foram escritas.
- **FR-015**: O time MUST NOT criar área de topo nova sem antes avaliar se
  a responsabilidade já cabe em aplicações, ambiente ou documentação.
- **FR-016**: O time MUST NOT criar aplicação executável nova sem
  responsabilidade de execução distinta das aplicações já existentes.
- **FR-017**: O time MUST NOT criar área de código compartilhado enquanto
  não houver pelo menos dois consumidores reais.
- **FR-018**: Tela e operação de interface que hoje convivem fora da
  aplicação de interface MUST passar a viver nessa aplicação. Código de
  serviço de negócio que hoje vive na raiz MUST passar a viver na
  aplicação de serviço. Configuração compartilhada de execução e de
  dados MUST passar à área de ambiente. Receita de construção e exemplo
  de configuração próprios de uma aplicação MUST ir com essa aplicação.
  Não MUST permanecer uma segunda cópia ativa no caminho antigo.
- **FR-019**: Processo de especificação, constituição e histórico de
  features MUST permanecer na raiz e MUST NOT ser tratado como aplicação
  executável.
- **FR-020**: A reorganização MUST ser considerada incompleta enquanto
  qualquer uma das quatro responsabilidades (interface, serviço de
  negócio, ambiente, documentação) estiver ausente ou misturada com
  outra.
- **FR-021**: Esta feature MUST NOT alterar a divisão interna de
  responsabilidades do serviço de negócio. Mudar o lugar dos arquivos
  existentes é permitido. Reescrever um fluxo para outra separação
  interna (operação, regra e persistência) MUST NOT fazer parte desta
  entrega.
- **FR-022**: Cada aplicação MAY guardar somente a receita de construção
  e o exemplo de configurações que lhe pertencem. A configuração
  compartilhada de execução e de dados MUST viver na área de ambiente.
  O ponto único de orquestração MUST permanecer na raiz.

### Key Entities

- **Repositório do produto**: Casa única da interface, do serviço de
  negócio, do ambiente e da documentação.
- **Aplicação executável**: Unidade com responsabilidade de execução
  própria. Nesta versão existem exatamente duas: a interface com o
  usuário e o serviço de negócio.
- **Interface com o usuário**: Aplicação que a pessoa usa para ver e
  operar o produto. Não decide regra de negócio e não persiste dados.
- **Serviço de negócio**: Aplicação que autentica, aplica regras e
  persiste dados. Não apresenta telas.
- **Ambiente de execução**: Configuração compartilhada que faz o produto
  subir e persistir dados no ambiente local. Não contém código das
  aplicações nem a receita de construção de uma aplicação só.
- **Receita de construção**: Instruções que pertencem a uma aplicação
  e dizem como ela própria é preparada para executar. Vive com a
  aplicação dona.
- **Documentação do produto**: Textos de estrutura, ambiente e decisões.
  Não contém código executável.
- **Contrato público já publicado**: Conjunto de operações e vocabulário
  que clientes existentes já usam. Esta feature não o altera.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Uma pessoa do time localiza as quatro áreas
  (interface, serviço de negócio, ambiente e documentação) em menos de 5
  minutos a partir da raiz, sem perguntar a outra pessoa.
- **SC-002**: Em 100% das inspeções, a área da interface não contém regra
  de negócio nem acesso a dados persistidos, e a área do serviço de
  negócio não contém telas.
- **SC-003**: Uma pessoa que segue o guia de ambiente sobe interface,
  serviço e dados a partir do ponto único da raiz e os acessa nos
  endereços já conhecidos na primeira tentativa, sem passo extra além
  dos documentados.
- **SC-004**: Em 100% dos fluxos já publicados de locação (cadastro,
  pedido, privacidade, listas administrativas, status e quantidade do
  dia), o resultado observável para o usuário final é o mesmo de antes da
  reorganização.
- **SC-005**: 100% das verificações automatizadas já existentes para as
  regras de locação continuam passando após a reorganização.
- **SC-006**: Uma pessoa nova no time, com o guia de estrutura em mãos,
  aponta a área correta para tela, regra, ambiente e decisão em 4 de 4
  pedidos na primeira tentativa.
- **SC-007**: A estrutura entregue contém exatamente as aplicações
  executáveis necessárias agora (interface e serviço de negócio) e
  nenhuma área criada só para necessidade futura.

## Assumptions

- O pedido de “rearquitetura do projeto” refere-se a reorganizar o
  repositório em fronteiras de responsabilidade, não a mudar regras de
  locação, papéis de usuário ou o contrato público já publicado.
- A estrutura-alvo é a já acordada pelo time: um repositório com área de
  aplicações (interface e serviço), área de ambiente, área de
  documentação e um ponto único de orquestração do ambiente local na
  raiz. O detalhe de pastas e ferramentas fica para o plano.
- A interface com o usuário e o serviço de negócio já existem. Esta
  feature os recoloca nas fronteiras corretas; não os reescreve e não cria
  uma terceira aplicação.
- Código compartilhado entre aplicações fica fora desta versão. Não se
  cria essa área por antecipação.
- Aplicações futuras (painel separado, processo em segundo plano,
  ferramenta de linha de comando) ficam fora desta versão. O guia apenas
  registra o critério para quando existirem.
- Confirmado: a rearquitetura é só reorganizar fronteiras do repositório.
  A divisão interna do serviço de negócio não entra nesta entrega. Só se
  altera o que for necessário para o código passar a viver na área
  correta.
- Constituição, especificações e histórico de features permanecem na raiz
  como processo do time.
- Endereços de desenvolvimento da interface e do serviço, e o vocabulário
  público já usado pelos clientes, não mudam.
- Confirmado: o README da raiz, a nova área de documentação e os guias
  de processo da próxima feature passam a descrever o layout novo. Specs
  históricas de locação não são reescritas.
- Nomes de área descrevem a responsabilidade (aplicações, interface,
  serviço, ambiente, documentação), nunca a tecnologia usada.
- Confirmado: cada aplicação guarda só a receita de construção e o
  exemplo de configurações que são dela. Execução compartilhada e dados
  ficam na área de ambiente. A orquestração local permanece em um ponto
  único na raiz.
