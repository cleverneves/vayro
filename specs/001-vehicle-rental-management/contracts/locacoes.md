# Contrato: Locações

Recurso `/api/v2/locacoes`. A data da solicitação é gravada pelo servidor. O período exibido usa datas de calendário: `data_inicio` e `data_fim` (último dia reservado, inclusivo).

## Objeto

```json
{
  "id": 7,
  "locatario": {
    "id": 10,
    "nome": "Ana Lima",
    "email": "ana@example.com",
    "telefone": "11999990000"
  },
  "automovel": {
    "id": 4,
    "placa": "ABC1D23",
    "modelo": {
      "id": 2,
      "nome": "Civic",
      "marca": { "id": 1, "nome": "Honda" }
    }
  },
  "data_inicio": "2026-10-03",
  "quantidade_dias": 2,
  "periodo": {
    "data_inicio": "2026-10-03",
    "data_fim": "2026-10-04"
  },
  "motivo": "viagem",
  "comentario": "Viagem a trabalho",
  "status": "solicitada",
  "observacao": null,
  "data_solicitacao": "2026-10-03"
}
```

`motivo`: `viagem`, `passeio` ou `dia-a-dia`.

`status`: `solicitada`, `confirmada`, `em_andamento`, `concluida`, `cancelada`.

`comentario` e `observacao` são string ou `null`. Comentário vazio na criação volta `null`.

O locatário dono recebe esse objeto na própria locação, incluindo `observacao`. Outro locatário não recebe esse objeto.

## POST /api/v2/locacoes

Locatário autenticado.

```json
{
  "automovel_id": 4,
  "data_inicio": "2026-10-03",
  "quantidade_dias": 2,
  "motivo": "viagem",
  "comentario": "Viagem a trabalho"
}
```

| Campo | Regra |
|-------|--------|
| automovel_id | obrigatório, automóvel existente e livre no período |
| data_inicio | obrigatória, hoje ou futuro |
| quantidade_dias | obrigatória, inteiro de 1 a 90 |
| motivo | obrigatório, um dos três valores |
| comentario | opcional, até 500 caracteres |

`201` com a locação em `status: solicitada` e `data_solicitacao` do dia corrente da operação. `comentario` omitido grava `null`.

Omissão de motivo, data ou automóvel; motivo fora da lista; data passada; quantidade menor que 1 ou maior que 90; comentário acima de 500: `422` e nenhuma linha nova.

Automóvel inexistente ou já reservado por locação `solicitada`, `confirmada` ou `em_andamento` com período sobreposto, inclusive em pedidos simultâneos: `409` `O automóvel não está disponível nesse período.`

O locatário não envia status, observação, data da solicitação nem identificador de outro cliente.

## GET /api/v2/locacoes

Locatário: somente as locações dele. Nenhuma de outro. Sem locações: `{ "data": [] }`.

Administrativo: todas, de qualquer status e qualquer `data_solicitacao`. Sem locações: `{ "data": [] }`.

Ordem: data da solicitação decrescente, id decrescente. Sem filtro de busca nesta versão.

## GET /api/v2/locacoes/quantidade-do-dia

Administrativo. Registrada antes de `/{locacao}`.

`200`:

```json
{
  "data": {
    "data": "2026-10-03",
    "quantidade": 3
  }
}
```

`data` é o dia corrente em `America/Sao_Paulo`. `quantidade` conta linhas com essa `data_solicitacao`, de qualquer status (inclusive `cancelada`) e com qualquer `data_inicio` (inclusive futura). Locação pedida em outro dia não entra, mesmo que o período inclua hoje. Sem linhas no dia: `quantidade` é `0`.

Locatário: `403` e o corpo não traz `quantidade`.

## GET /api/v2/locacoes/{locacao}

Dono ou administrativo: objeto completo.

Outro locatário, ou id inexistente: `404` `Locação não encontrada.` sem automóvel, motivo, dias, comentário, status ou observação.

## PATCH /api/v2/locacoes/{locacao}

Administrativo. Não existe atualização pelo locatário: motivo, automóvel, datas, quantidade, comentário, status e observação ficam fora do alcance dele.

```json
{
  "status": "confirmada",
  "observacao": "Documentos conferidos"
}
```

| Campo | Regra |
|-------|--------|
| status | opcional; se presente, só a transição permitida |
| observacao | opcional; até 500; string vazia ou `null` apaga; chave ausente preserva |

Pelo menos um dos dois campos presente. Os dois ausentes: `422`.

`200` com o objeto atualizado.

Casos:

- `solicitada` → `confirmada` com observação: ambos gravados.
- `confirmada` → `em_andamento` sem observação: status novo, observação anterior preservada.
- `em_andamento` → `concluida`: status final.
- `solicitada` ou `confirmada` → `cancelada`: deixa de reservar o automóvel e não aceita mudança posterior.
- Transição fora da sequência, inclusive a partir de `em_andamento`, `concluida` ou `cancelada`: `409`, status e observação anteriores preservados.
- Só observação, status omitido, locação ainda aberta: observação muda, status permanece.
- Locação `concluida` ou `cancelada`: `409` para status e para observação.
- Texto acima de 500: `422` e nada é salvo.
- Id inexistente: `404`.

Locatário neste caminho: `403`.

Transições aceitas, em vocabulário público:

| De | Para |
|----|------|
| solicitada | confirmada, cancelada |
| confirmada | em_andamento, cancelada |
| em_andamento | concluida |

`concluida` e `cancelada` não têm destino.
