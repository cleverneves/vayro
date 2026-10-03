# Contrato: Automóveis disponíveis

## GET /api/v2/carros/disponiveis

Locatário autenticado. Informa o período e recebe só os automóveis sem reserva sobreposta em `rentals`.

```http
GET /api/v2/carros/disponiveis?data_inicio=2026-10-03&quantidade_dias=2
Authorization: Bearer {token}
```

| Query | Regra |
|-------|--------|
| data_inicio | obrigatória, data, dia corrente ou futuro no fuso `America/Sao_Paulo` |
| quantidade_dias | obrigatória, inteiro de 1 a 90 |

`200`:

```json
{
  "data": [
    {
      "id": 4,
      "placa": "ABC1D23",
      "modelo": {
        "id": 2,
        "nome": "Civic",
        "marca": {
          "id": 1,
          "nome": "Honda"
        }
      }
    }
  ]
}
```

A resposta não inclui `disponivel`, `km` nem preço. A flag `carros.disponivel` do v1 não filtra esta lista.

Nenhum automóvel livre: `{ "data": [] }`. Esse resultado não cria locação.

Data ausente, data passada ou quantidade fora de 1..90: `422` com o campo correspondente, sem lista de solicitação válida.

Administrativo: `403`.

Catálogo (criar, editar, remover marca, modelo ou automóvel) permanece somente em `/api/v1`.
