# Contrato comum `/api/v2`

Base: `http://localhost:8989`. Rotas novas sob `/api/v2`. Autenticação continua em `/api/v1`.

## Autenticação

Rotas autenticadas exigem:

```http
Authorization: Bearer {token}
```

O token sai de `POST /api/v1/login` com e-mail e senha. `GET /api/v1/me` e a resposta de login incluem o campo aditivo `papel`: `locatario` ou `administrativo`. Os demais campos já publicados desses endpoints permanecem.

Cadastro de locatário (`POST /api/v2/locatarios`) é público e não devolve token. O acesso seguinte usa o login já existente.

Ausência ou token inválido: `401` com `{ "message": "Unauthenticated." }` no formato já usado pelo guard JWT.

## Envelope

Recurso e coleção usam `data`, no mesmo espírito dos API Resources atuais.

Coleções de `/api/v2` não paginam: não há `links` nem `meta`.

## Erros

Validação de formato (`422`), no formato já publicado:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["O e-mail já está em uso."]
  }
}
```

Conflito de domínio (`409`):

```json
{
  "message": "O automóvel não está disponível nesse período."
}
```

Mensagens de domínio usadas por este contrato:

| Situação | HTTP | message |
|----------|------|---------|
| E-mail já usado | 422 | O e-mail já está em uso. |
| Automóvel inexistente ou reservado no período | 409 | O automóvel não está disponível nesse período. |
| Transição de status ilegal | 409 | A mudança de status não é permitida. |
| Locação concluída ou cancelada | 409 | A locação encerrada não pode ser alterada. |
| Locação inexistente, ou de outro locatário | 404 | Locação não encontrada. |
| Locatário inexistente na consulta administrativa | 404 | Locatário não encontrado. |
| Papel sem permissão para a operação | 403 | Acesso recusado. |

`404` de locação alheia e `403` da quantidade do dia não incluem automóvel, motivo, dias, comentário, status, observação nem o número do dia.

## Papel

| papel | Pode |
|-------|------|
| `locatario` | O próprio perfil, automóveis livres no período, criar locação, listar e abrir só as próprias. |
| `administrativo` | Listar e abrir locatários, listar e abrir todas as locações, atualizar status e observação, ver a quantidade do dia. |

O middleware recusa o papel errado com `403`. O service repete a regra de titularidade e de papel, porque o teste de unidade chama o service sem HTTP.
