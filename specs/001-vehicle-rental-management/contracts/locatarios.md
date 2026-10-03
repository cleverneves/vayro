# Contrato: Locatários

Recurso `/api/v2/locatarios`. Campos de perfil: `nome`, `email`, `telefone`. A senha só entra no cadastro e nunca volta na resposta.

## POST /api/v2/locatarios

Público. Cria Cliente Locatário.

```json
{
  "nome": "Ana Lima",
  "email": "ana@example.com",
  "telefone": "11999990000",
  "senha": "segredo12"
}
```

| Campo | Regra |
|-------|--------|
| nome | obrigatório, string, até 120 |
| email | obrigatório, formato de e-mail, único em `users` e `clientes` |
| telefone | obrigatório, string, até 20 |
| senha | obrigatória, mínimo 8 caracteres |

`201`:

```json
{
  "data": {
    "id": 10,
    "nome": "Ana Lima",
    "email": "ana@example.com",
    "telefone": "11999990000"
  }
}
```

E-mail repetido, inclusive em cadastros simultâneos: `422` e nenhuma segunda conta. Campos ausentes ou e-mail inválido: `422` apontando o campo. A conta criada entra com `POST /api/v1/login`.

## GET /api/v2/locatarios/me

Locatário autenticado. Devolve o mesmo objeto `data` do cadastro, com os valores atuais.

## PATCH /api/v2/locatarios/me

Locatário autenticado. Altera um ou mais entre `nome`, `email` e `telefone`. Não aceita `senha` nem `papel`.

`200` com o perfil novo. Locações desse `id` continuam vinculadas a ele.

E-mail de outro locatário: `422`. Corpo inválido: `422` e o perfil anterior permanece.

Não existe `PATCH /api/v2/locatarios/{id}`. Um pedido a esse caminho não altera cadastro algum. No service, atualizar uma linha cujo `user_id` não é o ator é recusado e a linha permanece.

## GET /api/v2/locatarios

Administrativo. Lista todos os clientes, com ou sem locação, inclusive legados sem e-mail.

`200`:

```json
{
  "data": [
    {
      "id": 10,
      "nome": "Ana Lima",
      "email": "ana@example.com",
      "telefone": "11999990000"
    }
  ]
}
```

Sem clientes: `{ "data": [] }`. Locatário autenticado: `403` com `Acesso recusado.`

## GET /api/v2/locatarios/{locatario}

Administrativo. O mesmo objeto de perfil. Id inexistente: `404` `Locatário não encontrado.` Locatário autenticado: `403`.

A rota `me` é registrada antes de `{locatario}`.
